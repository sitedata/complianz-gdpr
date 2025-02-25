<?php
defined( 'ABSPATH' ) or die( "you do not have acces to this page!" );

add_filter( 'cmplz_known_script_tags', 'cmplz_twitter_script' );
function cmplz_twitter_script( $tags ) {
	$tags[] = array(
			'name' => 'twitter',
			'placeholder' => 'twitter',
			'category' => 'marketing',
			'urls' => array(
					'platform.twitter.com',
					'twitter-widgets.js',
			),
			'enable_placeholder' => '1',
			'placeholder_class' => 'twitter-tweet,twitter-timeline',
	);
	return $tags;
}

/**
 * Add some custom css for the placeholder
 */

add_action( 'cmplz_banner_css', 'cmplz_twitter_css' );
function cmplz_twitter_css() {
	?>
		.twitter-tweet.cmplz-blocked-content-container {padding: 10px 40px;}
	<?php
}
