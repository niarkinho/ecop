<?php


/**
 * KeyPress API Client
 * Description: The class needed for plugin to interact with KeyPress Server
 * Version: 1.0
 * Last change 
 * 14/02/2023 12:20:48
 * PHP: 8.0
 * Author: Fluenx
 * Author URI: https://www.fluenx.com/
 * Text Domain: keypress-i18n
 * Domain Path: /languages
 */

if( !class_exists( 'KeyPressAPIClient' ) ) { 	
	class KeyPressAPIClient {
		const APISERVER = 'https://solutions.fluenx.com';
		const APIBASE = 'wp-json/keypress';

		function __construct() {
			// overrides plugin update
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_plugins_transient' ), 80, 1 );

			// add details to plugin details
			add_filter( 'plugins_api', array( $this, 'modify_plugin_details' ), 10, 3 );

			// admin message
			add_action('after_plugin_row_' . static::PLUGIN_FILE, array( $this, 'pleaseActivate' ), 10, 3);

			add_action('admin_notices', array( $this, 'maybe_admin_notices' ) );

			$this->setUpPluginKeyPress();

		}

		protected function setUpPluginKeyPress() {
			return;
		}

		function modify_plugin_details( $result, $action = null, $args = null ) {

			// vars
			$plugin = false;

			// only for 'plugin_information' action
			if ( $action !== 'plugin_information' ) {
				return $result;
			}

			if( $args->slug != static::PLUGIN_SLUG ) {
				return $result;
			}

			// find plugin via slug
			$plugins = get_plugins();
			if( !isset( $plugins[static::PLUGIN_FILE] ) ) {
				return $result;
			}

			$updateInfo = $this->getUpdateInfo();
			if( !$updateInfo || is_wp_error( $updateInfo ) ) {
				return $result;
			}

			$response = (object) $updateInfo['data'];
			$response->sections = array();
			//plouf( $result, " zeirjzerizrizp r"); die('ozearok');
			foreach( array( 'description', 'installation', 'changelog', 'upgrade_notice' ) as $key ) {
				if( !empty( $updateInfo['data']['plugin_info'][$key]) ) {
					$response->sections[$key] = $updateInfo['data']['plugin_info'][$key];
				}
			}

			return $response;
		}

		function modify_plugins_transient( $transient ) {
			// bail early if no response (error)
			if ( ! isset( $transient->response ) ) {
				return $transient;
			}
			$checked = $this->getUpdateInfo();
			//plouf( $checked);
			if (is_wp_error( $checked ) ) { 
				// from license server
				return $transient;
			}
			//echo "<!-- KEYPRESS ";		plouf( $checked );		echo "-->";
			if( $checked['success'] ) {			// nouvelle version
				if( isset( $checked['data']['package'] ) ) {
					$salt = get_option( static::OPTIONKEY_SALT );
					$host = $this->getSiteHost();
					$checked['data']['package'] .= '&salt=' . $salt . '&host=' . $host;
				}
				$can_update = true;
				$transient->response[static::PLUGIN_FILE] = (object) $checked['data'];
			}
			else {
				$transient->no_update[static::PLUGIN_FILE] = (object) $checked['data'];
			}

			//plouf( $transient , " ierztirhzri");
			//$this->checked++;
			
			//plouf( $transient, "apres " );die('ok');
			return $transient;
		}

		public function getPluginUpdateTransientKey() {
			return 'keypress_' . static::PLUGIN_SLUG .'_updates';
		}

		protected function checkIfUpdatePossible( $ping_response ) {
			return $ping_response;
			/*
			$ping_response['data']['can_update'] = 1;
			$ping_response['data']['why_no_update'] = array();

			if( isset( $ping_response['data']['tested'] ) && version_compare( get_bloginfo( 'version' ), $ping_response['data']['tested']  ) ) {
				$ping_response['data']['can_update'] = 1;
				$ping_response['data']['why_no_update'][] = __( '<strong>Warning:</strong> This plugin <strong>has not been tested</strong> with your current version of WordPress.' );
			}
			if( isset( $ping_response['data']['requires'] ) && version_compare( $ping_response['data']['requires'],	get_bloginfo( 'version' )  ) ) {
				$ping_response['data']['can_update'] = 0;
				$ping_response['data']['why_no_update'][] = __( '<strong>Error:</strong> This plugin <strong>requires a newer version of WordPress</strong>.' );
			}
			if( isset( $ping_response['data']['requires_php'] ) && version_compare( $ping_response['data']['requires_php'],	PHP_VERSION ) ) {
				$ping_response['data']['can_update'] = 0;
				$ping_response['data']['why_no_update'][] = __( '<strong>Error:</strong> This plugin <strong>requires a newer version of PHP</strong>.' );
			}
			return $ping_response;
			*/
		}

		protected function getSiteHost() {
			$url = site_url( '/' );
			$parsed = parse_url( $url );
			return $parsed['host'];
		}

		protected function getLicenseKey() {
			return get_option( static::OPTIONKEY_LICENSE );
		}
		protected function getSalt() {
			return get_option( static::OPTIONKEY_SALT );
		}

		public function isActive() {

			return get_option( static::OPTIONKEY_STATUS ) == 'activated';

		}


		
		// this function check POST data for a license key change
		public function shouldWeActOnLicenseKeyChange( $new_license_key = false ) {

			//plouf($_POST);			plouf( $_GET, " get");

			if( !$new_license_key ) {
				if( isset( $_POST[$this->post_option_key] ) ) {
					if( !isset( $_GET['action'] ) || $_GET['action'] != 'deactivate_license'  )
						$new_license_key = $_POST[$this->post_option_key];
				}
			}

			if( !$new_license_key ) {
				return false;
			}


			$license_status = get_option( static::OPTIONKEY_STATUS );
			$old_license_key = $this->getLicenseKey();
			if( 
				$new_license_key != $old_license_key 
				|| 
				( !$old_license_key && $new_license_key ) 
				|| !$license_status
			) {
//			die("change of key");
				$salt = $this->checkLicense( $new_license_key );
				if( is_wp_error( $salt ) ) {
					update_option( 'keypress_last_error', "Salt failed with $new_license_key : " . $salt->get_error_message() );
					return false;
				}
				if( $salt ) {
					$activated = $this->activateInstallation( $salt );
				}
			}
			elseif( $license_status == 'salted' ) {
				$salt = $this->getSalt();	
				$activated = $this->activateInstallation( $salt );
				//echo " status = " . $license_status;					var_dump( $activated );					die('ok');
			}

		}

		public function checkLicense( $license_key = false ) {
			if( !$license_key )
				$license_key = $this->getLicenseKey();
			if( !$license_key ) 
				return false;

			$license_key = trim( $license_key );
			$product_sku = static::PLUGIN_SKU;
			$host = $this->getSiteHost();

			$method = 'GET';
			$endPoint = trailingslashit( static::APIVERSION ) . 'license/check';
			$args = compact('license_key', 'product_sku', 'host' );
			$result = $this->request( $method, $endPoint, $args );

			$installationSalt = false;
			if( is_wp_error( $result ) ) {
				update_option( static::OPTIONKEY_STATUS, 'salt_failed' );
				update_option( 'keypress_last_error', "Salt failed with $license_key, $product_sku, $host" );
				return $result;
			}
			elseif( $result['success'] && $result['data']['salt'] ) {
				update_option( static::OPTIONKEY_STATUS, 'salted' );
				
				$installationSalt = $result['data']['salt'];
				update_option( static::OPTIONKEY_SALT, $installationSalt );
				
				if( isset( $checkResult['data']['expires_at'] ) ) {
					update_option( static::OPTIONKEY_EXPIRES, $checkResult['data']['expires_at'] );
				}
			}
			else {
				update_option( static::OPTIONKEY_STATUS, 'failed_license_check' );
			}

			return $installationSalt;
		}

		public function activateInstallation( $salt = '') {

			if( empty( $salt ) ) {
				$salt = $this->getSalt();
			}

			if( is_wp_error( $salt ) ) {
				return $salt;
			}
			$salt = trim( $salt );
			$product_sku = static::PLUGIN_SKU;
			$host = $this->getSiteHost();

			$method = 'GET';
			$endPoint = trailingslashit( static::APIVERSION ) . 'installation/activate';
			$args = compact('salt', 'product_sku', 'host' );
			$this->debug = false;
			$result = $this->request( $method, $endPoint, $args );
			//plouf( $args, "$method $endPoint" );			plouf( $result );die('ok');

			if( is_wp_error( $result ) ) {
				update_option( static::OPTIONKEY_STATUS, 'failed_activation' );
				update_option( 'keypress_last_error', $result->get_error_message() );
				//plouf( $result, __METHOD__ );die('error');
			}
			elseif( $result['success'] ) {
				update_option( static::OPTIONKEY_STATUS, 'activated' );
			}
			else {
				update_option( static::OPTIONKEY_STATUS, 'failed_activation' );
			}
			return $result;
		}

		public function deActivateLicense() {
			$method = 'GET';
			$endPoint = trailingslashit( static::APIVERSION ) . 'license/deactivate';
			$salt = get_option( static::OPTIONKEY_SALT );
			$args = compact('salt');
			$result = $this->request( $method, $endPoint, $args );
			if( $result['success'] ) 
				update_option( static::OPTIONKEY_STATUS, 'deactivated' );
			else
				update_option( static::OPTIONKEY_STATUS, 'failed_deactivation' );
			return $result;

		}

		protected function pingProduct() {


			$args = array(
				'salt' => $this->getSalt(),
				'product_sku' => static::PLUGIN_SKU,
				'host' => $this->getSiteHost(),
				'current_version' => static::getCurrentVersion(),
				'wp_name'     => get_bloginfo( 'name' ),
				'wp_version'  => get_bloginfo( 'version' ),
				'wp_language' => get_bloginfo( 'language' ),
				'wp_timezone' => get_option( 'timezone_string' ),
				'php_version' => PHP_VERSION,
			);

			$method = 'GET';
			$endPoint = trailingslashit( static::APIVERSION ) . 'product/ping';

			$response = $this->request( $method, $endPoint, $args );
			//plouf( $args, "args");			plouf( $response , " zerizerihizr");

			if( empty( $response ) ) {
				update_option( 'keypress_last_updated_failed', true );
				return false;
			}
			if( is_wp_error( $response) ) {
				update_option( 'keypress_last_updated_failed', true );
				return $response;
			}

			update_option( 'keypress_last_updated_failed', false );

			$response['data']['slug'] = static::PLUGIN_SLUG;
			$response['data']['file'] = static::PLUGIN_FILE;

			return $response;
		}


		protected function request( $method = 'GET', $endPoint = '', $args = array() ) {

			$APIURL = trailingslashit( self::APISERVER ) . trailingslashit( self::APIBASE ) . $endPoint;

			// Create header
			$headers = array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json; charset=UTF-8',
			);
			
			// Initialize wp_args
			$wp_args = array(
				'headers' => $headers,
				'method'  => $method,
				'timeout' => 10,
			);
			
			// Populate the args for use in the wp_remote_request call
			if ( ! empty( $args ) ) {
				$wp_args['body'] = $args;
			}
			
			// Make the call and store the response in $res
			if( isset( $this->debug ) && $this->debug ) { plouf( $wp_args, $APIURL . __METHOD__ ); }
			$res = wp_remote_request( $APIURL, $wp_args );
			if( isset( $this->debug ) && $this->debug ) plouf($res, " res");
			
			// Check for success
			if ( ! is_wp_error( $res ) && in_array( (int) $res['response']['code'], array( 200, 201 ), true ) ) {
				return json_decode( $res['body'], true );
			} elseif ( is_wp_error( $res ) ) {
				//plouf($res);
				return $res;
			} else {
				$response = json_decode( $res['body'], true );
				if ( 'rest_no_route' === $response['code'] ) {
					$response['data']['status'] = 500;
				}
				$message = isset( $response['message'] ) ? $response['message'] : __( 'Check failed.', static::PLUGIN_TEXT_DOMAIN );
				$code = isset( $response['code'] ) ? $response['code'] : 'keypress_failed_request';
				return new WP_Error(
					$code,
					$message,
					$response
				);	
			}		

		}

		protected function getUpdateInfo( $force_check = 0 ) {
			$plugin_transient_update_name = $this->getPluginUpdateTransientKey();
			//plouf( $transient, "transiendsi" );

			
			$plugin_transient_update = $force_check ? false : get_transient( $plugin_transient_update_name );
			$plugin_transient_update = false;

			if( 
				$plugin_transient_update 
				&& !is_wp_error( $plugin_transient_update ) 
				&& isset( $plugin_transient_update['last_ping'] ) 
				&& ( time() - $plugin_transient_update['last_ping'] ) < static::MIN_TIME_BETWEEN_PINGS 
			) {
				$checked = $plugin_transient_update;
			}
			else {
				$checked = $this->pingProduct();
				if( is_wp_error( $checked ) )
					return $checked;
				$checked['last_ping'] = time();
				// dejà vérifié par WordPress
				//	$checked = $this->checkIfUpdatePossible( $checked );
				set_transient( $plugin_transient_update_name, $checked, static::MIN_TIME_BETWEEN_PINGS );
			}		
		
			return $checked;
		}

		public function pleaseActivate($plugin_file, $plugin_data, $plugin_status) {
			
			$class = $message = false;
			//echo " option ? ";			var_dump( get_option( static::OPTIONKEY_STATUS ) );				die('ok');

			if( $this->isActive() ) {
				return;
				$updateRequest = $this->getUpdateInfo();
				if( $updateRequest['data']['has_newer_version'] ) {
					$class = 'warning';
					$message = 'Mettre à jour';
				}

			}
			else {
				$class = 'info';
				$message = sprintf( 
							__('<a href="%s">Activate this plugin</a> to get updates.', 'keypress-i18n' ),
							esc_url( admin_url( static::PLUGIN_ADMIN_PAGE ) ) 
							);

			}

			if(!$message ) 
				return false;

			$wp_list_table = _get_list_table('WP_Plugins_List_Table');
			$slug = dirname($plugin_file);
			if(is_network_admin()){
				$active_class = is_plugin_active_for_network($plugin_file) ? ' active' : '';
			}else{
				$active_class = is_plugin_active($plugin_file) ? ' active' : '';
			}
			
			?>
			<tr class="plugin-update-tr<?php echo $active_class; ?> keypress" id="<?php echo $slug; ?>-update" data-plugin="<?php echo $plugin_file; ?>"><td colspan="<?php echo $wp_list_table->get_column_count(); ?>" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-<?php echo $class; ?> notice-alt">
					<p><?php echo $message; ?></p>
				</div>
			</tr>
			<?php
		}		


		function maybe_admin_notices() {
			global $keypress_admin_notices;
			if( !$keypress_admin_notices ) return;
			foreach( $keypress_admin_notices as $notice ) :
			?>
			<div class="notice notice-<?php echo $notice['class']; ?> is-dismissible">
				<p><?php echo $notice['message']; ?></p>
			</div>
			<?php 
			endforeach;
		}

		public function keypress_admin_page_display_box() {

			$license_status = get_option( static::OPTIONKEY_STATUS );
			if( $license_status == 'failed_activation' ) {
				$this->activateInstallation();
			}
			$license_status = get_option( static::OPTIONKEY_STATUS );
		?>


	<div id="keypress<?php echo static::PLUGIN_SLUG; ?>_status">
		<h2><?php printf( __('License: %s', 'keypress-i18n'), static::PLUGIN_TITLE );  ?></h2>
		<?php 
		$force_check = get_option( 'keypress_last_updated_failed' ) ? true : false;
		$pingResult = $this->getUpdateInfo( $force_check );

		//plouf( $pingResult);
		
		if( !$pingResult || is_wp_error( $pingResult ) ) {
		// retry
			//_e('We encountered an error while checking for updates info', 'keypress-i18n' );
			$license_key = $this->getLicenseKey();
			$salt = $this->checkLicense( $license_key );
			if( $salt && !is_wp_error( $salt ) ) {
				$activated = $this->activateInstallation( $salt );
				if( $activated && !is_wp_error( $activated ) ) {
					$force_check = true;
					$pingResult = $this->getUpdateInfo( $force_check );
				//	plouf( $pingResult, "retruy" );
				}
			}
		}


		if( !$pingResult || is_wp_error( $pingResult ) ) {

			?>
			<p><?php 

			if( is_wp_error( $pingResult ) ) {
				if( $pingResult->get_error_code()  == 'internal_server_error' ) {
					_e('The license server returned an error', 'keypress-i18n' );

				}
				else {
					printf(
						__('License check failed: <span title="%s"><em>%s</em></span>.
							<br />Please check your license or contact the publisher.', 'keypress-i18n' ),
						$pingResult->get_error_code(),
						$pingResult->get_error_message()
					);
				}
			}
			else {
					_e('The license server returned an error', 'keypress-i18n' );				
			}
				 ?></p>
			<?php 
		}
		else {
			$pingData = $pingResult['data'];
			?>
			<p><?php printf( __('Current version: <b>%s</b>', 'keypress-i18n' ), static::getCurrentVersion() ); ?></p>
			<p><?php printf( __('Available version: <b>%s</b>', 'keypress-i18n') , $pingData['new_version'] ); ?></p>


			<?php if ( get_option( static::OPTIONKEY_STATUS ) === false  ) {
				echo "
				<p>";
				printf( __('Purchase <a href="%s" target="_blank">a license on this page</a> to get updates', 'keypress-i18n' ),
					$pingData['url'] 
				); echo '</p>';
				echo "
				<p>";
				printf( __('Once purchased, <a href="%s" target="_blank">find your license key on this page</a>', 'keypress-i18n' ),
					trailingslashit( $pingData['account_page']  ) . 'downloads/'
				); echo '</p>';
				 

			}
			?>


			<?php if( isset( $pingData['valid_until'] ) ) {
				if( $pingData['valid_until'] == '0000-00-00 00:00:00') : ?>
					<p><?php _e('Your license is valid forever', 'keypress-i18n'); ?></p>
				<?php elseif( strtotime( $pingData['valid_until'] ) ) : ?>
					<p><?php printf( __('Your license expires on : %s', 'keypress-i18n'), date('d/m/Y', strtotime( $pingData['valid_until'] ) ) ); ?></p>
				<?php endif; ?>
			<?php } ?>


			<?php

			if( isset( $pingData['license_valid'] ) ) {

				
				if( $pingResult['success'] ) { // license valid + update

					$update_plugins = get_site_transient( 'update_plugins' );
					$wordpress_knows = false;
					if( $update_plugins ) foreach( $update_plugins->response as $plugin_file => $response ) {
						if( $plugin_file == static::PLUGIN_FILE ) 
							$wordpress_knows = true;
					}
					if( !$wordpress_knows ) {
						unset( $update_plugins->checked[static::PLUGIN_FILE] );
						$update_plugins->response[static::PLUGIN_FILE] = $pingData;
						set_transient( 'update_plugins', $update_plugins );
					}
					//plouf( $update_plugins);


					?>
				<p><?php printf(
					__('Please update the plugin on the <a href="%s">plugins page</a>', 'keypress-i18n' ),
					admin_url( 'plugins.php' )
					);?></p>

				
			<?php 

				}
				else { // license valid + noupdate	?>
					<p><i class="dashicons dashicons-yes"></i><?php _e('Up to date','keypress-i18n' );?></p>
				<?php
				}
			}
			else {
				// no license valid
				if( isset( $pingData['license_error'] ) ) {
					echo '<p class="notice notice-warning">' . $pingData['license_error'] . '</p>';
				}
			}
			

		}
		?>


		<?php 
		/*if( $license_status == 'activated' ) : ?>

			<?php
			$action_link = admin_url( static::PLUGIN_ADMIN_PAGE  .'&action=deactivate_license');
			$action_link = add_query_arg( '_wpnonce', wp_create_nonce( 'keypress_deactivate_license' ), $action_link );
			$action_message = __('Désactiver la licence', 'keypress-i18n' );
			$action_icon = '<i class="dashicons dashicons-no"></i>';
			?>
			<p><?php printf('<a class="button" href="%s">%s %s</a>', $action_link,  $action_icon, $action_message ); ?></p>
		<?php elseif( $license_status == 'deactivated' || $license_status == 'salted') : ?>
			<?php
			$action_link = admin_url( static::PLUGIN_ADMIN_PAGE . '&action=reactivate_license');
			$action_link = add_query_arg( '_wpnonce', wp_create_nonce( 'reactivate_license' ), $action_link );
			$action_message = __('Réactiver la licence', 'keypress-i18n' );
			$action_icon = '<i class="dashicons dashicons-yes"></i>';
			?>
			<p><?php printf('<a class="button" href="%s">%s %s</a>', $action_link,  $action_icon, $action_message ); ?></p>
		<?php else : ?>
			<p><?php _e('Merci d\'entrer une clef de license', 'keypress-i18n' ); ?></p>
		<?php endif;
		*/ 
		?>
		<?php
		if( $pingData && isset( $pingData['downloads_page'] ) ) {
			$license_account_link = add_query_arg( array('product_sku' => static::PLUGIN_SKU ), $pingData['downloads_page'] );
			printf( 
				__( '<p class="keypress_account">View your license(s) on <a href="%s" target="_blank">your account page</a></p>', 'keypress-i18n' ),
				$license_account_link
			);
		}
		?>
		<?php if( isset( $_GET['show'] ) ) printf('Last error: <pre>%s</pre>', get_option('keypress_last_error' ) ); ?>

		<span class="small"><?php _e('Powered by KeyPress', 'keypress-i18n' ); ?></span>
	</div>
	<style type="text/css">
		form#mainform {
			position: relative;
		}
		#keypress<?php echo static::PLUGIN_SLUG; ?>_status {
			position: absolute;
			top: 4rem;
			right: 1rem;
			padding: 1rem;
			border: 1px solid #c3c4c7;
			background-color: #dcdcde;
			color: #50575e;
		}

		div#keypress<?php echo static::PLUGIN_SLUG; ?>_status a i {
			padding-top: .3rem;
		}
		
        input.keypress_key {
            padding-left: 1.5rem;
        }

        p.license_action a {
            margin-left: 1.5rem;
            text-align: right;
            vertical-align: sub;
        }

        
	</style>
	<?php 
	}

	}



}