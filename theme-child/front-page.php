<?php get_header(); ?>
<div class="container my-5">
  <div class="row">
    <?php for($i=1;$i<=3;$i++): ?>
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title">Card <?php echo $i; ?></h5>
            <p class="card-text">Exemplo de conteúdo do card <?php echo $i; ?>.</p>
          </div>
        </div>
      </div>
    <?php endfor; ?>
  </div>
</div>
<?php get_footer(); ?>
