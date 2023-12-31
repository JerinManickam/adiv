<?php
/**
 * User Repository class.
 *
 * @since 1.0.0
 *
 * @package Masteriyo\Repository;
 */

namespace Masteriyo\Repository;

use Masteriyo\Models\User;
use Masteriyo\Database\Model;
use Masteriyo\ModelException;

/**
 * UserRepository class.
 */
class UserRepository extends AbstractRepository implements RepositoryInterface {

	/**
	 * Meta type.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $meta_type = 'user';

	/**
	 * Data stored in meta keys, but not considered "meta".
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'first_name'           => 'first_name',
		'last_name'            => 'last_name',
		'display_name'         => 'display_name',
		'show_admin_bar_front' => 'show_admin_bar_front',
		'use_ssl'              => 'use_ssl',
		'admin_color'          => 'admin_color',
		'rich_editing'         => 'rich_editing',
		'comment_shortcuts'    => 'comment_shortcuts',
		'syntax_highlighting'  => 'syntax_highlighting',
		'nickname'             => 'nickname',
		'description'          => 'description',
		'approved'             => '_approved',

		// Billing fields.
		'billing_first_name'   => '_billing_first_name',
		'billing_last_name'    => '_billing_last_name',
		'billing_company'      => '_billing_company',
		'billing_address_1'    => '_billing_address_1',
		'billing_address_2'    => '_billing_address_2',
		'billing_city'         => '_billing_city',
		'billing_postcode'     => '_billing_postcode',
		'billing_country'      => '_billing_country',
		'billing_state'        => '_billing_state',
		'billing_email'        => '_billing_email',
		'billing_phone'        => '_billing_phone',
	);

	/**
	 * Create a user in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param Model $user User object.
	 */
	public function create( Model &$user ) {
		if ( ! $user->get_date_created( 'edit' ) ) {
			$user->set_date_created( current_time( 'mysql', true ) );
		}

		if ( ! $user->get_nickname() ) {
			$user->set_nickName( $user->get_username( 'edit' ) );
		}

		$id = wp_insert_user(
			apply_filters(
				'masteriyo_new_user_data',
				array(
					'user_login'          => $user->get_username( 'edit' ),
					'user_pass'           => $user->get_password( 'edit' ),
					'user_nicename'       => $user->get_nicename( 'edit' ),
					'user_email'          => $user->get_email( 'edit' ),
					'user_url'            => $user->get_url( 'edit' ),
					'user_registered'     => $user->get_date_created( 'edit' ),
					'user_activation_key' => $user->get_activation_key( 'edit' ),
					'user_status'         => $user->get_status( 'edit' ),
					'display_name'        => $user->get_display_name( 'edit' ),
					'role'                => empty( $user->get_roles( 'edit' ) ) ? 'masteriyo_student' : current( $user->get_roles( 'edit' ) ),
				),
				$user
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			// Append roles if there are more than one roles.
			if ( count( $user->get_roles( 'edit' ) ) > 1 ) {
				$wp_user = get_user_by( 'id', $id );
				foreach ( $user->get_roles( 'edit' ) as $role ) {
					$wp_user->add_role( $role );
				}
			}

			$user->set_id( $id );
			$this->update_user_meta( $user, true );
			$user->apply_changes();

			do_action( 'masteriyo_new_user', $id, $user );
		}

	}

	/**
	 * Read a user.
	 *
	 * @since 1.0.0
	 *
	 * @param Model $user User object.
	 *
	 * @throws ModelException If invalid user.
	 */
	public function read( Model &$user ) {
		$user_obj = get_user_by( 'id', $user->get_id() );

		if ( ! $user->get_id() || ! $user_obj ) {
			throw new ModelException(
				'masteriyo_invalid_user_id',
				__( 'Invalid user.', 'masteriyo' ),
				400
			);
		}

		$user->set_props(
			array(
				'username'       => $user_obj->data->user_login,
				'password'       => $user_obj->data->user_pass,
				'nicename'       => $user_obj->data->user_nicename,
				'email'          => $user_obj->data->user_email,
				'url'            => $user_obj->data->user_url,
				'date_created'   => $user_obj->data->user_registered,
				'activation_key' => $user_obj->data->user_activation_key,
				'status'         => $user_obj->data->user_status,
				'display_name'   => $user_obj->data->display_name,
				'roles'          => ! empty( $user_obj->roles ) ? $user_obj->roles : array( 'masteriyo_student' ),
			)
		);

		$this->read_user_data( $user );
		$this->read_extra_data( $user );
		$user->set_object_read( true );

		do_action( 'masteriyo_user_read', $user->get_id(), $user );
	}

	/**
	 * Update a user in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param Model $user User object.
	 *
	 * @return void
	 */
	public function update( Model &$user ) {
		$changes        = $user->get_changes();
		$user_data_keys = array(
			'username',
			'nicename',
			'email',
			'url',
			'date_created',
			'status',
			'display_name',
			'roles',
		);

		// Only update the user when the user data changes.
		if ( array_intersect( $user_data_keys, array_keys( $changes ) ) ) {
			$user_data = array(
				'user_login'          => $user->get_username( 'edit' ),
				'user_nicename'       => $user->get_nicename( 'edit' ),
				'user_email'          => $user->get_email( 'edit' ),
				'user_url'            => $user->get_url( 'edit' ),
				'user_activation_key' => $user->get_activation_key( 'edit' ),
				'user_status'         => $user->get_status( 'edit' ),
				'display_name'        => $user->get_display_name( 'edit' ),
				'role'                => empty( $user->get_roles( 'edit' ) ) ? 'masteriyo_student' : current( $user->get_roles( 'edit' ) ),
			);

			wp_update_user( array_merge( array( 'ID' => $user->get_id() ), $user_data ) );

			// Append roles if there are more than one roles.
			if ( count( $user->get_roles( 'edit' ) ) > 1 ) {
				$wp_user = get_user_by( 'id', $id );
				foreach ( $user->get_roles( 'edit' ) as $role ) {
					$wp_user->add_role( $role );
				}
			}
		}

		// Only update password if a new one was set with set_password.
		if ( $user->get_password() ) {
			wp_update_user(
				array(
					'ID'        => $user->get_id(),
					'user_pass' => $user->get_password(),
				)
			);
			$user->set_password( '' );
		}

		$this->update_user_meta( $user );
		$user->apply_changes();

		do_action( 'masteriyo_update_user', $user->get_id(), $user );
	}

	/**
	 * Delete a user from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param Model $user User object.
	 * @param array $args Array of args to pass.alert-danger.
	 */
	public function delete( Model &$user, $args = array() ) {
		$id          = $user->get_id();
		$object_type = $user->get_object_type();
		$args        = array_merge(
			array(
				'reassign' => null,
			),
			$args
		);

		if ( ! $id ) {
			return;
		}

		do_action( 'masteriyo_before_delete_' . $object_type, $id, $user );
		wp_delete_user( $id, $args['reassign'] );

		$user->set_id( 0 );

		do_action( 'masteriyo_after_delete_' . $object_type, $id, $user );
	}

	/**
	 * Read user data. Can be overridden by child classes to load other props.
	 *
	 * @since 1.0.0
	 *
	 * @param User $user User object.
	 */
	protected function read_user_data( &$user ) {
		$id          = $user->get_id();
		$meta_values = $this->read_meta( $user );

		$set_props = array();

		$meta_values = array_reduce(
			$meta_values,
			function( $result, $meta_value ) {
				$result[ $meta_value->key ][] = $meta_value->value;
				return $result;
			},
			array()
		);

		foreach ( $this->internal_meta_keys as $prop => $meta_key ) {
			$meta_value         = isset( $meta_values[ $meta_key ][0] ) ? $meta_values[ $meta_key ][0] : null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value ); // get_user_meta only unserializes single values.
		}

		$user->set_props( $set_props );
	}

	/**
	 * Read extra data associated with the user.
	 *
	 * @since 1.0.0
	 *
	 * @param User $user User object.
	 */
	protected function read_extra_data( &$user ) {
		$meta_values = $this->read_meta( $user );

		$meta_values = array_reduce(
			$meta_values,
			function( $result, $meta_value ) {
				$result[ $meta_value->key ] = $meta_value->value;
				return $result;
			},
			array()
		);

		foreach ( $user->get_extra_data_keys() as $key ) {
			$function = 'set_' . $key;
			if ( is_callable( array( $user, $function ) )
				&& isset( $meta_values[ '_' . $key ] ) ) {
				$user->{$function}( $meta_values[ '_' . $key ] );
			}
		}
	}

	/**
	 * Helper method that updates all the user meta for a model based on it's settings in the Model class.
	 *
	 * @since 1.0.0
	 *
	 * @param Model $model model object.
	 * @param bool  $force Force update. Used during create.
	 */
	protected function update_user_meta( &$model, $force = false ) {
		// Make sure to take extra data into account.
		$extra_data_keys   = $model->get_extra_data_keys();
		$meta_key_to_props = $this->get_internal_meta_keys();

		foreach ( $extra_data_keys as $key ) {
			$meta_key_to_props[ $key ] = '_' . $key;
		}

		$meta_key_to_props = array_merge( $meta_key_to_props, $this->get_internal_meta_keys() );

		if ( $force ) {
			$props_to_update = $this->get_internal_meta_keys();
		} else {
			$props_to_update = $this->get_props_to_update( $model, $meta_key_to_props, 'user' );
		}

		foreach ( $props_to_update as $prop => $meta_key ) {
			if ( ! is_callable( array( $model, "get_{$prop}" ) ) ) {
				continue;
			}

			$value = $model->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				// Stores literal 'true' and 'false' as string in database.
				case 'rich_editing':
				case 'syntax_highlighting':
				case 'comment_shortcuts':
				case 'show_admin_bar_front':
					$value = $value ? 'true' : 'false';
					break;
			}

			$updated = $this->update_or_delete_user_meta( $model, $meta_key, $value );

			if ( $updated ) {
				$this->updated_props[] = $prop;
			}
		}

		// Update extra data associated with the model like button text or model URL for external models.
		if ( ! $this->extra_data_saved ) {
			foreach ( $extra_data_keys as $key ) {
				$meta_key = '_' . $key;
				$function = 'get_' . $key;
				if ( ! array_key_exists( $meta_key, $props_to_update ) ) {
					continue;
				}
				if ( is_callable( array( $model, $function ) ) ) {
					$value   = $model->{$function}( 'edit' );
					$value   = is_string( $value ) ? wp_slash( $value ) : $value;
					$updated = $this->update_or_delete_user_meta( $model, $meta_key, $value );

					if ( $updated ) {
						$this->updated_props[] = $key;
					}
				}
			}
		}
	}

	/**
	 * Update meta data in, or delete it from, the database.
	 *
	 * Avoids storing meta when it's either an empty string or empty array.
	 * Other empty values such as numeric 0 and null should still be stored.
	 * Data-stores can force meta to exist using `must_exist_meta_keys`.
	 *
	 * Note: WordPress `get_metadata` function returns an empty string when meta data does not exist.
	 *
	 * @since 1.0.0 Added to prevent empty meta being stored unless required.
	 *
	 * @param Model $object The Model object
	 * @param string  $meta_key Meta key to update.
	 * @param mixed   $meta_value Value to save.
	 *
	 *
	 * @return bool True if updated/deleted.
	 */
	protected function update_or_delete_user_meta( $object, $meta_key, $meta_value ) {
		if ( in_array( $meta_value, array( array(), '' ), true ) && ! in_array( $meta_key, $this->get_must_exist_meta_keys(), true ) ) {
			$updated = delete_user_meta( $object->get_id(), $meta_key );
		} else {
			$updated = update_user_meta( $object->get_id(), $meta_key, $meta_value );
		}

		return (bool) $updated;
	}
}
