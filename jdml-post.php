<?php

class JDML_Post {

  private $ID;

  function __construct($post_id) {
    $this->ID = $post_id;
  }

  // Returns an Integer of the corresponding post ID
  public function corresponding_post_id() {
    return get_post_meta($this->ID, '_jdml_corresponding_post_id', true);
  }

  // Returns a String of the other language, by the post id
  public function other_language() {
    $lang_slug = $this->post_language_slug();
    $languages = JDML::get_all_language_slugs();
    if (empty($lang_slug)) return false;
    $other_languages = array_values(array_diff($languages, array($lang_slug)));
    if (!empty($other_languages)) { return $other_languages[0]; }
    else { return false; }
  }

  // Returns a String of the post's language slug, ex: 'fr'
  public function post_language_slug() {
    $post_language = $this->post_language();
    if (!empty($post_language)) {
      return $post_language->slug;
    } else {
      return false;
    }
  }

  // Returns an Object of the post's language, containing these properties:
  // name (String), slug (String)
  public function post_language() {
    $terms = wp_get_object_terms($this->ID, JDML_TAX_SLUG);
    if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0])) { return $terms[0]; }
    else { return false; }
  }

  // Returns an Object of the corresponding post
  public function corresponding_post() {
    return get_post($this->corresponding_post_id());
  }

  // Returns a link to edit the post in the admin
  public function get_edit_post_link($label=null) {
    $p = get_post($this->ID);
    $label = is_null($label) ? $p->post_title : $label;
    return '<a href="post.php?action=edit&post='. $p->ID .'">'. $label .'</a>';
  }

  // Returns the HTML of a Language switcher
  public function get_language_switcher($label=null) {
    $post_id = is_null($this->ID) ? get_the_ID() : $this->ID;
    if (is_null($label)) {
      $corresponding_language = JDML::get_corresponding_language_object();
      $label = $corresponding_language->name;
    }
    $jdml_post = new JDML_Post($post_id);
    $corresponding_post = $jdml_post->corresponding_post();
    if ($corresponding_post) {
      return '<a href="'. $corresponding_post->guid .'">'. $label .'</a>';
    } else {
      return false;
    }
  }

}

