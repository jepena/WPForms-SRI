<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class Generate_Invites {

  public function generate_request_invite() {
    add_action('wpforms_process_before', array($this, 'select_wpforms_by_id'), 10, 2);
    add_filter('wpforms_smart_tags', array($this, 'custom_register_invite_code_smarttag'));
    add_filter('wpforms_smart_tag_process', array($this, 'custom_process_invite_code_smarttag'), 99, 2);
    add_shortcode('secure_request_invite_form', array($this, 'secure_request_invite_form_shortcode'));

    // Uncomment the line below to enable email notifications and use of page template
    // add_action('template_redirect', array($this, 'display_request_invite_url'));
    // add_action('wpforms_process_complete_45477', array($this, 'send_invite_code_email'), 10, 4);
  }


  public function generate_request_invite_url( $form_id = null ) {
    $prefix                   = get_option( 'wpforms_secure_invite_prefix', 'AIACFR-' );
    $code_length              = get_option( 'wpforms_secure_invite_code_length', 23 );
    $expiration_time_minutes  = get_option( 'wpforms_secure_invite_expiration_time', 10 );
    $redirect_page            = get_option( 'wpforms_secure_invite_redirect_page', '' );
    $shortcode_form           = get_option( 'wpforms_secure_invite_form_shortcode_for_woo_customer' );
    $enable_wc_form_request   = get_option('wpforms_secure_invite_enable_form_request_woo_customer', false);
    $shortcode_request_form   = get_option( 'wpforms_secure_invite_form_shortcode_request_for_woo_customer' );

    // $prefix        = 'AIACFR-'; // Define your prefix here
    $invite_code      = $prefix . wp_generate_password( $code_length, false, false ); // Generate a 23-character random code with prefix
    $expiration_time  = current_time( 'timestamp' ) + $expiration_time_minutes * 60; // Set expiration time to 10 minutes from now
    $user_ip          = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $expiration_time = date( 'Y-m-d H:i:s T', $expiration_time );
    $shortcode_form_id = self::get_shortcode_form_id( $shortcode_form );
    $shortcode_request_form_id = self::get_shortcode_form_id( $shortcode_request_form );

    // Create a new invite code post
    $post_id = wp_insert_post(array(
      'post_title'  => $invite_code,
      'post_type'   => 'wpforms_sri',
      'post_status' => 'publish'
    ));

    // Create unique URL for specific registration
    if( !empty($shortcode_form) && !empty($shortcode_request_form) 
    && $form_id === $shortcode_request_form_id && $enable_wc_form_request ) {
      $form_id = $shortcode_request_form_id;
      // Construct the unique URL for Woocommerce Customer account
      $unique_url = add_query_arg(
        array(  
          'pID'         => $post_id,
          'invite-CODE' => $invite_code,
          'F05m1D'      => $form_id
        ),
        $redirect_page
      );
    } else {
      // Construct the unique URL for special account
      $unique_url = add_query_arg(
        array(  
          'pID'         => $post_id,
          'invite-CODE' => $invite_code
        ),
        $redirect_page
      );
    }

    // Create an array of custom post metadata  
    $meta_data = array(
      'invite_request_code'     => $invite_code,
      'expiration_time' => $expiration_time,
      'unique_url'      => $unique_url,
      'status'          => 'unused',
      'user_ip'         => $user_ip
    );

    // Add serialized metadata
    update_post_meta( $post_id, 'invite_request_code_meta', serialize($meta_data) );

    return $invite_code;
  }


  public static function get_shortcode_form_id($shortcode) {
    if( empty($shortcode) ) {
      return false;
    }
    // Define the regular expression pattern to match the ID
    $pattern = '/\[wpforms id="(\d+)"\]/';

    // Check if the pattern matches the shortcode
    if( preg_match($pattern, $shortcode, $matches )) {
      // Return the ID
      return $matches[1];
    }

    // Return false if no match is found
    return false;
  }


  public function display_request_invite_url() {
    if( is_page_template('template-request-registration.php') ) {
    } elseif ( is_page('complete-your-registration') ) {
      $invite_code = isset($_GET['invite-CODE']) ? sanitize_text_field($_GET['invite-CODE']) : '';
      $postID = isset($_GET['pID']) ? sanitize_text_field($_GET['pID']) : '';
      $current_ip = $_SERVER['REMOTE_ADDR'];  

      if ( !empty($invite_code) && !empty($postID) ){
        $invite_code = sanitize_text_field($_GET['invite-CODE']);
        // Query the invite code post
        // $args = array(
        //   'post_type'  => 'wpforms_sri',
        //   'meta_key'   => 'invite_request_code',
        //   'meta_value' => $invite_code,
        //   'post_status' => 'publish',
        //   'posts_per_page' => 1
        // );

        $args = array(
          'post_type'   => 'wpforms_sri',
          'post_status' => 'publish',
          'p'           => $postID,
          'meta_query'  => array(
            array(
              'key' => 'invite_request_code_meta',
              'value' => $invite_code,
              'compare' => 'LIKE'
            )
          ),
          'posts_per_page' => 1
        );
        $query = new WP_Query($args);
        

        if( $query->have_posts() ) {
          $query->the_post();
          $post_id = get_the_ID();
          $meta_data = get_post_meta($post_id, 'invite_request_code_meta', true);
    
          if($meta_data) {
            $meta_data = maybe_unserialize($meta_data);
    
            $invite_code = isset($meta_data['invite_request_code']) ? $meta_data['invite_request_code'] : '';
            $expiration_time = isset($meta_data['expiration_time']) ? $meta_data['expiration_time'] : 0;
            $unique_url = isset($meta_data['unique_url']) ? $meta_data['unique_url'] : '';
            $status = isset($meta_data['status']) ? $meta_data['status'] : '';
            $stored_ip = isset($meta_data['user_ip']) ? $meta_data['user_ip'] : '';
            $current_time = current_time('timestamp');
            
            $current_dateTime = new DateTime();
            $current_dateTime->setTimestamp($current_time);
            $readable_current_time = $current_dateTime->format('Y-m-d H:i:s T'); // Convert to readable date and time format with timezone
    
            // Calculate remaining time in minutes
            $remaining_time = (strtotime($expiration_time) - $current_time ) / 60;
            // Valid invite code, show the registration form and remaining time
            echo '<div class="hidden-">
            <p>You have ' . round($remaining_time) . ' minutes left to complete your registration.</p>
            '.$expiration_time
            .'-'.$readable_current_time.
            '<br>---------'
            .$expiration_time
            .'-'.$current_time.
            '</div>
            ';
    
            if( $status === 'expired' ) {
              echo '<div class="container mx-auto">
              <div class="flex justify-center items-center h-48">
                <p class="font-medium text-xl">This invite link is already expired.</p>
              </div>
              </div>
              ';
            } else {
    
              if ( $readable_current_time > $expiration_time && $status !== 'unused')  {
                echo '<div class="container mx-auto">
                <div class="flex justify-center items-center h-48">
                  <p class="font-medium text-xl">This invite link is no longer accessible.</p>
                </div>
                </div>
                ';
                // Optionally redirect to a different page
                // wp_redirect('/contact-us');
              } else {
                // change to session if enduser is idle for 5 minutes update the status to expired
    
                // update_post_meta($post_id, 'status', 'expired');
                if( $stored_ip == $current_ip && $readable_current_time < $expiration_time) {
                  // display actual user registration form
                  echo do_shortcode('[user_registration_form id="6130"]');
    
                  // update the custom meta-field
                  $meta_data = array(
                    'invite_request_code' => $invite_code,
                    'expiration_time' => $expiration_time,
                    'unique_url' => $unique_url,
                    'status' => 'success',
                    'user_ip' => $stored_ip
                  );
                  update_post_meta($post_id, 'invite_request_code_meta', serialize($meta_data));
    
                } else {
                  echo '<div class="flex justify-center items-center h-48 text-center"><p class="font-normal text-lg">Your invite link is expired or Your IP address location is different. <br>Please request a new link <a href="/register-for-a-trade-account/" class="underline" target="_blank">here</a>.</p></div>';
                }
              }
            }
          }
        } else {
          echo '<div class="flex justify-center items-center h-48"><p class="text-center text-xl font-medium">Please enter the valid invite link to view the registration form.</p></div>';
        }

        wp_reset_postdata();
      }
    }
  }


  public function secure_request_invite_form_shortcode( $atts ) {
    // disable on admin or backend 
    if(is_admin()) return;

    $atts = shortcode_atts(
      array(
        'shortcode_name'  => '',
        'shortcode_id'    => ''
      ), 
      $atts
    );
    ob_start(); // Start output buffering
    $postID = isset($_GET['pID']) ? sanitize_text_field($_GET['pID']) : '';
    $invite_code = isset($_GET['invite-CODE']) ? sanitize_text_field($_GET['invite-CODE']) : '';
    $formID = isset($_GET['F05m1D']) ? sanitize_text_field($_GET['F05m1D']) : '';
    $current_ip = $_SERVER['REMOTE_ADDR'];  

    if ( !empty($invite_code) && !empty($postID) ) {
      $args = array(
        'post_type'   => 'wpforms_sri',
        'post_status' => 'publish',
        'p'           => $postID,
        'meta_query'  => array(
          array(
            'key' => 'invite_request_code_meta',
            'value' => $invite_code,
            'compare' => 'LIKE'
          )
        ),
        'posts_per_page' => 1
      );
      $query = new WP_Query($args);
      if( $query->have_posts() ) {

          $query->the_post();
          $post_id = get_the_ID();
          $meta_data = get_post_meta($post_id, 'invite_request_code_meta', true);

          if($meta_data) {
            $meta_data = maybe_unserialize($meta_data);

            $invite_code = isset($meta_data['invite_request_code']) ? $meta_data['invite_request_code'] : '';
            $expiration_time = isset($meta_data['expiration_time']) ? $meta_data['expiration_time'] : 0;
            $unique_url = isset($meta_data['unique_url']) ? $meta_data['unique_url'] : '';
            $status = isset($meta_data['status']) ? $meta_data['status'] : '';
            $stored_ip = isset($meta_data['user_ip']) ? $meta_data['user_ip'] : '';
            $current_time = current_time('timestamp');

            $current_dateTime = new DateTime();
            $current_dateTime->setTimestamp($current_time);
            $readable_current_time = $current_dateTime->format('Y-m-d H:i:s T'); // Convert to readable date and time format with timezone
          
            // Calculate remaining time in minutes
            $remaining_time = (strtotime($expiration_time) - $current_time ) / 60;
            // Valid invite code, show the registration form and remaining time
            echo '<div class="hidden" style="display: none;">
            <p>You have ' . round($remaining_time) . ' minutes left to complete your registration.</p>
            '.$expiration_time
            .'-'.$readable_current_time.
            '<br>'
            .$expiration_time
            .'-'.$current_time.
            '</div>
            ';

            if( $status === 'expired' ) {
              echo '<div class="container mx-auto">
              <div class="flex justify-center items-center h-48">
                  <p class="font-medium text-xl">This invite link is already expired.</p>
              </div>
              </div>
              ';
            } else {

              if ( $readable_current_time > $expiration_time && $status !== 'unused')  {
                echo '<div class="container mx-auto">
                <div class="flex justify-center items-center h-48">
                    <p class="font-medium text-xl">This invite link is no longer accessible.</p>
                </div>
                </div>
                ';
              } else {

              if( $stored_ip == $current_ip && $readable_current_time < $expiration_time) {
                // display actual user registration form
                $actual_form_shortcode = get_option( 'wpforms_secure_invite_form_shortcode' );
                $customer_form_shortcode = get_option( 'wpforms_secure_invite_form_shortcode_for_woo_customer' );
                $customer_shortcode_request_form   = get_option( 'wpforms_secure_invite_form_shortcode_request_for_woo_customer' );
                // get actual attribute shortcode value -- optional
                // $shortcode_name = html_entity_decode( $atts['shortcode_name'] );
                // $shortcode_id = html_entity_decode($atts['shortcode_id']);

                $request_shortcode_form_id = self::get_shortcode_form_id( $customer_shortcode_request_form );

                // Check what form need to display 
                if( $formID === $request_shortcode_form_id ) {
                  // Display the form for Woocommerce Customer account
                  echo do_shortcode( $customer_form_shortcode );
                } else {
                  // Display the form for special account if not empty
                  if( !empty( $actual_form_shortcode ) ) {
                    echo do_shortcode( $actual_form_shortcode );
                  } else {
                    echo '<p class="wpforms-sri__warning-message"><strong>Warning:</strong> No shortcode data found! Please insert the shortcode for the form you wish to display.</p>';
                  }
                }

                // update the custom meta-field
                $meta_data = array(
                  'invite_request_code' => $invite_code,
                  'expiration_time' => $expiration_time,
                  'unique_url' => $unique_url,
                  'status' => 'success',
                  'user_ip' => $stored_ip
                );

                // update_post_meta($post_id, 'status', 'expired');
                update_post_meta( $post_id, 'invite_request_code_meta', serialize($meta_data) );

              } else {
                echo '<div class="flex justify-center items-center h-48 text-center"><p class="font-normal text-lg">Your request invite link is expired or Your IP address location is different. <br>Please request a new link.</p></div>';
              }
            }
          }
        }
      } else {
        echo '<div class="flex justify-center items-center h-48"><p class="text-center text-xl font-medium">Please enter the valid request invite link to view the registration form.</p></div>';
      }

      wp_reset_postdata();
    } else {
      $page_url         = get_option('wpforms_secure_invite_page_unauthorized_access');
      $message_content  = get_option('wpforms_secure_invite_warning_message_content');
      if( !empty($page_url) ) {
        // Optionally redirect to a different page
        wp_redirect($page_url);
      } else {
        if( !empty($message_content) ) {
          echo "<div class='flex flex-col justify-center items-center h-72'>{$message_content}</div>";
        } else {
          echo '<div class="flex flex-col justify-center items-center h-72">
          <h2 class="mb-2 font-semibold uppercase">This page is restricted.</h2>
          <p class="text-center text-lg font-normal">To view the page content, please request an invite. </p>
          </div>';
        }
      }
    }

    return ob_get_clean(); // Return the buffered content
  }


  public function custom_register_invite_code_smarttag($tags) {
    $tags['request_invite_url'] = 'Request Invite URL';
    return $tags;
  }


  public function select_wpforms_by_id($entry, $form_data) {
    // $form_ids = explode(',', get_option('wpforms_secure_invite_form_ids'));
    $form_ids_option = get_option( 'wpforms_secure_invite_form_ids' );
    $form_ids = array_map('trim', explode(',', $form_ids_option));

    if ( !in_array( absint($form_data['id']), $form_ids) ) {
      return;
    }

    $this->generate_request_invite_url($form_data['id']);
  }
  

  public function custom_process_invite_code_smarttag($content, $tag) {
    if ('request_invite_url' === $tag) {
      $args = array(
        'post_type'      => 'wpforms_sri',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
      );
        
      $query = new WP_Query($args);
      
      if ($query->have_posts()) {
        while ($query->have_posts()) {
          $query->the_post();
          $post_id = get_the_ID();
          $meta_data = get_post_meta($post_id, 'invite_request_code_meta', true);

          if ($meta_data) {
            $meta_data = maybe_unserialize($meta_data);
              
            if( isset($meta_data['status']) && $meta_data['status'] === 'unused' ) {
              $unique_url = isset($meta_data['unique_url']) ? $meta_data['unique_url'] : '';
              $content = str_replace('{request_invite_url}', $unique_url, $content);
              break;
            }
          }
        }
        wp_reset_postdata();
      } else {
        $content = 'Contact us or try to submit a request form again.' . $tag;
      }
    }
    return $content;
  }

  
  public function send_invite_code_email($fields, $entry, $form_data, $entry_id) {
    $customer_email = $fields['customer_email']['value']; // Adjust the field name as needed

    $subject = 'Your Invite code';
    $message = 'Your Invite code is: ' . $invite_code . '. It will expire in 10 minutes.';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // wp_mail($customer_email, $subject, $message, $headers);
  }
}
