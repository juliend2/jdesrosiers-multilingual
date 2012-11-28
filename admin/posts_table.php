<?php

// New "language" column for posts (and other post types) table
// -----------------------------------------------------------------

class JDML_AdminPostTable {

  // Add a new column in the posts admin view
  static function add_new_column($defaults) {
    $defaults[JDML_TAX_SLUG_PLURAL] = __(JDML_TAX_SLUG, 'jdml');
    return $defaults;
  }

  // Add the custom column data in posts admin view
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
        echo '<br/>Translation: '. JDML::get_edit_post_link($corresponding_id);
      }
    } 
  }

  // sets the limit of posts to show per admin pages
  function edit_pages_per_page($posts_per_page) {
    return 10000; // which is unlikely to be met
  }

  // display the Language-based filtering links in the admin Posts view
  function views_edit_post($views) {
    global $post_type_object;
    $post_type = $post_type_object->name;
    $languages = JDML::get_all_languages();
    foreach ($languages as $language) {
      $class = !empty($_GET['post_status']) && !empty($_GET['language']) && $_GET['language']==$language->slug 
        ? ' class="current"' : '';
      $views[$language->slug] = '<a href="edit.php?language='.$language->slug.
        '&post_type='.$post_type.
        '&post_status=published"'.$class.'>'.$language->name.'</a>';
    }
    return $views;
  }

}

