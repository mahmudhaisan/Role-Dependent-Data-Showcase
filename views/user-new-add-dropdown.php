<table class="user-dropdown user-add-under-another form-table">
    <tr class="custom-dropdown-field form-field">
        <th scope="row"><label for="custom_field">Select Sales Partner Admin</label></th>
        <td>
            <select name="dependent_role_field" id="custom_field">
                <?php
                foreach ($sales_partner_admins as $sales_partner_admin) {
                    // Add each sales partner admin as an option with both ID and name as values and labels.
                    $user_id = $sales_partner_admin->ID;
                    $user_display_name = $sales_partner_admin->display_name;
                    $user_login = $sales_partner_admin->user_login;
                ?>


                    <option data-login="<?php echo esc_attr($user_login); ?>" value="<?php echo esc_attr($user_id); ?>">
                        <?php echo esc_html($user_display_name); ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </td>
    </tr>
</table>