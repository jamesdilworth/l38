<?php get_header();
    if( is_user_logged_in())

    $the_user = wp_get_current_user()
?>

<div class="container">
	<div class="row">

		<div class="fl-content <?php FLTheme::content_class(); ?>">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                <h1>Hello <?= $the_user->first_name; ?>!</h1>


                <!-- List out the fields in a small box -->
                <div class="my-profile acct-widget">
                    <div class="name"></div>
                </div>

                <!-- Has the user written in any stories? -->
                <div class="my-stories acct-widget">
                    Add My stories in here.
                </div>

                <!-- Does the user have any classified Ads -->
                <div class="my-classies acct-widget">
                    <div class="ad">
                        <div class="img"><img src=""></div>
                        <div class="title">The Title</div>
                        <div class="price">Price</div>
                        <div class="status">Status</div>
                    </div>
                </div>

			<?php endwhile;
            endif; ?>
		</div>

	</div>
</div>

<?php get_footer(); ?>
