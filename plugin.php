<?php
/*

 * Plugin Name:       Sales Admin Manager
 * Plugin URI:        https://github.com/mahmudhaisan/Role-Dependent-Data-Showcase
 * Description:       Manage Sales Admins and Sales Partner Admins.
 * Version:           1.0.0
 * Author:            Mahmud Haisan
 * Author URI:        https://github.com/mahmudhaisan
 * License:           GPL v2 or later
 * Text Domain:       dependent-user-role
 * Domain Path:       /languages/
 */

function enqueue_custom_scripts()
{
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'assets/style.css');
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
    global $pagenow;

    // Check the current user's role.
    $current_user = wp_get_current_user();
    $user_role = $current_user->roles[0];


    $user_add_edit_role = get_userdata($user->ID)->roles[0];

    // Only show the custom dropdown field for 'Administrator' role.
    if (current_user_can('administrator') || current_user_can('sales_partner_admin')) {
        // Get all users with the 'sales_partner_admin' role.
        $sales_partner_admins = get_users(array(
            'role' => 'sales_partner_admin',
        ));





        if ($pagenow == 'user-edit.php') {
            if (current_user_can('administrator') && !empty($sales_partner_admins)) {

                $current_user_id = $user->ID;
                $user__current_user_data = get_userdata($current_user_id);
                $user__current_user_data_role = $user__current_user_data->roles[0];
                $added_sales_partner_admin_id = get_user_meta($current_user_id, 'added_sales_partner_admin_id', true);

                include plugin_dir_path(__FILE__) . '/views/user-edit-dropdown.php';
            }
        }

        if ($pagenow == 'user-new.php') {
            if (current_user_can('administrator') && !empty($sales_partner_admins)) {

                include plugin_dir_path(__FILE__) . '/views/user-new-add-dropdown.php';
            }
        }
    }
}

add_action('user_new_form', 'add_custom_dropdown_field');
add_action('edit_user_profile', 'add_custom_dropdown_field');

function update_sales_partner_admin($user_id)
{
    // Get the current user's role.
    $current_user = wp_get_current_user();
    $user_role = $current_user->roles[0];

    // in_array('sales_partner_admin', get_userdata(4user_id));





    if (current_user_can('sales_partner_admin')) {

        $current_sales_partner_admin_id = $current_user->ID;

        if (in_array('sales_admin', get_userdata($user_id)->roles)) {

            update_user_meta($user_id, 'added_sales_partner_admin_id', $current_sales_partner_admin_id);
        }
    }


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
                WHERE (meta_key = 'added_sales_partner_admin_id' AND meta_value = %d)
                OR user_id IN (
                    SELECT user_id
                    FROM $wpdb->usermeta
                    WHERE meta_key = 'wp_capabilities'
                    AND meta_value LIKE %s
                )",
                $current_user_id,
                '%customer%'
            )
        );


        $current_role_in = $query->get('role__in');

        $meta_query = array();

        // Check if $user_ids is empty, and if so, set the query to return no users
        if (empty($user_ids)) {


            $meta_query[] = array(
                'key' => 'user_id', // Replace 'user_id' with the appropriate user meta key.
                'value' => '0',    // A value that is unlikely to exist.
                'compare' => '=',   // Compare condition that is always false.
            );


            // Apply the meta query to the user query.
            // $query->set('meta_query', $meta_query);

            // $query->set('role__in', array('customer'));

            if (empty($current_role_in)) {
                $query->set('role__in', array('customer'));
            }
        } else {
            $query->set('role__in', array('customer', 'sales_admin'));
            $query->set('include', $user_ids);
        }
    }
}

add_action('pre_get_users', 'modify_user_query_for_sales_partner');

if (is_admin() && defined('DOING_AJAX') && DOING_AJAX) {
    require plugin_dir_path(__FILE__) . '/ajax.php';
}


function customize_role_dropdown($all_roles)
{
    // Check if the current user is an administrator.
    if (current_user_can('administrator')) {
        // Allow all roles to be displayed in the dropdown for administrators.
        return $all_roles;
    }

    // Check if the current user is a manager.
    if (current_user_can('sales_partner_admin')) {
        // Define the roles that managers are allowed to see in the dropdown.
        $allowed_roles = array(
            'customer' => $all_roles['customer'],
            'sales_admin' => $all_roles['sales_admin'],
        );

        return $allowed_roles;
    }

    // Check if the current user is a manager.
    if (current_user_can('sales_admin')) {
        // Define the roles that managers are allowed to see in the dropdown.
        $allowed_roles = array(
            'customer' => $all_roles['customer'],
        );

        return $allowed_roles;
    }

    // If the current user doesn't match any of the conditions, return an empty array to hide all roles.
    return array();
}

add_filter('editable_roles', 'customize_role_dropdown');




// Hook into the 'pre_get_posts' filter to modify the query
add_action('pre_get_posts', 'modify_addify_quote_cpt_query');

function modify_addify_quote_cpt_query($query)
{
    // Check if it's the admin and the main query for the 'addify_quote' post type
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'addify_quote') {
        // Check the user's role
        $user = wp_get_current_user();

        if (in_array('administrator', $user->roles)) {
            // If the user is an administrator, do nothing (show all posts)
            return;
        } elseif (in_array('sales_partner_admin', $user->roles)) {
            // If the user is a sales_partner_admin, fetch all post IDs where '_customer_user' meta key exists
            $customer_user_post_ids = get_posts(array(
                'post_type' => 'addify_quote',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => '_customer_user',
                        'compare' => 'EXISTS',
                    ),
                ),
            ));



            // Initialize an array to store allowed post IDs
            $allowed_post_ids = array();

            // Iterate through the fetched post IDs
            foreach ($customer_user_post_ids as $post_id) {

                // Get the associated user ID from '_customer_user' meta key
                $current_user_id = $user->ID;
                $customer_user_id = get_post_meta($post_id, '_customer_user', true);



                // Check if the associated user has the role of 'customer'
                $customer_user = get_userdata($customer_user_id);

                $added_sales_partner_admin_id = get_user_meta($customer_user_id, 'added_sales_partner_admin_id');
                if ($customer_user && (in_array('customer', $customer_user->roles) || in_array('sales_admin', $customer_user->roles))) {


                    if (in_array('sales_admin', $customer_user->roles) && $added_sales_partner_admin_id[0] == $current_user_id) {
                        // If the associated user has the role of 'customer,' add the post ID to the allowed list
                        $allowed_post_ids[] = $post_id;
                    }

                    if (in_array('customer', $customer_user->roles)) {
                        // If the associated user has the role of 'customer,' add the post ID to the allowed list
                        $allowed_post_ids[] = $post_id;
                    }
                }
            }

            // Modify the query to include only the allowed post IDs
            if (!empty($allowed_post_ids)) {
                $query->set('post__in', $allowed_post_ids);
            } else {
                // If there are no allowed posts, set a query that returns no results
                $query->set('post__in', array(0));
            }
        }
    }
}
