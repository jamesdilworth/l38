<?php
class Advanced_Ads_Compatibility {
	public function __construct() {
		// Elementor plugin
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			add_filter( 'advanced-ads-placement-content-injection-xpath', array( $this, 'content_injection_elementor' ), 10, 1 );
		}
	}

	/**
	 * Modify xPath expression for Elementor plugin.
	 * The plugin does not wrap newly created text in 'p' tags.
	 *
	 * @param str $tag
	 * @return xPath expression
	 */
	public function content_injection_elementor( $tag ) {
		if ( $tag === 'p' ) {
			// 'p' or 'div.elementor-widget-text-editor' without nested 'p'
			$tag = "*[self::p or self::div[@class and contains(concat(' ', normalize-space(@class), ' '), ' elementor-widget-text-editor ') and not(descendant::p)]]";
		}
		return $tag;
	}
}
