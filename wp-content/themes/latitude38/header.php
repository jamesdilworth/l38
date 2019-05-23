<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<?php do_action( 'fl_head_open' ); ?>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<?php if(is_day() || in_category(199)) echo "<meta name='robots' content='noindex' />\n" ?>
<?php echo apply_filters( 'fl_theme_viewport', "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n" ); ?>
<?php echo apply_filters( 'fl_theme_xua_compatible', "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n" ); ?>
<link rel="profile" href="https://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php FLTheme::title(); ?>
<?php FLTheme::favicon(); ?>
<?php // FLTheme::add_font('Roboto Condensed'); -- Doesn't quite work ?>
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
<?php FLTheme::fonts(); ?>
<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5shiv.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/respond.min.js"></script>
<![endif]-->
<?php

wp_head();

FLTheme::head();

?>
</head>

<body <?php body_class(); ?> itemscope="itemscope" itemtype="https://schema.org/WebPage">

<?php if (!is_user_logged_in() && (!is_page('register'))) { ?>
    <div id="login-register" class="mfp-hide gf-modal">
        <div class="login-form gform_wrapper">
            <h3 class="gform_title">Member Login</h3>
            <form id="login" action="login" method="post">
                <p class="status"></p>
                <label for="email">Email or username</label>
                <input id="login-email" type="text" name="login-email">

                <label for="password">Password</label>
                <input id="login-password" type="password" name="login-password">

                <div><a class="lost" href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a></div>
                <div class="submit-field"><input class="btn" type="submit" value="Login" name="submit"></div>
                <?php wp_nonce_field( 'ajax-login-nonce', 'login-security' ); ?>
            </form>
            <div class="toggle-login-register">Not a member yet? <a href="/register/">Join the Latitude 38 Community</a></div>
        </div>
        <div class="register-form">
            <?php gravity_form(3, true, false, false, '', true, 10); ?>
            <div class="toggle-login-register">Already a member? <a href="">Sign in</a></div>
        </div>
    </div>
<?php } ?>

<?php

FLTheme::header_code();

do_action( 'fl_body_open' );

?>
<div class="fl-page">
	<?php

	do_action( 'fl_page_open' );

	FLTheme::fixed_header();

	do_action( 'fl_before_top_bar' );

	FLTheme::top_bar();

	do_action( 'fl_after_top_bar' );
	do_action( 'fl_before_header' );

	FLTheme::header_layout();

	do_action( 'fl_after_header' );
	do_action( 'fl_before_content' );

	?>
	<div class="fl-page-content" itemprop="mainContentOfPage">

		<?php do_action( 'fl_content_open' ); ?>
