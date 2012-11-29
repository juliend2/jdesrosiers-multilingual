<?php

class JDML { 

  // supported locales:
  public static $locales = array(
    'de' => "de_DE",  'en' => "en_US",  'zh' => "zh_CN",  'fi' => "fi",
    'fr' => "fr_FR",  'nl' => "nl_NL",  'sv' => "sv_SE",  'it' => "it_IT",
    'ro' => "ro_RO",  'hu' => "hu_HU",  'ja' => "ja",     'es' => "es_ES",
    'vi' => "vi",     'ar' => "ar",     'pt' => "pt_BR",  'pl' => "pl_PL",
  );

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

}
