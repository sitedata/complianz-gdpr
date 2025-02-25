<?php defined( 'ABSPATH' ) or die( "you do not have acces to this page!" );
/**
 *
 * API for Gutenberg blocks
 *
 * @return array documents (id, title, content)
 *
 */

add_action( 'rest_api_init', 'cmplz_documents_rest_route' );
function cmplz_documents_rest_route() {
	if ( isset($_GET['locale'])) {
		switch_to_locale( sanitize_text_field( $_GET['locale'] ) );
	}

	register_rest_route( 'complianz/v1', 'documents/', array(
		'methods'  => 'GET',
		'callback' => 'cmplz_rest_api_documents',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'complianz/v1', 'banner/', array(
		'methods'  => 'GET',
		'callback' => 'cmplz_rest_api_banner_data',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'complianz/v1', 'track/', array(
		'methods'  => 'POST',
		'callback' => 'cmplz_rest_api_ajax_track_status',
		'args' => array(),
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'complianz/v1', 'manage_consent_html/', array(
		'methods'  => 'GET',
		'callback' => 'cmplz_rest_api_manage_consent_html',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'complianz/v1', 'store_cookies/', array(
		'methods'  => 'POST',
		'callback' => 'cmplz_store_detected_cookies',
		'permission_callback' => function () {
			return current_user_can( 'manage_options' );
		}

	) );
}

/**
 * Track the status selected by the user, for statistics.
 *
 * */

function cmplz_rest_api_ajax_track_status( WP_REST_Request $request ) {
	$params = $request->get_json_params();
	$consented_categories = isset($params['consented_categories']) ? array_map('sanitize_title', $params['consented_categories']) : array('no_choice');
	$consented_services = isset($params['consented_services']) ? array_map('sanitize_title', $params['consented_services']) : array();
	$consenttype = isset($params['consenttype']) ? sanitize_title($params['consenttype']) : COMPLIANZ::$company->get_default_consenttype();
	$prefix = COMPLIANZ::$cookie_admin->get_cookie_prefix();
	foreach($consented_categories as $key => $consented_category ) {
		$consented_categories[$key] = str_replace($prefix, '', $consented_category);
	}
	do_action( 'cmplz_store_consent', $consented_categories, $consented_services, $consenttype );

	$response = json_encode( array(
		'success' => true,
	) );
	header( "Content-Type: application/json" );
	echo $response;
	exit;
}

/**
 * Get Banner data
 * @param WP_REST_Request $request
 */
function cmplz_rest_api_banner_data(WP_REST_Request $request){

	/**
	 * By default, the region which is returned is the region as selected in the wizard settings.
	 *
	 * */

	$region = apply_filters( 'cmplz_user_region', COMPLIANZ::$company->get_default_region() );
	$data                       = apply_filters( 'cmplz_user_data', array() );
	$data['consenttype']        = apply_filters( 'cmplz_user_consenttype', COMPLIANZ::$company->get_default_consenttype() );
	$data['region']             = $region;
	$data['version']            = cmplz_version;
	$data['forceEnableStats']   = !COMPLIANZ::$cookie_admin->cookie_warning_required_stats( $region );
	$data['do_not_track']       = apply_filters( 'cmplz_dnt_enabled', false );
	//We need this here because the integrations are not loaded yet, so the filter will return empty, overwriting the loaded data.
	unset( $data["set_cookies"] );
	$banner_id              = cmplz_get_default_banner_id();
	$banner                 = new CMPLZ_COOKIEBANNER( $banner_id );
	$data['banner_version'] = $banner->banner_version;
	$data                   = apply_filters('cmplz_ajax_loaded_banner_data', $data);
	$response               = json_encode( $data );
	header( "Content-Type: application/json" );
	echo $response;
	exit;
}

/**
 * @param WP_REST_Request $request
 *
 * @return array
 */
function cmplz_rest_api_documents( WP_REST_Request $request ) {
	$documents = COMPLIANZ::$document->get_required_pages();
	$output    = array();
	if ( is_array( $documents ) ) {
		foreach ( $documents as $region => $region_documents ) {
			foreach ( $region_documents as $type => $document ) {
				$html       = COMPLIANZ::$document->get_document_html( $type, $region );
				$region_ext = ( $region === 'eu' ) ? '' : '-' . $region;
				$output[]
				            = array(
					'id'      => $type . $region_ext,
					'title'   => $document['title'],
					'content' => $html,
				);
			}
		}
	}

	return $output;
}


/**
 * Output category consent checkboxes html
 */
function cmplz_rest_api_manage_consent_html( WP_REST_Request $request )
{
	$html = '';
	$do_not_track = apply_filters( 'cmplz_dnt_enabled', false );
	if ( $do_not_track ) {
		$html
			= sprintf( _x( "We have received a privacy signal from your browser. For this reason we have set your privacy settings on this website to strictly necessary. If you want to have full functionality, please consider excluding %s from your privacy settings.",
			"cookie policy", "complianz-gdpr" ), site_url() );
	} else {
		$consent_type = apply_filters( 'cmplz_user_consenttype', COMPLIANZ::$company->get_default_consenttype() );
		$path = trailingslashit( cmplz_path ).'cookiebanner/templates/';
		$banner_html = cmplz_get_template( "cookiebanner.php", array( 'consent_type' => $consent_type ), $path);

		if ( preg_match( '/<!-- categories start -->(.*?)<!-- categories end -->/s', $banner_html,  $matches ) ) {
			$html      = $matches[0];
			$banner_id = apply_filters( 'cmplz_user_banner_id', cmplz_get_default_banner_id() );
			$banner = new CMPLZ_COOKIEBANNER(  $banner_id );
			$cookie_settings = $banner->get_html_settings();

			foreach($cookie_settings as $fieldname => $value ) {
				if ( isset($value['text']) ) $value = $value['text'];
				if ( is_array($value) ) continue;
				$html = str_replace( '{'.$fieldname.'}', $value, $html );
			}
		}
	}
	$response = json_encode( $html );
	header( "Content-Type: application/json" );
	echo $response;
	exit;
}


/**
 * Store the detected cookies in the cookies table
 */

function cmplz_store_detected_cookies(WP_REST_Request $request) {
	$params = $request->get_json_params();

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $params['token'] )
	     && ( sanitize_title( $params['token'] )
	          == get_option( 'complianz_scan_token' ) )
	) {
		$post_cookies = isset( $params['cookies'] )
		                && is_array( $params['cookies'] )
			? $params['cookies'] : array();
		$cookies      = array_map( function ( $el ) {
			return sanitize_title( $el );
		}, $post_cookies );
		if ( ! is_array( $cookies ) ) {
			$cookies = array();
		}

		$post_storage = isset( $params['lstorage'] ) && is_array( $params['lstorage'] ) ? $params['lstorage'] : array();
		$localstorage = array_map( function ( $el ) {
			return sanitize_title( $el );
		}, $post_storage );
		if ( ! is_array( $localstorage ) ) {
			$localstorage = array();
		}

		//add local storage data
		$localstorage = array_map( 'sanitize_text_field', $localstorage );
		foreach ( $localstorage as $key => $value ) {
			$cookie = new CMPLZ_COOKIE();
			$cookie->add( $key, COMPLIANZ::$cookie_admin->get_supported_languages() );
			$cookie->type = 'localstorage';
			$cookie->isOwnDomainCookie = true;
			$cookie->save( true );
		}

		//add cookies
		$cookies = array_merge( $cookies, $_COOKIE );
		$cookies = array_map( 'sanitize_text_field', $cookies );
		foreach ( $cookies as $key => $value ) {
			$cookie = new CMPLZ_COOKIE();
			$cookie->add( $key, COMPLIANZ::$cookie_admin->get_supported_languages() );
			$cookie->type = 'cookie';
			$cookie->isOwnDomainCookie = true;
			$cookie->save( true );
		}

		//clear token
		update_option( 'complianz_scan_token', false );
		//store current requested page
		COMPLIANZ::$cookie_admin->set_page_as_processed( $params['complianz_id'] );
	}
}




