<?php
/**
 * iThemes Exchange Membership BuddyPress Add-on
 *
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
 * @return void
 */
function it_exchange_membership_buddypress_addon_bp_groups_admin_meta_boxes() {
	add_meta_box( 'bp_group_membership_access', _x( 'Membership Access', 'LION' ),
		'it_exchange_membership_buddypress_addon_bp_groups_admin_edit_metabox_membership_access',
		get_current_screen()->id, 'side', 'core' );
}

add_action( 'bp_groups_admin_meta_boxes', 'it_exchange_membership_buddypress_addon_bp_groups_admin_meta_boxes' );

/**
 * Outputs Membership Metabox Options in Edit Group admin screen
 *
 * @since 1.0.0
 *
 * @param BP_Groups_Group $group
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
			<input id="it-exchange-group-membership-restriction" type="checkbox"
			       name="it-exchange-group-membership-restriction" <?php checked( ! empty( $group_rules ), true ); ?>/>
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

	if ( ! defined( 'ITE_MEMBERSHIP_PLUGIN_VERSION' ) || version_compare( ITE_MEMBERSHIP_PLUGIN_VERSION, '1.19.8', '<' ) ) {
		?>
		<div id="it-exchange-add-on-required-plugin-nag" class="it-exchange-nag">
			<?php _e( 'The Membership BuddyPress add-on requires the iThemes Exchange Membership addon version 1.19.8 or greater. Please install it or upgrade your version.', 'LION' ); ?>
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
 * @param string $template
 *
 * @return string
 */
function it_exchange_membership_buddypress_addon_template_include( $template ) {
	if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {

		global $it_exchange_membership_buddypress_addon_is_content_restricted,
		       $it_exchange_membership_buddypress_addon_is_content_dripped,
		       $post, $it_exchange_membership_buddypress_addon_post_id;

		//BuddyPress sets this to 0 sometimes... we want it to be the  actual page ID though, which we set in the it_exchange_membership_buddypress_addon_fix_post_id() function.
		$post->ID = $it_exchange_membership_buddypress_addon_post_id;

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
 * @param string $content
 *
 * @return string
 */
function it_exchange_membership_buddypress_addon_remove_bp_replace_the_content( $content ) {
	if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {
		global $it_exchange_membership_buddypress_addon_is_content_restricted,
		       $it_exchange_membership_buddypress_addon_is_content_dripped,
		       $post,
		       $it_exchange_membership_buddypress_addon_post_id;

		if ( $it_exchange_membership_buddypress_addon_is_content_restricted || $it_exchange_membership_buddypress_addon_is_content_dripped ) {
			$post->ID = $it_exchange_membership_buddypress_addon_post_id; //BuddyPress sets this to 0 sometimes... we want it to be the  actual page ID though, which we set in the it_exchange_membership_buddypress_addon_fix_post_id() function.
			remove_filter( 'the_content', 'bp_replace_the_content' );
		}
	}

	return $content;
}

add_filter( 'the_content', 'it_exchange_membership_buddypress_addon_remove_bp_replace_the_content', 5 );

/**
 * Check if this is a user page that should be restricted.
 *
 * @ssince 1.0
 *
 * @param bool $restricted
 *
 * @return bool
 */
function it_exchange_membership_buddypress_addon_is_content_restricted( $restricted ) {

	if ( $restricted ) {
		return $restricted;
	}

	$bb_page_ids = bp_core_get_directory_page_ids();
	$members     = $bb_page_ids['members'] ? get_post( $bb_page_ids['members'] ) : null;

	if ( bp_is_user() ) {
		$evaluator = new IT_Exchange_Membership_Rule_Evaluator_Service(
			new IT_Exchange_Membership_Rule_Factory(), new IT_Exchange_User_Membership_Repository()
		);

		$customer = it_exchange_get_current_customer();

		if ( $evaluator->evaluate_content( $members, $customer ? $customer : null ) ) {
			$restricted = true;
		}
	}

	return $restricted;
}

add_filter( 'it_exchange_membership_addon_is_content_restricted', 'it_exchange_membership_buddypress_addon_is_content_restricted' );

/**
 * Determine if we're on a group page and if it is restricted...
 *
 * @since 1.2.1
 *
 * @param array $failed_rules
 *
 * @return array
 */
function it_exchange_membership_buddypress_addon_modify_failed_rules( $failed_rules ) {

	if ( ! empty( $failed_rules ) ) {
		return $failed_rules;
	}

	if ( bp_is_group() ) {

		$current_group = groups_get_current_group();

		$factory = new IT_Exchange_Membership_Rule_Factory();

		$group_rules = get_option( '_item-content-rule-buddypress-group-' . $current_group->id, array() );

		foreach ( $group_rules as $product ) {

			$membership = it_exchange_get_product( $product );

			if ( ! $membership instanceof IT_Exchange_Membership ) {
				continue;
			}

			$membership_rules = $factory->make_all_for_membership( $membership, 'bp-groups' );

			foreach ( $membership_rules as $membership_rule ) {
				if ( $membership_rule->get_term() == $current_group->id ) {
					if ( ! it_exchange_membership_addon_is_customer_member_of( $membership->ID ) ) {
						$failed_rules[] = $membership_rule;
					}
				}
			}
		}

		if ( count( $failed_rules ) < count( $group_rules ) ) {
			return array();
		}
	}

	return $failed_rules;
}

add_filter( 'it_exchange_membership_addon_is_content_restricted_failed_rules', 'it_exchange_membership_buddypress_addon_modify_failed_rules', 10 );

/**
 * Register our BuddyPress Group rule with Exchange Membership.
 *
 * @since 1.2.1
 *
 * @param array $rules
 * @param bool  $flat
 *
 * @return array
 */
function it_exchange_membership_buddypress_register_rule( $rules, $flat ) {

	if ( function_exists( 'groups_get_groups' ) ) {
		require_once dirname( __FILE__ ) . '/class.rule.php';

		if ( $flat ) {
			$rules[] = new IT_Exchange_BuddyPress_Group_Rule();
		} else {
			$rules[] = array( new IT_Exchange_BuddyPress_Group_Rule() );
		}
	}

	return $rules;
}

add_filter( 'it_exchange_membership_addon_get_content_rules', 'it_exchange_membership_buddypress_register_rule', 10, 2 );

/**
 * Register the BuddyPress rule with the rule factory.
 *
 * @since 1.2.1
 *
 * @param IT_Exchange_Membership_Content_Rule|null $rule
 * @param string                                   $type
 * @param array                                    $data
 * @param IT_Exchange_Membership                   $membership
 *
 * @return IT_Exchange_Membership_Content_Rule
 */
function it_exchange_membership_buddypress_rule_factory( $rule, $type, $data, $membership ) {

	if ( $rule ) {
		return $rule;
	}

	if ( $type === 'bp-groups' && function_exists( 'groups_get_groups' ) ) {
		require_once dirname( __FILE__ ) . '/class.rule.php';
		$rule = new IT_Exchange_BuddyPress_Group_Rule( $membership, $data );
	}

	return $rule;
}

add_filter( 'it_exchange_membership_rule_factory_make_rule', 'it_exchange_membership_buddypress_rule_factory', 10, 4 );

/**
 * Displays Membership Options on Create Group templates
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_buddpress_addon_bp_after_group_settings_creation_step() {
	global $bp;

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
			<input id="it-exchange-group-membership-restriction" type="checkbox"
			       name="it-exchange-group-membership-restriction" <?php checked( ! empty( $group_rules ), true ); ?>/>
			<strong><?php _e( 'Restrict this group', 'LION' ); ?></strong>
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

		require_once dirname( __FILE__ ) . '/class.rule.php';

		$group_id = $_POST['group_id'];

		$old_memberships = get_option( '_item-content-rule-buddypress-group-' . $group_id, array() );
		$new_memberships = ! empty( $_POST['it-exchange-group-memberships'] ) ? $_POST['it-exchange-group-memberships'] : array();

		$factory = new IT_Exchange_Membership_Rule_Factory();

		foreach ( $new_memberships as $new_membership ) {
			if ( ! in_array( $new_membership, $old_memberships ) ) {
				$membership = it_exchange_get_product( $new_membership );

				if ( $membership instanceof IT_Exchange_Membership ) {
					$rule = new IT_Exchange_BuddyPress_Group_Rule( $membership, array(
						'term' => $group_id
					) );
					$rule->save();
				}
			}
		}

		foreach ( $old_memberships as $old_membership ) {
			if ( ! in_array( $old_membership, $new_memberships ) ) {
				$membership = it_exchange_get_product( $old_membership );

				if ( $membership instanceof IT_Exchange_Membership ) {
					$membership_rules = $factory->make_all_for_membership( $membership, 'bp-groups' );

					foreach ( $membership_rules as $rule ) {
						if ( $rule->get_term() == $group_id ) {
							$rule->delete();
						}
					}
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

	$old_memberships = get_option( '_item-content-rule-buddypress-group-' . $group_id, array() );
	$new_memberships = ! empty( $_POST['it-exchange-group-memberships'] ) ? $_POST['it-exchange-group-memberships'] : array();

	$factory = new IT_Exchange_Membership_Rule_Factory();

	require_once dirname( __FILE__ ) . '/class.rule.php';

	foreach ( $new_memberships as $new_membership ) {
		if ( ! in_array( $new_membership, $old_memberships ) ) {
			$membership = it_exchange_get_product( $new_membership );

			if ( $membership instanceof IT_Exchange_Membership ) {
				$rule = new IT_Exchange_BuddyPress_Group_Rule( $membership, array(
					'term' => $group_id
				) );
				$rule->save();
			}
		}
	}

	foreach ( $old_memberships as $old_membership ) {
		if ( ! in_array( $old_membership, $new_memberships ) ) {
			$membership = it_exchange_get_product( $old_membership );

			if ( $membership instanceof IT_Exchange_Membership ) {
				$membership_rules = $factory->make_all_for_membership( $membership, 'bp-groups' );

				foreach ( $membership_rules as $rule ) {
					if ( $rule->get_term() == $group_id ) {
						$rule->delete();
					}
				}
			}
		}
	}
}

add_action( 'bp_group_admin_edit_after', 'it_exchange_membership_buddpress_addon_bp_group_admin_edit_after' );
