<?php
/**
 * Plugin Name: CPF Login Travel
 * Description: Allows users to log in using only their CPF, simplifying the authentication process on the travel platform.
 * Version: 1.0
 * Author: Cardoso Wellington
 */

if ( ! defined( 'ABSPATH') ) exit;

define( 'CPF_LOGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CPF_LOGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CPF_LOGIN_PATH . 'includes/class-cpf-login.php';
require_once CPF_LOGIN_PATH . 'includes/class-cpf-admin.php';
require_once CPF_LOGIN_PATH . 'includes/cpf-database.php';
require_once CPF_LOGIN_PATH . 'includes/cpf-endpoints.php';
require_once CPF_LOGIN_PATH . 'includes/cpf-functions.php';

register_activation_hook( __FILE__, 'cpf_create_table' );

function cpf_login_travel_init(){
  if( class_exists( 'CPF_Login' ) ){
    new CPF_Login();
  }
  if( class_exists( 'CPF_Admin' ) ){
    new CPF_Admin();
  }
}
add_action( 'plugins_loaded', 'cpf_login_travel_init' );