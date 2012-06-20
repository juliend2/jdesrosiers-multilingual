<?php
/* 
Plugin Name: JDesrosiers Multilingual
Plugin URI: 
Description: A plugin that adds simple features to help make your WordPress site multilingual
Author: Julien Desrosiers
Version: 1.2
Author URI: http://www.juliendesrosiers.com
*/

//
// WARNING: JDesrosiers multilingual supports only two languages, 
// so you can only build bilingual sites for now.
//

// -----------------------------------------------------------------
// DEFINES
// -----------------------------------------------------------------

define('JDML_TAX_NAME', 'Languages');
define('JDML_TAX_SINGLE', 'Language');
define('JDML_TAX_SLUG', 'language');
define('JDML_TAX_SLUG_PLURAL', 'languages');

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


// ----------------------------------------------------------------
// FUNCTIONS AND CLASSES
// -----------------------------------------------------------------

// Helper functions
// -----------------------------------------------------------------

// Displays a Language switcher
function jdml_the_language_switcher($post_id=null, $label=null) {
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

function jdml_language_switcher($post_id=null, $label=null) {
  echo jdml_the_language_switcher($post_id, $label);
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

// New "language" column for posts (and other post types) table
// -----------------------------------------------------------------

class JDML_AdminPostTable {

  static function add_new_column($defaults) {
    $defaults[JDML_TAX_SLUG_PLURAL] = __(JDML_TAX_SLUG, 'jdml');
    return $defaults;
  }

  static function add_column_data($column_name, $post_id) {
    if ($column_name == JDML_TAX_SLUG_PLURAL) {
      $_taxonomy = JDML_TAX_SLUG;
      $terms = get_the_terms($post_id, $_taxonomy);
      if (!empty($terms)) {
        $out = array();
        foreach ($terms as $t) {
          $out[] = '<strong>'.$t->name.'</strong>';
        }
        echo join(', ', $out);
      } else {
        _e('Language not set');
      }
      $corresponding_id = get_post_meta($post_id, '_jdml_corresponding_post_id', true);
      if (!empty($corresponding_id)) {
        echo '<br/>Translation: '. jdml_get_edit_post_link($corresponding_id);
      }
    } 
  }

  // sets the limit of posts to show per admin pages
  function edit_pages_per_page($posts_per_page) {
    return 10000; // which is unlikely to be met
  }

  function posts_query_filter($query) {
    global $pagenow;
    if (is_admin() && $pagenow == 'edit.php') {
      $lang = jdml_admin_get_current_language_slug();
      $query->query_vars['language'] = $lang;
    }
  }

  function views_edit_post($views) {
    $languages = jdml_get_all_languages();
    foreach ($languages as $language) {
      $views[$language->slug] = '<a href="edit.php?language='.$language->slug.'&post_type=page">'.$language->name.'</a>';
    }
    return $views;
  }

}

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

// Meta box
// -----------------------------------------------------------------

function jdml_add_language_metaboxe() {
  global $jdml_post_types;
  foreach ($jdml_post_types as $post_type) {
    add_meta_box('jdml_corresponding_post_id', __('Corresponding Object', 'jdml'), 'jdml_corresponding_post_id', $post_type, 'side', 'default');
  }
}

// The Post's corresponding post id Metabox
function jdml_corresponding_post_id() {
  global $post;
  echo '<input type="hidden" name="jdmlcorrespondingpostidmeta_noncename" '
   . 'id="jdmlcorrespondingpostidmeta_noncename" value="'
   . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

  // Get the corresponding post id data if its already been entered
  $corresponding_id = jdml_get_corresponding_post_id($post->ID);
  // Echo out the field
  $other_language = jdml_get_other_language_by_post($post->ID);
  $get_posts_conditions = array(
    'numberposts' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_type' => $post->post_type,
    'post_status' => 'publish'
  );
  if (!empty($other_language)) {
    $get_posts_conditions['language'] = $other_language;
  }
  $probable_corresponding_posts = get_posts($get_posts_conditions);
  $html = '<p><label for="jdml_corresponding_post_id"><strong>'. __('Choose the '. $post->post_type .' translation', 'jdml') .'</strong></label></p>';
  $html .= '<p><select name="_jdml_corresponding_post_id" id="jdml_corresponding_post_id">';
  $html .= '<option value="">'. __('[Select a '. $post->post_type .']', 'jdml') .'</option>';
  foreach ($probable_corresponding_posts as $p) {
    $html .= '<option value="'. $p->ID .'" ';
    if ($corresponding_id && $corresponding_id == $p->ID) {
      $html .= ' selected="selected" ';
    }
    $html .= ' >'. $p->post_title .'</option>';
  }
  $html .= '</select></p>';

  $corresponding_id = get_post_meta($post->ID, '_jdml_corresponding_post_id', true);
  if (!empty($corresponding_id)) {
    $html .= '<p>'. jdml_get_edit_post_link($corresponding_id, __('Edit the Translation')) .'</p>';
  }

  echo $html;
}

// Save the metabox data
function jdml_save_post_meta($post_id, $post) {
  global $jdml_post_types;
  $key = '_jdml_corresponding_post_id';
  // if we're not in a jdml-enabled post type, skip.
  if (in_array($post->post_type, $jdml_post_types)) return $post;
  // verify this came from our screen and with proper authorization,
  // because save_post can be triggered at other times
  if (empty($_POST[$key]) || empty($_POST['jdmlcorrespondingpostidmeta_noncename']) || !wp_verify_nonce($_POST['jdmlcorrespondingpostidmeta_noncename'], plugin_basename(__FILE__)) ) {
    return $post->ID;
  }
  // Is the user allowed to edit the posts?
  // TODO: see if we can verify this for more than just the 'post' post-type
  if (!current_user_can('edit_post', $post->ID)) {
    return $post->ID;
  }
  // OK, we're authenticated: we need to find and save the data
  // We'll put it into an array to make it easier to loop though.
  $corresponding_id = $_POST[$key];

  // set the post's corresponding post:
  $updated_post = update_post_meta($post->ID, $key, $corresponding_id);
  if (!$corresponding_id) delete_post_meta($post->ID, ''); // Delete if blank

  // set the corresponding post's corresponding post:
  $corresponding_corresponding = $post->post_type == 'revision' ? $post->post_parent : $post->ID;
  $updated_corresponding = update_post_meta((int)$corresponding_id, $key, $corresponding_corresponding);
  if (!$post->ID) delete_post_meta((int)$corresponding_id, ''); // Delete if blank
}

// custom taxonomy permalinks

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

// Returns http://www.mysite.com/the/page/
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

// set the locale according to the current language
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
}
// add_filter('parse_query', array('JDML_AdminPostTable', 'posts_query_filter'));
add_filter('views_edit-page', array('JDML_AdminPostTable', 'views_edit_post'));

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


