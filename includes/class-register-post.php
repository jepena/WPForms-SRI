<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class Register_Post {
  /**
   * Register the custom post type
  */
  public static function register_post_types() { 
    $labels = array(
      'name'               => _x('Secure Request Invites', 'post type general name', 'wpforms-request-invite'),
      'singular_name'      => _x('Secure Request Invite', 'post type singular name', 'wpforms-request-invite'),
      'menu_name'          => _x('Secure Request Invites', 'admin menu', 'wpforms-request-invite'),
      'name_admin_bar'     => _x('Secure Request Invite', 'add new on admin bar', 'wpforms-request-invite'),
      'add_new'            => _x('Add New', 'Secure Request Invite', 'wpforms-request-invite'),
      'add_new_item'       => __('Add New Secure Request Invite', 'wpforms-request-invite'),
      'new_item'           => __('New Secure Request Invite', 'wpforms-request-invite'),
      'edit_item'          => __('Edit Secure Request Invite', 'wpforms-request-invite'),
      'view_item'          => __('View Secure Request Invite', 'wpforms-request-invite'),
      'all_items'          => __('All Secure Request Invites', 'wpforms-request-invite'),
      'search_items'       => __('Search Secure Request Invites', 'wpforms-request-invite'),
      'parent_item_colon'  => __('Parent Secure Request Invites:', 'wpforms-request-invite'),
      'not_found'          => __('No Secure Request Invites found.', 'wpforms-request-invite'),
      'not_found_in_trash' => __('No Secure Request Invites found in Trash.', 'wpforms-request-invite')
    );

    $args = array(
      'labels'             => $labels,
      'public'             => false,
      'publicly_queryable' => false,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array('slug' => 'secure-request-invite'),
      'menu_icon'          => 'dashicons-lock',
      'capability_type'    => 'post',
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => 60,
      'supports'           => array('title')
    );

    register_post_type('wpforms_sri', $args);
  }
}
