<?php

if( ! defined( 'ABSPATH' ) ) exit;

function cpf_normalize($cpf){
  return preg_replace( '/[^0-9]/', '', $cpf );
}

function cpf_is_valid($cpf){
  if( strlen( $cpf ) != 11 ) return false;
  if( preg_match( '/(\d)\1{10}/', $cpf ) ) return false;

  for( $i = 9; $i < 11; $i++ ){
    for( $j = 0, $x = 0; $x < $i; $x++ ){
      $j += $cpf[$x] * ( ( $i + 1 ) - $x );
    }
    $j = ( ( 10 * $j ) % 11 ) % 10;
    if( $cpf[$i]  != $j ) return false;
  }
  return true;
}