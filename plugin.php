<?php
/* 
Plugin Name: JDesrosiers Multilingual
Plugin URI: 
Description: A plugin that adds simple features to help make your WordPress site multilingual
Author: Julien Desrosiers
Version: 1.2.1
Author URI: http://www.juliendesrosiers.com
*/

//
// WARNING: JDesrosiers multilingual supports only two languages, 
// so you can only build bilingual sites with it, for now.
//

// -----------------------------------------------------------------
// DEFINES
// -----------------------------------------------------------------
define('JDML_TAX_NAME', 'Languages');
define('JDML_TAX_SINGLE', 'Language');
define('JDML_TAX_SLUG', 'language');
define('JDML_TAX_SLUG_PLURAL', 'languages');
define('JDML_ROOT', dirname(__FILE__));

// -----------------------------------------------------------------
// INCLUDES
// -----------------------------------------------------------------
include_once JDML_ROOT . '/admin/posts_table.php';
include_once JDML_ROOT . '/admin/meta_box.php';
include_once JDML_ROOT . '/taxonomies.php';
include_once JDML_ROOT . '/filters.php';
include_once JDML_ROOT . '/jdml-post.php';
include_once JDML_ROOT . '/jdml.php';

// global variables:

// jdml-enabled post types (as slugs):
$jdml_post_types = array(
  'post', 'page'
); 


// -----------------------------------------------------------------
// GENERAL FUNCTIONS
// -----------------------------------------------------------------

function the_language_switcher($post_id=null, $label=null) {
  $jdml_post = new JDML_Post($post_id);
  echo $jdml_post->get_language_switcher($label);
}

// Sets the $jdml_post_types global variable when cpt registrations are being processed
function jdml_registered_post_types() {
  global $jdml_post_types;
  $jdml_post_types = array();
  $post_types = get_post_types('','names'); 
  foreach ($post_types as $post_type ) {
    if (!in_array($post_type, array('nav_menu_item', 'revision'))) {
      // support this post type:
      $jdml_post_types[] = $post_type;
      // add language taxonomy to this post_type:
      register_taxonomy_for_object_type(JDML_TAX_SLUG, $post_type);
    }
  }
}

// -----------------------------------------------------------------
// ACTIONS AND FILTERS
// -----------------------------------------------------------------

// New "language" column for posts (and other post types) table:
foreach ($jdml_post_types as $post_type) {
  add_filter('manage_'.$post_type.'_posts_columns', array("JDML_AdminPostTable", 'add_new_column')); // post type's index column title
  add_action('manage_'.$post_type.'s_custom_column', array("JDML_AdminPostTable", 'add_column_data'), 10, 2); // post type's index column data
  add_filter('edit_'.$post_type.'_per_page', array('JDML_AdminPostTable', 'edit_pages_per_page'));
  add_filter('views_edit-'.$post_type, array('JDML_AdminPostTable', 'views_edit_post'));
}

// Taxonomy:
add_action('init', 'jdml_create_language_taxonomy', 0);

// Meta box:
add_action('admin_init', 'jdml_add_language_metaboxe');
add_action('save_post', 'jdml_save_post_meta', 1, 2);

// Posts permalink translation:
add_filter('post_link', 'jdml_language_permalink', 10, 3);
add_filter('post_type_link', 'jdml_language_permalink', 10, 3);

// Set the locale
add_filter('locale', 'jdml_set_locale');

// When post types are registered
add_action('registered_post_type', 'jdml_registered_post_types');


