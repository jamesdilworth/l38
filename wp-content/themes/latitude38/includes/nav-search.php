<div class="fl-page-nav-search">
	<a href="javascript:void(0);" class="fa fa-search"></a>

    <form method="get" role="search" action="https://www.google.com/search" title="<?php echo esc_attr_x( 'Type and press Enter to search.', 'Search form mouse hover title.', 'fl-automator' ); ?>">
        <input type="hidden" name="ie" value="UTF-8" />
        <input type="hidden" name="oe" value="UTF-8" />
        <input type="search" class="fl-search-input form-control" name="q" value="" placeholder="<?php echo esc_attr_x( 'Search', 'Search form field placeholder text.', 'fl-automator' ); ?>" />
        <input type="hidden" name="domains" value="www.latitude38.com" />
        <input type="hidden" name="sitesearch" value="www.latitude38.com" checked="checked" />
    </form>
    <!--
	<form method="get" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr_x( 'Type and press Enter to search.', 'Search form mouse hover title.', 'fl-automator' ); ?>">
		<input type="search" class="fl-search-input form-control" name="s" placeholder="<?php echo esc_attr_x( 'Search', 'Search form field placeholder text.', 'fl-automator' ); ?>" value="<?php echo get_search_query(); ?>" />
	</form>
    -->
</div>
