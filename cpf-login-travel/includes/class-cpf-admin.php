<?php

if( ! defined( 'ABSPATH' ) ) exit;

class CPF_Admin{
  public function __construct(){
    add_action( 'admin_menu', [ $this , 'add_admin_page'] );
    add_action( 'admin_post_cpf_add_manual', [ $this, 'handle_manual_add' ] );
    add_action( 'admin_post_cpf_import_csv', [ $this, 'handle_csv_import' ] );
  }

  public function add_admin_page(){
    add_menu_page(
      'Gerenciar CPFs',
      'Gerenciar CPFs',
      'manage_options',
      'cpf-login-admin',
      [ $this, 'render_admin_page' ],
      'dashicons-id',
      25
    );
  }

  public function render_admin_page(){

    if( ! current_user_can( 'manage_options' ) ){
      wp_die( 'Acesso negado.' );
    }

    global $wpdb;
    $table = $wpdb->prefix . "cpf_users";
    $items = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC LIMIT 10" );

  ?>

    <div class="wrap">
      <h1>Gerenciar CPFs</h1>
      <h2>Cadastro CPF manualmente</h2>
      <form method="post" action="<?php echo esc_url(admin_url( 'admin-post.php' ));?>">
        <?php wp_nonce_field( 'cpf_add_manual_nonce' ); ?>
        <input type="hidden" name="action" value="cpf_add_manual" >
        <input type="text" name="cpf" placeholder="000.000.000-00" required >
        <button class="button button-primary" type="submit">Salvar CPF</button>
      </form>

      <hr>

      <h2>Upload de CSV</h2>
      <p>Arquivo CSV com 1 CPF por linha (apenas números ou formatado):</p>
      <form method="post" action="<?php echo esc_url(admin_url( 'admin-post.php' )); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field( 'cpf_import_csv_nonce' ); ?>
        <input type="hidden" name="action" value="cpf_import_csv">
        <input type="file" name="cpf_file" accept=".csv" required >
        <button class="button button-primary" type="submit">Enviar Arquivo</button>
      </form>

      <hr>

      <h2>CPFs cadastrados (ultimos 10)</h2>
      <table class="widefat fixed striped">
        <thead><tr><th>ID</th><th>CPF</th><th>Cadastrado</th></tr></thead>
        <tbody>
          <?php foreach( $items as $it ): ?>
            <tr>
              <td><?php echo esc_html( $it->id );?></td>
              <td><?php echo esc_html( substr( $it->cpf, 0, 3 ) . '.***.***-' . substr( $it->cpf, 9 )); ?></td>
              <td><?php echo esc_html( $it->created_at ); ?></td>
            </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
  <?php 
  }

  public function handle_manual_add(){
    if( ! current_user_can( 'manage_options' ) ) wp_die( 'Acesso negado.' );
    check_admin_referer( 'cpf_add_manual_nonce' );
    if( empty( $_POST['cpf'] ) ) wp_redirect( admin_url( 'admin.php?page=cpf-login-admin' ) );

    require_once CPF_LOGIN_PATH . 'includes/cpf-functions.php';
    $cpf = cpf_normalize( $_POST[ 'cpf' ] );

    if( ! cpf_is_valid($cpf ) ){
      wp_redirect( add_query_arg( 'cpf_add', 'invalid', admin_url( 'admin.php?page=cpf-login-admin' ) ) );
      exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . "cpf_users";
    $wpdb->insert( $table, [ 'cpf' => $cpf ] );

    wp_redirect( add_query_arg( 'cpf_add', 'ok', admin_url( 'admin.php?page=cpf-login-admin' ) ) );
    exit;

  }

  public function handle_csv_import(){
    if( ! current_user_can( 'manage_options' ) ) wp_die( 'Acesso negado.' );
    check_admin_redirect( 'cpf_import_csv_nonce' );
    if( empty($_FILES[ 'cpf_file' ][ 'tmp_name' ]) ) wp_redirect(admin_url( 'admin-php?page=cpf-login-admin' ));

    $file = fopen( $_FILES[ 'cpf_file' ][ 'tmp_name' ], 'r' );
    require_once CPF_LOGIN_PATH . 'includes/cpf-functions.php';
    $count = 0; $invalid = 0; $duplicates = 0;
  
    global $wpdb;
    $table = $wpdb->prefix . "cpf_users";

    while( ($line = fgetcsv($file)) !== FALSE ){
      $cpf = cpf_normalize( $line[0] );
      if( cpf_is_valid( $cpf ) ){
        $inserted = $wpdb->insert( $table, [ 'cpf' => $cpf ] );
        if( $inserted ) $count++; else $duplicates;
      }else{
        $invalid++;
      }
    }
    fclose( $file );

    wp_redirect(add_query_arg( [ 'import_done' => '1', 'added' => $count, 'invalid'=>$invalid, 'dups'=>$duplicates ], admin_url('admin.php?page=cpf-login-admin') ));
    exit;
  }

}