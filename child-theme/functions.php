<?php
function child_theme_scripts() {
  wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
  wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'child_theme_scripts');


function child_theme_register_menus() {
  register_nav_menus([
    'primary' => __('Menu Principal', 'child-theme'),
  ]);
}
add_action('after_setup_theme', 'child_theme_register_menus');
