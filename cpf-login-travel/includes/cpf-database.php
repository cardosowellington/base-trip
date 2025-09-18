<?php

if( ! defined( 'ABSPATH' ) ) exit;

function cpf_create_table(){
  global $wpdb;
  $table = $wpdb->prefix . "cpf_users";
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table(
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    cpf varchar(14) NOT NULL,
    nome varchar(100) NOT NULL,
    email varchar(150) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
}