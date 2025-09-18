jQuery( document ).ready( function( $ ){
  $( '#cpf-login-form' ).on( 'submit', function( e ){
    e.preventDefault()
    var cpf = $('#cpf').val()

    $.post( cpfLoginAjax.url, {
      action: 'cpf_login',
      nonce: cpfLoginAjax.nonce,
      cpf: cpf
    },function( response ){
      if( response.success ){
        $( '#cpf-login-msg' ).html( '<span style="color:green">'+response.data.msg+'</span>' );
        window.location.href = response.data.redirect
      }else{
        $( '#cpf-login-msg' ).html( '<span style="color:red">'+response.data.msg+'</span>' );
      }
    } )
  } )
} )