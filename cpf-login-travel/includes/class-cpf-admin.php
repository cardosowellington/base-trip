<?php

if( ! defined( 'ABSPATH' ) ) exit;

class CPF_Admin{
  public function __construct(){
    add_action( 'admin_menu', [ $this , 'add_admin_page'] );
    add_action( 'admin_post_cpf_add_manual', [ $this, 'handle_manual_add' ] );
    add_action( 'admin_post_cpf_import_csv', [ $this, 'handle_csv_import' ] );
    add_action( 'admin_post_cpf_delete', [ $this, 'handle_delete' ] );
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

    if( ! current_user_can( 'manage_options' ) ) wp_die( 'Acesso negado.' );

    if( isset($_GET['cpf_error']) ){
      echo '<div class="notice notice-error"><p>'.esc_html( urldecode( $_GET['cpf_error'] ) ).'</p></div';
    }

    if( isset($_GET['cpf_success']) ){
      echo '<div class="notice notice-success"><p>'.esc_html( urldecode($_GET['cpf_success'])).'</p></div';
    }

    if( isset($_GET['import_done']) ){
      $added = isset($_GET['added']) ? intval($_GET['added']) : 0;
      $invalid = isset($_GET['invalid']) ? intval($_GET['invalid']) : 0;
      $dups = isset($_GET['dups']) ? intval($_GETP['dups']) : 0;
      echo '<div class="notice notice-success"><p>'.esc_html("Importado: $added | Inválido: $invalid | Duplicados: $dups").'</p></div>';
    }

    if( isset($_GET['cpf_deleted']) ){
      echo '<div class="notice notice-success"><p>CPF excluído com sucesso!</p></div>';
    }

    global $wpdb;
    $table = $wpdb->prefix . "cpf_users";
    $items = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC LIMIT 10" );
    ?>
    <div class="wrap">
      <h1>Gerenciar CPFs</h1>
      <h2>Usuário</h2>
      <form method="post" action="<?php echo esc_url(admin_url( 'admin-post.php' ));?>">
        <?php wp_nonce_field( 'cpf_add_manual_nonce' ); ?>
        <input type="hidden" name="action" value="cpf_add_manual" >
        <label>CPF:</label>
        <input type="text" name="cpf" placeholder="000.000.000-00" required >
        <label>Nome completp: </label>
        <input type="text" name="nome" placeholder="Nome completo" required >
        <label>E-mail: </label>
        <input type="email" name="email" placeholder="email@exemplo.com" required >
        <button class="button button-primary" type="submit">Salvar</button>
      </form>

      <hr>

      <h2>Importar CSV</h2>
      <p>Formato: <code>cpf, nome, email</code></p>
      <form method="post" action="<?php echo esc_url(admin_url( 'admin-post.php' )); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field( 'cpf_import_csv_nonce' ); ?>
        <input type="hidden" name="action" value="cpf_import_csv">
        <input type="file" name="cpf_file" accept=".csv" required >
        <button class="button button-primary" type="submit">Enviar Arquivo</button>
      </form>

      <hr>

      <h2>CPFs cadastrados (ultimos 10)</h2>
      <table class="widefat fixed striped">
        <thead><tr><th>ID</th><th>CPF</th><th>Nome</th><th>E-amil</th></tr></thead>
        <tbody>
          <?php foreach( $items as $it ): ?>
            <tr>
              <td><?php echo esc_html( $it->id );?></td>
              <td><?php echo esc_html( substr( $it->cpf, 0, 3 ) . '.***.***-' . substr( $it->cpf, 9 )); ?></td>
              <td><?php echo esc_html( $it->nome ); ?></td>
              <td><?php echo esc_html( $it->email ); ?></td>
              <td><?php echo esc_html( $it->created_at ); ?></td>
              <td>
                <form action="<?php echo esc_url(admin_url('admin-post.php'));?>" onsubmit="return confirm('tem certeza que deseja excluir este CPF?');">
                  <?php wp_nonce_field( 'cpf_delete_nonce' ); ?>
                  <input type="hidden" name="action" value="cpf_delete">
                  <input type="hidden" name="id" value="<?php echo esc_attr($it->id); ?>">
                  <button class="button button-secondary" type="submit">Excluir</button>
                </form>
              </td>
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
    
    if( empty( $_POST['cpf'] ) || empty( $_POST['nome'] ) || empty( $_POST['email'] ) ){
      wp_redirect( add_query_arg( 'cpf_error', urldecode( 'Todos os campons são obrigatórios.' ), admin_url( 'admin.php?page=cpf-login-admin' ) ) );
      exit;
    }     

    require_once LOGIN_PATH . 'includes/cpf-functions.php';
    $cpf = cpf_normalize( $_POST[ 'cpf' ] );
    $nome = sanitize_text_field( $_POST[ 'nome' ] );
    $email = sanitize_email( $_POST[ 'email' ] );

    if( ! cpf_is_valid($cpf ) ){
      wp_redirect( add_query_arg( 'cpf_error', urldecode('CPF inválido.'), admin_url( 'admin.php?page=cpf-login-admin' ) ) );
      exit;
    }

    if( empty($nome ) ){
      wp_redirect( add_query_arg( 'cpf_error', urldecode('Nome inválido.'), admin_url( 'admin.php?page=cpf-login-admin' ) ) );
      exit;
    }

    if( ! is_email($email ) ){
      wp_redirect( add_query_arg( 'cpf_error', urldecode('E-mail inválido.'), admin_url( 'admin.php?page=cpf-login-admin' ) ) );
      exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . "cpf_users";
    $inserted = $wpdb->insert( $table, [ 'cpf' => $cpf, 'nome' => $nome, 'email' => $email ] );

    if( $inserted === false ){
      $err = $wpdb->last_error ?: 'Erro desconhecido';
      wp_redirect(add_query_arg( 'cpf_error', urldecode( "Falha ao inserir: $err" ), admin_url('admin.php?page=cpf-login-admin') ));
      exit;
    }else{
      wp_redirect( add_query_arg( 'cpf_success', urldecode( 'CPF cadastrado com sucesso' ), admin_url( 'admin.php?page=cpf-login-admin' ) ) );
      exit;
    }
  }

  public function handle_csv_import(){
    if( ! current_user_can( 'manage_options' ) ) wp_die( 'Acesso negado.' );
    check_admin_redirect( 'cpf_import_csv_nonce' );
    if( empty($_FILES[ 'cpf_file' ][ 'tmp_name' ]) ) wp_redirect(admin_url( 'admin-php?page=cpf-login-admin' ));

    $file = fopen( $_FILES[ 'cpf_file' ][ 'tmp_name' ], 'r' );
    require_once LOGIN_PATH . 'includes/cpf-functions.php';
    $count = 0; $invalid = 0; $duplicates = 0;

    global $wpdb;
    $table = $wpdb->prefix . "cpf_users";

    while( ($line = fgetcsv($file)) !== FALSE ){
      if( count($line) < 3 ){ $invalid++; continue; }

      $cpf = cpf_normalize( $line[0] );
      $nome = sanitize_text_field($line[1]);
      $email = sanitize_email($line[2]);

      if( cpf_is_valid( $cpf ) && !empty( $nome ) && is_email( $email ) ){
        $inserted = $wpdb->insert( $table, [ 'cpf' => $cpf, 'nome' => $nome, 'email' => $email ] );
        if( $inserted ) $count++; else $duplicates;
      }else{
        $invalid++;
      }
    }
    fclose( $file );

    wp_redirect(add_query_arg( [ 'import_done' => '1', 'added' => $count, 'invalid'=>$invalid, 'dups'=>$duplicates ], admin_url('admin.php?page=cpf-login-admin') ));
    exit;
  }

  public function handle_delete(){

    if( ! current_user_can( 'manage_options' ) ) wp_die( 'Acesso negado.' );
    check_admin_referer('cpf_delete_nonce');

    if( empty( $_POST['id'] ) ){
      wp_redirect(admin_url( 'admin.php?page=cpf-login-admin' ));
    }

    global $wpdb;
    $table = $wpdb->prefix . "cpf_users";
    $deleted = $wpdb->delete($table, [ 'id' => intval($_POST['id'] )]);

    if( $deleted === false ){
      wp_redirect( add_query_arg( 'cpf_error', urldecode( 'Falha ao excluir.' ), admin_url( 'admin.php?page=cpf-login-admin' ) ) );
    }else{
      wp_redirect( add_query_arg( 'cpf_deleted', '1', admin_url( 'admin.php?page=cpf-login-admin' ) ) );
    }
    exit;
  }
} 