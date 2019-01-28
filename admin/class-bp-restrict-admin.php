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
	
	private $option_name = 'basic_restrict';

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
		
		if ( is_admin() || is_customize_preview() ) {
			add_filter( 'redux/options/bp_restrict_opt/sections', array( $this, 'register_options' ) );
		}
		
		add_action( "template_redirect", array( $this, "restrict_rules" ) );
		
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
	
	public function register_options( $sections ) {
		$sections[] = array(
			
			'icon'       => 'el-icon-group',
			'icon_class' => 'icon-large',
			'title'      => __( 'Basic restrict', 'bp-restrict' ),
			'customizer' => false,
			'desc'       => __( 'Basic restriction settings for Logged-in or Guest users', 'bp-restrict' ),
			'fields'     => array(
				array(
					'id'       => $this->option_name,
					'type'     => 'callback',
					'title'    => __( 'Restriction settings', 'bp-restrict' ),
					'sub_desc' => '',
					'callback' => array( $this, 'data_set' ),
				)
			)
		);
		
		return $sections;
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
	
	/**
	 * Get saved restriction settings
	 * @return array
	 * @since 1.0
	 */
	function get_restrictions() {
		return bp_restrict()->option( $this->option_name );
	}
	
	
	/**
	 * Applies restrictions based on plugin options
	 * @return void
	 * @since 1.0
	 */
	function restrict_rules() {
		
		//no redirection for super-admin
		if ( is_super_admin() ) {
			return;
		}
		
		//if buddypress is not activated
		if ( ! function_exists( 'bp_is_active' ) ) {
			return;
		}
		
		//full current url
		$actual_link = bp_restrict()->get_full_url();
		
		//our request uri
		$home_url = home_url();
		
		//WPML support
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			global $sitepress;
			$home_url = $sitepress->language_url( ICL_LANGUAGE_CODE );
		}
		
		$home_url = str_replace( "www.", "", $home_url );
		$uri = str_replace( untrailingslashit( $home_url ), "", $actual_link );
		
		$all_restrictions = bp_restrict()->get_settings();
		
		//loop through remaining restrictions
		foreach ( $all_restrictions as $restriction ) {
			if(  isset( $restriction['logged_in'] ) && $restriction['logged_in'] ) {
				continue;
			}
			$v = str_replace( '%current_url%', '"'. $uri . '"', $restriction['condition'] );
			
			$check = false;
			eval( '$check = ' . $v . ';' );
			if ( $check ) {
				$this->check_access( $restriction['name'] );
			}
		}
		
		do_action( 'bp_restrict_pmpro_extra_restriction_rules', $this );
	}
	
	/**
	 * Checks $area for applied restrictions based on user status(logged in, membership level)
	 * and does the proper redirect
	 * @global object $current_user
	 *
	 * @param string $area
	 * @param array $restrict_options
	 * @param boolean $return Whether to just return true if the restriction should be applied
	 *
	 * @return boolean|void
	 * @since 1.0
	 */
	public function check_access( $area, $restrict_options = null, $return = false ) {
		global $current_user;
		
		if ( ! $restrict_options ) {
			$restrict_options = $this->get_restrictions();
		}
		
		$default_redirect = apply_filters( 'bp_restrict_url_redirect', bp_get_signup_page() );
		
		//no restriction
		if ( $restrict_options[ $area ]['type'] == 0 ) {
			return;
		}
		
		//restrict all members -> go to home url
		if ( $restrict_options[ $area ]['type'] == 1 ) {
			wp_redirect( apply_filters( 'bp_restrict_home_redirect', home_url() ) );
			exit;
		}
		
		if ( is_user_logged_in() ) {
			if ( $restrict_options[ $area ]['type'] == 2 ) {
				$this->return_restriction( $return, $default_redirect );
				exit;
			}
		} //not logged in
		else {
			if ( $restrict_options[ $area ]['type'] == 3 ) {
				$this->return_restriction( $return, $default_redirect );
				exit;
			}
		}
	}
	
	/**
	 * Calculate if we want to apply the redirect or just return true when restriction is applied
	 *
	 * @param boolean $return
	 * @param string $default_redirect
	 *
	 * @return boolean
	 *
	 * @since 4.0.3
	 */
	function return_restriction( $return = false, $default_redirect = null ) {
		$custom_link = apply_filters( 'bp_restrict_return_restriction_custom_link', $default_redirect );
		if ( $return === false ) {
			wp_redirect( $custom_link );
			exit;
		} else {
			return true;
		}
	}
	
	
	/**
	 * Options settings callback function
	 *
	 * @global object $wpdb
	 *
	 * @param string $field
	 * @param array $value
	 */
	public function data_set( $field, $value ) {
		
		global $wpdb;
		if ( empty( $value ) && ! is_array( $value ) ) {
			$value = [];
		}
		
		$restriction_options = bp_restrict()->get_settings();
		
		echo '<table class="membership-settings">';
		foreach ( $restriction_options as $pays ) :
			if (isset( $pays['logged_in'] ) && $pays['logged_in'] ) {
			continue;
			}
			?>
			<tr>
				<td scope="row" valign="top">
					<label for="<?php echo $pays['name']; ?>"><strong><?php echo $pays['title']; ?></strong></label>
				</td>
				<td>
					<select id="<?php echo $pays['name']; ?>"
					        name="<?php echo 'bp_restrict_opt' . '[' . $field['id'] . ']'; ?>[<?php echo $pays['name']; ?>][type]">
						<option value="0"
						        <?php if ( ! isset( $value[ $pays['name'] ]['type'] ) ) { ?>selected="selected"<?php } ?>><?php _e( 'No', 'bp-restrict' ); ?></option>
						<option value="1"
						        <?php if ( isset( $value[ $pays['name'] ]['type'] ) && $value[ $pays['name'] ]['type'] == 1 ) { ?>selected="selected"<?php } ?>><?php _e( 'Restrict All Members', 'bp-restrict' ); ?></option>
						<option value="2"
						        <?php if ( isset( $value[ $pays['name'] ]['type'] ) && $value[ $pays['name'] ]['type'] == 2 ) { ?>selected="selected"<?php } ?>><?php _e( 'Restrict Logged In Users', 'bp-restrict' ); ?></option>
						<option value="3"
						        <?php if ( isset( $value[ $pays['name'] ]['type'] ) && $value[ $pays['name'] ]['type'] == 3 ) { ?>selected="selected"<?php } ?>><?php _e( 'Restrict Guest Users', 'bp-restrict' ); ?></option>
					</select>
				</td>
			</tr>
		<?php endforeach; ?>
		
		<?php
		
		echo '</table>';
	}

}
