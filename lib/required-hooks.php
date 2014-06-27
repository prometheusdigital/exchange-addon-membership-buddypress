<?php
/**
 * iThemes Exchange Membership BuddyPress Add-on
 * @package exchange-addon-membership-buddypress
 * @since 1.0.0
*/

/**
 * Shows the nag when needed.
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_buddypress_addon_show_version_nag() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	if ( !is_plugin_active( 'exchange-addon-membership/exchange-addon-membership.php' ) ) {
		?>
		<div id="it-exchange-add-on-required-plugin-nag" class="it-exchange-nag">
			<?php _e( 'The Membership BuddyPress add-on requires the iThemes Exchange Membership addon. Please install it.', 'LION' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery("#it-exchange-add-on-required-plugin-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
				}
			});
		</script>
		<?php
	}
	
	if ( !is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		?>
		<div id="it-exchange-add-on-required-plugin-nag" class="it-exchange-nag">
			<?php _e( 'The Membership BuddyPress add-on requires BuddyPress plugin. Please install it.', 'LION' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery("#it-exchange-add-on-required-plugin-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
				}
			});
		</script>
		<?php
	}
}
add_action( 'admin_notices', 'it_exchange_membership_buddypress_addon_show_version_nag' );

/**
 * BuddyPress modifies the Post ID (sets to 0 or -9999) for the non-WordPress pages off of 
 * the Members and Activity and activity pages 
 * (e.g. /members/lew or http://lew.dev.ithemes.com/members/test7/activity/mentions/)
 *
 * So, we hook into template_include, grab the proper $post->ID before they possibly modify it
 * Then we set our own global to keep track of it (and set it later)
 * We also check to see if that page is restricted or dripped per the Exchange Membership functions
 * And we return the $template without modifying at all.
 * 
 * Later we hook into 'the_content', at which point we check to see if the restricted or dripped
 * globals are true and if so, we remove the buddypress content because Exchange Membership
 * hooks into the_content to supply the restricted/dripped messages
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_buddypress_addon_template_include( $template ) {
	if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {
		global $it_exchange_membership_buddypress_addon_is_content_restricted, $it_exchange_membership_buddypress_addon_is_content_dripped, $post, $it_exchange_membership_buddypress_addon_post_id;
		$it_exchange_membership_buddypress_addon_is_content_restricted = $it_exchange_membership_buddypress_addon_is_content_dripped = false;
		$it_exchange_membership_buddypress_addon_post_id = $post->ID;
		
		if ( it_exchange_membership_addon_is_content_restricted() ) {
			$it_exchange_membership_buddypress_addon_is_content_restricted = true;
		}
					
		if ( it_exchange_membership_addon_is_content_dripped() ) {
			$it_exchange_membership_buddypress_addon_is_content_dripped = true;
		}
	}
	return $template;
}
add_filter( 'template_include', 'it_exchange_membership_buddypress_addon_template_include', 1 );

/**
 * We hook into 'the_content' (earlier than BuddyPress), so we check to see if the restricted or dripped
 * globals are true and if so, we remove the buddypress content filter because Exchange Membership
 * hooks into the_content to supply the restricted/dripped messages
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_buddypress_addon_remove_bp_replace_the_content( $content ) {
	if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {
		global $it_exchange_membership_buddypress_addon_is_content_restricted, $it_exchange_membership_buddypress_addon_is_content_dripped, $post, $it_exchange_membership_buddypress_addon_post_id;
		
		if ( $it_exchange_membership_buddypress_addon_is_content_restricted || $it_exchange_membership_buddypress_addon_is_content_dripped ) {
			$post->ID = $it_exchange_membership_buddypress_addon_post_id; //BuddyPress sets this to 0 sometimes... we want it to be the  actual page ID though, which we set in the it_exchange_membership_buddypress_addon_template_include() function.
			remove_filter( 'the_content', 'bp_replace_the_content' );
		}
	}
		
	return $content;
}
add_filter( 'the_content', 'it_exchange_membership_buddypress_addon_remove_bp_replace_the_content', 5 );
