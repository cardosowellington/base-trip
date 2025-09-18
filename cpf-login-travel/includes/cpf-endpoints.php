<?php

add_action( 'wp_ajax_nopriv_cpf_login', 'cpf_login_handler' );
add_action( 'wp_ajax_cpf_login', 'cpf_login_handler' );

function cpf_login_handler(){
  check_ajax_referer( 'cpf_login_nonce', 'nonce' );

  $cpf = isset( $_POST['cpf'] ) ? sanitize_text_field( $_POST['cpf'] ) : '';

  require_once  CPF_LOGIN_PATH . 'includes/cpf-functions.php';
  $cpf = cpf_normalize($cpf);

  if( ! cpf_is_valid($cpf) ){
    wp_send_json_error( [ 'msg' => 'CPF inválido ou não cadastrado em nossa base' ] );
  }

  global $wpdb;
  $table = $wpdb->prefix . "cpf_users";
  $user = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table WHERE cpf = %s", $cpf ) );

  if( $user ){
    $wp_user = get_user_by( 'login', 'cpf_user' );
    if( ! $wp_user ){
      $user_id = wp_create_user( 'cpf_user', wp_generate_password(), 'cpf@meucpf.com.br' );
      $wp_user = get_user_by( 'id', $user_id );
    }

    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID, true );
    wp_send_json_success( [ 'msg' => 'Login realizado com sucesso', 'redirect' => home_url( '/dashboard' ) ] );
  }else{
    wp_send_json_error( [ 'msg' => 'CPF inválido ou não cadastrado' ] );
  }

}