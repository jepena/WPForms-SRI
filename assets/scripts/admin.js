jQuery(document).ready(function($) {
  // Function to toggle warning message visibility
  function toggleWarningMessage() {
    var isRedirectPageActive = $('[name="wpforms_secure_invite_page_unauthorized_access"]').val();
    if (isRedirectPageActive) {
      $('#wp-wpforms_secure_invite_warning_message_content-wrap').slideUp(700);
    } else {
      $('#wp-wpforms_secure_invite_warning_message_content-wrap').slideDown(700);
    }
  }

  // Function to set the default state of form fields
  function setDefaultFieldState() {
    if (!$('input[name="wpforms_secure_invite_enable_form_request_woo_customer"]').is(':checked')) {
      $('input[name="wpforms_secure_invite_form_shortcode_for_woo_customer"]').closest('tr').hide();
      $('input[name="wpforms_secure_invite_form_shortcode_request_for_woo_customer"]').closest('tr').hide();
    }
  }

  // Function to toggle form fields visibility
  function toggleFormFields() {
    if ($('input[name="wpforms_secure_invite_enable_form_request_woo_customer"]').is(':checked')) {
      $('input[name="wpforms_secure_invite_form_shortcode_for_woo_customer"]').closest('tr').slideDown(700);
      $('input[name="wpforms_secure_invite_form_shortcode_request_for_woo_customer"]').closest('tr').slideDown(700);
    } else {
      $('input[name="wpforms_secure_invite_form_shortcode_for_woo_customer"]').closest('tr').slideUp(700);
      $('input[name="wpforms_secure_invite_form_shortcode_request_for_woo_customer"]').closest('tr').slideUp(700);
    }
  }

  // Event listener for changes in redirect page input
  $('[name="wpforms_secure_invite_page_unauthorized_access"]').on('input', function() {
    toggleWarningMessage();
  });

  // Event listener for changes in checkbox state
  $('input[name="wpforms_secure_invite_enable_form_request_woo_customer"]').change(function() {
    toggleFormFields();
  });

  // Function to initialize components
  function init() {
    setDefaultFieldState(); // default check for form fields
    toggleWarningMessage(); // Initial check for warning message
    toggleFormFields(); // Initial check for form fields
  }

  // Initialize the script
  init();

});
