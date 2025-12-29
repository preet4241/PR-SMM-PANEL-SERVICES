<?php
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'SMM_Plugin_SubPanel' ) ) {
    /**
     * SMM Plugin Panel
     *
     * Setting Page to Manage Plugins
     *
     * @class      SMM_Plugin_Panel
     * @package    SMMS
     * @since      1.0
     * @author     Softnwords Themes
     */
    class SMM_Plugin_SubPanel extends SMM_Plugin_Panel {

        /**
         * @var string version of class
         */
        public $version = '1.0.0';

        /**
         * @var array a setting list of parameters
         */
        public $settings = array();


        /**
         * @var array
         */
        private $_main_array_options = array();

        /**
         * Constructor
         *
         * @since  1.0
         * @author sam softnwords
         */

        public function __construct( $args = array() ) {
            if ( !empty( $args ) ) {
                $this->settings             = $args;
                $this->settings[ 'parent' ] = $this->settings[ 'page' ];
                $this->_tabs_path_files     = $this->get_tabs_path_files();

                add_action( 'admin_init', array( $this, 'register_settings' ) );
                add_action('admin_menu', array($this, 'add_setting_page'), 10); // normal priority
				add_action('admin_menu', array($this, 'fix_admin_menu'), 99);   // later priority to safely remove submenu
                add_action( 'admin_bar_menu', array( &$this, 'add_admin_bar_menu' ), 100 );
                add_action( 'admin_init', array( &$this, 'add_fields' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            }
        }


        /**
         * Register Settings
         *
         * Generate wp-admin settings pages by registering your settings and using a few callbacks to control the output
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function register_settings() {
            register_setting( 'smm_' . $this->settings[ 'page' ] . '_options', 'smm_' . $this->settings[ 'page' ] . '_options', array( &$this, 'options_validate' ) );
        }


        /**
         * Add Setting SubPage
         *
         * add Setting SubPage to wordpress administrator
         *
         * @return array validate input fields
         * @since    1.0
         * @author   sam softnwords
         */
        /*public function add_setting_page() {
            global $admin_page_hooks;
            $logo = smms_plugin_fw_get_default_logo();

            $admin_logo = function_exists( 'smm_get_option' ) ? smm_get_option( 'admin-logo-menu' ) : '';

            if ( isset( $admin_logo ) && !empty( $admin_logo ) && $admin_logo != '' && $admin_logo ) {
                $logo = $admin_logo;
            }

            if ( !isset( $admin_page_hooks[ 'smms_plugin_panel' ] ) ) {
                $position = apply_filters( 'smm_plugins_menu_item_position', '62.32' );
                add_menu_page( 'smms_plugin_panel', 'SMM-API', 'nosuchcapability', 'smms_plugin_panel', null, $logo, $position );
                
				$admin_page_hooks[ 'smms_plugin_panel' ] = 'smms-plugins'; // prevent issues for backward compatibility
            }

            add_submenu_page( 'smms_plugin_panel', $this->settings[ 'label' ], $this->settings[ 'label' ], 'manage_options', $this->settings[ 'page' ], array( $this, 'smm_panel' ) );
            remove_submenu_page( 'smms_plugin_panel', 'smms_plugin_panel' );

        }*/
		public function add_setting_page() {
			global $admin_page_hooks;
    
			$logo = smms_plugin_fw_get_default_logo();
			$admin_logo = function_exists('smm_get_option') ? smm_get_option('admin-logo-menu') : '';

				if (!empty($admin_logo)) {
					$logo = $admin_logo;
					}

		if (!isset($admin_page_hooks['smms_plugin_panel'])) {
        $position = apply_filters('smm_plugins_menu_item_position', '62.32');
        
        add_menu_page(
            'SMM-API Settings',   // Correct page title
            'SMM-API',            // Menu title
            'manage_options',     // Correct capability
            'smms_plugin_panel',  // Menu slug
            array($this, 'smm_panel'), // Callback to render main page
            $logo,
            $position
        );

        $admin_page_hooks['smms_plugin_panel'] = 'smms-plugins'; // for backward compatibility
		}

		// Only add the submenu if it is different from the main menu
		add_submenu_page(
        'smms_plugin_panel',
        $this->settings['label'],
        $this->settings['label'],
        'manage_options',
        $this->settings['page'],
        array($this, 'smm_panel')
		);
		
		}

	// Separate function to remove default submenu
	public function fix_admin_menu() {
    remove_submenu_page('smms_plugin_panel', 'smms_plugin_panel');
	}

        /**
         * Show a tabbed panel to setting page
         *
         * a callback function called by add_setting_page => add_submenu_page
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function smm_panel() {
            $tabs        = '';
            $current_tab = $this->get_current_tab();
            $smm_options = $this->get_main_array_options();


            // tabs
            foreach ( $this->settings[ 'admin-tabs' ] as $tab => $tab_value ) {
                $active_class = ( $current_tab == $tab ) ? ' nav-tab-active' : '';
                $tabs         .= '<a class="nav-tab' . esc_attr($active_class) . '" href="?page=' . esc_attr($this->settings[ 'page' ]) . '&tab=' . esc_attr($tab) . '">' . esc_html($tab_value) . '</a>';
            }
            ?>
            <div id="icon-themes" class="icon32"><br/></div>
            <h2 class="nav-tab-wrapper">
                <?php 
			 $allowed_html = array(
				 'a'   		=> array(),
				 'class'	=> array(),
				 'href'		=> array()
			 );
			echo wp_kses($tabs, $allowed_html) ?>
            </h2>
            <?php
            $custom_tab_action = $this->is_custom_tab( $smm_options, $current_tab );
            if ( $custom_tab_action ) {
                $this->print_custom_tab( $custom_tab_action );

                return;
            }
            ?>
            <?php
            $panel_content_class = apply_filters( 'smm_admin_panel_content_class', 'smm-admin-panel-content-wrap' );
            ?>
            <div id="wrap" class="smms-plugin-fw plugin-option smm-admin-panel-container">
                <?php $this->message(); ?>
                <div class="<?php echo esc_attr ($panel_content_class) ?>">
                    <h2><?php echo esc_html( $this->get_tab_title()) ?></h2>
                    <?php if ( $this->is_show_form() ) : ?>
                        <form id="smms-plugin-fw-panel" method="post" action="options.php">
                            <?php do_settings_sections( 'smm' ); ?>
                            <p>&nbsp;</p>
                            <?php settings_fields( 'smm_' . $this->settings[ 'parent' ] . '_options' ); ?>
                            <input type="hidden" name="<?php echo esc_attr( $this->get_name_field( 'current_tab' )) ?>" value="<?php echo esc_attr( $current_tab ) ?>"/>
                            <input type="submit" class="button-primary" value="<?php echo esc_attr( 'Save Changes') ?>" style="float:left;margin-right:10px;"/>
                        </form>
                        <form method="post">
                            <?php $warning = sprintf(esc_html( 'If you continue with this action, you will reset all options in this page.', 'smm-api' ) )?>
                            <input type="hidden" name="smm-action" value="reset"/>
                            <input type="submit" name="smm-reset" class="button-secondary" value="<?php esc_attr_e( 'Reset to default', 'smm-api' ) ?>"
                                   onclick="return confirm('<?php printf( '%s', 
									esc_html($warning)) . '\n' . esc_html_e( 'Are you sure?', 'smm-api' ) ?>');"/>
                        </form>
                        <p>&nbsp;</p>
                    <?php endif ?>
                </div>
            </div>
            <?php
        }


    }

}

