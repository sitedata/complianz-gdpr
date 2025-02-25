<?php
defined( 'ABSPATH' ) or die( "you do not have acces to this page!" );
if ( !defined('CMPLZ_GOOGLE_MAPS_INTEGRATION_ACTIVE') ) define('CMPLZ_GOOGLE_MAPS_INTEGRATION_ACTIVE', true);

/**
 * Add a script to the blocked list
 * @param array $tags
 *
 * @return array
 */
function cmplz_mappress_script( $tags ) {
	$tags[] = array(
			'name' => 'google-maps',
			'category' => 'marketing',
			'placeholder' => 'google-maps',
			'urls' => array(
					'mappress-google-maps-for-wordpress/js/mappress',
			),
			'enable_placeholder' => '1',
			'placeholder_class' => 'mapp-canvas-panel',
			'enable_dependency' => '1',
			'dependency' => [
					'maps.js' => 'wpgmp_map',
			],
	);

	return $tags;
}
add_filter( 'cmplz_known_script_tags', 'cmplz_mappress_script' );

/**
 * Add some custom css for the placeholder
 */

function cmplz_mapppress_css() {
	?>
		.mapp-main .cmplz-placeholder-element {
			height: 100%;
			width: 100%;
		}
	<?php
}
add_action( 'cmplz_banner_css', 'cmplz_mapppress_css' );


/**
 * Add services to the list of detected items, so it will get set as default, and will be added to the notice about it
 *
 * @param $services
 *
 * @return array
 */
function cmplz_mappress_detected_services( $services ) {

	if ( ! in_array( 'google-maps', $services ) ) {
		$services[] = 'google-maps';
	}

	return $services;
}

add_filter( 'cmplz_detected_services', 'cmplz_mappress_detected_services' );
