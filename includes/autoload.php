<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

spl_autoload_register( function ( $class_name ) {
  // Base directory for the namespace prefix
  $base_dir = WPFORMS_SRI_PATH . 'includes/';

  // Get the relative class name
  $relative_class = $class_name;

  // Replace the namespace prefix with the base directory, replace namespace
  // separators with directory separators in the relative class name, append
  // with .php
  $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

  // If the file exists, require it
  if ( file_exists( $file ) ) {
    require $file;
  }
});
