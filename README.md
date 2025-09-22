# Projeto Base Trip (Wordpress) com Docker

Este projeto cria um ambiente WordPress completo usando **Docker**, com:

- WordPress 6.x em **pt-BR**  
- Tema filho customizado com **Bootstrap 5**  
- Plugins básicos instalados e ativos (Yoast SEO, Contact Form 7, WP Super Cache)  
- Página inicial (`front-page.php`) com grid responsivo  
- Navbar com menu e submenus totalmente funcional  
- Criação automática de usuário admin e menu principal  

## Tecnologias
- PHP 8.2 + Apache
- MySQL 8.0
- WordPress 6.5
- Bootstrap 5 (pré-instalado em tema filho)

## Como rodar o projeto

1. Clone este repositório:
   ```bash
   git clone https://github.com/cardosowellington/base-trip.git
   cd projeto-base-trip

2. Dê permissão de execução ao script:
   ```bash
   chmod +x init.sh

3. Suba os containers:
   ```bash
   docker-compose up -d --build

4. Execute o script de instalação completo:
   ```bash
   ./init.sh

5. Acesse no navegador:
   http://localhost:8080/wp-admin
   Login admin:
      user: admin
      pass: admin123


### Comandos úteis do Docker 

1. Subir containers:
   ```bash
   docker-compose up -d

2. Parar containers:
   ```bash
   docker-compose down
   
3. Ver logs:
   ```bash
   docker-compose logs -f
   
4. Acessar o container WordPress:
   ```bash
   docker exec -it wordpress bash

4. Acessar MySQL:
   ```bash
   docker exec -it wp_db mysql -u wp_user -pwp_pass wordpress

## OBS:
- O init.sh pode ser rodado quantas vezes for necessário; ele ignora duplicatas de menu e tema.