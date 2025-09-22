<?php

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_nopriv_cpf_login', 'cpf_login_handler' );
add_action( 'wp_ajax_cpf_login', 'cpf_login_handler' );

function cpf_login_handler(){
  check_ajax_referer( 'cpf_login_nonce', 'nonce' );

  $cpf_raw = isset( $_POST['cpf'] ) ? sanitize_text_field( $_POST['cpf'] ) : '';
  require_once  LOGIN_PATH . 'includes/cpf-functions.php';
  $cpf = cpf_normalize($cpf_raw);

  if( ! cpf_is_valid($cpf) ){
    wp_send_json_error( [ 'msg' => 'CPF inválido ou não cadastrado em nossa base' ] );
  }

  global $wpdb;
  $table = $wpdb->prefix . "cpf_users";
  $row = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table WHERE cpf = %s", $cpf ) );

  if( $row ){
    $login = 'cpf_user_' . $row->id;
    $user = get_user_by( 'login', $login );
    if( ! $user ){
      $user_id = wp_create_user( $login, wp_generate_password(), $row->email );
      wp_update_user( [ 'ID' => $user_id, 'display_name' => $row->name ] );
      $user = get_user_by( 'id', $user_id );
      if( $user ){
        $user->set_role( 'subscriber' );
      }
    }

    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID, true );
    wp_send_json_success( [ 'msg' => 'Login realizado com sucesso', 'redirect' => home_url( '/dashboard' ) ] );
  }else{
    wp_send_json_error( [ 'msg' => 'CPF inválido ou não cadastrado' ] );
  }
}