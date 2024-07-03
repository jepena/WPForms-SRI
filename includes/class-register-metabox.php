<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class Register_Metabox {

  public function register_metabox() {
    add_action('add_meta_boxes', array($this, 'invite_request_meta_box') );
    add_action('save_post', array($this, 'save_invite_request_code_meta_box_data'), 10, 3);
  }

  public function invite_request_meta_box( $post_type ) {
    $post_types = array( 'wpforms_sri' );

    add_meta_box(
      'invite_request_meta_box', // Unique ID for the meta box
      'Invite Request Details',  // Meta box title
      array($this, 'invite_request_metabox_callback'), // Callback function
      $post_types, // Post type
      'normal', // Context
      'high' // Priority
    );

  }

  public function invite_request_metabox_callback( $post ) {
    wp_nonce_field(basename(__FILE__), 'invite_request_code_meta_box_nonce');
    // Retrieve current metadata
    $meta_data = get_post_meta($post->ID, 'invite_request_code_meta', true);

    if ( $meta_data ) {
      $meta_data = unserialize($meta_data);
    } else {
      $meta_data = array(
        'invite_request_code' => '',
        'expiration_time' => '',
        'unique_url' => '',
        'status' => '',
        'user_ip' => ''
      );
    }
      
  ?>
  <div class="invite-code-metabox-holder">
    <div style="display:flex;margin-bottom:10px;margin-top:1rem;">
      <label for="unique_url" style="width:200px;margin-right:5px">Unique URL</label>
      <input type="text" name="unique_url" id="unique_url" value="<?php echo esc_attr($meta_data['unique_url']); ?>" class="widefat" disabled="disabled" />
      <button type="button" id="copy_unique_url" class="button"><?php echo esc_html__('Copy', 'wpforms-request-invite') ?></button>
    </div>
    <div style="display:flex;margin-bottom:10px;">
      <label for="expiration_time" style="width:200px;margin-right:5px">Expiration Time</label>
      <input type="text" name="expiration_time" id="expiration_time" value="<?php echo esc_attr($meta_data['expiration_time']); ?>" class="widefat date-picker" />
    </div>
    <div style="display:flex;margin-bottom:10px;">
      <label for="status" style="width:170px;margin-right:5px">Status</label>
      <select name="status" id="status" class="widef">
        <option value="unused" <?php if ($meta_data['status'] === 'unused') echo 'selected="selected"'; ?> >unused</option>
        <option value="success" <?php if ($meta_data['status'] === 'success') echo 'selected="selected"'; ?> >success</option>
        <option value="expired" <?php if ($meta_data['status'] === 'expired') echo 'selected="selected"'; ?> >expired</option>
      </select>
    </div>
    <div style="display:flex;">
      <label for="user_ip" style="width:200px;margin-right:5px">User IP</label>
      <input type="text" name="user_ip" id="user_ip" value="<?php echo esc_attr($meta_data['user_ip']); ?>" class="widefat" disabled="disabled" />
    </div>
    <div style="display:none;">
      <label for="invite_request_code" style="width:200px;margin-right:5px">Invite Code</label>
      <input type="text" name="invite_request_code" id="invite_request_code" value="<?php echo esc_attr($meta_data['invite_request_code']); ?>" class="widefat" disabled="disabled" />
    </div>
  </div>
<?php
  }

  // Save metabox
  public function save_invite_request_code_meta_box_data( $post_id ) {
    // Check if our nonce is set.
    if (!isset($_POST['invite_request_code_meta_box_nonce']) || !wp_verify_nonce($_POST['invite_request_code_meta_box_nonce'], basename(__FILE__))) {
      return $post_id;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $post_id;
    }

    // Check the user's permissions.
    if (!current_user_can('edit_post', $post_id) || 'wpforms_sri' != $_POST['post_type']) {
      return $post_id;
    }

    // Retrieve current metadata
    $meta_data = get_post_meta($post_id, 'invite_request_code_meta', true);

    if ( $meta_data ) {
      $meta_data = unserialize($meta_data);
      // Sanitize user input.
      $invite_request_code = $meta_data['invite_request_code'];
      $expiration_time = sanitize_text_field($_POST['expiration_time']);
      $unique_url = $meta_data['unique_url'];
      $status = sanitize_text_field($_POST['status']);
      $user_ip = $meta_data['user_ip'];

      $expiration_time = isset($expiration_time) ? $expiration_time : $meta_data['expiration_time'];
      $status = isset($status) ? $status : $meta_data['invite_request_code'];

      // Update the meta field in the database.
      $meta_data = array(
        'invite_request_code' => $invite_request_code,
        'expiration_time'     => $expiration_time,
        'unique_url'          => $unique_url,
        'status'              => $status,
        'user_ip'             => $user_ip
      );

      update_post_meta($post_id, 'invite_request_code_meta', serialize($meta_data));
    }

  }
}
