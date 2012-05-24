<?php
/* 
Plugin Name: JDesrosiers Multilingual
Plugin URI: 
Description: A plugin that adds simple features to make your WordPress site multilingual
Author: Julien Desrosiers
Version: 1.0 
Author URI: http://www.juliendesrosiers.com
*/

define('JDML_TAX_NAME', 'Languages');
define('JDML_TAX_SINGLE', 'Language');
define('JDML_TAX_SLUG', 'language');
define('JDML_TAX_SLUG_PLURAL', 'languages');

function jdml_create_language_taxonomy() {
  register_taxonomy(JDML_TAX_SLUG, array('page', 'post'), array(
    'hierarchical' => false,
    'labels' => array(
      'name' => _x( JDML_TAX_NAME, 'taxonomy general name'),
      'singular_name' => _x( JDML_TAX_SINGLE, 'taxonomy singular name' ),
      'search_items' =>  __( 'Search ' . JDML_TAX_NAME ),
      'all_items' => __( 'All ' . JDML_TAX_NAME ),
      'parent_item' => __( 'Parent ' . JDML_TAX_SINGLE ),
      'parent_item_colon' => __( 'Parent ' . JDML_TAX_SINGLE . ':' ),
      'edit_item' => __( 'Edit ' . JDML_TAX_SINGLE ), 
      'update_item' => __( 'Update ' . JDML_TAX_SINGLE ),
      'add_new_item' => __( 'Add New ' . JDML_TAX_SINGLE ),
      'new_item_name' => __( 'New ' . JDML_TAX_SINGLE . ' Name' ),
      'menu_name' => __( JDML_TAX_SINGLE ),
    ),
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => JDML_TAX_SLUG )
  ));
}

function jdml_add_new_column($defaults) {
  $defaults[JDML_TAX_SLUG_PLURAL] = __(JDML_TAX_SLUG);
  return $defaults;
}

function jdml_add_column_data($column_name, $post_id) {
  if ($column_name == JDML_TAX_SLUG_PLURAL) {
    $_taxonomy = JDML_TAX_SLUG;
    $terms = get_the_terms($post_id, $_taxonomy);
    if (!empty($terms)) {
      $out = array();
      foreach ($terms as $t) {
        $out[] = $t->name;
      }
      echo join(', ', $out);
    } else {
      _e('Language not set');
    }
  } 
}

add_filter('manage_post_posts_columns', 'jdml_add_new_column'); // post index column title
add_filter('manage_page_posts_columns', 'jdml_add_new_column'); // page index column title
add_action('manage_posts_custom_column', 'jdml_add_column_data', 10, 2); // post index column data
add_action('manage_pages_custom_column', 'jdml_add_column_data', 10, 2); // page index column data
add_action('init', 'jdml_create_language_taxonomy', 0 );

