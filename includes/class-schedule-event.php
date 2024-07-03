<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class WPForms_Events {

  public function run_events() {
    // Schedule hourly event
    add_action('wp', array( $this,'schedule_invite_code_cron_job'));
    add_filter('cron_schedules', array( $this,'wpforms_sri_cron_schedules'));
    add_action('wpforms_sri_cron', array( $this,'wpforms_sri_cron'));
  }

  public function wpforms_sri_cron_schedules($schedules) {
    if (!isset($schedules["every_ten_minutes"])) {
      $schedules["every_ten_minutes"] = array(
        'interval' => 600, // 600 seconds = 10 minutes
        'display'  => __('Every 10 Minutes')
      );
    }
    return $schedules;
  }

  public function schedule_invite_code_cron_job() {
    if (!wp_next_scheduled('wpforms_sri_cron')) {
      wp_schedule_event(time(), 'hourly', 'wpforms_sri_cron');
    }
  }

 public function wpforms_sri_cron() {
    $args = array(
      'post_type'      => 'wpforms_sri',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'meta_query'     => array(
        array(
          'key'     => 'invite_request_code_meta',
          'compare' => 'EXISTS'
        )
      )
    );

    $query = new WP_Query($args);
    $current_time = current_time('timestamp');

    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $meta_data = get_post_meta($post_id, 'invite_request_code_meta', true);

        if ($meta_data) {
          $meta_data = maybe_unserialize($meta_data);
          $invite_code = isset($meta_data['invite_request_code']) ? $meta_data['invite_request_code'] : '';
          $expiration_time = isset($meta_data['expiration_time']) ? $meta_data['expiration_time'] : 0;
          $unique_url = isset($meta_data['unique_url']) ? $meta_data['unique_url'] : '';
          $status = isset($meta_data['status']) ? $meta_data['status'] : '';
          $stored_ip = isset($meta_data['user_ip']) ? $meta_data['user_ip'] : '';

          // Check if post is 3 hours old or more
          if (strtotime($expiration_time) <= strtotime('-3 hours', $current_time) && $status !== 'expired') {
            $meta_data['status'] = 'expired';
            update_post_meta($post_id, 'invite_request_code_meta', maybe_serialize($meta_data));
          }

          // Check if post is 1 week old (7 days) or more
          if (strtotime(get_the_date('Y-m-d H:i:s', $post_id)) <= strtotime('-7 days', $current_time)) {
              // move to trash
              wp_trash_post($post_id, true);              
              // delete permanently
              // wp_delete_post($post_id, true);
          }
        }
      }
    }

    wp_reset_postdata();
  }
}
