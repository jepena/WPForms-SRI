<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class WPForms_SRI_Columns {

  public function manage_columns() {
    // Columns
    add_filter('manage_wpforms_sri_posts_columns', array($this, 'set_wpforms_sri_columns'));
    add_action('manage_wpforms_sri_posts_custom_column', array($this, 'display_wpforms_sri_column'), 10, 2);
  }

  // Add custom columns to the wpforms_sri post type
  public function set_wpforms_sri_columns( $columns ) {    

    // Create a new array to hold the reordered columns
    $new_columns = array();

    // Iterate over the existing columns
    foreach ($columns as $key => $value) {
      // Add the custom 'status' column before the 'date' column
      if ($key === 'date') {
        $new_columns['expiration'] = __('Expiration', 'wpforms-request-invite');
        $new_columns['status'] = __('Status', 'wpforms-request-invite');
      }
      // Add the existing column to the new array
      $new_columns[$key] = $value;
    }

    return $new_columns;
  }

  // Populate the custom columns
  public function display_wpforms_sri_column( $column, $post_id ) {
    switch( $column ) {
      case 'status':
        $meta_data = get_post_meta($post_id, 'invite_request_code_meta', true);
        if( $meta_data ) {
          $data = unserialize($meta_data);
          echo $data['status'];
        }
      break;

      case 'expiration':
        $meta_data = get_post_meta($post_id, 'invite_request_code_meta', true);
        if( $meta_data ) {
          $data = unserialize($meta_data);
          echo $data['expiration_time'];
        }
      break;
    }
  }
}

