<?php
defined( 'ABSPATH' ) or die( "you do not have acces to this page!" );

add_filter( 'cmplz_known_script_tags', 'cmplz_calendly_script' );
function cmplz_calendly_script( $tags ) {
	$tags[] = array(
		'name' => 'calendly',
		'category' => 'marketing',
		'placeholder' => 'calendly',
		'urls' => array(
			'assets.calendly.com',
			'calendly.com',
		),
		'enable_placeholder' => '1',
		'placeholder_class' => 'calendly-inline-widget',
	);
	return $tags;
}

