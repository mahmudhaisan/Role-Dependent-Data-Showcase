<?php
/*
Plugin Name: Sales Admin Manager
Description: Manage Sales Admins and Sales Partner Admins.
Version: 1.0
Author: Mahmud Haisan
*/

function enqueue_custom_scripts()
{
    wp_enqueue_script('custom-user-registration', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'enqueue_custom_scripts');


// Add custom roles for Sales Admin and Sales Partner Admin if they don't exist
function add_sales_admin_roles()
{
    // Sales Admin role
    if (!get_role('sales_admin')) {
        add_role('sales_admin', 'Sales Admin', array(
            'read' => true,
            // Add more capabilities as needed
        ));
    }

    // Sales Partner Admin role
    if (!get_role('sales_partner_admin')) {
        add_role('sales_partner_admin', 'Sales Partner Admin', array(
            'read' => true,
            // Add more capabilities as needed
        ));
    }
}
add_action('init', 'add_sales_admin_roles');

function add_custom_dropdown_field($user)
{

    // Check the current user's role.
    $current_user = wp_get_current_user();
    $user_role = $current_user->roles[0];

    // var_dump($user);
    // var_dump($current_user);

    // exit;


    // Only show the custom dropdown field for 'Administrator' role.
    if (current_user_can('administrator') || current_user_can('sales_partner_admin')) {
        // Get all users with the 'sales_partner_admin' role.
        $sales_partner_admins = get_users(array(
            'role' => 'sales_partner_admin',
        ));

        if (!empty($sales_partner_admins)) {
?>
            <table class="user-dropdown">
                <tr class="custom-dropdown-field">
                    <th scope="row"><label for="custom_field">Select Sales Partner Admin</label></th>
                    <td>
                        <select name="dependent_role_field" id="custom_field">
                            <?php
                            foreach ($sales_partner_admins as $sales_partner_admin) {
                                // Add each sales partner admin as an option.
                                $user_id = $sales_partner_admin->ID;

                                $user_display_name = $sales_partner_admin->display_name;
                                echo '<option value="' . esc_attr($user_id) . '">' . esc_html($user_display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
<?php
        }
    }
}

add_action('user_new_form', 'add_custom_dropdown_field');
add_action('show_user_profile', 'add_custom_dropdown_field');
add_action('edit_user_profile', 'add_custom_dropdown_field');

function update_sales_partner_admin($user_id)
{
    // Get the current user's role.
    $current_user = wp_get_current_user();
    $user_role = $current_user->roles[0];

    // Check if the current user has the 'administrator' role.
    if ($user_role === 'administrator') {
        // Get the selected 'sales_partner_admin' ID from the custom field.
        $selected_sales_partner_id = isset($_POST['dependent_role_field']) ? intval($_POST['dependent_role_field']) : 0;

        // Check if the selected ID is valid and corresponds to a 'sales_partner_admin' user.
        if ($selected_sales_partner_id > 0 && in_array('sales_partner_admin', get_userdata($selected_sales_partner_id)->roles)) {
            // Get the current array of user IDs from 'added_sales_partner_admin_id'.
            $existing_id = get_user_meta($user_id, 'added_sales_partner_admin_id', true);


            // Update the user meta with the modified array.
            update_user_meta($user_id, 'added_sales_partner_admin_id', $selected_sales_partner_id);
        }
    }
}

add_action('user_register', 'update_sales_partner_admin');


add_action('user_register', 'update_sales_partner_admin');
add_action('personal_options_update', 'update_sales_partner_admin');
add_action('edit_user_profile_update', 'update_sales_partner_admin');







function modify_user_query_for_sales_partner($query)
{
    $current_user = wp_get_current_user();

    if (in_array('sales_partner_admin', $current_user->roles)) {
        global $wpdb;

        $current_user_id = get_current_user_id();

        $user_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT user_id
         FROM $wpdb->usermeta
         WHERE meta_key = 'added_sales_partner_admin_id'
         AND meta_value = %d",
                $current_user_id
            )
        );

        $query->set('include', $user_ids);
    }
}

add_action('pre_get_users', 'modify_user_query_for_sales_partner');


if (is_admin() && defined('DOING_AJAX') && DOING_AJAX) {
    require plugin_dir_path(__FILE__) . '/ajax.php';
}
