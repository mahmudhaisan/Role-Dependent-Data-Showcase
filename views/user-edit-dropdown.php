<table class="user-dropdown form-table user-add-under-another">
    <tr class="custom-dropdown-field form-field">
        <th scope="row"><label for="custom_field">Select Sales Partner Admin</label></th>
        <td>
            <select name="dependent_role_field" id="custom_field">
                <?php
                foreach ($sales_partner_admins as $sales_partner_admin) {
                    // Add each sales partner admin as an option with both ID and name as values and labels.
                    $sales_partner_user_id = $sales_partner_admin->ID;
                    $user_display_name = $sales_partner_admin->display_name;
                    $user_login = $sales_partner_admin->user_login;


                    // Check if the user ID matches the $added_sales_partner_admin_id
                    $selected = ($added_sales_partner_admin_id == $sales_partner_user_id) ? 'selected' : '';
                ?>

                    <option data-login="<?php echo esc_attr($user_login); ?>" value="<?php echo esc_attr($sales_partner_user_id); ?>" <?php echo $selected; ?>>
                        <?php echo esc_html($user_display_name); ?>
                    </option>
                <?php

                }
                ?>
            </select>
        </td>
    </tr>
</table>