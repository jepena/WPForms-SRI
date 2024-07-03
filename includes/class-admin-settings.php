<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class Admin_Settings {

  public function admin_settings() {
    add_action('admin_menu', array( $this, 'add_admin_menu') );
    add_action('admin_init', array( $this, 'register_settings') );

    // enqueue assets
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
  }

  /**
   * Add the settings page under the custom post type menu.
   */
  public static function add_admin_menu() {
    add_submenu_page(
      'edit.php?post_type=wpforms_sri', // Parent slug
      __( 'Secure Request Invite Settings', 'wpforms-request-invite' ), // Page title
      __( 'Settings', 'wpforms-request-invite' ), // Menu title
      'manage_options', // Capability
      'wpforms-secure-request-invite-settings', // Menu slug
      array( __CLASS__, 'settings_page' ) // Callback function
    );
  }

  /**
   * Register settings and fields.
   */
  public static function register_settings() {
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_prefix');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_code_length');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_expiration_time');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_redirect_page');
    // register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_form_ids');
    register_setting(
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_form_ids',
      [
        'sanitize_callback' => [__CLASS__, 'sanitize_form_ids']
      ]
    );
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_form_shortcode');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_enable_form_request_woo_customer');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_form_shortcode_for_woo_customer');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_form_shortcode_request_for_woo_customer');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_page_unauthorized_access');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_warning_message_content');
    register_setting('wpforms_secure_invite_settings', 'wpforms_secure_invite_remove_data');

    add_settings_section(
      'wpforms_secure_invite_settings_section',
      __( 'General Settings', 'wpforms-request-invite' ),
      array( __CLASS__, 'settings_section_callback' ),
      'wpforms_secure_invite_settings'
    );

    add_settings_section(
      'wpforms_secure_invite_settings_note',
      __( 'Embed in a Page', 'wpforms-request-invite' ),
      array( __CLASS__, 'settings_section_note_callback' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_prefix',
      __( 'Invite Code Prefix', 'wpforms-request-invite' ),
      array( __CLASS__, 'prefix_render' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_code_length',
      __( 'Invite Code Length', 'wpforms-request-invite' ),
      array( __CLASS__, 'code_length_render' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_expiration_time',
      __( 'Expiration Time (minutes)', 'wpforms-request-invite' ),
      array( __CLASS__, 'expiration_time_render' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_redirect_page',
      __( 'Redirect Page', 'wpforms-request-invite' ),
      array( __CLASS__, 'redirect_page_render' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_form_ids',
      __( 'WPForm IDs', 'wpforms-request-invite' ),
      array( __CLASS__, 'form_ids_render' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_form_shortcode',
      __( 'Shortcode Form', 'wpforms-request-invite' ),
      array( __CLASS__, 'form_shortcode' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_enable_form_request_woo_customer',
      __( 'Enable Customer Request Form', 'wpforms-request-invite' ),
      array( __CLASS__, 'enable_form_request_woo_customer' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_form_shortcode_for_woo_customer',
      __( 'Woocommerce Customer Form Shortcode', 'wpforms-request-invite' ),
      array( __CLASS__, 'form_shortcode_for_woo_customer' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_form_shortcode_request_for_woo_customer',
      __( 'Customer Request Form Shortcode', 'wpforms-request-invite' ),
      array( __CLASS__, 'form_shortcode_request_for_woo_customer' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_page_unauthorized_access',
      __( 'Redirect URL for Unauthorized Access', 'wpforms-request-invite' ),
      array( __CLASS__, 'page_unauthorized_access' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_warning_message_content',
      __( 'Warning Message', 'wpforms-request-invite' ),
      array( __CLASS__, 'warning_message_content' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );

    add_settings_field(
      'wpforms_secure_invite_remove_data',
      __( 'Remove Data on Uninstall', 'wpforms-request-invite' ),
      array( __CLASS__, 'remove_data_render' ),
      'wpforms_secure_invite_settings',
      'wpforms_secure_invite_settings_section'
    );
  }

  public function get_registered_settings() {
    $default = [

    ];

    return $default;
  }

  public static function sanitize_form_ids($input) {
    if (is_array($input)) {
      // Join the array into a comma-separated string
      return implode(',', array_map('sanitize_text_field', $input));
    }
    return sanitize_text_field($input);
  }

  public static function settings_section_callback() {
    echo '<p>' . __( 'Configure the settings for Secure Request Invites.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function settings_section_note_callback() {
    echo '<p>' . __( 'Use the shortcode below to embed the request invite form on the page you want to secure.', 'wpforms-request-invite' ) . '</p>';
    echo '
    <div style="display:flex; flex-direction: column;">
      <input type="text" name="unique_url" id="unique_url" value="[secure_request_invite_form]" class="widefat" disabled="disabled" style="width:210px;" />
      <p style="display:none;">To override the shortcode settings value simply add the attributes shortcode_name="xxx" and shortcode_id="xxx" </p>
    </div>';
  }

  public static function prefix_render() {
    $value = get_option('wpforms_secure_invite_prefix', 'WPF03M5CFR-');
    echo '<input type="text" name="wpforms_secure_invite_prefix" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __( 'Set a prefix for your invite codes to easily identify them.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function code_length_render() {
    $value = get_option('wpforms_secure_invite_code_length', 23);
    echo '<input type="number" name="wpforms_secure_invite_code_length" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __( 'Specify the number of characters for each invite code after the prefix.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function expiration_time_render() {
    $value = get_option('wpforms_secure_invite_expiration_time', 10);
    echo '<input type="number" name="wpforms_secure_invite_expiration_time" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __( 'Determine how long the invite code remains valid in minutes.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function redirect_page_render() {
    $value = get_option('wpforms_secure_invite_redirect_page', home_url('/complete-your-registration'));
    echo '<input type="url" name="wpforms_secure_invite_redirect_page" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __( 'Enter the page URL to which users will be redirected to complete their registration.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function form_ids_render() {
    $value = get_option('wpforms_secure_invite_form_ids', '');
    $forms = wpforms()->get( 'form' )->get(
			'',
			[
				'orderby' => 'title',
				'cap'     => 'view_entries_form_single',
			]
		);

    // Convert saved IDs to an array for easier comparison
    $selected_forms = explode(',', $value);
    echo '<select name="wpforms_secure_invite_form_ids[]" multiple="multiple" style="width: 100%;">';
    foreach ($forms as $form) {
      $selected = in_array($form->ID, $selected_forms) ? 'selected' : '';
      echo '<option value="' . esc_attr($form->ID) . '" ' . $selected . '>' . esc_html($form->post_title) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . __( 'Select the WPForms that will send an email to the customer with a unique URL.', 'wpforms-request-invite' ) . '</p>';

    // echo '<input type="text" name="wpforms_secure_invite_form_ids[]" value="' . esc_attr($value) . '" />';
    // echo '<p class="description">' . __( 'Enter form IDs separated by commas. These are the WPForms that will send an email to the customer with a unique URL.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function form_shortcode() {
    $value = get_option('wpforms_secure_invite_form_shortcode', '');
    echo '<input type="text" name="wpforms_secure_invite_form_shortcode" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __( 'Enter the shortcode to display the registration form specifically designed for special users.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function enable_form_request_woo_customer() {
    $value = get_option('wpforms_secure_invite_enable_form_request_woo_customer', false);
    $checked = $value ? 'checked' : '';
    echo '<input type="checkbox" name="wpforms_secure_invite_enable_form_request_woo_customer" value="1" ' . $checked . ' />';
    echo '<p class="description">' . __( 'Enable the WooCommerce Customer registration form.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function form_shortcode_for_woo_customer() {
    $value = get_option('wpforms_secure_invite_form_shortcode_for_woo_customer', '');
    echo '<input type="text" name="wpforms_secure_invite_form_shortcode_for_woo_customer" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __( 'Enter the shortcode to display the actual WooCommerce customer registration form.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function form_shortcode_request_for_woo_customer() {
    $value = get_option('wpforms_secure_invite_form_shortcode_request_for_woo_customer', '');
    echo '<input type="text" name="wpforms_secure_invite_form_shortcode_request_for_woo_customer" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __( 'Enter the shortcode for the request form to initiate the account opening process.', 'wpforms-request-invite' ) . '</p>';
  }


  public static function page_unauthorized_access() {
    $value = get_option('wpforms_secure_invite_page_unauthorized_access', '');
    echo '<input type="url" name="wpforms_secure_invite_page_unauthorized_access" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __( 'The URL users are redirected to if they attempt to access a restricted page without proper authorization. If this field is empty the warning message will be the fallback.', 'wpforms-request-invite' ) . '</p>';
  }

  public static function warning_message_content() {
    $content = get_option('wpforms_secure_invite_warning_message_content', 'THIS PAGE IS RESTRICTED. To view the page content, please request an invite here.');
    $editor_id = 'wpforms_secure_invite_warning_message_content';
    $settings = array(
      'textarea_name' => 'wpforms_secure_invite_warning_message_content',
      'textarea_rows' => 10,
      'media_buttons' => true,
    );

    wp_editor($content, $editor_id, $settings);
    echo '<p class="description">' . __( 'The content users see if they attempt to access a restricted page without proper authorization.', 'wpforms-request-invite' ) . '</p>';
  }


  public static function remove_data_render() {
    $value = get_option('wpforms_secure_invite_remove_data', false);
    $checked = $value ? 'checked' : '';
    echo '<input type="checkbox" name="wpforms_secure_invite_remove_data" value="1" ' . $checked . ' />';
    echo '<p class="description">' . __( 'Remove all plugin data upon deactivation or deletion.', 'wpforms-request-invite' ) . '</p>';
  }
  /**
   * Render the settings page content.
   */
  public static function settings_page() {
?>
    <div class="wpforms-request-invite-admin-form-holder wrap" style="padding:0 1.5rem;">
    
      <h1><?php _e( 'Secure Request Invite Settings', 'wpforms-request-invite' ); ?></h1>
      <form method="post" action="options.php" class="<?php echo $hidden_field; ?>">
        <?php
        settings_fields('wpforms_secure_invite_settings');
        do_settings_sections('wpforms_secure_invite_settings');
        submit_button();
        ?>
      </form>
    </div>
<?php
  }

  public function enqueue_admin_scripts( $hook ) {
    // For Admin
    wp_enqueue_style('wpforms-request-invite-admin-css', plugin_dir_url(__FILE__) . '../assets/styles/admin.css',  array(), '1.0.0', 'all');
    wp_enqueue_script('wpforms-request-invite-admin-js', plugin_dir_url(__FILE__) . '../assets/scripts/admin.js', array('jquery'), '1.0.0', true);

    // Check if we are on the post edit page or the specific plugin settings page
    if ( 'post.php' != $hook && 'post-new.php' != $hook && 'edit.php' != $hook ) {
      return;
    }

    // Check if we are on the specific plugin settings page
    $current_screen = get_current_screen();
    // if ( 'edit.php' == $hook && isset($current_screen->post_type) && $current_screen->post_type == 'wpforms_sri' && isset($_GET['page']) && $_GET['page'] == 'wpforms-secure-invite-settings' ) {
      // wp_enqueue_script('jquery-ui-datepicker');
      // wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');
      // wp_enqueue_script('clipboard-js', 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js', array(), '2.0.10', true);
      // wp_enqueue_script('wpforms-invite-code-admin-js', plugin_dir_url(__FILE__) . '../assets/scripts/admin.js', array('jquery', 'jquery-ui-datepicker', 'clipboard-js'), '1.0', true);
    // }
    // For Post
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('datetimepicker', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js', array('jquery'), '2.5.20', true);
    wp_enqueue_script('clipboard-js', 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js', array(), '2.0.10', true);
    wp_enqueue_script('wpforms-request-invite-cpt-js', plugin_dir_url(__FILE__) . '../assets/scripts/wpforms-sri-post.js', array('jquery', 'datetimepicker', 'clipboard-js'), '1.0.0', true);
    
    // Styles
    wp_enqueue_style('datetimepicker-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css');
  }
}

