<?php
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// Exit if accessed directly

if ( !class_exists( 'SMM_Plugin_Panel' ) ) {
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
    class SMM_Plugin_Panel {

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
        protected $_tabs_path_files;

        /**
         * @var array
         */
        private $_main_array_options = array();

        /**
         * @var array
         */
        public $links;

        /**
         * @var bool
         */
        protected static $_actions_initialized = false;

        /**
         * Constructor
         *
         * @since  1.0
         * @author sam softnwords
         *
         * @param array $args
         */
        public function __construct( $args = array() ) {

            if ( !empty( $args ) ) {

                $default_args = array(
                    'parent_slug' => 'edit.php?',
                    'page_title'  => __( 'Plugin Settings', 'smm-api' ),
                    'menu_title'  => __( 'Settings', 'smm-api' ),
                    'capability'  => 'manage_options',
                    'icon_url'    => '',
                    'position'    => null
                );

                $args = apply_filters( 'smm_plugin_fw_panel_option_args', wp_parse_args( $args, $default_args ) );
                if ( isset( $args[ 'parent_page' ] ) && 'smm_plugin_panel' === $args[ 'parent_page' ] )
                    $args[ 'parent_page' ] = 'smms_plugin_panel';

                $this->settings         = $args;
                $this->_tabs_path_files = $this->get_tabs_path_files();

                if ( isset( $this->settings[ 'create_menu_page' ] ) && $this->settings[ 'create_menu_page' ] ) {
                    $this->add_menu_page();
                }

                if ( !empty( $this->settings[ 'links' ] ) ) {
                    $this->links = $this->settings[ 'links' ];
                }

                add_action( 'admin_init', array( $this, 'register_settings' ) );
                add_action( 'admin_menu', array( $this, 'add_setting_page' ), 20 );
				add_action('admin_menu', array($this, 'remove_duplicate_submenu_page'), 99);
                add_action( 'admin_menu', array( $this, 'add_premium_version_upgrade_to_menu' ), 100 );
                add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
                add_action( 'admin_init', array( $this, 'add_fields' ) );

                // init actions once to prevent multiple actions
                static::_init_actions();
            }

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

	        //smms-plugin-ui
	        add_action('smms_plugin_fw_before_smms_panel', array($this, 'add_plugin_banner'), 10, 1 );
	        add_action( 'wp_ajax_smms_plugin_fw_save_toggle_element', array( $this, 'save_toggle_element_options' ) );

        }

        /**
         * Init actions once to prevent multiple actions
         *
         * @since  3.0.0
         * @author sam
         */
        protected static function _init_actions() {
            if ( !static::$_actions_initialized ) {
                add_filter( 'admin_body_class', array( __CLASS__, 'add_body_class' ) );

                // sort plugins by name in SMMS Plugins menu
                add_action( 'admin_menu', array( __CLASS__, 'sort_plugins' ), 90 );
                add_filter( 'add_menu_classes', array( __CLASS__, 'add_menu_class_in_smms_plugin' ) );


                static::$_actions_initialized = true;
            }
        }

        /**
         * Add smms-plugin-fw-panel in body classes in Panel pages
         *
         * @param $admin_body_classes
         *
         * @since  3.0.0
         * @author sam
         *
         * @return string
         */
        public static function add_body_class( $admin_body_classes ) {
            global $pagenow;
            if ( ( 'admin.php' == $pagenow && strpos( (string)get_current_screen()->id, 'smms-plugins_page' ) !== false ) )
                $admin_body_classes = substr_count( $admin_body_classes, ' smms-plugin-fw-panel ' ) == 0 ? $admin_body_classes . ' smms-plugin-fw-panel ' : $admin_body_classes;

            return $admin_body_classes;
        }

        /**
         * Add Menu page link
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function add_menu_page() {
            global $admin_page_hooks;

            if ( !isset( $admin_page_hooks[ 'smms_plugin_panel' ] ) ) {
                $position   = apply_filters( 'smm_plugins_menu_item_position', '62.32' );
                $capability = apply_filters( 'smm_plugin_panel_menu_page_capability', 'manage_options' );
                $show       = apply_filters( 'smm_plugin_panel_menu_page_show', true );

                //  SMMS text must not be translated
                if ( !!$show ) {
                    add_menu_page( 'SMM-API Settings',
					'SMM-API',
					$capability,
					'smms_plugin_panel',
					array($this, 'smm_panel'), // Callback to render main page,
					smms_plugin_fw_get_default_logo(),
					$position );
                    $admin_page_hooks[ 'smms_plugin_panel' ] = 'smms-plugins'; // prevent issues for backward compatibility
                }
            }
        }

        /**
         * Remove duplicate submenu
         *
         * Submenu page hack: Remove the duplicate SMM Plugin link on subpages
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function remove_duplicate_submenu_page() {
            /* === Duplicate Items Hack === */
            remove_submenu_page( 'smms_plugin_panel', 'smms_plugin_panel' );
        }

        /**
         * Enqueue script and styles in admin side
         *
         * Add style and scripts to administrator
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         * @author   sam
         */
        public function admin_enqueue_scripts() {
            global $pagenow;

            // enqueue styles only in the current panel page
            if ( 'admin.php' === $pagenow && strpos( (string)get_current_screen()->id, $this->settings[ 'page' ] ) !== false || apply_filters( 'smm_plugin_panel_asset_loading', false ) ) {
                wp_enqueue_media();

                wp_enqueue_style( 'smms-plugin-fw-fields' );
                wp_enqueue_style( 'smm-jquery-ui-style' );
                wp_enqueue_style( 'raleway-font' );

                wp_enqueue_script( 'jquery-ui' );
                wp_enqueue_script( 'jquery-ui-core' );
                wp_enqueue_script( 'jquery-ui-dialog' );
                wp_enqueue_script( 'smms_how_to' );
                wp_enqueue_script( 'smms-plugin-fw-fields' );
            }

            if ( ( 'admin.php' == $pagenow && smms_plugin_fw_is_panel() ) || apply_filters( 'smm_plugin_panel_asset_loading', false ) ) {
                wp_enqueue_media();
                wp_enqueue_style( 'smm-plugin-style' );
                wp_enqueue_script( 'smm-plugin-panel' );
            }

            if ( 'admin.php' == $pagenow && strpos( (string)get_current_screen()->id, 'smms_upgrade_premium_version' ) !== false ) {
                wp_enqueue_style( 'smm-upgrade-to-pro' );
                wp_enqueue_script( 'colorbox' );
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
            register_setting( 'smm_' . $this->settings[ 'parent' ] . '_options', 'smm_' . $this->settings[ 'parent' ] . '_options', array( $this, 'options_validate' ) );
        }

        /**
         * Options Validate
         *
         * a callback function called by Register Settings function
         *
         * @param $input
         *
         * @return array validate input fields
         * @since    1.0
         * @author   sam softnwords
         */
        public function options_validate( $input ) {

            $current_tab = !empty( $input[ 'current_tab' ] ) ? $input[ 'current_tab' ] : 'general';

            $smm_options = $this->get_main_array_options();

            // default
            $valid_input = $this->get_options();

            $submit = ( !empty( $input[ 'submit-general' ] ) ? true : false );
            $reset  = ( !empty( $input[ 'reset-general' ] ) ? true : false );

            foreach ( $smm_options[ $current_tab ] as $section => $data ) {
                foreach ( $data as $option ) {
                    if ( isset( $option[ 'sanitize_call' ] ) && isset( $option[ 'id' ] ) ) { 
                        if ( is_array( $option[ 'sanitize_call' ] ) ) :
                            foreach ( $option[ 'sanitize_call' ] as $callback ) {
                                if ( is_array( $input[ $option[ 'id' ] ] ) ) {
                                    $valid_input[ $option[ 'id' ] ] = array_map( $callback, $input[ $option[ 'id' ] ] );
                                } else {
                                    $valid_input[ $option[ 'id' ] ] = call_user_func( $callback, $input[ $option[ 'id' ] ] );
                                }
                            }
                        else :
                            if ( is_array( $input[ $option[ 'id' ] ] ) ) {
                                $valid_input[ $option[ 'id' ] ] = array_map( $option[ 'sanitize_call' ], $input[ $option[ 'id' ] ] );
                            } else {
                                $valid_input[ $option[ 'id' ] ] = call_user_func( $option[ 'sanitize_call' ], $input[ $option[ 'id' ] ] );
                            }
                        endif;
                    } else {
                        if ( isset( $option[ 'id' ] ) ) {
                            $value = isset( $input[ $option[ 'id' ] ] ) ? $input[ $option[ 'id' ] ] : false;
                            if ( isset( $option[ 'type' ] ) && in_array( $option[ 'type' ], array( 'checkbox', 'onoff' ) ) ) {
                                $value = smms_plugin_fw_is_true( $value ) ? 'yes' : 'no';
                            }

                            if ( !empty( $option[ 'smms-sanitize-callback' ] ) && is_callable( $option[ 'smms-sanitize-callback' ] ) ) {
                                $value = call_user_func( $option[ 'smms-sanitize-callback' ], $value );
                            }

                            $valid_input[ $option[ 'id' ] ] = $value;
                        }
                    }

                }
            }

            return $valid_input;
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
        public function add_setting_page() {
            $this->settings[ 'icon_url' ] = isset( $this->settings[ 'icon_url' ] ) ? $this->settings[ 'icon_url' ] : '';
            $this->settings[ 'position' ] = isset( $this->settings[ 'position' ] ) ? $this->settings[ 'position' ] : null;
            $parent                       = $this->settings[ 'parent_page' ];

            if ( !empty( $parent ) ) {
                add_submenu_page( $parent, $this->settings[ 'page_title' ], $this->settings[ 'menu_title' ], $this->settings[ 'capability' ], $this->settings[ 'page' ], array( $this, 'smm_panel' ) );
            } else {
                add_menu_page( $this->settings[ 'page_title' ], $this->settings[ 'menu_title' ], $this->settings[ 'capability' ], $this->settings[ 'page' ], array( $this, 'smm_panel' ), $this->settings[ 'icon_url' ], $this->settings[ 'position' ] );
            }
            remove_submenu_page( 'smms_plugin_panel', 'smms_plugin_panel' );
            do_action( 'smm_after_add_settings_page' );


        }

        /**
         * Add Premium Version upgrade menu item
         *
         * @return   void
         * @since    2.9.13
         * @author   sam softnwords
         */
        public function add_premium_version_upgrade_to_menu() {
            /* === Add the How To menu item only if the customer haven't a premium version enabled === */
            if ( function_exists( 'SMM_Plugin_Licence' ) && !!SMM_Plugin_Licence()->get_products() ) {
                return;
            }

            global $submenu;
            if ( apply_filters( 'smm_show_upgrade_to_premium_version', isset( $submenu[ 'smms_plugin_panel' ] ) ) ) {
                $submenu['smms_plugin_panel'][] = array(
    'How to Use',          // page_title
    'How to Use',          // menu_title
    'install_plugins',     // capability
    'how_to',              // menu_slug
    'how_to_callback'      // function (defined below)
);
            }
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
	        $premium_class = isset( $this->settings['class'] ) ? 'smms-premium' : 'premium';
	        $wrap_class = isset( $this->settings['class'] ) ? $this->settings['class'] : '';
        ?>
        <div class="wrap <?php esc_attr( $wrap_class) ?>">
            <?php
             do_action('smms_plugin_fw_before_smms_panel', $this->settings[ 'page' ] );
            // tabs
            foreach ( $this->settings[ 'admin-tabs' ] as $tab => $tab_value ) {
                $active_class = ( $current_tab == $tab ) ? ' nav-tab-active' : '';
                $active_class .= 'premium' == $tab ? ' '.$premium_class: '';
                $tabs         .= '<a class="nav-tab' . esc_attr($active_class) . '" href="?' . esc_attr($this->settings[ 'parent_page' ]) . '&page=' . esc_attr($this->settings[ 'page' ]) . '&tab=' . esc_attr($tab) . '">' . esc_html($tab_value) . '</a>';
            }
            ?>
            <div id="icon-themes" class="icon32"><br/></div>
            <h2 class="nav-tab-wrapper">
                <?php 
			$allowed_html = array(
			'a' 		=> array(),
			'class'		=> array(),
			'href'		=> array()
			
			);
			
			echo wp_kses($tabs, $allowed_html); ?>
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
                <div class="<?php esc_attr( $panel_content_class) ?>">
                    <h2><?php esc_html( $this->get_tab_title()) ?></h2>
                    <?php if ( $this->is_show_form() ) : ?>
                        <form id="smms-plugin-fw-panel" method="post" action="options.php">
                            <?php do_settings_sections( 'smm' ); ?>
                            <p>&nbsp;</p>
                            <?php settings_fields( 'smm_' . $this->settings[ 'parent' ] . '_options' ); ?>
                            <input type="hidden" name="<?php echo esc_attr($this->get_name_field( 'current_tab' )) ?>" value="<?php echo esc_attr( $current_tab ) ?>"/>
                            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'smm-api' ) ?>" style="float:left;margin-right:10px;"/>
                        </form>
                        <form method="post">
                            <?php $warning = __( 'If you continue with this action, you will reset all options in this page.', 'smm-api' ) ?>
                            <input type="hidden" name="smm-action" value="reset"/>
                            <input type="submit" name="smm-reset" class="button-secondary" value="<?php esc_attr_e( 'Reset to default', 'smm-api' ) ?>"
                                   onclick="return confirm('<?php echo esc_attr( $warning) . '\n' . esc_html__( 'Are you sure?', 'smm-api' ) ?>');"/>
                        </form>
                        <p>&nbsp;</p>
                    <?php endif ?>
                </div>
            </div>
        </div>
            <?php
        }

        public function is_custom_tab( $options, $current_tab ) {
            foreach ( $options[ $current_tab ] as $section => $option ) {
                if ( isset( $option[ 'type' ] ) && isset( $option[ 'action' ] ) && 'custom_tab' == $option[ 'type' ] && !empty( $option[ 'action' ] ) ) {
                    return $option[ 'action' ];
                } else {
                    return false;
                }
            }

            return false;
        }

        /**
         * Fire the action to print the custom tab
         *
         *
         * @param string $action Action to fire
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         * @author   sam
         */
        public function print_custom_tab( $action ) {
            do_action( $action );
        }

        /**
         * Add sections and fields to setting panel
         *
         * read all options and show sections and fields
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function add_fields() {
            $smm_options = $this->get_main_array_options();
            $current_tab = $this->get_current_tab();

            if ( !$current_tab ) {
                return;
            }
            foreach ( $smm_options[ $current_tab ] as $section => $data ) {
                add_settings_section( "smm_settings_{$current_tab}_{$section}", $this->get_section_title( $section ), $this->get_section_description( $section ), 'smm' );
                foreach ( $data as $option ) {
                    if ( isset( $option[ 'id' ] ) && isset( $option[ 'type' ] ) && isset( $option[ 'name' ] ) ) {
                        add_settings_field( "smm_setting_" . $option[ 'id' ], $option[ 'name' ], array( $this, 'render_field' ), 'smm', "smm_settings_{$current_tab}_{$section}", array( 'option' => $option, 'label_for' => $this->get_id_field( $option[ 'id' ] ) ) );
                    }
                }
            }
        }


        /**
         * Add the tabs to admin bar menu
         *
         * set all tabs of settings page on wp admin bar
         *
         * @return void|array return void when capability is false
         * @since  1.0
         * @author sam softnwords
         */
        public function add_admin_bar_menu() {

            global $wp_admin_bar;

            if ( !current_user_can( 'manage_options' ) ) {
                return;
            }

            if ( ! empty( $this->settings[ 'admin_tabs' ] ) ) {
                foreach ( $this->settings[ 'admin-tabs' ] as $item => $title ) {

                    $parent      = $this->settings['parent']       ?? 'default_parent';
					$parent_page = $this->settings['parent_page']  ?? 'default_page';
					$title       = $title                          ?? 'Default Title';
					$item_id     = $parent . '-' . $item;

					$wp_admin_bar->add_menu( array(
													'parent' => $parent,
													'title'  => $title,
													'id'     => $item_id,
													'href'   => admin_url('themes.php?page=' . urlencode($parent_page) . '&tab=' . urlencode($item))
											) );

                }
            }
        }


        /**
         * Get current tab
         *
         * get the id of tab showed, return general is the current tab is not defined
         *
         * @return string
         * @since  1.0
         * @author sam softnwords
         */
        function get_current_tab() {
            $admin_tabs = array_keys( $this->settings[ 'admin-tabs' ] );

            if ( !isset( $_GET[ 'page' ] ) || $_GET[ 'page' ] != $this->settings[ 'page' ] ) {
                return false;
            }
            if ( isset( $_REQUEST[ 'smm_tab_options' ] ) ) {
                return wc_clean($_REQUEST[ 'smm_tab_options' ]);
            } elseif ( isset( $_GET[ 'tab' ] ) && isset( $this->_tabs_path_files[ $_GET[ 'tab' ] ] ) ) {
                return sanitize_text_field($_GET[ 'tab' ]);
            } elseif ( isset( $admin_tabs[ 0 ] ) ) {
                return $admin_tabs[ 0 ];
            } else {
                return 'general';
            }
        }


        /**
         * Message
         *
         * define an array of message and show the content od message if
         * is find in the query string
         *
         * @return void
         * @since  1.0
         * @author sam softnwords
         */
        public function message() {

            $message = array(
                'element_exists'   => $this->get_message( '<strong>' . __( 'The element you have entered already exists. Please, enter another name.', 'smm-api' ) . '</strong>', 'error', false ),
                'saved'            => $this->get_message( '<strong>' . __( 'Settings saved', 'smm-api' ) . '.</strong>', 'updated', false ),
                'reset'            => $this->get_message( '<strong>' . __( 'Settings reset', 'smm-api' ) . '.</strong>', 'updated', false ),
                'delete'           => $this->get_message( '<strong>' . __( 'Element deleted correctly.', 'smm-api' ) . '</strong>', 'updated', false ),
                'updated'          => $this->get_message( '<strong>' . __( 'Element updated correctly.', 'smm-api' ) . '</strong>', 'updated', false ),
                'settings-updated' => $this->get_message( '<strong>' . __( 'Element updated correctly.', 'smm-api' ) . '</strong>', 'updated', false ),
                'imported'         => $this->get_message( '<strong>' . __( 'Database imported correctly.', 'smm-api' ) . '</strong>', 'updated', false ),
                'no-imported'      => $this->get_message( '<strong>' . __( 'An error has occurred during import. Please try again.', 'smm-api' ) . '</strong>', 'error', false ),
                'file-not-valid'   => $this->get_message( '<strong>' . __( 'The added file is not valid.', 'smm-api' ) . '</strong>', 'error', false ),
                'cant-import'      => $this->get_message( '<strong>' . __( 'Sorry, import is disabled.', 'smm-api' ) . '</strong>', 'error', false ),
                'ord'              => $this->get_message( '<strong>' . __( 'Sorting successful.', 'smm-api' ) . '</strong>', 'updated', false )
            );

            foreach ( $message as $key => $value ) {
                if ( isset( $_GET[ $key ] ) ) {
                    echo esc_attr( $message[ $key ]);
                }
            }

        }

        /**
         * Get Message
         *
         * return html code of message
         *
         * @param        $message
         * @param string $type can be 'error' or 'updated'
         * @param bool   $echo
         *
         * @return void|string
         * @since  1.0
         * @author sam softnwords
         */
        public function get_message( $message, $type = 'error', $echo = true ) {
            $message = '<div id="message" class="' . esc_attr($type) . ' fade"><p>' . esc_html($message) . '</p></div>';
            if ( $echo ) {
				$allowed_html = array(
			'id' 		=> array(),
			'class'		=> array(),
			'strong'	=> array(),
			'p'			=> array()
			
			);
                echo wp_kses($message,$allowed_html);
            }

            return $message;
        }


        /**
         * Get Tab Path Files
         *
         * return an array with filenames of tabs
         *
         * @return array
         * @since    1.0
         * @author   sam softnwords
         */
        function get_tabs_path_files() {

            $option_files_path = $this->settings[ 'options-path' ] . '/';

            $tabs = array();

            foreach ( ( array ) glob( $option_files_path . '*.php' ) as $filename ) {
                preg_match( '/(.*)-options\.(.*)/', basename( $filename ), $filename_parts );

                if ( !isset( $filename_parts[ 1 ] ) ) {
                    continue;
                }

                $tab = $filename_parts[ 1 ];

                $tabs[ $tab ] = $filename;
            }

            return $tabs;
        }

        /**
         * Get main array options
         *
         * return an array with all options defined on options-files
         *
         * @return array
         * @since    1.0
         * @author   sam softnwords
         */
        function get_main_array_options() {
            if ( !empty( $this->_main_array_options ) ) {
                return $this->_main_array_options;
            }

            $options_path = $this->settings[ 'options-path' ];

            foreach ( $this->settings[ 'admin-tabs' ] as $item => $v ) {
                $path = $options_path . '/' . $item . '-options.php';
                $path = apply_filters( 'smms_plugin_panel_item_options_path', $path, $options_path, $item, $this );
                if ( file_exists( $path ) ) {
                    $this->_main_array_options = array_merge( $this->_main_array_options, include $path );
                }
            }

            return $this->_main_array_options;
        }


        /**
         * Set an array with all default options
         *
         * put default options in an array
         *
         * @return array
         * @since  1.0
         * @author sam softnwords
         */
        public function get_default_options() {
            $smm_options     = $this->get_main_array_options();
            $default_options = array();

            foreach ( $smm_options as $tab => $sections ) {
                foreach ( $sections as $section ) {
                    foreach ( $section as $id => $value ) {
                        if ( isset( $value[ 'std' ] ) && isset( $value[ 'id' ] ) ) {
                            $default_options[ $value[ 'id' ] ] = $value[ 'std' ];
                        }
                    }
                }
            }

            unset( $smm_options );

            return $default_options;
        }


        /**
         * Get the title of the tab
         *
         * return the title of tab
         *
         * @return string
         * @since    1.0
         * @author   sam softnwords
         */
        function get_tab_title() {
            $smm_options = $this->get_main_array_options();
            $current_tab = $this->get_current_tab();

            foreach ( $smm_options[ $current_tab ] as $sections => $data ) {
                foreach ( $data as $option ) {
                    if ( isset( $option[ 'type' ] ) && $option[ 'type' ] == 'title' ) {
                        return $option[ 'name' ];
                    }
                }
            }
        }

        /**
         * Get the title of the section
         *
         * return the title of section
         *
         * @param $section
         *
         * @return string
         * @since    1.0
         * @author   sam softnwords
         */
        function get_section_title( $section ) {
            $smm_options = $this->get_main_array_options();
            $current_tab = $this->get_current_tab();

            foreach ( $smm_options[ $current_tab ][ $section ] as $option ) {
                if ( isset( $option[ 'type' ] ) && $option[ 'type' ] == 'section' ) {
                    return $option[ 'name' ];
                }
            }
        }

        /**
         * Get the description of the section
         *
         * return the description of section if is set
         *
         * @param $section
         *
         * @return string
         * @since    1.0
         * @author   sam softnwords
         */
        function get_section_description( $section ) {
            $smm_options = $this->get_main_array_options();
            $current_tab = $this->get_current_tab();

            foreach ( $smm_options[ $current_tab ][ $section ] as $option ) {
                if ( isset( $option[ 'type' ] ) && $option[ 'type' ] == 'section' && isset( $option[ 'desc' ] ) ) {
                    return '<p>' . esc_html($option[ 'desc' ]) . '</p>';
                }
            }
        }


        /**
         * Show form when necessary
         *
         * return true if 'showform' is not defined
         *
         * @return bool
         * @since  1.0
         * @author sam softnwords
         */
        function is_show_form() {
            $smm_options = $this->get_main_array_options();
            $current_tab = $this->get_current_tab();

            foreach ( $smm_options[ $current_tab ] as $sections => $data ) {
                foreach ( $data as $option ) {
                    if ( !isset( $option[ 'type' ] ) || $option[ 'type' ] != 'title' ) {
                        continue;
                    }
                    if ( isset( $option[ 'showform' ] ) ) {
                        return $option[ 'showform' ];
                    } else {
                        return true;
                    }
                }
            }
        }

        /**
         * Get name field
         *
         * return a string with the name of the input field
         *
         * @param string $name
         *
         * @return string
         * @since  1.0
         * @author sam softnwords
         */
        function get_name_field( $name = '' ) {
            return 'smm_' . $this->settings[ 'parent' ] . '_options[' . $name . ']';
        }

        /**
         * Get id field
         *
         * return a string with the id of the input field
         *
         * @param string $id
         *
         * @return string
         * @since  1.0
         * @author sam softnwords
         */
        function get_id_field( $id ) {
            return 'smm_' . $this->settings[ 'parent' ] . '_options_' . $id;
        }


        /**
         * Render the field showed in the setting page
         *
         * include the file of the option type, if file do not exists
         * return a text area
         *
         * @param array $param
         *
         * @return void
         * @since  1.0
         * @author sam softnwords
         */
        function render_field( $param ) {

            if ( !empty( $param ) && isset( $param [ 'option' ] ) ) {
                $option     = $param [ 'option' ];
                $db_options = $this->get_options();

                $custom_attributes = array();

                if ( !empty( $option[ 'custom_attributes' ] ) && is_array( $option[ 'custom_attributes' ] ) ) {
                    foreach ( $option[ 'custom_attributes' ] as $attribute => $attribute_value ) {
                        $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                    }
                }

                $custom_attributes = implode( ' ', $custom_attributes );
                $std               = isset( $option[ 'std' ] ) ? $option[ 'std' ] : '';
                $db_value          = ( isset( $db_options[ $option[ 'id' ] ] ) ) ? $db_options[ $option[ 'id' ] ] : $std;

                if ( isset( $option[ 'deps' ] ) )
                    $deps = $option[ 'deps' ];

                if ( 'on-off' === $option[ 'type' ] )
                    $option[ 'type' ] = 'onoff';

                if ( $field_template_path = smms_plugin_fw_get_field_template_path( $option ) ) {
                    $field_container_path = apply_filters( 'smms_plugin_fw_panel_field_container_template_path', SMM_CORE_PLUGIN_TEMPLATE_PATH . '/panel/panel-field-container.php', $option );
                    file_exists( $field_container_path ) && include( $field_container_path );
                } else {
                    do_action( "smm_panel_{$option['type']}", $option, $db_value, $custom_attributes );
                }
            }
        }

        /**
         * Get options from db
         *
         * return the options from db, if the options aren't defined in the db,
         * get the default options ad add the options in the db
         *
         * @return array
         * @since  1.0
         * @author sam softnwords
         */
        public function get_options() {
            $options = get_option( 'smm_' . $this->settings[ 'parent' ] . '_options' );
            if ( $options === false || ( isset( $_REQUEST[ 'smm-action' ] ) && $_REQUEST[ 'smm-action' ] == 'reset' ) ) {
                $options = $this->get_default_options();
            }

            return $options;
        }

        /**
         * Show a box panel with specific content in two columns as a new woocommerce type
         *
         *
         * @param array $args
         *
         * @return   void
         * @since    1.0
         * @author   sam
         */
        public static function add_infobox( $args = array() ) {
            if ( !empty( $args ) ) {
                extract( $args );
                require_once( SMM_CORE_PLUGIN_TEMPLATE_PATH . '/panel/boxinfo.php' );
            }
        }

        /**
         * Show a box panel with specific content in two columns as a new woocommerce type
         *
         * @deprecated 3.0.12 Do nothing! Method left to prevent Fatal Error if called directly
         *
         * @param array $args
         *
         * @return   void
         */
        public static function add_videobox( $args = array() ) {

        }

        /**
         * Fire the action to print the custom tab
         *
         * @deprecated 3.0.12 Do nothing! Method left to prevent Fatal Error if called directly
         * @return void
         */
        public function print_video_box() {

        }

        /**
         * sort plugins by name in SMMS Plugins menu
         *
         * @since    3.0.0
         * @author   sam
         */
        public static function sort_plugins() {
            global $submenu;
            if ( !empty( $submenu[ 'smms_plugin_panel' ] ) ) {
                $sorted_plugins = $submenu[ 'smms_plugin_panel' ];

                usort( $sorted_plugins, function ( $a, $b ) {
                    return strcmp( current( $a ), current( $b ) );
                } );

                $submenu[ 'smms_plugin_panel' ] = $sorted_plugins;
            }
        }

        /**
         * add menu class in SMMS Plugins menu
         *
         * @since    3.0.0
         * @author   sam
         */
        public static function add_menu_class_in_smms_plugin( $menu ) {
            global $submenu;

            if ( !empty( $submenu[ 'smms_plugin_panel' ] ) ) {
                $item_count = count( $submenu[ 'smms_plugin_panel' ] );
                $columns    = absint( $item_count / 20 ) + 1;
                $columns    = max( 1, min( $columns, 3 ) );
                $columns    = apply_filters( 'smms_plugin_fw_smms_plugins_menu_columns', $columns, $item_count );

                if ( $columns > 1 ) {
                    $class = "smms-plugin-fw-menu-$columns-columns";
                    foreach ( $menu as $order => $top ) {
                        if ( 'smms_plugin_panel' === $top[ 2 ] ) {
                            $c                   = $menu[ $order ][ 4 ];
                            $menu[ $order ][ 4 ] = add_cssclass( $class, $c );
                            break;
                        }
                    }
                }
            }

            return $menu;
        }

        /**
         * Check if inside the admin tab there's the premium tab to
         * check if the plugin is a free or not
         *
         * @author sam
         */
	    function is_free() {
		    return  ( ! empty( $this->settings['admin-tabs'] ) && isset($this->settings['admin-tabs']['premium']));
	    }

	    /**
	     * Add plugin banner
	     */
	    public function add_plugin_banner( $page ) {

		    if ( $page != $this->settings['page'] || ! isset( $this->settings['class'] ) ) {
			    return;
		    }

		    if( $this->is_free() && isset( $this->settings['plugin_slug'] ) ):
			    $banners = apply_filters( 'smms_plugin_fw_banners_free', array(
				    'upgrade' => array(
					    //'image'  => SMM_CORE_PLUGIN_URL. '/assets/images/banner_premium.png',
					   // 'link' => 'https://softnwords.com/themes/plugins/'.$this->settings['plugin_slug'],
				    ),
				    'rate'    => array(
					    'image'  => SMM_CORE_PLUGIN_URL. '/assets/images/rate_banner.png',
					    'link' => 'https://wordpress.org/plugins/'.$this->settings['plugin_slug'].'/reviews/#new-post',
				    ),
			    ), $page );
			    ?>
                <h1 class="notice-container"></h1>
                <div class="smms-plugin-fw-banner smms-plugin-fw-banner-free">
                    <h1><?php echo esc_html( $this->settings['page_title'] ) ?></h1>
				    <?php if( $banners ) : ?>
                        <div class="smms-banners">
                            <ul>
							    <?php foreach ( $banners as $banner ): ?>
                                    <li><a href="<?php echo esc_url( $banner['link'])?>" target="_blank"><img src="<?php echo esc_url( $banner['image'])?>"></a></li>
							    <?php endforeach; ?>
                            </ul>
                        </div>

				    <?php endif ?>
                </div>
		    <?php else: ?>
                <h1 class="notice-container"></h1>
                <div class="smms-plugin-fw-banner">
                    <h1><?php echo esc_html( $this->settings['page_title'] ) ?>
					    <?php if ( isset( $this->settings['plugin_description'] ) ): ?>
                            <span><?php echo esc_html( $this->settings['plugin_description'] ) ?></span>
					    <?php endif ?>
                    </h1>
                </div>

		    <?php endif ?>
		    <?php
	    }

	    /**
	     * Add additional element after print the field.
	     *
	     *@since 3.2
	     *@author sam
	     */
	    public function add_smms_ui( $field ) {

		    global $pagenow;

		    if ( ! isset( $this->settings['class'] ) || empty( $this->settings['class'] ) || ! isset( $field['type'] ) ) {
			    return;
		    }
		    if ( 'admin.php' === $pagenow && strpos( (string)get_current_screen()->id, $this->settings['page'] ) !== false ) {
			    switch ( $field['type'] ) {
				    case 'datepicker':
					    echo '<span class="smms-icon icon-calendar"></span>';
					    break;
				    default:
					    break;
			    }
		    }
	    }

	    /**
         *
         */
	    public function save_toggle_element_options(  ) {
            return true;
	    }
    }


}