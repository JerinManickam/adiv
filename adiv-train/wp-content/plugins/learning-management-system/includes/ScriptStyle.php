<?php
/**
 * Ajax.
 *
 * @package Masteriyo
 *
 * @since 1.0.0
 */

namespace Masteriyo;

use Masteriyo\Query\CourseCategoryQuery;

defined( 'ABSPATH' ) || exit;

/**
 * Main Masteriyo class.
 *
 * @class Masteriyo\Masteriyo
 */

class ScriptStyle {

	/**
	 * Scripts.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public static $scripts = array();

	/**
	 * Styles.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public static $styles = array();

	/**
	 * Localized scripts.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public static $localized_scripts = array();

	/**
	 * Initialization.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::init_hooks();
		self::init_scripts();
		self::init_styles();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function init_hooks() {
		add_action( 'init', array( __CLASS__, 'after_wp_init' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_public_scripts_styles' ), PHP_INT_MAX - 10 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_scripts_styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_public_localized_scripts' ), PHP_INT_MAX - 9 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_localized_scripts' ) );

		// Remove third party styles from learn page.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'remove_styles_scripts_in_learn_page' ), PHP_INT_MAX );
	}

	/**
	 * Initialization after WordPress is initialized.
	 *
	 * @since 1.3.0
	 */
	public static function after_wp_init() {
		self::register_block_scripts_and_styles();
		self::localize_block_scripts();
	}

	/**
	 * Get application version.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private static function get_version() {
		return Constants::get( 'MASTERIYO_VERSION' );
	}

	/**
	 * Get asset name suffix.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_asset_suffix() {
		$version = Constants::get( 'MASTERIYO_VERSION' );

		if ( Constants::is_true( 'SCRIPT_DEBUG' ) ) {
			return ".{$version}";
		}
		return ".{$version}.min";
	}

	/**
	 * Get asset dependencies.
	 *
	 * @since 1.4.1
	 *
	 * @param string $asset_name
	 *
	 * @return array
	 */
	public static function get_asset_deps( $asset_name ) {
		$version        = Constants::get( 'MASTERIYO_VERSION' );
		$asset_filepath = Constants::get( 'MASTERIYO_PLUGIN_DIR' ) . "/assets/js/build/{$asset_name}.{$version}.asset.php";

		if ( ! file_exists( $asset_filepath ) || ! is_readable( $asset_filepath ) ) {
			return array();
		}
		$asset = (array) include_once $asset_filepath;

		if ( isset( $asset['dependencies'] ) ) {
			return $asset['dependencies'];
		}
		return array();
	}

	/**
	 * Initialize the scripts.`
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private static function init_scripts() {
		$suffix = self::get_asset_suffix();

		$account_src         = self::get_asset_url( "/assets/js/build/masteriyo-account{$suffix}.js" );
		$admin_src           = self::get_asset_url( "/assets/js/build/masteriyo-backend{$suffix}.js" );
		$learn_src           = self::get_asset_url( "/assets/js/build/masteriyo-interactive{$suffix}.js" );
		$single_course_src   = self::get_asset_url( "/assets/js/build/single-course{$suffix}.js" );
		$login_form_src      = self::get_asset_url( "/assets/js/build/login-form{$suffix}.js" );
		$checkout_src        = self::get_asset_url( "/assets/js/build/checkout{$suffix}.js" );
		$ask_review_src      = self::get_asset_url( "/assets/js/build/ask-review{$suffix}.js" );
		$jquery_block_ui_src = self::get_asset_url( "/assets/js/build/jquery-block-ui{$suffix}.js" );

		if ( masteriyo_is_development() ) {
			$account_src         = 'http://localhost:3000/dist/account.js';
			$admin_src           = 'http://localhost:3000/dist/backend.js';
			$learn_src           = 'http://localhost:3000/dist/interactive.js';
			$single_course_src   = self::get_asset_url( '/assets/js/frontend/single-course.js' );
			$login_form_src      = self::get_asset_url( '/assets/js/frontend/login-form.js' );
			$checkout_src        = self::get_asset_url( '/assets/js/frontend/checkout.js' );
			$ask_review_src      = self::get_asset_url( '/assets/js/frontend/ask-review.js' );
			$jquery_block_ui_src = self::get_asset_url( '/assets/js/frontend/jquery-block-ui.js' );
		}

		self::$scripts = apply_filters(
			'masteriyo_enqueue_scripts',
			array(
				'dependencies'    => array(
					'src'      => self::get_asset_url( "/assets/js/build/masteriyo-dependencies{$suffix}.js" ),
					'context'  => array( 'admin', 'public' ),
					'callback' => function() {
						return masteriyo_is_production() && ( masteriyo_is_admin_page() || masteriyo_is_learn_page() || ( is_user_logged_in() && masteriyo_is_account_page() ) );
					},
				),
				'blocks'          => array(
					'src'           => self::get_asset_url( "/assets/js/build/blocks{$suffix}.js" ),
					'context'       => 'blocks',
					'deps'          => array_merge( self::get_asset_deps( 'blocks' ), array( 'jquery', 'wp-dom-ready', 'wp-hooks', 'wp-keyboard-shortcuts' ) ),
					'register_only' => true,
				),
				'admin'           => array(
					'src'      => $admin_src,
					'deps'     => array_merge( self::get_asset_deps( 'masteriyo-backend' ), array( 'wp-core-data', 'wp-components', 'wp-element' ) ),
					'context'  => 'admin',
					'callback' => 'masteriyo_is_admin_page',
				),
				'single-course'   => array(
					'src'      => $single_course_src,
					'deps'     => array( 'jquery' ),
					'context'  => 'public',
					'callback' => 'masteriyo_is_single_course_page',
				),
				'account'         => array(
					'src'      => $account_src,
					'deps'     => self::get_asset_deps( 'masteriyo-account' ),
					'version'  => self::get_version(),
					'context'  => 'public',
					'callback' => 'is_user_logged_in',
				),
				'login-form'      => array(
					'src'      => $login_form_src,
					'deps'     => array( 'jquery' ),
					'version'  => self::get_version(),
					'context'  => 'public',
					'callback' => 'masteriyo_is_load_login_form_assets',
				),
				'checkout'        => array(
					'src'      => $checkout_src,
					'deps'     => array( 'jquery', 'masteriyo-jquery-block-ui' ),
					'version'  => self::get_version(),
					'context'  => 'public',
					'callback' => 'masteriyo_is_checkout_page',
				),
				'ask-review'      => array(
					'src'      => $ask_review_src,
					'deps'     => array( 'jquery' ),
					'version'  => self::get_version(),
					'context'  => 'admin',
					'callback' => 'masteriyo_is_show_review_notice',
				),
				'learn'           => array(
					'src'      => $learn_src,
					'deps'     => array_merge( self::get_asset_deps( 'masteriyo-interactive' ), array( 'wp-data', 'wp-core-data', 'wp-components', 'wp-element' ) ),
					'version'  => self::get_version(),
					'context'  => 'public',
					'callback' => 'masteriyo_is_learn_page',
				),
				'jquery-block-ui' => array(
					'src'      => $jquery_block_ui_src,
					'version'  => self::get_version(),
					'context'  => 'public',
					'callback' => 'masteriyo_is_checkout_page',
				),
			)
		);
	}

	/**
	 * Initialize the styles.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private static function init_styles() {
		$suffix = self::get_asset_suffix();

		self::$styles = apply_filters(
			'masteriyo_enqueue_styles',
			array(
				'public'        => array(
					'src'     => self::get_asset_url( "/assets/css/public{$suffix}.css" ),
					'has_rtl' => false,
					'deps'    => array( 'common' ),
					'context' => 'public',
				),
				'dependencies'  => array(
					'src'     => self::get_asset_url( '/assets/js/build/masteriyo-dependencies.css' ),
					'has_rtl' => false,
					'context' => 'admin',
				),
				'block'         => array(
					'src'      => self::get_asset_url( "/assets/css/block{$suffix}.css" ),
					'has_rtl'  => false,
					'context'  => 'admin',
					'callback' => function() {
						$screen = get_current_screen();

						return $screen && ( $screen->is_block_editor() || 'customize' === $screen->id );
					},
				),
				'review-notice' => array(
					'src'      => self::get_asset_url( "/assets/css/review-notice{$suffix}.css" ),
					'has_rtl'  => false,
					'context'  => 'admin',
					'callback' => 'masteriyo_is_show_review_notice',
				),
			)
		);
	}

	/**
	 * Get styles according to context.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context Style/Script context (admin, public  none, etc.)
	 *
	 * @return array
	 */
	public static function get_styles( $context ) {
		// Set default values.
		$styles = array_map(
			function( $style ) {
				return array_replace_recursive( self::get_default_style_options(), $style );
			},
			self::$styles
		);

		// Filter according to admin or public static context.
		$styles = array_filter(
			$styles,
			function( $style ) use ( $context ) {
				return in_array( $context, (array) $style['context'], true );
			}
		);

		return $styles;
	}

	/**
	 * Get scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context Script context. (admin, public,static  none).
	 *
	 * @return array
	 */
	public static function get_scripts( $context ) {
		// Set default values.
		$scripts = array_map(
			function( $script ) {
				return array_replace_recursive( self::get_default_script_options(), $script );
			},
			self::$scripts
		);

		// Filter according to admin or public static context.
		$scripts = array_filter(
			$scripts,
			function( $script ) use ( $context ) {
				return in_array( $context, (array) $script['context'], true );
			}
		);

		return $scripts;
	}

	/**
	 * Default script options.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_default_script_options() {
		return apply_filters(
			'masteriyo_get_default_script_options',
			array(
				'src'           => '',
				'deps'          => array( 'jquery' ),
				'version'       => self::get_version(),
				'context'       => 'none',
				'in_footer'     => true,
				'register_only' => false,
				'callback'      => '',
			)
		);
	}

	/**
	 * Default style options.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_default_style_options() {
		return apply_filters(
			'masteriyo_get_default_style_options',
			array(
				'src'           => '',
				'deps'          => array(),
				'version'       => self::get_version(),
				'media'         => 'all',
				'has_rtl'       => false,
				'context'       => array( 'none' ),
				'in_footer'     => true,
				'register_only' => false,
				'callback'      => '',
			)
		);
	}

	/**
	 * Return asset URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Assets path.
	 *
	 * @return string
	 */
	private static function get_asset_url( $path ) {
		return apply_filters( 'masteriyo_get_asset_url', plugins_url( $path, Constants::get( 'MASTERIYO_PLUGIN_FILE' ) ), $path );
	}

	/**
	 * Register a script for use.
	 *
	 * @since 1.0.0
	 *
	 * @uses   wp_register_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = '', $in_footer = true ) {
		wp_register_script( "masteriyo-{$handle}", $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @since 1.0.0
	 *
	 * @uses   wp_enqueue_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = '', $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts, true ) && $path ) {
			wp_register_script( "masteriyo-{$handle}", $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( "masteriyo-{$handle}" );
	}

	/**
	 * Register a style for use.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @uses   wp_register_style()
	 * @param  string   $handle  Name of the stylesheet. Should be unique.
	 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
	 * @param  string   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '( orientation: portrait )' and '( max-width: 640px )'.
	 * @param  boolean  $has_rtl If has RTL version to load too.
	 */
	private static function register_style( $handle, $path, $deps = array(), $version = '', $media = 'all', $has_rtl = false ) {
		if ( ! isset( self::$styles[ $handle ] ) ) {
			self::$styles[ $handle ] = array(
				'src'     => $path,
				'deps'    => $deps,
				'version' => $version,
				'media'   => $media,
				'has_rtl' => $has_rtl,
			);
		}
		wp_register_style( "masteriyo-{$handle}", $path, $deps, $version, $media );

		if ( $has_rtl ) {
			wp_style_add_data( "masteriyo-{$handle}", 'rtl', 'replace' );
		}
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @since 1.0.0
	 *
	 * @uses   wp_enqueue_style()
	 * @param  string   $handle  Name of the stylesheet. Should be unique.
	 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
	 * @param  string   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '( orientation: portrait )' and '( max-width: 640px )'.
	 * @param  boolean  $has_rtl If has RTL version to load too.
	 */
	private static function enqueue_style( $handle, $path = '', $deps = array(), $version = '', $media = 'all', $has_rtl = false ) {
		if ( ! in_array( $handle, self::$styles, true ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
		}
		wp_enqueue_style( "masteriyo-{$handle}" );
	}

	/**
	 * Load public static scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public static function load_public_scripts_styles() {
		$scripts = self::get_scripts( 'public' );
		$styles  = self::get_styles( 'public' );

		foreach ( $scripts as $handle => $script ) {
			if ( true === (bool) $script['register_only'] ) {
				self::register_script( $handle, $script['src'], $script['deps'], $script['version'] );
				continue;
			}

			if ( empty( $script['callback'] ) ) {
				self::enqueue_script( $handle, $script['src'], $script['deps'], $script['version'] );
			} elseif ( is_callable( $script['callback'] ) && call_user_func_array( $script['callback'], array() ) ) {
				self::enqueue_script( $handle, $script['src'], $script['deps'], $script['version'] );
			}
		}

		foreach ( $styles as $handle => $style ) {
			if ( true === (bool) $style['register_only'] ) {
				self::register_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
				continue;
			}

			if ( empty( $style['callback'] ) ) {
				self::enqueue_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
			} elseif ( is_callable( $style['callback'] ) && call_user_func_array( $style['callback'], array() ) ) {
				self::enqueue_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
			}
		}

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'masteriyo-learn', 'masteriyo', Constants::get( 'MASTERIYO_LANGUAGES' ) );
			wp_set_script_translations( 'masteriyo-account', 'masteriyo', Constants::get( 'MASTERIYO_LANGUAGES' ) );
		}

		self::load_custom_inline_styles();
		self::load_block_styles();
	}

	/**
	 * Load inline custom colors.
	 *
	 * @since 1.0.4
	 */
	public static function load_custom_inline_styles() {
		$primary_color = masteriyo_get_setting( 'general.styling.primary_color' );

		// Bail early if the primary color is not set.
		if ( empty( trim( $primary_color ) ) ) {
			return;
		}

		$primary_light = masteriyo_color_luminance( $primary_color, 0.3 );
		$primary_dark  = masteriyo_color_luminance( $primary_color, -0.05 );

		$custom_css = "
			:root {
				--masteriyo-color-primary: ${primary_color};
				--masteriyo-color-primary-light: ${primary_light};
				--masteriyo-color-primary-dark: ${primary_dark};
				--masteriyo-color-btn-blue-hover: ${primary_light};
			}
		";
		wp_add_inline_style( 'masteriyo-public', $custom_css );
	}

	/**
	 * Load block styles.
	 *
	 * @since 1.3.0
	 */
	public static function load_block_styles() {
		$post_id  = get_the_ID();
		$settings = masteriyo_get_settings();

		wp_add_inline_style( 'masteriyo-public', $settings->get( 'general.widgets_css' ) );

		if ( empty( $post_id ) ) {
			return;
		}
		$css = get_post_meta( $post_id, '_masteriyo_css', true );

		if ( empty( $css ) ) {
			return;
		}
		wp_add_inline_style( 'masteriyo-public', $css );
	}

	/**
	 * Register block scripts and styles.
	 *
	 * @since 1.3.0
	 */
	public static function register_block_scripts_and_styles() {
		$scripts = self::get_scripts( 'blocks' );
		$styles  = self::get_styles( 'blocks' );

		foreach ( $scripts as $handle => $script ) {
			if ( true === (bool) $script['register_only'] ) {
				self::register_script( $handle, $script['src'], $script['deps'], $script['version'] );
				continue;
			}

			if ( empty( $script['callback'] ) ) {
				self::enqueue_script( $handle, $script['src'], $script['deps'], $script['version'] );
			} elseif ( is_callable( $script['callback'] ) && call_user_func_array( $script['callback'], array() ) ) {
				self::enqueue_script( $handle, $script['src'], $script['deps'], $script['version'] );
			}
		}

		foreach ( $styles as $handle => $style ) {
			if ( true === (bool) $style['register_only'] ) {
				self::register_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
				continue;
			}

			if ( empty( $style['callback'] ) ) {
				self::enqueue_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
			} elseif ( is_callable( $style['callback'] ) && call_user_func_array( $style['callback'], array() ) ) {
				self::enqueue_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
			}
		}

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'masteriyo-blocks', 'masteriyo', Constants::get( 'MASTERIYO_LANGUAGES' ) );
		}
	}

	/**
	 * Localize block scripts.
	 *
	 * @since 1.3.0
	 */
	public static function localize_block_scripts() {
		global $pagenow;
		$args       = array(
			'order'   => 'ASC',
			'orderby' => 'name',
			'number'  => '',
		);
		$query      = new CourseCategoryQuery( $args );
		$categories = $query->get_categories();

		self::$localized_scripts = apply_filters(
			'masteriyo_localized_block_scripts',
			array(
				'blocks' => array(
					'name' => '_MASTERIYO_BLOCKS_DATA_',
					'data' => array(
						'categories'      => array_map(
							function( $category ) {
								return $category->get_data();
							},
							$categories
						),
						'isWidgetsEditor' => 'widgets.php' === $pagenow ? 'yes' : 'no',
						'isCustomizer'    => 'customize.php' === $pagenow ? 'yes' : 'no',
					),
				),
			)
		);

		foreach ( self::$localized_scripts as $handle => $script ) {
			\wp_localize_script( "masteriyo-{$handle}", $script['name'], $script['data'] );
		}
	}

	/**
	 * Load public static scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public static function load_admin_scripts_styles() {
		$scripts = self::get_scripts( 'admin' );
		$styles  = self::get_styles( 'admin' );

		foreach ( $scripts as $handle => $script ) {
			if ( true === (bool) $script['register_only'] ) {
				self::register_script( $handle, $script['src'], $script['deps'], $script['version'] );
				continue;
			}

			if ( empty( $script['callback'] ) ) {
				self::enqueue_script( $handle, $script['src'], $script['deps'], $script['version'] );
			} elseif ( is_callable( $script['callback'] ) && call_user_func_array( $script['callback'], array() ) ) {
				self::enqueue_script( $handle, $script['src'], $script['deps'], $script['version'] );
			}
		}

		foreach ( $styles as $handle => $style ) {
			if ( true === (bool) $style['register_only'] ) {
				self::register_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
				continue;
			}

			if ( empty( $style['callback'] ) ) {
				self::enqueue_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
			} elseif ( is_callable( $style['callback'] ) && call_user_func_array( $style['callback'], array() ) ) {
				self::enqueue_style( $handle, $style['src'], $style['deps'], $style['version'], $style['media'], $style['has_rtl'] );
			}
		}

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'masteriyo-admin', 'masteriyo', Constants::get( 'MASTERIYO_LANGUAGES' ) );
		}
	}

	/**
	 * Load admin localized scripts.
	 *
	 * @since 1.0.0
	 */
	public static function load_admin_localized_scripts() {
		$courses_page = get_post( masteriyo_get_page_id( 'courses' ) );
		$courses_slug = ! is_null( $courses_page ) ? $courses_page->post_name : '';

		$account_page = get_post( masteriyo_get_page_id( 'account' ) );
		$account_slug = ! is_null( $account_page ) ? $account_page->post_name : '';

		$checkout_page = get_post( masteriyo_get_page_id( 'checkout' ) );
		$checkout_slug = ! is_null( $checkout_page ) ? $checkout_page->post_name : '';

		self::$localized_scripts = apply_filters(
			'masteriyo_localized_admin_scripts',
			array(
				'admin'      => array(
					'name' => '_MASTERIYO_',
					'data' => array(
						'rootApiUrl'          => esc_url_raw( untrailingslashit( rest_url() ) ),
						'nonce'               => wp_create_nonce( 'wp_rest' ),
						'review_notice_nonce' => wp_create_nonce( 'masteriyo_review_notice_nonce' ),
						'ajax_url'            => admin_url( 'admin-ajax.php' ),
						'home_url'            => home_url(),
						'pageSlugs'           => array(
							'courses'  => $courses_slug,
							'account'  => $account_slug,
							'checkout' => $checkout_slug,
						),
						'currency'            => array(
							'code'     => masteriyo_get_currency(),
							'symbol'   => html_entity_decode( masteriyo_get_currency_symbol( masteriyo_get_currency() ) ),
							'position' => masteriyo_get_setting( 'payments.currency.currency_position' ),
						),
						'imageSizes'          => get_intermediate_image_sizes(),
						'countries'           => masteriyo( 'countries' )->get_countries(),
						'states'              => array_filter(
							masteriyo( 'countries' )->get_states(),
							function( $state ) {
								return ! empty( $state );
							}
						),
						'show_review_notice'  => masteriyo_is_show_review_notice() ? 'yes' : 'no',
					),
				),
				'ask-review' => array(
					'name' => '_MASTERIYO_ASK_REVIEW_DATA_',
					'data' => array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'nonce'    => wp_create_nonce( 'masteriyo_review_notice_nonce' ),
					),
				),
			)
		);

		foreach ( self::$localized_scripts as $handle => $script ) {
			\wp_localize_script( "masteriyo-{$handle}", $script['name'], $script['data'] );
		}
	}

	/**
	 * Load public static localized scripts.
	 *
	 * @since 1.0.0
	 */
	public static function load_public_localized_scripts() {
		self::$localized_scripts = apply_filters(
			'masteriyo_localized_public_scripts',
			array(
				'account'       => array(
					'name' => '_MASTERIYO_',
					'data' => array(
						'rootApiUrl'      => esc_url_raw( untrailingslashit( rest_url() ) ),
						'current_user_id' => get_current_user_id(),
						'nonce'           => wp_create_nonce( 'wp_rest' ),
						'labels'          => array(
							'save'                   => __( 'Save', 'masteriyo' ),
							'saving'                 => __( 'Saving...', 'masteriyo' ),
							'profile_update_success' => __( 'Your profile was updated successfully.', 'masteriyo' ),
						),
						'currency'        => array(
							'code'     => masteriyo_get_currency(),
							'symbol'   => html_entity_decode( masteriyo_get_currency_symbol( masteriyo_get_currency() ) ),
							'position' => masteriyo_get_setting( 'payments.currency.currency_position' ),
						),
						'urls'            => array(
							'logout'       => wp_logout_url( get_home_url() ),
							'account'      => masteriyo_get_page_permalink( 'account' ),
							'courses'      => masteriyo_get_page_permalink( 'courses' ),
							'home'         => home_url(),
							'myCourses'    => admin_url( 'admin.php?page=masteriyo#/courses' ),
							'addNewCourse' => admin_url( 'admin.php?page=masteriyo#/courses/add-new-course' ),
						),
					),
				),
				'login-form'    => array(
					'name' => 'masteriyo_data',
					'data' => array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'nonce'    => wp_create_nonce( 'masteriyo_login_nonce' ),
						'labels'   => array(
							'sign_in'    => __( 'Sign In', 'masteriyo' ),
							'signing_in' => __( 'Signing In...', 'masteriyo' ),
						),
					),
				),
				'single-course' => array(
					'name' => 'masteriyo_data',
					'data' => array(
						'rootApiUrl'              => esc_url_raw( rest_url() ),
						'nonce'                   => wp_create_nonce( 'wp_rest' ),
						'rating_indicator_markup' => masteriyo_get_rating_indicators_markup( 'masteriyo-rating-input-icon' ),
						'max_course_rating'       => masteriyo_get_max_course_rating(),
						'review_deleted_notice'   => masteriyo_get_template_html( 'notices/review-deleted.php' ),
						'labels'                  => array(
							'type_confirm'   => __( 'Type CONFIRM to proceed.', 'masteriyo' ),
							'try_again'      => __( 'Try again', 'masteriyo' ),
							'submit'         => __( 'Submit', 'masteriyo' ),
							'update'         => __( 'Update', 'masteriyo' ),
							'delete'         => __( 'Delete', 'masteriyo' ),
							'submitting'     => __( 'Submitting...', 'masteriyo' ),
							'deleting'       => __( 'Deleting...', 'masteriyo' ),
							'reply_to'       => __( 'Reply to', 'masteriyo' ),
							'edit_reply'     => __( 'Edit reply', 'masteriyo' ),
							'edit_review'    => __( 'Edit review', 'masteriyo' ),
							'submit_success' => __( 'Submitted successfully.', 'masteriyo' ),
							'update_success' => __( 'Updated successfully.', 'masteriyo' ),
							'delete_success' => __( 'Deleted successfully.', 'masteriyo' ),
						),
					),
				),
				'checkout'      => array(
					'name' => '_MASTERIYO_CHECKOUT_',
					'data' => array(
						'ajaxURL'             => admin_url( 'admin-ajax.php' ),
						'checkoutURL'         => add_query_arg( array( 'action' => 'masteriyo_checkout' ), admin_url( 'admin-ajax.php' ) ),
						'i18n_checkout_error' => esc_html__( 'Error processing checkout. Please try again.', 'masteriyo' ),
						'is_checkout'         => true,
						'mto_ajax_url'        => '/?masteriyo-ajax=%%endpoint%%',
					),
				),
				'learn'         => array(
					'name' => '_MASTERIYO_',
					'data' => array(
						'rootApiUrl' => esc_url_raw( rest_url() ),
						'nonce'      => wp_create_nonce( 'wp_rest' ),
						'urls'       => array(
							'logout'  => wp_logout_url( get_home_url() ),
							'account' => masteriyo_get_page_permalink( 'account' ),
							'courses' => masteriyo_get_page_permalink( 'courses' ),
							'home'    => home_url(),
						),
						'logo'       => masteriyo_get_setting( 'learn_page.general.logo_id' ) ? masteriyo_get_learn_page_logo_data() : masteriyo_get_custom_logo_data(),
						'siteTitle'  => get_bloginfo( 'name' ),
						'userAvatar' => get_avatar_url( get_current_user_id() ),
						'qaEnable'   => masteriyo_get_setting( 'learn_page.display.enable_questions_answers' ),
					),
				),
			)
		);

		foreach ( self::$localized_scripts as $handle => $script ) {
			\wp_localize_script( "masteriyo-{$handle}", $script['name'], $script['data'] );
		}
	}

	/**
	 * Remove styles and scripts in learn page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function remove_styles_scripts_in_learn_page() {
		global $wp_styles;

		// Bail early if the page is not learn.
		if ( ! masteriyo_is_learn_page() ) {
			return;
		}

		$whitelist = self::get_whitelist_styles_in_learn_page();

		foreach ( $wp_styles->registered as $style ) {
			if ( ! in_array( $style->handle, $whitelist, true ) ) {
				wp_deregister_style( $style->handle );
			}
		}

		foreach ( $wp_styles->queue as $handle ) {
			if ( ! in_array( $handle, $whitelist, true ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}

	/**
	 * Get the list of whitelist styles in learn page.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_whitelist_styles_in_learn_page() {
		return array_unique(
			apply_filters(
				'masteriyo_whitelist_scripts_learn_page',
				array(
					'masteriyo-learn',
					'masteriyo-dependencies',
					'colors',
					'common',
					'forms',
					'admin-menu',
					'dashboard',
					'list-tables',
					'edit',
					'revisions',
					'media',
					'themes',
					'about',
					'nav-menus',
					'widgets',
					'site-icon',
					'l10n',
					'code-editor',
					'site-health',
					'wp-admin',
					'login',
					'install',
					'wp-color-picker',
					'customize-controls',
					'customize-widgets',
					'customize-nav-menus',
					'buttons',
					'dashicons',
					'admin-bar',
					'wp-auth-check',
					'editor-buttons',
					'mediea-views',
					'wp-pointer',
					'customize-preview',
					'wp-embed-template-ie',
					'imgareaselect',
					'wp-jquery-ui-dialog',
					'mediaelement',
					'wp-mediaelement',
					'thickbox',
					'wp-codemirror',
					'deprecated-media',
					'farbtastic',
					'jcrop',
					'colors-fresh',
					'open-sans',
					'wp-editor-font',
					'wp-block-library-theme',
					'wp-edit-blocks',
					'wp-block-editor',
					'wp-block-library',
					'wp-block-directory',
					'wp-components',
					'wp-editor',
					'wp-format-library',
					'wp-list-resuable-blocks',
					'wp-nux',
				)
			)
		);
	}
}
