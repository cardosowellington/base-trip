<?php
class WP_Bootstrap_Navwalker extends Walker_Nav_Menu {
  function start_lvl(&$output, $depth = 0, $args = null) {
    $indent = str_repeat("\t", $depth);
    $output .= "\n$indent<ul class=\"dropdown-menu\">\n";
  }

  function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
    $classes = empty($item->classes) ? array() : (array) $item->classes;
    $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
    $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

    $output .= '<li' . $class_names . '>';

    $atts = array();
    $atts['title']  = ! empty($item->attr_title) ? $item->attr_title : '';
    $atts['target'] = ! empty($item->target)     ? $item->target     : '';
    $atts['rel']    = ! empty($item->xfn)        ? $item->xfn        : '';
    $atts['href']   = ! empty($item->url)        ? $item->url        : '';

    $atts = apply_filters('nav_menu_link_attributes', $atts, $item);

    $attributes = '';
    foreach ($atts as $attr => $value) {
      if (! empty($value)) {
        $value = 'href' === $attr ? esc_url($value) : esc_attr($value);
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }
    }

    $title = apply_filters('the_title', $item->title, $item->ID);

    $item_output  = $args->before;
    $item_output .= '<a class="nav-link"'. $attributes .'>';
    $item_output .= $args->link_before . $title . $args->link_after;
    $item_output .= '</a>';
    $item_output .= $args->after;

    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }
}
