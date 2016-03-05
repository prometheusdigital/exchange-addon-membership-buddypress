<?php
/**
 * Contains the BP Group rule class.
 *
 * @since  1.2.1
 * @author iThemes
 */

/**
 * Class IT_Exchange_BuddyPress_Group_Rule
 */
class IT_Exchange_BuddyPress_Group_Rule extends IT_Exchange_Membership_Base_Content_Rule {

	/**
	 * @inheritdoc
	 */
	public function evaluate( IT_Exchange_User_Membership $user_membership, WP_Post $post ) {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function get_field_html( $context ) {

		ob_start();

		$selected = empty( $data[ 'term' ] ) ? false : $data[ 'term' ];

		$groups = groups_get_groups( array(
			'per_page'          => false,
			'show_hidden'       => true,
			'populate_extras'   => false,
			'update_meta_cache' => false
		) );

		?>

		<label for="<?php echo $context; ?>-buddypress-group" class="screen-reader-text">
			<?php _e( 'Select a group to restrict access to.', 'LION' ); ?>
		</label>

		<select class="it-exchange-membership-content-type-buddypress-group"
		        id="<?php echo $context; ?>-buddypress-group"
		        name="<?php echo $context; ?>[term]">

			<?php foreach ( $groups[ 'groups' ] as $group ): ?>
				<option value="<?php echo $group->id; ?>" <?php selected( $group->id, $selected ); ?>>
					<?php echo $group->name; ?>
				</option>
			<?php endforeach; ?>

		</select>


		<?php

		return ob_get_clean();
	}

	/**
	 * Save the data.
	 *
	 * @since 1.18
	 *
	 * @param array $data
	 *
	 * @return bool
	 *
	 * @throws UnexpectedValueException
	 * @throws InvalidArgumentException
	 */
	public function save( array $data = array() ) {

		$r2 = true;

		if ( $r1 = parent::save( $data ) ) {

			$rule = $this->data;

			if ( ! ( $rules = get_option( '_item-content-rule-buddypress-group-' . $rule[ 'term' ] ) ) ) {
				$rules = array();
			}

			if ( ! in_array( $this->get_membership()->ID, $rules ) ) {

				/**
				 * Fires when a buddypress group is added to the protection rules.
				 *
				 * @since 1.2.1
				 *
				 * @param int    $product_id
				 * @param string $group
				 * @param array  $rule
				 */
				do_action( 'it_exchange_membership_add_buddypress_group_rule', $this->get_membership()->ID, $this->get_term(), $rule );

				$rules[] = $this->get_membership()->ID;

				$r2 = update_option( '_item-content-rule-buddypress-group-' . $rule[ 'term' ], $rules );
			}
		}

		return $r1 && $r2;
	}

	/**
	 * Delete the rule from the database.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function delete() {

		$r1 = true;
		$r2 = parent::delete();

		$rule = $this->data;

		if ( ! ( $rules = get_option( '_item-content-rule-buddypress-group-' . $rule[ 'term' ] ) ) ) {
			$rules = array();
		}

		if ( false !== $key = array_search( $this->get_membership()->ID, $rules ) ) {

			/**
			 * Fires when a buddypress group is removed from the protection rules.
			 *
			 * @since 1.2.1
			 *
			 * @param int    $product_id
			 * @param string $group
			 * @param array  $rule
			 */
			do_action( 'it_exchange_membership_remove_buddypress_group_rule', $this->get_membership()->ID, $this->get_term(), $rule );

			unset( $rules[ $key ] );
			if ( empty( $rules ) ) {
				delete_option( '_item-content-rule-buddypress-group-' . $rule[ 'term' ] );
			} else {
				update_option( '_item-content-rule-buddypress-group-' . $rule[ 'term' ], $rules );
			}
		}

		return $r1 && $r2;
	}

	/**
	 * @inheritdoc
	 */
	public function get_type( $label = false ) {
		return $label ? __( 'BuddyPress Groups', 'LION' ) : 'bp-groups';
	}

	/**
	 * @inheritdoc
	 */
	public function matches_post( WP_Post $post ) {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function get_matching_posts( $number = 5 ) {
		return array();
	}

	/**
	 * @inheritdoc
	 */
	public function get_more_content_url() {

		$group = groups_get_group( array( 'group_id' => $this->get_term() ) );

		return bp_get_group_permalink( $group  );
	}

	/**
	 * @inheritdoc
	 */
	public function is_post_exempt( WP_Post $post ) {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function set_post_exempt( WP_Post $post, $exempt = true ) {
		// no-op
	}

	/**
	 * @inheritdoc
	 */
	public function get_selection( $label = false ) {
		return $label ? __( 'BuddyPress Group', 'LION' ) : 'bp-group';
	}

	/**
	 * @inheritdoc
	 */
	public function get_short_description() {

		$group = groups_get_group( array( 'group_id' => $this->get_term() ) );

		return bp_get_group_name( $group );
	}
}