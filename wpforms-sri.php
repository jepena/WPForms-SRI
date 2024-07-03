<?php
/**
 * Plugin Name: WPForms - Secure Request Invite
 * Plugin URI: https://jepena.github.io/
 * Description: Enhance your site's security with WPForms Secure Invite Registration. This plugin ensures only invited users can register by sending unique, time-limited URLs via email. Perfect for exclusive memberships or private access.
 * Author: Jacinto Pena
 * Author URI: https://jepena.github.io/
 * Version: 1.0.2
 * Text Domain: wpforms-request-invite
 * Domain Path: /lang/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */
 
 if ( ! defined( 'WPINC' ) ) exit; // Exit if accessed directly
 
 /**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if ( ! defined( 'WPFORMS_SRI_PATH' ) ) {
	define( 'WPFORMS_SRI_PATH', plugin_dir_path( __FILE__ ) );
}

define( 'WPFORMS_SRI_CURRENT_VERSION', '1.0.2' );

// Autoload required classes
$wpforms_sri_autoload_file = WPFORMS_SRI_PATH . 'includes/autoload.php';

if ( is_readable( $wpforms_sri_autoload_file ) ) {
	// require $wpforms_sri_autoload_file;
}

// Autoload required classes
require_once WPFORMS_SRI_PATH . 'includes/class-register-post.php';
require_once WPFORMS_SRI_PATH . 'includes/class-register-metabox.php';
require_once WPFORMS_SRI_PATH . 'includes/class-manage-columns.php';
require_once WPFORMS_SRI_PATH . 'includes/class-admin-settings.php';
require_once WPFORMS_SRI_PATH . 'includes/class-generate-invites.php';
require_once WPFORMS_SRI_PATH . 'includes/class-schedule-event.php';
require_once WPFORMS_SRI_PATH . 'includes/class-deactivation-hook.php';

/**
 * The main WPForms_SRI class
 */
class WPForms_SRI {

  /**
   * Constructor to ensure WPForms_SRI is only setup once.
   */
  public function __construct() {
    // Hook into the admin_init action to check if WPForms is active
    add_action('admin_init', array($this, 'check_wpforms_plugin'));
    
    $this->initialize();
  }

  /**
   * Initialize the plugin
   */
  public function initialize() {
    add_action( 'init', array( 'Register_Post', 'register_post_types' ) );

    $admin = new Admin_Settings;
    $admin->admin_settings();

    $columns = new WPForms_SRI_Columns;
    $columns->manage_columns();

    $metabox = new Register_Metabox;
    $metabox->register_metabox();

    $generate_invite = new Generate_Invites();
    $generate_invite->generate_request_invite();

    // Set all post meta status to "expired" if post is mpre than 3 hours
    $schedule_events = new WPForms_Events();
    $schedule_events = $schedule_events->run_events();
  }

  public function check_wpforms_plugin() {
    if ( !$this->is_wpforms_active() ) {
      add_action('admin_notices', array($this, 'wpforms_plugin_inactive_notice'));
    }
  }

  public function is_wpforms_active() {
    // Check for both WPForms Free and WPForms Pro
    return is_plugin_active('wpforms/wpforms.php') || is_plugin_active('wpforms-lite/wpforms.php');
  }

  public function wpforms_plugin_inactive_notice() {
?>
    <div class="notice notice-error">
      <p><?php _e('The WPForms plugin is not active. This plugin requires WPForms to be active in order to function correctly.', 'wpforms-request-invite'); ?></p>
    </div>
<?php
  }
}

/**
* Returns an instance of WPForms_SRI.
*
* @return WPForms_SRI
*/
function wpforms_sri() {
  static $instance;

  if ( ! isset( $instance ) ) {
    $instance = new WPForms_SRI();
  }
  return $instance;
}

/**
 * Initialize the plugin.
 */
wpforms_sri();

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'wpforms_secure_invite_activate' );
register_deactivation_hook( __FILE__, array('WPForms_Deactivation', 'run_deactivation_hook' ) );
register_uninstall_hook(__FILE__, array( 'WPForms_Deactivation','wpforms_uninstall_init' ) );

/**
 * Activation callback
 */
function wpforms_secure_invite_activate() {
  Register_Post::register_post_types();
  flush_rewrite_rules();
}

