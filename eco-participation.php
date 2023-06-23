<?php
/**
 * Plugin Name: EcoParticipation 
 * Description: Saisie et affichage de l'éco participation dans WooCommerce
 * Version: 1.5.4
 * Requires at least: 5.0
 * Tested up to: 6.1.1
 * Plugin Slug: ecopart
 * Author: Fluenx
 * Author URI: https://www.fluenx.com/
 * Text Domain: ecopart
 * Domain Path: /languages
 */

//return;

defined( 'ECOPART_NAME' ) 		or define( 'ECOPART_NAME', 		plugin_basename( __FILE__ ) ); // plugin name as known by WP.
defined( 'ECOPART_SLUG' ) 		or define( 'ECOPART_SLUG', 		'ecopart' );// plugin slug ( should match above meta: Text Domain ).
defined( 'ECOPART_PATH' ) 		or define( 'ECOPART_PATH', 		realpath( __DIR__ ) ); // our directory.
defined( 'ECOPART_URL' ) 		or define( 'ECOPART_URL', 		plugins_url( '', __FILE__ ) );

// $plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ), false ); defined( 'WPDEEPLPRO_VERSION' ) 	or define( 'WPDEEPLPRO_VERSION', 	$plugin_data['Version'] );

try {
	include_once trailingslashit( ECOPART_PATH ) . 'eco-participation-front.php';
	include_once trailingslashit( ECOPART_PATH ) . 'eco-participation-functions.php';
	include_once trailingslashit( ECOPART_PATH ) . 'eco-participation-hooks.php';


	
	if( is_admin() ) {

		include_once trailingslashit( ECOPART_PATH ) . 'keypress-api-client.php';
		include_once trailingslashit( ECOPART_PATH ) . 'keypress-eco-participation.php';
		global $KeyPressEcoPart;
    	$KeyPressEcoPart = new KeyPressEcoPart();

		include_once trailingslashit( ECOPART_PATH ) . 'eco-participation-admin.php';
		include_once trailingslashit( ECOPART_PATH ) . 'eco-participation-back.php';
	}
} catch ( Exception $e ) {
	if( current_user_can( 'manage_options' ) ) {
		print_r( $e );
		die( __( 'Problème de chargement du plugin eco participation','ecopart' ) );
	}
}

add_action( 'plugins_loaded', 'ecopart_plugins_loaded', 0 );
function ecopart_plugins_loaded() {
	if( !function_exists('get_plugin_data' ))  return;
    load_plugin_textdomain( 'ecopart', false, trailingslashit( ECOPART_PATH ) . 'languages/' );
    
//    add_filter( 'upgrader_package_options', 'ecopart_test_upgrader_package_options');
    //add_filter('upgrader_pre_download', 'ecopart_upgrader_pre_download', 10 , 4 );
//    add_filter( 'plugins_api', 'ecopart_plugins_api', 10, 3 );


}

if( !function_exists( 'plouf' ) ) {
	function plouf( $e, $txt = '' ) {
		echo "\n";
		if( $txt != '' ) echo "<br />\n$txt";
		echo '<pre>';
		print_r( $e );
		echo '</pre>';
	}
}

/*add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ecopart_plugin_action_links' );
function ecopart_plugin_action_links( $links ) {
 $links = array_merge( 
 	array(
 		'<a href="' . esc_url( admin_url( '/options-general.php?page=ecopart_settings' ) ) . '">' . __( 'Settings', 'ecopart' ) . '</a>'
 	), 
 	$links 
 );
 return $links;
}*/

register_activation_hook( __FILE__, 'ecopart_plugin_activate' );
function ecopart_plugin_activate() {
	$presets = array(
		'ecopart_display_cart'	=> 'woocommerce_cart_item_name',
		'ecopart_display_product'	=> 'woocommerce_single_product_summary',
		'ecopart_hide_empty_ecopart' => 1,
		'ecopart_show_in_woocommerce-pdf-invoices-packing-slips'	=> 1,
	);
	foreach( $presets as $key => $value ) {
		if( get_option( $key) === false ) {
			add_option( $key, $value );
		}
	}

}

register_deactivation_hook( __FILE__, 'ecopart_plugin_deactivate' );
function ecopart_plugin_deactivate() {
}

add_action( 'init', 'ecopart_init' );
function ecopart_init() {
	if( is_admin() ) {
		WC_Settings_Tab_EcoPart::init();
	}
}



add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ecopart_action_link' );
function ecopart_action_link( $links ) {
	if( !class_exists( 'WooCommerce' ) ) {
		$links = array_merge(
	 		$links,
 			array( __('<span style="color: red;">WooCommerce inactif</span>') )
 		);
		return $links;
	}
	$links = array_merge( 
		array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=settings_ecoparticipation' ) ) . '">' . __( 'Réglages', 'ecopart' ) . '</a>'
		), 
		$links 
	);

	return $links;
}
