#!/bin/bash
set -e

# Variáveis
DB_NAME=wp_db
DB_USER=wp_user
DB_PASS=wp_pass
DB_HOST=db
WP_URL="http://localhost:8080"
WP_TITLE="Site WordPress Docker"
WP_ADMIN_USER=admin
WP_ADMIN_PASS=admin123
WP_ADMIN_EMAIL=admin@example.com
WP_PATH="/var/www/html"
THEME_SLUG="child-theme"

echo "Iniciando setup do WordPress com Docker + WP-CLI..."

# Garante que containers estão rodando
docker-compose up -d --build

echo "Aguardando MySQL iniciar..."
sleep 15

echo "Baixando WordPress pt-BR..."
docker-compose run --rm wpcli core download --locale=pt_BR --path=$WP_PATH

echo "Criando wp-config.php..."
docker-compose run --rm wpcli config create \
  --dbname=$DB_NAME --dbuser=$DB_USER --dbpass=$DB_PASS --dbhost=$DB_HOST --path=$WP_PATH --skip-check

echo "Instalando WordPress..."
docker-compose run --rm wpcli core install \
  --url=$WP_URL --title="$WP_TITLE" \
  --admin_user=$WP_ADMIN_USER --admin_password=$WP_ADMIN_PASS --admin_email=$WP_ADMIN_EMAIL \
  --path=$WP_PATH

echo "Instalando plugins básicos..."
docker-compose run --rm wpcli plugin install yoast-seo contact-form-7 wp-super-cache --activate --path=$WP_PATH

echo "Criando Child Theme com Bootstrap..."
docker exec -it wordpress bash -c "mkdir -p wp-content/themes/$THEME_SLUG"
cat <<EOF | docker exec -i wordpress tee wp-content/themes/$THEME_SLUG/style.css > /dev/null
/*
  Theme Name: Theme Child
  Author: Cardoso Wellington
  URL: https://github.com/cardosowellington
  Description: Child theme for Cardoso Wellington
  Template: twentytwentyfive
  Version: 1.0
  Slug: child-theme
*/
EOF

cat <<'EOF' | docker exec -i wordpress tee wp-content/themes/$THEME_SLUG/functions.php > /dev/null
<?php
function child_theme_scripts() {
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'child_theme_scripts');
?>
EOF

cat <<'EOF' | docker exec -i wordpress tee wp-content/themes/$THEME_SLUG/front-page.php > /dev/null
<?php get_header(); ?>
<div class="container my-5">
  <div class="row">
    <?php for($i=1;$i<=3;$i++): ?>
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title">Card <?php echo $i; ?></h5>
            <p class="card-text">Exemplo de conteúdo do card <?php echo $i; ?>.</p>
          </div>
        </div>
      </div>
    <?php endfor; ?>
  </div>
</div>
<?php get_footer(); ?>
EOF

echo "Child Theme criado!"

echo "Ativando Child Theme..."
docker-compose run --rm wpcli theme activate $THEME_SLUG --path=$WP_PATH

echo "Criando menu principal..."
docker-compose run --rm wpcli wp menu create "Menu Principal" --path=$WP_PATH || true
docker-compose run --rm wpcli wp menu item add-custom "Menu Principal" "Início" "$WP_URL" --path=$WP_PATH
docker-compose run --rm wpcli wp menu item add-custom "Menu Principal" "Sobre" "$WP_URL/sobre" --path=$WP_PATH
docker-compose run --rm wpcli wp menu item add-custom "Menu Principal" "Serviços" "$WP_URL/servicos" --path=$WP_PATH
docker-compose run --rm wpcli wp menu item add-custom "Menu Principal" "Blog" "$WP_URL/blog" --path=$WP_PATH
docker-compose run --rm wpcli wp menu item add-custom "Menu Principal" "Contato" "$WP_URL/contato" --path=$WP_PATH

# Submenus de Serviços
SERVICOS_ID=$(docker-compose run --rm wpcli wp menu item list "Menu Principal" --path=$WP_PATH --fields=ID,title --format=csv | grep "Serviços" | cut -d',' -f1)
docker-compose run --rm wpcli wp menu item add-custom "Menu Principal" "Serviço 1" "$WP_URL/servico-1" --parent-id=$SERVICOS_ID --path=$WP_PATH
docker-compose run --rm wpcli wp menu item add-custom "Menu Principal" "Serviço 2" "$WP_URL/servico-2" --parent-id=$SERVICOS_ID --path=$WP_PATH
docker-compose run --rm wpcli wp menu item add-custom "Menu Principal" "Serviço 3" "$WP_URL/servico-3" --parent-id=$SERVICOS_ID --path=$WP_PATH

# Atribuir à localização menu-principal
docker-compose run --rm wpcli wp menu location assign "Menu Principal" menu-principal --path=$WP_PATH

echo "Setup concluído! Acesse: $WP_URL"
