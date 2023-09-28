<?php 


function get_dropdown_options() {
    $selected_role = sanitize_text_field($_POST['role']);
    // Create an array of dropdown options based on the selected role.
    $options = array();

    // Customize this section to add options based on the selected role.
    if ($selected_role === 'sales_admin') {
        $options = array(
            'Option 1',
            'Option 2',
            'Option 3',
        );
    } 
    // Generate the dropdown HTML based on the options.
    $dropdown_html = '<select name="custom_dropdown">';
    foreach ($options as $option) {
        $dropdown_html .= '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
    }
    $dropdown_html .= '</select>';

    echo $dropdown_html;
    wp_die();
}
add_action('wp_ajax_get_dropdown_options', 'get_dropdown_options');

