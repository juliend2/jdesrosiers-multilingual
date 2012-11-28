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

// global variables:

// jdml-enabled post types (as slugs):
$jdml_post_types = array(
  'post', 'page'
); 

// jdml-supported locales:
$jdml_locales = array(
  'de' => "de_DE",
  'en' => "en_US",
  'zh' => "zh_CN",
  'fi' => "fi",
  'fr' => "fr_FR",
  'nl' => "nl_NL",
  'sv' => "sv_SE",
  'it' => "it_IT",
  'ro' => "ro_RO",
  'hu' => "hu_HU",
  'ja' => "ja",
  'es' => "es_ES",
  'vi' => "vi",
  'ar' => "ar",
  'pt' => "pt_BR",
  'pl' => "pl_PL",
);


// Helper functions
// -----------------------------------------------------------------

// Displays a Language switcher
function jdml_get_language_switcher($post_id=null, $label=null) {
  $post_id = is_null($post_id) ? get_the_ID() : $post_id;
  if (is_null($label)) {
    $corresponding_language = jdml_get_corresponding_language_object();
    $label = $corresponding_language->name;
  }
  $corresponding_post = jdml_get_corresponding_post($post_id);
  if ($corresponding_post) {
    return '<a href="'. $corresponding_post->guid .'">'. $label .'</a>';
  } else {
    return false;
  }
}

function the_language_switcher($post_id=null, $label=null) {
  echo jdml_get_language_switcher($post_id, $label);
}

// Returns an Object of the post's language, containing these properties:
// name (String), slug (String)
function jdml_get_post_language($post_id) {
  $terms = wp_get_object_terms($post_id, JDML_TAX_SLUG);
  if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0])) { return $terms[0]; }
  else { return false; }
}

// Returns a String of the post's language slug, ex: 'fr'
function jdml_get_post_language_slug($post_id) {
  $post_language = jdml_get_post_language($post_id);
  if (!empty($post_language)) {
    return $post_language->slug;
  } else {
    return false;
  }
}

// post_id - Integer of the current post
//
// Returns an Object of the corresponding post
function jdml_get_corresponding_post($post_id) {
  $corresponding_id = jdml_get_corresponding_post_id($post_id);
  return get_post($corresponding_id);
}

// post_id - Integer of the current post
//
// Returns an Integer of the corresponding post ID
function jdml_get_corresponding_post_id($post_id) {
  return get_post_meta($post_id, '_jdml_corresponding_post_id', true);
}

// Returns an array of all the language Objects, which contains these 
// properties: name (String), slug (String)
function jdml_get_all_languages() {
  return get_terms(JDML_TAX_SLUG, array(
    'orderby' => 'slug', 
    'number' => 2
  ));
}

// Returns the current language slug as a String (like 'en')
function jdml_get_current_language_slug() {
  // the query var is available too late in the execution to be used to define de $locale:
  // return get_query_var('language');
  // ...So we use the URL from the $_SERVER superglobal:
  $base_url = get_bloginfo('wpurl');
  $current_url = jdml_get_current_page_url();
  $segments = substr($current_url, strlen($base_url));
  preg_match('%^/?(\w{2})%', $segments, $matches);
  return !empty($matches) ? $matches[1] : '';
}

// Returns the current language (taxonomy) Object
function jdml_get_corresponding_language_object() {
  $languages = jdml_get_all_languages();
  $current_language_slug = jdml_get_current_language_slug();
  foreach ($languages as $lang) {
    if ($lang->slug !== $current_language_slug) {
      return $lang;
    }
  }
  return false;
}

// Returns the current language (taxonomy) Object
function jdml_get_current_language_object() {
  $languages = jdml_get_all_languages();
  $current_language_slug = jdml_get_current_language_slug();
  foreach ($languages as $lang) {
    if ($lang->slug === $current_language_slug) {
      return $lang;
    }
  }
  return false;
}

// Returns an array of all the language slugs as Strings
function jdml_get_all_language_slugs() {
  $slugs = array();
  foreach (jdml_get_all_languages() as $lang) {
    $slugs[] = $lang->slug;
  }
  return $slugs;
}

// post_id - Integer of the current post id
//
// Returns a String of the other language, by the post id
function jdml_get_other_language_by_post($post_id) {
  $lang_slug = jdml_get_post_language_slug($post_id);
  $languages = jdml_get_all_language_slugs();
  if (empty($lang_slug)) return false;
  $other_languages = array_values(array_diff($languages, array($lang_slug)));
  if (!empty($other_languages)) { return $other_languages[0]; }
  else { return false; }
}

// Returns the current language slug ('fr', 'es', etc)
function jdml_admin_get_current_language_slug() {
  if (!empty($_GET['jdml_language_slug'])) {
    return $_GET['jdml_language_slug'];
  } else {
    $all_languages = jdml_get_all_language_slugs();
    return $all_languages[0];
  }
}

// Admin-related functions
// -----------------------------------------------------------------

// post_id - Integer ID of the post
//
// Returns a link to edit the post in the admin
function jdml_get_edit_post_link($post_id, $label=null) {
  $p = get_post($post_id);
  $label = is_null($label) ? $p->post_title : $label;
  return '<a href="post.php?action=edit&post='. $p->ID .'">'. $label .'</a>';
}

// custom taxonomy permalinks

// A filter that replaces the %language% segment by the post's language 
// slug, in the permalink
function jdml_language_permalink($permalink, $post_id, $leavename) {
  // if we didn't find %language% in this url, return the unchanged url:
  if (strpos($permalink, '%'. JDML_TAX_SLUG .'%') === FALSE) return $permalink;
  // Get the post:
  $post = get_post($post_id);
  if (!$post) return $permalink;
  // Get the taxonomy terms:
  $terms = wp_get_object_terms($post->ID, JDML_TAX_SLUG);
  if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0])) $taxonomy_slug = $terms[0]->slug;
  else $taxonomy_slug = 'other';
  // Return the translated version of the URL:
  return str_replace('%'. JDML_TAX_SLUG .'%', $taxonomy_slug, $permalink);
}

// Returns the current URL (http://www.mysite.com/the/page/)
function jdml_get_current_page_url() {
  $pageURL = 'http';
  if (is_ssl()) { $pageURL .= "s"; }
  $pageURL .= "://";
  if ($_SERVER["SERVER_PORT"] != "80") {
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
  } else {
    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
  }
  return $pageURL;
}

// Set the locale according to the current language
function jdml_set_locale( $lang ) {
  global $jdml_locales;
  $current_lang = jdml_get_current_language_slug();
  // the current language is a supported locale?
  if (array_key_exists($current_lang, $jdml_locales)) {
    return $jdml_locales[$current_lang];
  }
  // or else: return original language
  return $lang;
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


