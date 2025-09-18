<?php

class CPF_Login{
  public function __construct(){
    add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    add_shortcode( 'cpf_login_form', [ $this, 'render_form' ] );
    require_once CPF_LOGIN_PATH . 'includes/cpf-endpoints.php';
  }

  public function enqueue_assets(){
    wp_enqueue_style( 'cpf-login-css', CPF_LOGIN_URL . 'assets/css/cpf-login.css' );
    wp_enqueue_script( 'cpf-login-js', CPF_LOGIN_URL . 'assets/js/cpf-login.js', [ 'jquery' ], null, true );
    wp_localize_script( 'cpf-login-js', 'cpfLoginAjax', [
      'url' => admin_url( 'admin-ajax.php' ),
      'nonce' => wp_create_nonce( 'cpf_login_nonce' )
    ] );
  }

  public function render_form(){
    ob_start();?>
    <form id="cpf-login-form">
      <label for="cpf">Digite seu CPF</label>
      <input type="text" id="cpf" name="cpf" required maxlength="14" placeholder="000.000.000-00">
      <button type="submit">Entrar</button>
      <div id="cpf-login-msg"></div>
    </form>
  <?php return ob_get_clean();
  }
}