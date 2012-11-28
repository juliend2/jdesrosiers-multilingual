<?php

// Taxonomy
// -----------------------------------------------------------------

function jdml_create_language_taxonomy() {
  global $jdml_post_types;
  register_taxonomy(JDML_TAX_SLUG, $jdml_post_types, array(
    'hierarchical' => true,
    'labels' => array(
      'name' => _x( JDML_TAX_NAME, 'jdml'),
      'singular_name' => _x( JDML_TAX_SINGLE, 'taxonomy singular name', 'jdml' ),
      'search_items' =>  __( 'Search ' . JDML_TAX_NAME , 'jdml'),
      'all_items' => __( 'All ' . JDML_TAX_NAME , 'jdml'),
      'parent_item' => __( 'Parent ' . JDML_TAX_SINGLE , 'jdml'),
      'parent_item_colon' => __( 'Parent ' . JDML_TAX_SINGLE . ':' , 'jdml'),
      'edit_item' => __( 'Edit ' . JDML_TAX_SINGLE , 'jdml'), 
      'update_item' => __( 'Update ' . JDML_TAX_SINGLE , 'jdml'),
      'add_new_item' => __( 'Add New ' . JDML_TAX_SINGLE , 'jdml'),
      'new_item_name' => __( 'New ' . JDML_TAX_SINGLE . ' Name' , 'jdml'),
      'menu_name' => __( JDML_TAX_SINGLE , 'jdml'),
    ),
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => JDML_TAX_SLUG )
  ));
}

