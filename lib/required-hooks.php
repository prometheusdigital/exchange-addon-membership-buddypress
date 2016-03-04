<?php
/**
 * iThemes Exchange Membership BuddyPress Add-on
 * @package exchange-addon-membership-buddypress
 * @since   1.0.0
 */

/**
 * Enqueues Membership scripts to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @param string $hook_suffix WordPress passed variable
 *
 * @return void
 */
function it_exchange_membership_buddypress_addon_admin_wp_enqueue_scripts( $hook_suffix ) {
	if ( 'toplevel_page_bp-groups' === $hook_suffix ) {
		wp_enqueue_script( 'it-exchange-membership-addon-add-edit-group', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-group.js', array( 'jquery' ) );
	}
}

add_action( 'admin_enqueue_scripts', 'it_exchange_membership_buddypress_addon_admin_wp_enqueue_scripts' );

/**
 * Enqueues Membership styles to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_buddypress_addon_admin_wp_enqueue_styles() {
	global $hook_suffix;
	if ( 'toplevel_page_bp-groups' === $hook_suffix ) {
		wp_enqueue_style( 'it-exchange-membership-addon-add-edit-group', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-group.css' );
	}
}

add_action( 'admin_print_styles', 'it_exchange_membership_buddypress_addon_admin_wp_enqueue_styles' );

/**
 * Enqueues Membership scripts to WordPress frontend
 *
 * @since 1.0.0
 *
 * @param string $current_view WordPress passed variable
 *
 * @return void
 */
function it_exchange_membership_buddypress_addon_bp_enqueue_scripts() {
	// Frontend Membership Dashboard CSS & JS
	wp_enqueue_script( 'it-exchange-membership-buddypress-addon-public-js', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/buddypress.js' ), array( 'jquery' ), false, true );
	wp_enqueue_style( 'it-exchange-membership-buddypress-addon-public-css', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/styles/buddypress.css' ) );
}

add_action( 'bp_enqueue_scripts', 'it_exchange_membership_buddypress_addon_bp_enqueue_scripts' );

/**
 * Adds Membership Metabox Options to Edit Group admin screen
 *
 * @since 1.0.0
 *
 * @param int $group_id
 *
 * @return void
 */
function it_exchange_membership_buddypress_addon_bp_groups_admin_meta_boxes() {
	add_meta_box( 'bp_group_membership_access', _x( 'Membership Access', 'LION' ), 'it_exchange_membership_buddypress_addon_bp_groups_admin_edit_metabox_membership_access', get_current_screen()->id, 'side', 'core' );
}

add_action( 'bp_groups_admin_meta_boxes', 'it_exchange_membership_buddypress_addon_bp_groups_admin_meta_boxes' );

/**
 * Outputs Membership Metabox Options in Edit Group admin screen
 *
 * @since 1.0.0
 *
 * @param int $group_id
 *
 * @return void
 */
function it_exchange_membership_buddypress_addon_bp_groups_admin_edit_metabox_membership_access( $group ) {
	$group_rules = get_option( '_item-content-rule-buddypress-group-' . $group->id, array() );

	if ( empty( $group_rules ) ) {
		$hidden = 'hidden';
	} else {
		$hidden = '';
	}

	?>
	<div>
		<label for="it-exchange-group-membership-restriction">
			<input id="it-exchange-group-membership-restriction" type="checkbox" name="it-exchange-group-membership-restriction" <?php checked( ! empty( $group_rules ), true ); ?>/>
			<strong><?php _e( 'Restrict this group', 'buddypress' ); ?></strong>
		</label>
	</div>

	<div class="it-exchange-buddypress-group-memberships select <?php echo $hidden; ?>">

		<p><?php _e( 'Who can access this group?', 'LION' ); ?></p>

		<select multiple="multiple" name="it-exchange-group-memberships[]" size="5">
			<?php
			$membership_products = it_exchange_get_products( array(
				'product_type' => 'membership-product-type',
				'show_hidden'  => true,
				'numberposts'  => - 1
			) );
			foreach ( $membership_products as $membership ) {
				echo '<option value="' . $membership->ID . '" ' . selected( in_array( $membership->ID, $group_rules ), true, false ) . '>' . get_the_title( $membership->ID ) . '</option>';
			}
			?>
		</select>
	</div>
	<?php
}

/**
 * Shows the nag when needed.
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_buddypress_addon_show_version_nag() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if ( ! is_plugin_active( 'exchange-addon-membership/exchange-addon-membership.php' ) ) {
		?>
		<div id="it-exchange-add-on-required-plugin-nag" class="it-exchange-nag">
			<?php _e( 'The Membership BuddyPress add-on requires the iThemes Exchange Membership addon. Please install it.', 'LION' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function () {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery( "#it-exchange-add-on-required-plugin-nag" ).insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
				}
			} );
		</script>
		<?php
	}

	if ( ! is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		?>
		<div id="it-exchange-add-on-required-plugin-nag" class="it-exchange-nag">
			<?php _e( 'The Membership BuddyPress add-on requires BuddyPress plugin. Please install it.', 'LION' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function () {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery( "#it-exchange-add-on-required-plugin-nag" ).insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
				}
			} );
		</script>
		<?php
	}
}

add_action( 'admin_notices', 'it_exchange_membership_buddypress_addon_show_version_nag' );

/**
 * We check to see if that page is restricted or dripped per the Exchange Membership functions
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

		$post->ID = $it_exchange_membership_buddypress_addon_post_id; //BuddyPress sets this to 0 sometimes... we want it to be the  actual page ID though, which we set in the it_exchange_membership_buddypress_addon_fix_post_id() function.

		$it_exchange_membership_buddypress_addon_is_content_restricted = it_exchange_membership_addon_is_content_restricted();
		$it_exchange_membership_buddypress_addon_is_content_dripped    = it_exchange_membership_addon_is_content_dripped();
	}

	return $template;
}

add_filter( 'template_include', 'it_exchange_membership_buddypress_addon_template_include', 1 );

/**
 * BuddyPress modifies the Post ID (sets to 0 or -9999) for the non-WordPress pages off of
 * the Members and Activity and activity pages
 * (e.g. /members/lew or http://lew.dev.ithemes.com/members/test7/activity/mentions/)
 *
 * So, we hook into wp, grab the proper $post->ID before they possibly modify it
 * Then we set our own global to keep track of it (and reset it later)
 *
 * @since 1.1.0
 *
 * @return void
 */
function it_exchange_membership_buddypress_addon_fix_post_id() {
	if ( ! is_admin() ) {
		global $post, $it_exchange_membership_buddypress_addon_post_id;

		if ( $post ) {
			$it_exchange_membership_buddypress_addon_post_id = $post->ID;
		}
	}
}

add_action( 'wp', 'it_exchange_membership_buddypress_addon_fix_post_id', 1 );

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
			$post->ID = $it_exchange_membership_buddypress_addon_post_id; //BuddyPress sets this to 0 sometimes... we want it to be the  actual page ID though, which we set in the it_exchange_membership_buddypress_addon_fix_post_id() function.
			remove_filter( 'the_content', 'bp_replace_the_content' );
		}
	}

	return $content;
}

add_filter( 'the_content', 'it_exchange_membership_buddypress_addon_remove_bp_replace_the_content', 5 );

/**
 * Determine if we're on a group page and if it is restricted...
 *
 * @since 1.0.0
 *
 * @param bool  $restriction
 * @param array $member_access
 *
 * @return bool
 */
function it_exchange_membership_buddypress_addon_is_content_restricted( $restriction, $member_access ) {
	if ( ! $restriction ) { //If it's already restricted, just skip this...

		$bb_page_ids = bp_core_get_directory_page_ids();
		$members     = $bb_page_ids['members'] ? get_post( $bb_page_ids['members'] ) : null;

		if ( $members && ( it_exchange_membership_addon_is_content_restricted( $members ) || it_exchange_membership_addon_is_content_dripped( $members ) ) ) {

			if ( bp_is_user() ) {
				$restriction = true;
			}
		}

		if ( bp_is_group() ) {

			$current_group = groups_get_current_group();

			$group_rules = get_option( '_item-content-rule-buddypress-group-' . $current_group->id );

			if ( ! empty( $group_rules ) ) {

				if ( empty( $member_access ) ) {
					return true;
				}

				foreach ( $member_access as $product_id => $txn_id ) {
					if ( in_array( $product_id, $group_rules ) ) {
						return false;
					}
				}
				$restriction = true;
			}

		}

	}

	return $restriction;
}

add_filter( 'it_exchange_membership_addon_is_content_restricted', 'it_exchange_membership_buddypress_addon_is_content_restricted', 10, 2 );

/**
 * Output BuddyPress User Groups option as available content to restrict
 *
 * @since 1.0.0
 *
 * @param string $return
 * @param string $selection
 * @param string $selection_type
 *
 * @return string
 */
function it_exchange_membership_buddypress_addon_get_selections( $return, $selection, $selection_type ) {
	if ( function_exists( 'groups_get_groups' ) ) {
		if ( 'bp-groups' === $selection_type ) {
			$selected = 'selected="selected"';
		} else {
			$selected = '';
		}

		$return .= '<option data-type="bp-groups" value="bp-group" ' . $selected . '>' . __( 'BuddyPress User Groups', 'LION' ) . '</option>';
	}

	return $return;
}

add_filter( 'it_exchange_membership_addon_get_selections', 'it_exchange_membership_buddypress_addon_get_selections', 10, 3 );

/**
 * Output BuddyPress User Groups options as available content to restrict
 *
 * @since 1.0.0
 *
 * @return string
 */
function it_exchange_membership_buddypress_addon_get_custom_selected_options( $options, $value, $selected ) {
	//BuddyPress Groups
	if ( function_exists( 'groups_get_groups' ) && 'bp-groups' === $selected ) {
		$groups = groups_get_groups( array(
			'per_page'          => false,
			'show_hidden'       => true,
			'populate_extras'   => false,
			'update_meta_cache' => false
		) );
		foreach ( $groups['groups'] as $group ) {

			if ( $group->id === $value ) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}

			$options .= '<option data-type="bp-group" value="' . $group->id . '" ' . $selected . '>' . $group->name . '</option>';
		}
	}

	return $options;

}

add_filter( 'it_exchange_membership_addon_get_custom_selected_options', 'it_exchange_membership_buddypress_addon_get_custom_selected_options', 10, 3 );

function it_exchange_membership_buddypress_addon_update_content_access_rules_options( $product_id, $selected, $selection, $term ) {
	if ( 'bp-groups' === $selected ) {
		if ( ! ( $rules = get_option( '_item-content-rule-buddypress-group-' . $term ) ) ) {
			$rules = array();
		}

		if ( ! in_array( $product_id, $rules ) ) {
			$rules[] = $product_id;
			update_option( '_item-content-rule-buddypress-group-' . $term, $rules );
		}
	}

}

add_action( 'it_exchange_membership_addon_update_content_access_rules_options', 'it_exchange_membership_buddypress_addon_update_content_access_rules_options', 10, 4 );

function it_exchange_membership_buddypress_addon_update_content_access_diff_rules_options( $product_id, $selected, $selection, $term ) {
	if ( 'bp-groups' === $selected ) {
		if ( ! ( $rules = get_option( '_item-content-rule-buddypress-group-' . $term ) ) ) {
			$rules = array();
		}

		if ( false !== $key = array_search( $product_id, $rules ) ) {
			unset( $rules[ $key ] );
			if ( empty( $rules ) ) {
				delete_option( '_item-content-rule-buddypress-group-' . $term );
			} else {
				update_option( '_item-content-rule-buddypress-group-' . $term, $rules );
			}
		}
	}
}

add_action( 'it_exchange_membership_addon_update_content_access_diff_rules_options', 'it_exchange_membership_buddypress_addon_update_content_access_diff_rules_options', 10, 4 );


/**
 * This adds the BuddyPress restricted content to the Membership Dashboard
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_buddpress_addon_membership_content_dashboard_empty_restricted_posts_result( $result, $options, $selection, $selected, $value ) {

	if ( 'bp-group' === $selection ) {

		if ( $group_id = groups_get_id( $value ) ) {

			$args = array( 'group_id' => $group_id );

			if ( $group = groups_get_group( $args ) ) {

				$result .= $options['before'];

				$result .= '<li>';
				$result .= '<div class="it-exchange-content-group it-exchange-content-single">';
				$result .= '	<div class="it-exchange-content-item-icon">';
				$result .= '		<a class="it-exchange-item-icon" href="' . bp_get_group_permalink( $group ) . '"></a>';
				$result .= '	</div>';
				$result .= '	<div class="it-exchange-content-item-info">';
				$result .= '		<p class="it-exchange-group-content-label">';
				$result .= '			<a href="' . bp_get_group_permalink( $group ) . '">';
				$result .= '				<span class="it-exchange-item-title">' . $group->name . '</span>';
				$result .= '			</a>';
				$result .= '		</p>';
				$result .= '	</div>';
				$result .= '</div>';
				$result .= '</li>';

				$result .= $options['after'];

			}

		}

	}

	return $result;

}

add_filter( 'it_exchange_membership_addon_membership_content_dashboard_empty_restricted_posts_result', 'it_exchange_membership_buddpress_addon_membership_content_dashboard_empty_restricted_posts_result', 10, 5 );

/**
 * This adds the BuddyPress restricted content to the Membership Shortcode
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_buddpress_addon_membership_content_shortcode_empty_restricted_posts_result( $result, $atts, $selection, $selected, $value ) {

	if ( 'bp-group' === $selection ) {

		if ( $group_id = groups_get_id( $value ) ) {

			$args = array( 'group_id' => $group_id );

			if ( $group = groups_get_group( $args ) ) {

				$result .= $atts['before'];

				$result .= '<li>';
				$result .= '<div class="it-exchange-content-group it-exchange-content-single it-exchange-content-available">';
				$result .= '	<div class="it-exchange-content-item-icon"><span class="it-exchange-item-icon"></span></div>';
				$result .= '	<div class="it-exchange-content-item-info"><p class="it-exchange-group-content-label">' . $group->name . '</p></div>';
				$result .= '</div>';
				$result .= '</li>';

				$result .= $atts['after'];

			}

		}

	}

	return $result;

}

add_filter( 'it_exchange_membership_addon_membership_content_shortcode_empty_restricted_posts_result', 'it_exchange_membership_buddpress_addon_membership_content_shortcode_empty_restricted_posts_result', 10, 5 );

/**
 * Displays Membership Options on Create Group templates
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_buddpress_addon_bp_after_group_settings_creation_step() {
	global $bp;

	$checked = '';

	if ( ! empty( $bp->groups->new_group_id ) ) {
		$group_id = $bp->groups->new_group_id;
	} else if ( ! empty( $_COOKIE['bp_new_group_id'] ) ) {
		$group_id = $_COOKIE['bp_new_group_id'];
	} else {
		$group_id = false;
	}

	$group_rules = get_option( '_item-content-rule-buddypress-group-' . $group_id, array() );

	if ( empty( $group_rules ) ) {
		$hidden = 'it-exchange-hidden';
	} else {
		$hidden = '';
	}

	?>
	<h4><?php _e( 'iThemes Exchange Membership Options', 'LION' ); ?></h4>

	<div>
		<label for="it-exchange-group-membership-restriction">
			<input id="it-exchange-group-membership-restriction" type="checkbox" name="it-exchange-group-membership-restriction" <?php checked( ! empty( $group_rules ), true ); ?>/>
			<strong><?php _e( 'Restrict this group', 'buddypress' ); ?></strong>
		</label>
	</div>

	<div class="it-exchange-buddypress-group-memberships select <?php echo $hidden; ?>">

		<p><?php _e( 'Who can access this group?', 'LION' ); ?></p>

		<select multiple="multiple" name="it-exchange-group-memberships[]" size="5">
			<?php
			$membership_products = it_exchange_get_products( array(
				'product_type' => 'membership-product-type',
				'show_hidden'  => true
			) );
			foreach ( $membership_products as $membership ) {
				echo '<option value="' . $membership->ID . '" ' . selected( in_array( $membership->ID, $group_rules ), true, false ) . '>' . get_the_title( $membership->ID ) . '</option>';
			}
			?>
		</select>
	</div>
	<?php
}

add_action( 'bp_after_group_settings_creation_step', 'it_exchange_membership_buddpress_addon_bp_after_group_settings_creation_step' );

/**
 * Saves Membership Options from Create Group templates
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_buddpress_addon_groups_create_group_step_save_group_settings() {

	if ( isset( $_POST['group_id'] ) ) {

		$group_id    = $_POST['group_id'];
		$group_rules = get_option( '_item-content-rule-buddypress-group-' . $group_id, array() );

		$membership_products = it_exchange_get_products( array(
			'product_type' => 'membership-product-type',
			'show_hidden'  => true
		) );

		foreach ( $membership_products as $membership ) {

			$existing_access_rules      = it_exchange_get_product_feature( $membership->ID, 'membership-content-access-rules' );
			$membership_product_feature = it_exchange_get_product_feature( $membership->ID, 'membership-content-access-rules' );
			$memberships                = ! empty( $_POST['it-exchange-group-memberships'] ) ? $_POST['it-exchange-group-memberships'] : array();
			if ( empty( $_POST['it-exchange-group-membership-restriction'] ) ) {
				$memberships = array();
			}

			if ( in_array( $membership->ID, $memberships ) ) {

				$value = array(
					'selection' => 'bp-group',
					'selected'  => 'bp-groups',
					'term'      => $group_id,
				);
				if ( false === array_search( $value, $membership_product_feature ) ) {
					$membership_product_feature[] = $value;
					it_exchange_update_product_feature( $membership->ID, 'membership-content-access-rules', $membership_product_feature );
					it_exchange_membership_buddypress_addon_update_content_access_rules_options( $membership->ID, 'bp-groups', 'bp-group', $group_id );
				}

			} else {

				$value = array(
					'selection' => 'bp-group',
					'selected'  => 'bp-groups',
					'term'      => $group_id,
				);
				if ( false !== $key = array_search( $value, $membership_product_feature ) ) {
					unset( $membership_product_feature[ $key ] );
					it_exchange_update_product_feature( $membership->ID, 'membership-content-access-rules', $membership_product_feature );
					it_exchange_membership_buddypress_addon_update_content_access_diff_rules_options( $membership->ID, 'bp-groups', 'bp-group', $group_id );
				}
			}
		}

	}

}

add_action( 'groups_create_group_step_save_group-settings', 'it_exchange_membership_buddpress_addon_groups_create_group_step_save_group_settings' );

/**
 * Saves Membership Options from Edit Group admin screen
 *
 * @since 1.0.0
 *
 * @param int $group_id
 *
 * @return void
 */
function it_exchange_membership_buddpress_addon_bp_group_admin_edit_after( $group_id ) {

	$group_rules = get_option( '_item-content-rule-buddypress-group-' . $group_id, array() );

	$membership_products = it_exchange_get_products( array(
		'product_type' => 'membership-product-type',
		'show_hidden'  => true
	) );

	foreach ( $membership_products as $membership ) {

		$existing_access_rules      = it_exchange_get_product_feature( $membership->ID, 'membership-content-access-rules' );
		$membership_product_feature = it_exchange_get_product_feature( $membership->ID, 'membership-content-access-rules' );
		$memberships                = ! empty( $_POST['it-exchange-group-memberships'] ) ? $_POST['it-exchange-group-memberships'] : array();
		if ( empty( $_POST['it-exchange-group-membership-restriction'] ) ) {
			$memberships = array();
		}

		if ( in_array( $membership->ID, $memberships ) ) {

			$value = array(
				'selection' => 'bp-group',
				'selected'  => 'bp-groups',
				'term'      => $group_id,
			);
			if ( false === array_search( $value, $membership_product_feature ) ) {
				$membership_product_feature[] = $value;
				it_exchange_update_product_feature( $membership->ID, 'membership-content-access-rules', $membership_product_feature );
				it_exchange_membership_buddypress_addon_update_content_access_rules_options( $membership->ID, 'bp-groups', 'bp-group', $group_id );
			}

		} else {

			$value = array(
				'selection' => 'bp-group',
				'selected'  => 'bp-groups',
				'term'      => $group_id,
			);
			if ( false !== $key = array_search( $value, $membership_product_feature ) ) {
				unset( $membership_product_feature[ $key ] );
				it_exchange_update_product_feature( $membership->ID, 'membership-content-access-rules', $membership_product_feature );
				it_exchange_membership_buddypress_addon_update_content_access_diff_rules_options( $membership->ID, 'bp-groups', 'bp-group', $group_id );
			}
		}
	}

}

add_action( 'bp_group_admin_edit_after', 'it_exchange_membership_buddpress_addon_bp_group_admin_edit_after' );
