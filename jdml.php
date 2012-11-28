<?php

class JDML { 

  // supported locales:
  public static $locales = array(
    'de' => "de_DE",  'en' => "en_US",  'zh' => "zh_CN",  'fi' => "fi",
    'fr' => "fr_FR",  'nl' => "nl_NL",  'sv' => "sv_SE",  'it' => "it_IT",
    'ro' => "ro_RO",  'hu' => "hu_HU",  'ja' => "ja",     'es' => "es_ES",
    'vi' => "vi",     'ar' => "ar",     'pt' => "pt_BR",  'pl' => "pl_PL",
  );

  // Returns the HTML of a Language switcher
  static function get_language_switcher($post_id=null, $label=null) {
    $post_id = is_null($post_id) ? get_the_ID() : $post_id;
    if (is_null($label)) {
      $corresponding_language = self::get_corresponding_language_object();
      $label = $corresponding_language->name;
    }
    $corresponding_post = self::get_corresponding_post($post_id);
    if ($corresponding_post) {
      return '<a href="'. $corresponding_post->guid .'">'. $label .'</a>';
    } else {
      return false;
    }
  }

  // Returns an Object of the post's language, containing these properties:
  // name (String), slug (String)
  static function get_post_language($post_id) {
    $terms = wp_get_object_terms($post_id, JDML_TAX_SLUG);
    if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0])) { return $terms[0]; }
    else { return false; }
  }

  // Returns a String of the post's language slug, ex: 'fr'
  static function get_post_language_slug($post_id) {
    $post_language = self::get_post_language($post_id);
    if (!empty($post_language)) {
      return $post_language->slug;
    } else {
      return false;
    }
  }

  // Returns an array of all the language slugs as Strings
  static function get_all_language_slugs() {
    $slugs = array();
    foreach (self::get_all_languages() as $lang) {
      $slugs[] = $lang->slug;
    }
    return $slugs;
  }

  // Returns an array of all the language Objects, which contains these 
  // properties: name (String), slug (String)
  static function get_all_languages() {
    return get_terms(JDML_TAX_SLUG, array(
      'orderby' => 'slug', 
      'number' => 2
    ));
  }

  // Returns the current language (taxonomy) Object
  static function get_corresponding_language_object() {
    $languages = self::get_all_languages();
    $current_language_slug = self::get_current_language_slug();
    foreach ($languages as $lang) {
      if ($lang->slug !== $current_language_slug) {
        return $lang;
      }
    }
    return false;
  }

  // Returns the current language slug as a String (like 'en')
  static function get_current_language_slug() {
    // the query var is available too late in the execution to be used to define de $locale:
    // return get_query_var('language');
    // ...So we use the URL from the $_SERVER superglobal:
    $base_url = get_bloginfo('wpurl');
    $current_url = jdml_get_current_page_url();
    $segments = substr($current_url, strlen($base_url));
    preg_match('%^/?(\w{2})%', $segments, $matches);
    return !empty($matches) ? $matches[1] : '';
  }

  // post_id - Integer of the current post
  //
  // Returns an Object of the corresponding post
  static function get_corresponding_post($post_id) {
    $corresponding_id = self::get_corresponding_post_id($post_id);
    return get_post($corresponding_id);
  }

  // post_id - Integer of the current post
  //
  // Returns an Integer of the corresponding post ID
  static function get_corresponding_post_id($post_id) {
    return get_post_meta($post_id, '_jdml_corresponding_post_id', true);
  }

  // post_id - Integer of the current post id
  //
  // Returns a String of the other language, by the post id
  static function get_other_language_by_post($post_id) {
    $lang_slug = self::get_post_language_slug($post_id);
    $languages = self::get_all_language_slugs();
    if (empty($lang_slug)) return false;
    $other_languages = array_values(array_diff($languages, array($lang_slug)));
    if (!empty($other_languages)) { return $other_languages[0]; }
    else { return false; }
  }

  // post_id - Integer ID of the post
  //
  // Returns a link to edit the post in the admin
  function get_edit_post_link($post_id, $label=null) {
    $p = get_post($post_id);
    $label = is_null($label) ? $p->post_title : $label;
    return '<a href="post.php?action=edit&post='. $p->ID .'">'. $label .'</a>';
  }

}
