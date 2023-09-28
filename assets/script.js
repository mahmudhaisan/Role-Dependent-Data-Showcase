jQuery(document).ready(function($) {
    // Initially hide the custom field.
    // $('.user-dropdown').hide();
    $('#role').change(function() {
        var selectedRole = $(this).val();
        if (selectedRole == 'sales_admin') {
            console.log('working');
            // Show the custom field when 'Sales Admin' is selected.
            $('.user-dropdown').show();
        } else {
            // Hide the custom field for other roles.
            $('.user-dropdown').hide();
        }
    });
});