<?php
/*
 * Plugin Name: ExchangeWP - Membership BuddyPress Add-on
 * Version: 0.0.1
 * Description: Adds the ExchangeWP Membership management functionality to BuddyPress
 * Plugin URI: https://exchangewp.com/downloads/membership-buddypress/
 * Author: ExchangeWP
 * Author URI: https://exchangewp.com
 * ExchangeWP Package: exchange-addon-membership-buddypress

 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

define( 'ITE_MEMBERSHIP_BUDDYPRESS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * This registers our plugin as a membership addon
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_register_membership_buddypress_addon() {
	$options = array(
		'name'              => __( 'Membership for BuddyPress', 'LION' ),
		'description'       => __( 'Add Memberships functionality to the BuddyPress plugin.', 'LION' ),
		'author'            => 'ExchangeWP',
		'author_url'        => 'http://exchangewp.com/downloads/membership-buddypress/',
		'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/buddypress50px.png' ),
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'other',
		'basename'          => plugin_basename( __FILE__ ),
		'settings-callback' => 'it_exchange_membership_buddypress_addon_settings_callback',
		'labels'      => array(
			'singular_name' => __( 'Membership BuddyPress', 'LION' ),
		),
	);
	it_exchange_register_addon( 'membership-buddypress', $options );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_membership_buddypress_addon' );

/**
 * Loads the translation data for WordPress
 *
 * @uses load_plugin_textdomain()
 * @since 1.0.3
 * @return void
*/
function it_exchange_membership_buddypress_set_textdomain() {
	load_plugin_textdomain( 'LION', false, dirname( plugin_basename( __FILE__  ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'it_exchange_membership_buddypress_set_textdomain' );

/**
 * Registers Plugin with iThemes updater class
 *
 * @since 1.0.0
 *
 * @param object $updater ithemes updater object
 * @return void
*/
function exchange_membership_buddypress_plugin_updater() {

	$license_check = get_transient( 'exchangewp_license_check' );

	if ($license_check->license == 'valid' ) {
		$license_key = it_exchange_get_option( 'exchangewp_licenses' );
		$license = $license_key['exchange_license'];

		$edd_updater = new EDD_SL_Plugin_Updater( 'https://exchangewp.com', __FILE__, array(
				'version' 		=> '0.0.1', 				// current version number
				'license' 		=> $license, 				// license key (used get_option above to retrieve from DB)
				'item_id'		 	=> 591, 					  // name of this plugin
				'author' 	  	=> 'ExchangeWP',    // author of this plugin
				'url'       	=> home_url(),
				'wp_override' => true,
				'beta'		  	=> false
			)
		);
	}

}

add_action( 'admin_init', 'exchange_membership_buddypress_plugin_updater', 0 );
