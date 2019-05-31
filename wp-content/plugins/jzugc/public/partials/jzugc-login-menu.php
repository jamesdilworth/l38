<?php
/**
 * Provide a dropdown navigation menu for the dashboard, or for the sign-in button.
 */
$sections = array(
    'My Profile' => JZUGC_PATH . 'public/partials/partial-my-account.php',
    'Edit Account' => JZUGC_PATH . 'public/partials/partial-edit-profile.php'
);

if(has_filter('jzugc_my_account_sections')) {
    $sections = apply_filters('jzugc_my_account_sections', $sections);
}

?>

<div class='login-container'>
	<?php if (is_user_logged_in()) : ?>
        <div class="account dropdown">
            <a href='/my-account/'><i class='fa fa-user-circle' style="padding-right:3px;"></i> My Account</a>
            <a class="dropdown-toggle" style="padding-left:5px;" href="#" data-toggle="dropdown"><i class="fa fa-caret-down"></i></a>
            <div class="dropdown-menu">
               <?php
                    foreach($sections as $name=>$path) {
                        echo "<a class='dropdown-item' href='/my-account/#" . sanitize_title($name) . "'>$name</a>";
                    }
                ?>
                <a class="dropdown-item" href="<?php echo wp_logout_url( home_url()); ?>">Sign Out</a>
            </div>
        </div>
    <?php else : ?>
        <a class='login-register-link' data-mfp-src='#login-register' href='/login/'><i class='fa fa-sign-in'></i> Sign In</a>
    <?php endif; ?>
</div>
