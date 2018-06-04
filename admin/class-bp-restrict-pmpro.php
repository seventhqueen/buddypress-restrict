<?php
/**
 * PMPRO Restrictions
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
 * @package bp_restrict
 * @author  SeventhQueen <plugins@seventhqueen.com>
 */
/**
 * Paid Memberships Pro integration
 *
 * @package    BuddyPress Restrict
 * @subpackage BuddyPress Restrict/admin/pmpro
 * @author     SeventhQueen <plugins@seventhqueen.com>
 */
class BP_Restrict_Pmpro {
	
	private $option_name = 'pmpro_restrict';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'profile_message_ux_send_private_message' ), 2 );
		add_action( "template_redirect", array( $this, "restrict_rules" ) );
		
		add_shortcode( 'bp_restrict_pmpro_access', array( $this, 'access_func' ) );
		add_action( 'bp_before_member_header_meta', array( $this, 'membership_info' ) );
		
		if ( is_admin() || is_customize_preview() ) {
			add_filter( 'redux/options/bp_restrict_opt/sections', array( $this, 'register_options' ) );
		}
		
	}

	/**
	 * Create a new instance of this class and register hooks
	 *
	 * @return bp_restrict_Pmpro
	 */
	public static function create(){
		$instance = new self();
		return $instance;
	}
	
	
	/**
	 * Get saved restriction settings
	 * @return array
	 * @since 1.0
	 */
	function get_restrictions() {
		return bp_restrict()->option( $this->option_name );
	}

	
	public function register_options( $sections ) {
		$sections[] = array(
			
			'icon'       => 'el-icon-group',
			'icon_class' => 'icon-large',
			'title'      => __( 'PMPRO restrict', 'bp-restrict' ),
			'customizer' => false,
			'desc'       => __( 'Settings related to restrictions for Paid Memberships Pro plugin', 'bp-restrict' ),
			'fields'     => array(
				array(
					'id'       => $this->option_name,
					'type'     => 'callback',
					'title'    => __( 'Membership settings', 'bp-restrict' ),
					'sub_desc' => '',
					'callback' => array( $this, 'pmpro_data_set' ),
				)
			)
		);
		
		return $sections;
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
		
		//if PMPRO is not activated
		if ( ! function_exists( 'pmpro_url' ) ) {
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
		
		if ( pmpro_url( "levels" ) ) {
			$default_redirect = pmpro_url( "levels" );
		} else {
			$default_redirect = bp_get_signup_page();
		}
		$default_redirect = apply_filters( 'bp_restrict_pmpro_url_redirect', $default_redirect );
		
		//no restriction
		if ( $restrict_options[ $area ]['type'] == 0 ) {
			return;
		}
		
		//restrict all members -> go to home url
		if ( $restrict_options[ $area ]['type'] == 1 ) {
			wp_redirect( apply_filters( 'bp_restrict_pmpro_home_redirect', home_url() ) );
			exit;
		}
		
		//is a member
		if ( isset( $current_user->membership_level ) && $current_user->membership_level->ID ) {
			
			//if restrict my level
			if ( $restrict_options[ $area ]['type'] == 2 && isset( $restrict_options[ $area ]['levels'] ) && is_array( $restrict_options[ $area ]['levels'] ) && ! empty( $restrict_options[ $area ]['levels'] ) && pmpro_hasMembershipLevel( $restrict_options[ $area ]['levels'] ) ) {
				$this->return_restriction( $return, $default_redirect );
				exit;
			}
			
			//logged in but not a member
		} else if ( is_user_logged_in() ) {
			if ( $restrict_options[ $area ]['type'] == 2 && isset( $restrict_options[ $area ]['not_member'] ) && $restrict_options[ $area ]['not_member'] == 1 ) {
				$this->return_restriction( $return, $default_redirect );
				exit;
			}
		} //not logged in
		else {
			if ( $restrict_options[ $area ]['type'] == 2 && isset( $restrict_options[ $area ]['guest'] ) && $restrict_options[ $area ]['guest'] == 1 ) {
				$this->return_restriction( $return, $default_redirect );
				exit;
			}
		}
	}


	function has_access( $area, $user_id = false ) {
		if ( ! function_exists( 'pmpro_url' ) ) {
			return false;
		}
		if ( ! $user_id ) {
			global $current_user;
			$user = $current_user;
		} else {
			$user = get_user_by( 'ID', $user_id );
		}
		$restrict_options = $this->get_restrictions();
		
		
		//no restriction
		if ( $restrict_options[ $area ]['type'] == 0 ) {
			return true;
		}
		
		//restrict all members -> go to home url
		if ( $restrict_options[ $area ]['type'] == 1 ) {
			return false;
		}
		
		//is a member
		if ( isset( $user->membership_level ) && $user->membership_level->ID ) {
			
			//if restrict my level
			if ( $restrict_options[ $area ]['type'] == 2 && isset( $restrict_options[ $area ]['levels'] )
			     && is_array( $restrict_options[ $area ]['levels'] )
			     && ! empty( $restrict_options[ $area ]['levels'] )
			     && pmpro_hasMembershipLevel( $restrict_options[ $area ]['levels'], $user->ID ) ) {
				return false;
			}
			
			//logged in but not a member
		} else if ( is_user_logged_in() ) {
			if ( $restrict_options[ $area ]['type'] == 2 && isset( $restrict_options[ $area ]['not_member'] )
			     && $restrict_options[ $area ]['not_member'] == 1 ) {
				return false;
			}
		} //not logged in
		else {
			if ( $restrict_options[ $area ]['type'] == 2 && isset( $restrict_options[ $area ]['guest'] )
			     && $restrict_options[ $area ]['guest'] == 1 ) {
				return false;
			}
		}
		
		return true;
	}


	function access_func( $atts, $content = "" ) {
		$atts = shortcode_atts( array(
			'area' => '',
			'user' => '',
			'type' => 'access' //no_access
		), $atts, 'bp_restrict_pmpro_access' );
		
		if ( ! $atts['user'] ) {
			$user_id = get_current_user_id();
		} else {
			$user = get_user_by( 'login', $atts['user'] );
			if ( ! $user ) {
				return '';
			}
			$user_id = $user->ID;
		}
		
		
		if ( $atts['area'] != '' ) {
			if ( $atts['type'] == 'access' && $this->has_access( $atts['area'], $user_id ) ) {
				return $content;
			} elseif ( $atts['type'] == 'no_access' && ! $this->has_access( $atts['area'], $user_id ) ) {
				return $content;
			}
		}
		
		return '';
		
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
	 * Add membership info next to profile page username
	 * @since 1.0
	 */
	function membership_info() {
		global $membership_levels, $current_user;
		if ( ! $membership_levels ) {
			return;
		}
		
		if ( bp_is_my_profile() ) {
			if ( isset( $current_user->membership_level ) && $current_user->membership_level->ID ) {
				echo '<a href="' . pmpro_url( "account" ) . '"><span class="label radius pmpro_label">' . $current_user->membership_level->name . '</span></a>';
			} else {
				echo '<a href="' . pmpro_url( "levels" ) . '"><span class="label radius pmpro_label">' . __( "Upgrade account", 'bp-restrict' ) . '</span></a>';
			}
		}
	}



	

	/**
	 * BP Profile Message UX compatibility
	 * @since 4.0.3
	 */
	public function profile_message_ux_send_private_message() {
		if ( isset( $_POST['private_message_content'] ) && ! empty( $_POST['private_message_content'] ) ) {
			$content_restricted = __( "You aren't allowed to perform this action", "buddypress_restrict" );
			
			if ( $this->check_access( 'pm', null, true ) ) {
				bp_core_add_message( $content_restricted, 'error' );
				bp_core_redirect( bp_displayed_user_domain() );
			}
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
	public function pmpro_data_set( $field, $value ) {
		
		global $wpdb;
		if ( empty( $value ) && ! is_array( $value ) ) {
			$value = [];
		}
		
		$restriction_options = bp_restrict()->get_settings();
		$sqlQuery = "SELECT * FROM $wpdb->pmpro_membership_levels";
		$levels   = $wpdb->get_results( $sqlQuery, OBJECT );
		
		echo '<table class="membership-settings">';
		foreach ( $restriction_options as $pays ) :
			?>
			<tr>
				<td scope="row" valign="top">
					<label for="<?php echo $field['id'];?>_<?php echo $pays['name']; ?>"><strong><?php echo $pays['title']; ?></strong></label>
				</td>
				<td>
					<select id="<?php echo $field['id'];?>_<?php echo $pays['name']; ?>"
					        name="<?php echo 'bp_restrict_opt' . '[' . $field['id'] . ']'; ?>[<?php echo $pays['name']; ?>][type]"
					        onchange="bp_restrict_pmpro_update<?php echo $field['id'] . '_' . $pays['name']; ?>TRs();">
						<option value="0"
						        <?php if ( ! isset( $value[ $pays['name'] ]['type'] ) ) { ?>selected="selected"<?php } ?>><?php _e( 'No', 'pmpro' ); ?></option>
						<option value="1"
						        <?php if ( isset( $value[ $pays['name'] ]['type'] ) && $value[ $pays['name'] ]['type'] == 1 ) { ?>selected="selected"<?php } ?>><?php _e( 'Restrict All Members', 'pmpro' ); ?></option>
						<option value="2"
						        <?php if ( isset( $value[ $pays['name'] ]['type'] ) && $value[ $pays['name'] ]['type'] == 2 ) { ?>selected="selected"<?php } ?>><?php _e( 'Restrict Certain Levels', 'pmpro' ); ?></option>
					</select>
				</td>
			</tr>
			<tr id="<?php echo $field['id'] . '_' . $pays['name']; ?>levels_tr"
			    <?php if ( isset( $value[ $pays['name'] ]['type'] ) && $value[ $pays['name'] ]['type'] != 2 ) { ?>style="display: none;"<?php } ?>>
				<td scope="row" valign="top">
					<label
						for="<?php echo 'bp_restrict_opt' . '[' . $field['id'] . ']'; ?>[<?php echo $pays['name']; ?>][levels][]"><?php _e( 'Choose Levels to Restrict', 'pmpro' ); ?>
						:</label>
				</td>
				<td>
					<div class="checkbox_box"
					     <?php if ( count( $levels ) > 3 ) { ?>style="height: 100px; overflow: auto;"<?php } ?>>
						<div class="clickable"><label><input type="checkbox" id="<?php echo $pays['name']; ?>levels_guest"
						                                     name="<?php echo 'bp_restrict_opt' . '[' . $field['id'] . ']'; ?>[<?php echo $pays['name']; ?>][guest]"
						                                     value="1"
						                                     <?php if ( isset( $value[ $pays['name'] ]['guest'] ) && $value[ $pays['name'] ]['guest'] == 1 ) { ?>checked="checked"<?php } ?>> <?php echo __( "Not logged in", "buddypress_restrict" ); ?>
							</label></div>
						<div class="clickable"><label><input type="checkbox"
						                                     id="<?php echo $pays['name']; ?>levels_not_member"
						                                     name="<?php echo 'bp_restrict_opt' . '[' . $field['id'] . ']'; ?>[<?php echo $pays['name']; ?>][not_member]"
						                                     value="1"
						                                     <?php if ( isset( $value[ $pays['name'] ]['not_member'] ) && $value[ $pays['name'] ]['not_member'] == 1 ) { ?>checked="checked"<?php } ?>> <?php echo __( "Not members", "buddypress_restrict" ); ?>
							</label></div>
						<?php
						if ( isset( $value[ $pays['name'] ]['levels'] ) ) {
							if ( ! is_array( $value[ $pays['name'] ]['levels'] ) ) {
								$value[ $pays['name'] ]['levels'] = explode( ",", $value[ $pays['name'] ]['levels'] );
							}
						} else {
							$value[ $pays['name'] ]['levels'] = array();
						}
						foreach ( $levels as $level ) {
							?>
							<div class="clickable"><label><input type="checkbox" class="bp-restrict-no-click-event"
							                                     id="<?php echo $pays['name']; ?>levels_<?php echo $level->id; ?>"
							                                     name="<?php echo 'bp_restrict_opt' . '[' . $field['id'] . ']'; ?>[<?php echo $pays['name']; ?>][levels][]"
							                                     value="<?php echo $level->id; ?>"
							                                     data-initval="<?php echo $level->id; ?>"
							                                     <?php if ( in_array( $level->id, $value[ $pays['name'] ]['levels'] ) ) { ?>checked="checked"<?php } ?>> <?php echo $level->name ?>
								</label></div>
							<?php
						}
						?>
					</div>
				</td>
			</tr>
			
			<script>
				function bp_restrict_pmpro_update<?php echo $field['id'] . '_' . $pays['name'];?>TRs() {
					var <?php echo $pays['name'];?> = jQuery('#<?php echo $field['id'] . '_' . $pays['name'];?>').val();
					if ( <?php echo $pays['name'];?> == 2 ) {
						jQuery('#<?php echo $field['id'] . '_' . $pays['name'];?>levels_tr').show();
					} else {
						jQuery('#<?php echo $field['id'] . '_' . $pays['name'];?>levels_tr').hide();
					}
	
					if ( <?php echo $pays['name'];?> > 0 ) {
						jQuery('#<?php echo $field['id'] . '_' . $pays['name'];?>_explanation').show();
					} else {
						jQuery('#<?php echo $field['id'] . '_' . $pays['name'];?>_explanation').hide();
					}
				}
				bp_restrict_pmpro_update<?php echo $field['id'] . '_' . $pays['name'];?>TRs();
			</script>
		<?php endforeach; ?>
		
		<?php
		
		echo '</table>';
	}
	
}
