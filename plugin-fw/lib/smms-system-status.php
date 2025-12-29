<?php
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'SMMS_System_Status' ) ) {
	/**
	 * SMMS System Status Panel
	 *
	 * Setting Page to Manage Plugins
	 *
	 * @class      SMMS_System_Status
	 * @package    SMMS
	 * @since      1.0
	 * @author     softnwords
	 */
	class SMMS_System_Status {

		/**
		 * @var array The settings require to add the submenu page "System Status"
		 */
		protected $_settings = array();

		/**
		 * @var string the page slug
		 */
		protected $_page = 'smms_system_info';

		/**
		 * @var array plugins requirements list
		 */
		protected $_plugins_requirements = array();

		/**
		 * @var array requirements labels
		 */
		protected $_requirement_labels = array();

		/**
		 * Single instance of the class
		 *
		 * @var \SMMS_System_Status
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main plugin Instance
		 *
		 * @since  1.0.0
		 * @return SMMS_System_Status
		 * @author softnwords
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 * @return void
		 * @author softnwords
		 */
		public function __construct() {

			if ( ! is_admin() ) {
				return;
			}

			$system_info  = get_option( 'smms_system_info' );
			$error_notice  = ( @$system_info['errors'] === true) ? ' <span class="smms-system-info-menu update-plugins">!</span>' : '';

			$this->_settings = array(
				'parent_page' => 'smms_plugin_panel',
				'page_title'  => 'System Status',
				'menu_title'  => 'System Status' . $error_notice,
				'capability'  => 'manage_options',
				'page'        => $this->_page,
			);

			$this->_requirement_labels = array(
				'min_wp_version'    => 'WordPress Version', 
				'min_wc_version'    => 'WooCommerce Version',
				'wp_memory_limit'   => 'Available Memory', 
				'min_php_version'   => 'PHP Version',
				'min_tls_version'   => 'TLS Version',
				'wp_cron_enabled'   => 'WordPress Cron',
				'simplexml_enabled' => 'SimpleXML', 
				'mbstring_enabled'  => 'MultiByte String',
				'imagick_version'   => 'ImageMagick Version',
				'gd_enabled'        => 'GD Library', 
				'opcache_enabled'   => 'OPCache Save Comments',
				'url_fopen_enabled' => 'URL FOpen',
			);

			add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 99 );
			add_action( 'admin_init', array( $this, 'check_system_status' ) );
			add_action( 'admin_notices', array( $this, 'activate_system_notice' ), 15 );
			add_action( 'admin_enqueue_scripts', array( $this, 'dismissable_notice' ), 20 );


		}

		/**
		 * Add "System Information" submenu page under SMMS Plugins
		 *
		 * @since  1.0.0
		 * @return void
		 * @author softnwords
		 */
		public function add_submenu_page() {
			add_submenu_page(
				$this->_settings['parent_page'],
				$this->_settings['page_title'],
				$this->_settings['menu_title'],
				$this->_settings['capability'],
				$this->_settings['page'],
				array( $this, 'show_information_panel' )
			);
		}

		/**
		 * Add "System Information" page template under SMMS Plugins
		 *
		 * @since  1.0.0
		 * @return void
		 * @author softnwords
		 */
		public function show_information_panel() {

			$path   = defined( 'SMM_CORE_PLUGIN_PATH' ) ? SMM_CORE_PLUGIN_PATH : get_template_directory() . '/core/plugin-fw/';
			$labels = $this->_requirement_labels;

			require_once( $path . '/templates/sysinfo/system-information-panel.php' );

		}

		/**
		 * Perform system status check
		 *
		 * @since  1.0.0
		 * @return void
		 * @author softnwords
		 */
		public function check_system_status() {

			if ( '' == get_option( 'smms_system_info' ) || ( isset( $_GET['page'] ) && $_GET['page'] == $this->_page ) ) {

				$this->add_requirements( __( 'SMMS Plugins', 'smm-api' ), array( 'min_wp_version' => '4.9', 'min_wc_version' => '3.4', 'min_php_version' => '5.6.20' ) );
				$this->add_requirements( __( 'WooCommerce', 'smm-api' ), array( 'wp_memory_limit' => '64M' ) );

				$system_info   = $this->get_system_info();
				$check_results = array();
				$errors        = false;

				foreach ( $system_info as $key => $value ) {
					$check_results[ $key ] = array( 'value' => $value );

					if ( isset( $this->_plugins_requirements[ $key ] ) ) {

						foreach ( $this->_plugins_requirements[ $key ] as $plugin_name => $required_value ) {

							switch ( $key ) {
								case 'wp_cron_enabled'  :
								case 'mbstring_enabled' :
								case 'simplexml_enabled':
								case 'gd_enabled':
								case 'url_fopen_enabled':
								case 'opcache_enabled'  :

									if ( ! $value ) {
										$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
										$errors                                          = true;
									}
									break;

								case 'wp_memory_limit'  :
									$required_memory = $this->memory_size_to_num( $required_value );

									if ( $required_memory > $value ) {
										$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
										$errors                                          = true;
									}
									break;

								default:
									if ( ! version_compare( $value, $required_value, '>=' ) ) {
										$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
										$errors                                          = true;
									}

							}

						}

					}

				}

				update_option( 'smms_system_info', array( 'system_info' => $check_results, 'errors' => $errors ) );

			}

		}

		/**
		 * Handle plugin requirements
		 *
		 * @since  1.0.0
		 *
		 * @param $plugin_name  string
		 * @param $requirements array
		 *
		 * @return void
		 * @author softnwords
		 */
		public function add_requirements( $plugin_name, $requirements ) {

			$allowed_requirements = array_keys( $this->_requirement_labels );

			foreach ( $requirements as $requirement => $value ) {

				if ( in_array( $requirement, $allowed_requirements ) ) {
					$this->_plugins_requirements[ $requirement ][ $plugin_name ] = $value;
				}
			}

		}

		/**
		 * Manages notice dismissing
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  softnwords
		 */
		public function dismissable_notice() {
			$script_path = defined( 'SMM_CORE_PLUGIN_URL' ) ? SMM_CORE_PLUGIN_URL : get_template_directory_uri() . '/core/plugin-fw';
			$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'smms-system-info', $script_path . '/assets/js/smms-system-info' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
		}

		/**
		 * Show system notice
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  softnwords
		 */
		public function activate_system_notice() {

			$system_info = get_option( 'smms_system_info', '' );

			if ( ( isset( $_GET['page'] ) && $_GET['page'] == $this->_page ) || ( ! empty( $_COOKIE['hide_smms_system_alert'] ) && 'yes' == $_COOKIE['hide_smms_system_alert'] ) || ( $system_info == '' ) || ( $system_info != '' && $system_info['errors'] === false ) ) {
				return;
			}

			$show_notice = true;

			if ( true === $show_notice ) :
				wp_enqueue_script( 'smms-system-info' );
				?>
                <div id="smms-system-alert" class="notice notice-error is-dismissible" style="position: relative;">
                    <p>
                        <span class="smms-logo"><img src="<?php echo esc_url( smms_plugin_fw_get_default_logo())?>" /></span>
                        <b><?php /* translators: search here */ esc_html_e( 'Warning!', 'smm-api' ) ?></b><br />
						<?php printf('The system check has detected some compatibility issues on your installation. <a href="%s">Click here to know more</a>' , esc_url( add_query_arg( array( 'page' => $this->_page ) )) )  ?>
                    </p>
                    <span class="notice-dismiss"></span>

                </div>
			<?php endif;
		}

		/**
		 * Get system information
		 *
		 * @since   1.0.0
		 * @return  array
		 * @author  softnwords
		 */
		public function get_system_info() {

			//Get TLS version
			//$ch = curl_init( 'https://www.howsmyssl.com/a/check' );
			//curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			
			//curl_close( $ch );
			$json = "NA";
			//json_decode( $data );
			//$tls  = str_replace( 'TLS ', '', $json->tls_version );
			$tls  = str_replace( 'TLS ', '', $json);

			//Get PHP version
			preg_match( "#^\d+(\.\d+)*#", PHP_VERSION, $match );
			$php_version = $match[0];

			// WP memory limit.
			$wp_memory_limit = $this->memory_size_to_num( WP_MEMORY_LIMIT );
			if ( function_exists( 'memory_get_usage' ) ) {
				$wp_memory_limit = max( $wp_memory_limit, $this->memory_size_to_num( @ini_get( 'memory_limit' ) ) );
			}

			$imagick_version = '0.0.0';
			if ( class_exists( 'Imagick' ) ) {
				preg_match( "/([0-9]+\.[0-9]+\.[0-9]+)/", Imagick::getVersion()['versionString'], $imatch );
				$imagick_version = $imatch[0];
			}

			return apply_filters( 'smms_system_additional_check', array(
				'min_wp_version'    => get_bloginfo( 'version' ),
				'min_wc_version'    => function_exists( 'WC' ) ? WC()->version : __( 'Not installed', 'smm-api' ),
				'wp_memory_limit'   => $wp_memory_limit,
				'min_php_version'   => $php_version,
				'min_tls_version'   => $tls,
				'imagick_version'   => $imagick_version,
				'wp_cron_enabled'   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
				'mbstring_enabled'  => extension_loaded( 'mbstring' ),
				'simplexml_enabled' => extension_loaded( 'simplexml' ),
				'gd_enabled'        => extension_loaded( 'gd' ) && function_exists( 'gd_info' ),
				'opcache_enabled'   => ini_get( 'opcache.save_comments' ),
				'url_fopen_enabled' => ini_get( 'allow_url_fopen' ),
			) );

		}

		/**
		 * Convert site into number
		 *
		 * @since   1.0.0
		 *
		 * @param   $memory_size string
		 *
		 * @return  integer
		 * @author  softnwords
		 */
		public function memory_size_to_num( $memory_size ) {
			$unit       = strtoupper( substr( $memory_size, - 1 ) );
			$size       = substr( $memory_size, 0, - 1 );
			$multiplier = array(
				'P' => 5,
				'T' => 4,
				'G' => 3,
				'M' => 2,
				'K' => 1,
			);
			for ( $i = 1; $i <= $multiplier[ $unit ]; $i ++ ) {
				$size *= 1024;
			}

			return $size;
		}

	}
}

/**
 * Main instance of plugin
 *
 * @return SMMS_System_Status object
 * @since  1.0
 * @author softnwords
 */
if ( ! function_exists( 'SMMS_System_Status' ) ) {
	function SMMS_System_Status() {
		return SMMS_System_Status::instance();
	}
}

SMMS_System_Status();
