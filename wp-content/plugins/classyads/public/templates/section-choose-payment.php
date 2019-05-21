<?php
/**
 * Assumes $current_jzuser and $profiles have already been set
 *
 */
?>
<div class="fieldset" id="choose_payment_fields">

    <?php
        echo "<p>Choose a card:</p>";
        $output = "";
        foreach($profiles as $profile) {
            $output .=  "<input type='radio' name='cim_payment_profile_id' value='" . $profile['id'] . "'> xxxx xxxx xxxx " . $profile['last4'] . " (" . $profile['expires'] . ")<br>";
        }

        // Admin override to allow non-payment.
        if(current_user_can('edit_posts')) {
            $output .= "<input type='radio' name='cim_payment_profile_id' value='admin_override'> <span class='admin_note'>Admin Override: No Payment.. you can do manually through Authorize.Net interface<br>";
        }

        $output .= "<a href='/edit-profile/#payments'>Add a new payment method</a>";
        echo $output;
    ?>
</div>
