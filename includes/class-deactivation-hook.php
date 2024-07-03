<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class WPForms_Deactivation {

  public static function run_deactivation_hook() {
    // unregister the post
    unregister_post_type( 'wpforms_sri' );

    // remove cron
    $timestamp = wp_next_scheduled('wpforms_sri_cron');
    wp_unschedule_event($timestamp, 'wpforms_sri_cron');

    // remove data
    self::wpforms_sri_remove_data();
    flush_rewrite_rules();
  }

  public static function wpforms_uninstall_init() {
    self::wpforms_sri_remove_data();
  }

  public static function wpforms_sri_remove_data() {
    
    if ( get_option('wpforms_secure_invite_remove_data', false) ) {
      // Remove all invite code posts
      $args = array(
        'post_type'       => 'wpforms_sri',
        'post_status'     => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
        'posts_per_page'  => -1
      );

      $query = new WP_Query($args);
      
      while ( $query->have_posts() ) {
        $query->the_post();
        wp_delete_post(get_the_ID(), true);
      }

      wp_reset_postdata();

      // Remove the plugin options
      delete_option('wpforms_secure_invite_prefix');
      delete_option('wpforms_secure_invite_code_length');
      delete_option('wpforms_secure_invite_expiration_time');
      delete_option('wpforms_secure_invite_redirect_page');
      delete_option('wpforms_secure_invite_form_ids');
      delete_option('wpforms_secure_invite_form_shortcode');
      delete_option('wpforms_secure_invite_remove_data');
    }
  }
}
