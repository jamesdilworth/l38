<header class="fl-page-header fl-page-header-primary<?php FLTheme::header_classes(); ?>"<?php FLTheme::header_data_attrs(); ?> itemscope="itemscope" itemtype="https://schema.org/WPHeader">
	<div class="fl-page-header-wrap">
		<div class="fl-page-header-container container">
			<div class="fl-page-header-row row">
				<div class="col-md-4 col-sm-12 fl-page-header-logo-col">
					<div class="fl-page-header-logo" itemscope="itemscope" itemtype="https://schema.org/Organization">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="url"><?php FLTheme::logo(); ?></a>
					</div>
				</div>
				<div class="fl-page-nav-col col-md-8 col-sm-12">
					<div class="fl-page-nav-wrap">
						<nav class="fl-page-nav fl-nav navbar navbar-default" role="navigation" aria-label="<?php echo esc_attr( FLTheme::get_nav_locations( 'header' ) ); ?>" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".fl-page-nav-collapse">
								<span><?php FLTheme::nav_toggle_text(); ?></span>
							</button>
							<div class="fl-page-nav-collapse collapse navbar-collapse">
                                <?php

								FLTheme::nav_search();

								wp_nav_menu(array(
									'theme_location' => 'header',
									'items_wrap' => '<ul id="%1$s" class="nav navbar-nav navbar-right %2$s">%3$s</ul>',
									'container' => false,
									// 'fallback_cb' => 'FLTheme::nav_menu_fallback',
								));

								?>
							</div>
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
</header><!-- .fl-page-header -->
