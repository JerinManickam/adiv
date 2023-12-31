<?php
/**
 * User model.
 *
 * @since 1.0.0
 *
 * @package Masteriyo\Models;
 */

namespace Masteriyo\Models;

use Masteriyo\Helper\Utils;
use Masteriyo\Database\Model;
use Masteriyo\ModelException;
use Masteriyo\Cache\CacheInterface;
use Masteriyo\Repository\UserRepository;

defined( 'ABSPATH' ) || exit;

/**
 * User model.
 *
 * @since 1.0.0
 */
class User extends Model {

	/**
	 * This is the name of this object type.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $object_type = 'user';

	/**
	 * Cache group.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $cache_group = 'users';

	/**
	 * Stores user data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $data = array(
		'username'             => '',
		'password'             => '',
		'nicename'             => '',
		'email'                => '',
		'url'                  => '',
		'date_created'         => null,
		'date_modified'        => null,
		'activation_key'       => '',
		'status'               => 0,
		'display_name'         => '',
		'nickname'             => '',
		'first_name'           => '',
		'last_name'            => '',
		'description'          => '',
		'rich_editing'         => true,
		'syntax_highlighting'  => true,
		'comment_shortcuts'    => false,
		'admin_color'          => 'fresh',
		'use_ssl'              => false,
		'spam'                 => false,
		'show_admin_bar_front' => true,
		'locale'               => '',
		'roles'                => array(),
		// Billing details.
		'billing_first_name'   => '',
		'billing_last_name'    => '',
		'billing_company'      => '',
		'billing_address_1'    => '',
		'billing_address_2'    => '',
		'billing_city'         => '',
		'billing_postcode'     => '',
		'billing_country'      => '',
		'billing_state'        => '',
		'billing_email'        => '',
		'billing_phone'        => '',
	);

	/**
	 * Get the user if ID
	 *
	 * @since 1.0.0
	 *
	 * @param UserRepository $user_repository User Repository.
	 */
	public function __construct( UserRepository $user_repository ) {
		parent::__construct();
		$this->repository = $user_repository;
	}

	/**
	 * Get User's avatar URL.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Avatar arguments.
	 *
	 * @return string
	 */
	public function get_avatar_url( $args = null ) {
		return get_avatar_url( $this->get_id(), $args );
	}

	/**
	 * Get User's course archive page URL.
	 *
	 * @since 1.4.3
	 *
	 * @return string
	 */
	public function get_course_archive_url() {
		$url = get_author_posts_url( $this->get_id() );
		$url = add_query_arg(
			array(
				'post_type' => 'mto-course',
			),
			$url
		);

		return $url;
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get user's login username.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_username( $context = 'view' ) {
		return $this->get_prop( 'username', $context );
	}

	/**
	 * Get users's login password(hash format).
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_password( $context = 'view' ) {
		return $this->get_prop( 'password', $context );
	}

	/**
	 * Get URL-friendly user name.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_nicename( $context = 'view' ) {
		return $this->get_prop( 'nicename', $context );
	}

	/**
	 * Get user's email address.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_email( $context = 'view' ) {
		return $this->get_prop( 'email', $context );
	}

	/**
	 * Get user's URL.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_url( $context = 'view' ) {
		return $this->get_prop( 'url', $context );
	}

	/**
	 * Get created/registered date.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context );
	}

	/**
	 * Get password reset key.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_activation_key( $context = 'view' ) {
		return $this->get_prop( 'activation_key', $context );
	}

	/**
	 * Get user's status.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Get user's display name. Default is the user's username.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_display_name( $context = 'view' ) {
		return $this->get_prop( 'display_name', $context );
	}

	/**
	 * Get user's nickname. Default is the user's username.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_nickname( $context = 'view' ) {
		return $this->get_prop( 'nickname', $context );
	}

	/**
	 * Get user's first name.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_first_name( $context = 'view' ) {
		return $this->get_prop( 'first_name', $context );
	}

	/**
	 * Get users's last name.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_last_name( $context = 'view' ) {
		return $this->get_prop( 'last_name', $context );
	}

	/**
	 * Get the user's biographical description.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_description( $context = 'view' ) {
		return $this->get_prop( 'description', $context );
	}

	/**
	 * Get whether to enable the rich-editor for the user. Default true.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function get_rich_editing( $context = 'view' ) {
		return $this->get_prop( 'rich_editing', $context );
	}

	/**
	 * Get whether to enable the rich code editor for the user. Default true.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function get_syntax_highlighting( $context = 'view' ) {
		return $this->get_prop( 'syntax_highlighting', $context );
	}

	/**
	 * Get whether to enable comment moderation keyboard shortcuts for the user. Default false.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function get_comment_shortcuts( $context = 'view' ) {
		return $this->get_prop( 'comment_shortcuts', $context );
	}

	/**
	 * Get admin color scheme for the user. Default 'fresh'.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function get_admin_color( $context = 'view' ) {
		return $this->get_prop( 'admin_color', $context );
	}

	/**
	 * Get whether the user should always access the admin over https. Default false.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function get_use_ssl( $context = 'view' ) {
		return $this->get_prop( 'use_ssl', $context );
	}

	/**
	 * Multisite only. Whether the user is marked as spam. Default false.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function get_spam( $context = 'view' ) {
		return $this->get_prop( 'spam', $context );
	}

	/**
	 * Get whether to display the Admin Bar for the user on the site's front end. Default true.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_show_admin_bar_front( $context = 'view' ) {
		return $this->get_prop( 'show_admin_bar_front', $context );
	}

	/**
	 * Get user's locale. Default empty.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_locale( $context = 'view' ) {
		return $this->get_prop( 'locale', $context );
	}

	/**
	 * Get user's roles.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_roles( $context = 'view' ) {
		return $this->get_prop( 'roles', $context );
	}

	/**
	 * Get user's billing first name.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_first_name( $context = 'view' ) {
		return $this->get_prop( 'billing_first_name', $context );
	}

	/**
	 * Get user's billing last name.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_last_name( $context = 'view' ) {
		return $this->get_prop( 'billing_last_name', $context );
	}

	/**
	 * Get user's billing company.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_company( $context = 'view' ) {
		return $this->get_prop( 'billing_company', $context );
	}

	/**
	 * Get user's billing address.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_address( $context = 'view' ) {
		return $this->get_billing_address_1( $context );
	}

	/**
	 * Get user's billing address 1.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_address_1( $context = 'view' ) {
		return $this->get_prop( 'billing_address_1', $context );
	}

	/**
	 * Get user's billing address 1.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_address_2( $context = 'view' ) {
		return $this->get_prop( 'billing_address_2', $context );
	}

	/**
	 * Get user's billing city.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_city( $context = 'view' ) {
		return $this->get_prop( 'billing_city', $context );
	}

	/**
	 * Get user's billing post code.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_postcode( $context = 'view' ) {
		return $this->get_prop( 'billing_postcode', $context );
	}

	/**
	 * Get user's billing country.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_country( $context = 'view' ) {
		return $this->get_prop( 'billing_country', $context );
	}

	/**
	 * Get user's billing state.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_state( $context = 'view' ) {
		return $this->get_prop( 'billing_state', $context );
	}

	/**
	 * Get user's billing email.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_email( $context = 'view' ) {
		return $this->get_prop( 'billing_email', $context );
	}

	/**
	 * Get user's billing phone number.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_billing_phone( $context = 'view' ) {
		return $this->get_prop( 'billing_phone', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set user's username.
	 *
	 * @since 1.0.0
	 *
	 * @param string $username User's username.
	 */
	public function set_username( $username ) {
		$this->set_prop( 'username', $username );
	}

	/**
	 * Set user's password.
	 *
	 * @since 1.0.0
	 *
	 * @param string $password User's password.
	 */
	public function set_password( $password ) {
		$this->set_prop( 'password', $password );
	}

	/**
	 * Set user's nicename.
	 *
	 * @since 1.0.0
	 *
	 * @param string $nicename User's nicename.
	 */
	public function set_nicename( $nicename ) {
		$this->set_prop( 'nicename', $nicename );
	}

	/**
	 * Set user's email.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email User's email.
	 */
	public function set_email( $email ) {
		$this->set_prop( 'email', $email );
	}

	/**
	 * Set user's url.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url User's url.
	 */
	public function set_url( $url ) {
		$this->set_prop( 'url', $url );
	}

	/**
	 * Set user's registration/creation date.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date_created User's date_created.
	 */
	public function set_date_created( $date_created ) {
		$this->set_prop( 'date_created', $date_created );
	}

	/**
	 * Set user's password reset/activation key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $activation_key User's activation_key.
	 */
	public function set_activation_key( $activation_key ) {
		$this->set_prop( 'activation_key', $activation_key );
	}

	/**
	 * Set user's status.
	 *
	 * @since 1.0.0
	 *
	 * @param string $status User's status.
	 */
	public function set_status( $status ) {
		$this->set_prop( 'status', $status );
	}

	/**
	 * Set user's display_name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $display_name User's display_name.
	 */
	public function set_display_name( $display_name ) {
		$this->set_prop( 'display_name', $display_name );
	}

	/**
	 * Set user's nickname.
	 *
	 * @since 1.0.0
	 *
	 * @param string $nickname User's nickname.
	 */
	public function set_nickname( $nickname ) {
		$this->set_prop( 'nickname', $nickname );
	}

	/**
	 * Set user's first name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $first_name User's first_name.
	 */
	public function set_first_name( $first_name ) {
		$this->set_prop( 'first_name', $first_name );
	}

	/**
	 * Set user's last name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $last_name User's last_name.
	 */
	public function set_last_name( $last_name ) {
		$this->set_prop( 'last_name', $last_name );
	}

	/**
	 * Set user's biographical description.
	 *
	 * @since 1.0.0
	 *
	 * @param string $description User's description.
	 */
	public function set_description( $description ) {
		$this->set_prop( 'description', $description );
	}

	/**
	 * Set the rich-editor for the user.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $rich_editing User's rich_editing.
	 */
	public function set_rich_editing( $rich_editing ) {
		$this->set_prop( 'rich_editing', masteriyo_string_to_bool( $rich_editing ) );
	}

	/**
	 * Set the rich code editor for the user.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $syntax_highlighting User's syntax highlighting.
	 */
	public function set_syntax_highlighting( $syntax_highlighting ) {
		$this->set_prop( 'syntax_highlighting', masteriyo_string_to_bool( $syntax_highlighting ) );
	}

	/**
	 * Set comment moderation keyboard shortcuts for the user.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $comment_shortcuts User's comment shortcuts.
	 */
	public function set_comment_shortcuts( $comment_shortcuts ) {
		$this->set_prop( 'comment_shortcuts', masteriyo_string_to_bool( $comment_shortcuts ) );
	}

	/**
	 * Set admin color scheme for the user.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $admin_color Admin color scheme of the user.
	 */
	public function set_admin_color( $admin_color ) {
		$this->set_prop( 'admin_color', $admin_color );
	}

	/**
	 * Set the user should always access the admin over https.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $use_ssl User's use_ssl.
	 */
	public function set_use_ssl( $use_ssl ) {
		$this->set_prop( 'use_ssl', masteriyo_string_to_bool( $use_ssl ) );
	}

	/**
	 * Set the user as spam. Multisite only.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $span
	 */
	public function set_spam( $spam ) {
		$this->set_prop( 'spam', $spam );
	}

	/**
	 * Set display the Admin Bar for the user on the site's front end.
	 *
	 * @since 1.0.0
	 *
	 * @param string $show_admin_bar_front User's show admin bar front..
	 */
	public function set_show_admin_bar_front( $show_admin_bar_front ) {
		$this->set_prop( 'show_admin_bar_front', masteriyo_string_to_bool( $show_admin_bar_front ) );
	}

	/**
	 * Set user's locale.
	 *
	 * @since 1.0.0
	 *
	 * @param string $locale User's locale.
	 */
	public function set_locale( $locale ) {
		$this->set_prop( 'locale', $locale );
	}

	/**
	 * Set user's roles.
	 *
	 * @since 1.0.0
	 *
	 * @param string $roles User's roles.
	 */
	public function set_roles( $roles ) {
		$roles = (array) $roles;

		if ( $roles && ! masteriyo_is_role_exists( $roles ) ) {
			throw new ModelException( 'user_invalid_roles', __( 'Invalid roles', 'masteriyo' ) );
		}
		$roles = (array) $roles;

		if ( is_array( $roles ) && ! empty( $roles ) && ! empty( $GLOBALS['wp_roles']->roles ) ) {
			foreach ( $roles as $role ) {
				if ( ! in_array( $role, array_keys( $GLOBALS['wp_roles']->roles ), true ) ) {
					throw new ModelException( 'user_invalid_roles', __( 'Invalid roles', 'masteriyo' ) );
				}
			}
		}

		$this->set_prop( 'roles', $roles );
	}

	/**
	 * Set user's billing first name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $first_name User's billing first name.
	 * @return void
	 */
	public function set_billing_first_name( $first_name ) {
		$this->set_prop( 'billing_first_name', $first_name );
	}

	/**
	 * Set user's billing last name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $last_name User's billing last name.
	 * @return void
	 */
	public function set_billing_last_name( $last_name ) {
		$this->set_prop( 'billing_last_name', $last_name );
	}

	/**
	 * Set user's billing company.
	 *
	 * @since 1.0.0
	 *
	 * @param string $company User's billing company.
	 * @return void
	 */
	public function set_billing_company( $company ) {
		$this->set_prop( 'billing_company', $company );
	}

	/**
	 * Set user's billing address_1.
	 *
	 * @since 1.0.0
	 *
	 * @param string $address_1 User's billing address_1.
	 * @return void
	 */
	public function set_billing_address_1( $address_1 ) {
		$this->set_prop( 'billing_address_1', $address_1 );
	}

	/**
	 * Set user's billing address_2.
	 *
	 * @since 1.0.0
	 *
	 * @param string $address_2 User's billing address_2.
	 * @return void
	 */
	public function set_billing_address_2( $address_2 ) {
		$this->set_prop( 'billing_address_2', $address_2 );
	}

	/**
	 * Set user's billing city.
	 *
	 * @since 1.0.0
	 *
	 * @param string $city User's billing city.
	 */
	public function set_billing_city( $city ) {
		$this->set_prop( 'billing_city', $city );
	}

	/**
	 * Set user's billing post code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $postcode User's billing post code.
	 */
	public function set_billing_postcode( $postcode ) {
		$this->set_prop( 'billing_postcode', $postcode );
	}


	/**
	 * Set user's billing country.
	 *
	 * @since 1.0.0
	 *
	 * @param string $country User's country.
	 */
	public function set_billing_country( $country ) {
		$this->set_prop( 'billing_country', $country );
	}

	/**
	 * Set user's billing state.
	 *
	 * @since 1.0.0
	 *
	 * @param string $state User's billing state.
	 */
	public function set_billing_state( $state ) {
		$this->set_prop( 'billing_state', $state );
	}

	/**
	 * Set user's billing email.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email User's billing email.
	 */
	public function set_billing_email( $email ) {
		$this->set_prop( 'billing_email', $email );
	}

	/**
	 * Set user's billing phone.
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone User's billing phone.
	 */
	public function set_billing_phone( $phone ) {
		$this->set_prop( 'billing_phone', $phone );
	}
}
