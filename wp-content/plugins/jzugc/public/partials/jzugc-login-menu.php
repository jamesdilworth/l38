<?php
/**
 * Provide a dropdown navigation menu for the dashboard, or for the sign-in button.
 */
?>

<div class='login-container'>
	<?php if (is_user_logged_in()) : ?>

        <div class="account dropdown">
            <a href='/my-account/'><i class='fa fa-user-circle' style="padding-right:3px;"></i> My Account</a>
            <a class="dropdown-toggle" style="padding-left:5px;" href="#" data-toggle="dropdown"><i class="fa fa-caret-down"></i></a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="/my-account/">My Profile</a>
                <a class="dropdown-item" href="/my-account/#edit-profile">Edit Profile</a>
                <a class="dropdown-item" href="/my-account/#classies">My Classy Ads</a>
                <a class="dropdown-item" href="<?php echo wp_logout_url( home_url()); ?>">Sign Out</a>
            </div>
        </div>

    <?php else : ?>

        <a class='login-register-link' data-mfp-src='#login-register' href='/login/'><i class='fa fa-sign-in'></i> Sign In</a>

    <?php endif; ?>
</div>
