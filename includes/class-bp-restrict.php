<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wordpress.org/plugins
 * @since      1.0.0
 *
 * @package    bp_restrict
 * @subpackage bp_restrict/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    bp_restrict
 * @subpackage bp_restrict/includes
 * @author     SeventhQueen <plugins@seventhqueen.com>
 */
class BP_Restrict {

	/**
	 *
	 * @var BP_Restrict
	 */
	private static $instance;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The class responsible for defining internationalization functionality
	 * of the plugin.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     bp_restrict_i18n
	 */
	protected $i18n;

	/**
	 * The class responsible for defining all actions that occur in the admin area.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     bp_restrict_Admin
	 */
	public $admin;

	/**
	 * The class responsible for defining all actions that occur in the public-facing
	 * side of the site.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     bp_restrict_Public
	 */
	public $public;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'bp-restrict';
		$this->version     = '1.0.0';

	}

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function option( $name = '' ) {
		if ( '' == $name ) {
			return false;
		}
		$options = get_option( 'bp_restrict_opt' );

		if ( is_array( $options ) && isset( $options[ $name ] ) ) {
			return $options[ $name ];
		}

		return false;

	}

	public function get_settings() {
		$settings      = [];
		$allowed_chars = apply_filters( 'bp_restrict_allowed_chars', "a-z 0-9~%.:_\-" );
		$members_slug  = str_replace( '/', '\/', bp_get_members_root_slug() );

		$settings[] = array(
			'title'     => __( 'Members directory restriction', 'bp-restrict' ),
			'front'     => __( 'View members directory', 'bp-restrict' ),
			'name'      => 'members_dir',
			'condition' => 'bp_is_members_directory()',
		);

		$settings[] = array(
			'title'     => __( 'Restrict viewing other profiles', 'bp-restrict' ),
			'front'     => __( 'View members profile', 'bp-restrict' ),
			'name'      => 'view_profiles',
			'condition' => 'bp_is_user() && ! bp_is_my_profile()',
		);

		if ( function_exists( 'bp_get_groups_root_slug' ) ) {
			$settings[] = array(
				'title'     => __( 'Groups directory restriction', 'bp-restrict' ),
				'front'     => __( 'Access group directory', 'bp-restrict' ),
				'name'      => 'groups_dir',
				'condition' => 'bp_is_groups_directory()',
			);
			$settings[] = array(
				'title'     => __( 'Group page restriction', 'bp-restrict' ),
				'front'     => __( 'Access to groups', 'bp-restrict' ),
				'name'      => 'view_groups',
				'condition' => 'bp_is_group()',
			);
		}

		if ( function_exists( 'bp_get_activity_root_slug' ) ) {
			$settings[] = array(
				'title'     => __( 'Site activity restriction', 'bp-restrict' ),
				'front'     => __( 'View site activity', 'bp-restrict' ),
				'name'      => 'show_activity',
				'condition' => 'bp_is_activity_directory()',
			);
		}


		$settings[] = array(
			'title'     => __( 'Sending private messages restriction', 'bp-restrict' ),
			'front'     => __( 'Send Private messages', 'bp-restrict' ),
			'name'      => 'pm',
			'logged_in' => true,
			'condition' => 'preg_match("/\/' . $members_slug . '\/" . bp_get_loggedin_user_username() . "\/messages\/compose\/?/", %current_url% )',
		);

		$settings[] = array(
			'title'     => __( 'Viewing private messages restriction', 'bp-restrict' ),
			'front'     => __( 'View Private messages', 'bp-restrict' ),
			'name'      => 'pm_view',
			'logged_in' => true,
			'condition' => 'preg_match("/\/' . $members_slug . '\/" . bp_get_loggedin_user_username() . "\/messages\/view\/[' . $allowed_chars . '\/]?\/?/", %current_url% )',
		);

		$settings[] = array(
			'title'     => __( 'RtMedia plugin - Restrict users from adding media.', 'bp-restrict' ),
			'front'     => __( 'Add media to your profile', 'bp-restrict' ),
			'name'      => 'add_media',
			'logged_in' => true,
			'condition' => 'preg_match("/\/' . $members_slug . '\/" . bp_get_loggedin_user_username() . "\/media\/?/", %current_url% )' .
			               '|| preg_match("/\/' . $members_slug . '\/" . bp_get_loggedin_user_username() . "\/album\/?/", %current_url% )',
		);

		$settings = apply_filters( 'bp_restrict_settings', $settings );

		return $settings;
	}

	/**
	 * Get the current page url
	 * @return string
	 */
	function get_full_url() {
		$s        = empty( $_SERVER["HTTPS"] ) ? '' : ( ( $_SERVER["HTTPS"] === "on" ) ? "s" : '' );
		$protocol = substr( strtolower( $_SERVER["SERVER_PROTOCOL"] ), 0, strpos( strtolower( $_SERVER["SERVER_PROTOCOL"] ), "/" ) ) . $s;
		$port     = ( $_SERVER["SERVER_PORT"] == "80" || $_SERVER["SERVER_PORT"] == "443" ) ? "" : ( ":" . $_SERVER["SERVER_PORT"] );
		$uri      = $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
		$segments = explode( '?', $uri, 2 );
		$url      = $segments[0];
		$url      = str_replace( "www.", "", $url );

		return $url;
	}

	/**
	 * Load the required dependencies for this plugin.     *
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		if ( is_admin() || is_customize_preview() ) {
			if ( ! class_exists( 'ReduxFramework' ) && file_exists( BP_RESTRICT_DIR . 'includes/options/framework.php' ) ) {
				require_once BP_RESTRICT_DIR . 'includes/options/framework.php';

			}
		}


		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bp-restrict-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-bp-restrict-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-bp-restrict-public.php';
	}

	/**
	 * Load class instances and register hooks
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function load_classes() {

		$this->i18n   = bp_restrict_i18n::create( $this->get_plugin_name(), $this->get_version() );
		$this->public = bp_restrict_Public::create( $this->get_plugin_name(), $this->get_version() );
		$this->admin  = bp_restrict_Admin::create( $this->get_plugin_name(), $this->get_version() );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since   1.0.0
	 */
	public function run() {

		$this->load_dependencies();
		$this->load_classes();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {

		return $this->plugin_name;

	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {

		return $this->version;

	}

}
