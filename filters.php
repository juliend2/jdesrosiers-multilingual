<?php

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

// Set the locale according to the current language
function jdml_set_locale( $lang ) {
  $current_lang = JDML::get_current_language_slug();
  // the current language is a supported locale?
  if (array_key_exists($current_lang, JDML::$locales)) {
    return JDML::$locales[$current_lang];
  }
  // or else: return original language
  return $lang;
}

