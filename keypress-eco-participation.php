<?php

class KeyPressEcoPart extends KeyPressAPIClient {
	const APIVERSION = 'v1';
	const PLUGIN_SKU = 'ECOPARTICIPATION';
	const PLUGIN_SLUG = 'ecoparticipation';
	const PLUGIN_TITLE = 'Éco-participation';
	const PLUGIN_FILE = 'eco-participation/eco-participation.php';
	const PLUGIN_ADMIN_PAGE = '';
	//const URL_RENEWAL = 'https://solutions.fluenx.com/produit/eco-participation-pour-woocommerce/';

	const OPTIONKEY_LICENSE = 'keypress_ecopart_key';
	const OPTIONKEY_SALT = 'keypress_ecopart_salt';
	const OPTIONKEY_EXPIRES = 'keypress_ecopart_expires';
	const OPTIONKEY_STATUS = 'keypress_ecopart_status';
	const MIN_TIME_BETWEEN_PINGS = 3600;


	protected function setUpPluginKeyPress() {
		$this->post_option_key = static::OPTIONKEY_LICENSE;
		// display box in admin
		add_action('ecopart_settings_before', array( $this, 'keypress_admin_page_display_box' ) );

		// the hook to which hook the license activation
		add_action('admin_init', array( $this, 'shouldWeActOnLicenseKeyChange' ), 10, 1 );
	}

	static function getCurrentVersion() {
		if( defined('ECOPART_VERSION') )
			return ECOPART_VERSION;
		$plugin_data = get_plugin_data( trailingslashit( ECOPART_PATH ) . 'eco-participation.php' );
    	define('ECOPART_VERSION', $plugin_data['Version'] ); 
		return ECOPART_VERSION;
	}


}
