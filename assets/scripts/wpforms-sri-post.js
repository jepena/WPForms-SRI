jQuery(document).ready(function($) {
  // Initialize the date picker
  // $('.date-picker').datepicker({
  //   dateFormat: 'yy-mm-dd'
  // });
  // Initialize the date picker
  // $('input[name="expiration_time"]').datepicker({
  //   dateFormat: 'yy-mm-dd 00:00:00 UTC', // Date format to display in the picker
  //   onSelect: function(dateText, inst) {
  //     // Use moment.js to add the time part to the selected date
  //     var selectedDate = moment(dateText, 'YYYY-MM-DD');
  //     var formattedDate = selectedDate.format('YYYY-MM-DD HH:mm:ss') + ' UTC';
  //     $(this).val(formattedDate);
  //   }
  // });

  // Initialize the datetime picker
  $('input[name="expiration_time"]').datetimepicker({
    format: 'Y-m-d H:i:s', // Date and time format
    step: 1, // Step for minutes, default is 60
    onSelectDate: function(ct, $i) {
        // This function runs when a date is selected
        // Adding the UTC timezone
        var date = new Date(ct);
        var formattedDate = date.toISOString().replace('T', ' ').substring(0, 19) + ' UTC';
        $i.val(formattedDate);
    },
    onSelectTime: function(ct, $i) {
        // This function runs when a time is selected
        // Adding the UTC timezone
        var date = new Date(ct);
        var formattedDate = date.toISOString().replace('T', ' ').substring(0, 19) + ' UTC';
        $i.val(formattedDate);
    }
  });

  // Provide feedback on copy
  $('#copy_unique_url').on('click', function(event) {
    event.preventDefault();
    var $button = $(this);
    var $copy_this = $button.parent().find('[name="unique_url"]');
    
    $copy_this
    .prop( 'disabled', false )
    .select()
    .prop( 'disabled', true );
    // Copy it
    document.execCommand( 'copy' );

    // Add visual feedback to copy command is done.
    $button.text('Copied');

    setTimeout(function() {
      $button.text('Copy');
    }, 2000);

  });

});
