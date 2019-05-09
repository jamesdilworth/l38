<!-- Edit Stuff -->
<form class="jz-form" method="post" id="adduser" action="<?php the_permalink(); ?>">

    <h3><?php _e('Personal info', 'textdomain'); ?></h3>

    <div class="left-half ">
        <label for="first-name"><?php _e('First name', 'textdomain'); ?></label>
        <input class="text-input" name="first-name" type="text" id="first-name" value="<?php the_author_meta( 'first_name', $current_user->ID ); ?>" />
    </div>
    <div class="right-half">
        <label for="last-name"><?php _e('Last name', 'textdomain'); ?></label>
        <input class="text-input" name="last-name" type="text" id="last-name" value="<?php the_author_meta( 'last_name', $current_user->ID ); ?>" />
    </div>

    <p>
        <label for="email"><?php _e('E-mail *', 'textdomain'); ?></label>
        <input class="text-input" name="email" type="text" id="email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
    </p>

    <p>
        <label for="phone"><?php _e('Phone', 'textdomain'); ?></label>
        <input class="text-input" name="phone" type="text" id="phone" value="<?php the_author_meta( 'phone', $current_user->ID ); ?>" />
    </p>
    <p>
        <label for="user_location">Where do you sail from (primarily)?</label>
        <input class="text-input" name="user_location" type="text" id="user_location" value="<?php the_author_meta( 'user_location', $current_user->ID ); ?>" />
    </p>

    <?php
    // action hook for plugin and extra fields
    // do_action('edit_user_profile', $current_user);
    ?>

    <h3><?php _e('Change password', 'textdomain'); ?></h3>


    <div class="left-half form-password">
        <label for="pass1"><?php _e('Password *', 'profile'); ?> </label>
        <input class="text-input" name="pass1" type="password" id="pass1" />
    </div><!-- .form-password -->
    <div class="right-half form-password">
        <label for="pass2"><?php _e('Repeat password *', 'profile'); ?></label>
        <input class="text-input" name="pass2" type="password" id="pass2" />
    </div><!-- .form-password -->
    <p><?php _e('If both password fields are left empty, your password will not change', 'textdomain'); ?></p>

    <p class="form-submit">
        <input name="updateuser" type="submit" id="updateuser" class="submit button" value="<?php _e('Update profile', 'textdomain'); ?>" />
        <?php wp_nonce_field( 'update-user' ) ?>
        <input name="honey-name" value="" type="text" style="display:none;"></input>
        <input name="action" type="hidden" id="action" value="update-user" />
    </p><!-- .form-submit -->

</form><!-- #adduser -->
