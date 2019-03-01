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


<?php if (!is_user_logged_in()) { ?>
    <div id="login-register" class="mfp-hide gf-modal">
        <div class="login-form">
            <form id="login" action="login" method="post">
                <h1>Site Login</h1>
                <p class="status"></p>
                <label for="username">Username</label>
                <input id="username" type="text" name="username">

                <label for="password">Password</label>
                <input id="password" type="password" name="password">

                <a class="lost" href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a>
                <input class="submit_button" type="submit" value="Login" name="submit">
                <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
            </form>
        </div>
        <div class="register-form">
            <h1>Join the Latitude 38 Community</h1>
            <?php gravity_form(1, false, false, false, '', true, 10); ?>
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
