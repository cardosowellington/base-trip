#!/bin/bash
set -e

WP_PATH=/var/www/html
SITE_URL="http://localhost:8080"
SITE_TITLE="Meu Site WordPress"
ADMIN_USER="admin"
ADMIN_PASS="admin123"
ADMIN_EMAIL="admin@example.com"
PARENT_THEME="twentytwentyfour"
CHILD_THEME="child-theme"

echo "Subindo containers..."
docker-compose up -d

echo "Aguardando banco de dados..."
sleep 15

WP="docker-compose run --rm -e PHP_MEMORY_LIMIT=512M wpcli wp --path=$WP_PATH --allow-root"

echo "Baixando WordPress pt-BR..."
$WP core download --locale=pt_BR --force

echo "Instalando WordPress..."
$WP config create \
  --dbname=wordpress \
  --dbuser=wordpress \
  --dbpass=wordpress \
  --dbhost=db:3306 \
  --skip-check \
  --force

$WP core install \
  --url="$SITE_URL" \
  --title="$SITE_TITLE" \
  --admin_user="$ADMIN_USER" \
  --admin_password="$ADMIN_PASS" \
  --admin_email="$ADMIN_EMAIL" \
  --skip-email


echo "Instalando plugins básicos..."
$WP plugin install contact-form-7 --force --activate

# echo "Criando tema filho..."
# CHILD_PATH="$WP_PATH/wp-content/themes/$CHILD_THEME"
# mkdir -p $CHILD_PATH

# cat > $CHILD_PATH/style.css <<EOL
# /*
# Theme Name: Child Theme
# Author: Cardoso Wellington
# Author URI: https://github.com/cardosowellington
# Description: Wordpress child theme for functional learning.
# Template: $PARENT_THEME
# Version: 1.0
# */
# @import url("../$PARENT_THEME/style.css");
# EOL

# cat > $CHILD_PATH/functions.php <<EOL
# <?php
# add_action('wp_enqueue_scripts', function() {
#     wp_enqueue_style('parent-style', get_template_directory_uri() . '/$PARENT_THEME/style.css');
# });
# EOL

# $WP theme activate $CHILD_THEME

echo "Criando menu principal..."
$WP menu create "Menu Principal" || true
$WP menu item add-custom "Menu Principal" Home $SITE_URL || true
$WP menu item add-custom "Menu Principal" Sobre "$SITE_URL/sobre" || true
$WP menu item add-custom "Menu Principal" Contato "$SITE_URL/contato" || true
$WP menu location assign "Menu Principal" primary || true

echo "Setup concluído!"
echo "   Acesse: $SITE_URL"
echo "   Usuário: $ADMIN_USER"
echo "   Senha:   $ADMIN_PASS"
