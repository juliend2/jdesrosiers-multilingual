<?php

// Meta box
// -----------------------------------------------------------------

// Add the language metabox on every registered custom post type
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
  $corresponding_id = JDML::get_corresponding_post_id($post->ID);
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
    $html .= '<p>'. JDML::get_edit_post_link($corresponding_id, __('Edit the Translation')) .'</p>';
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
  if (!$corresponding_id) delete_post_meta($post->ID, $key); // Delete if blank

  // set the corresponding post's corresponding post:
  $corresponding_corresponding = $post->post_type == 'revision' ? $post->post_parent : $post->ID;
  $updated_corresponding = update_post_meta((int)$corresponding_id, $key, $corresponding_corresponding);
  if (!$post->ID) delete_post_meta((int)$corresponding_id, $key); // Delete if blank
}
