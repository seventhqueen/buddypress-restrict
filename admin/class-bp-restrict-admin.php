<?php
/**
 * The admin-specific functionality of the plugin.
 * bp_restrict
 *
 * @package   bp_restrict_Admin
 * @author    SeventhQueen <plugins@seventhqueen.com>
 * @license   GPL-2.0+
 * @link      http://wordpress.org/plugins
 * @copyright SeventhQueen
 */

/**
 * bp_restrict_Admin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *
 * @package bp_restrict_Admin
 * @author  SeventhQueen <plugins@seventhqueen.com>
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class bp_restrict_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	public $integrations;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->integrations = new stdClass();
		
		
	}

	/**
	 * Register class hooks
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		
		//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'after_setup_theme', array( $this, 'load_options' ) );
		
		add_action( 'plugins_loaded', array( $this, 'load_dependencies' ) );

	}

	/**
	 * Create a new instance of this class and register hooks
	 * @param $plugin_name
	 * @param $version
	 *
	 * @return bp_restrict_Admin
	 */
	public static function create( $plugin_name, $version ){
		$instance = new self( $plugin_name, $version );
		$instance->register_hooks();
		return $instance;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in bp_restrict_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The bp_restrict_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/main-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in bp_restrict_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The bp_restrict_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/main-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function load_options() {
		if ( file_exists( BP_RESTRICT_DIR . 'includes/function-options-init.php' ) ) {
			require_once( BP_RESTRICT_DIR . 'includes/function-options-init.php' );
		}
	}
	
	public function load_dependencies() {
		
		if ( function_exists( 'pmpro_url' ) ) {
			require_once( BP_RESTRICT_DIR . 'admin/class-bp-restrict-pmpro.php' );
			$this->integrations->pmpro = BP_Restrict_Pmpro::create();
		}
	}

}
