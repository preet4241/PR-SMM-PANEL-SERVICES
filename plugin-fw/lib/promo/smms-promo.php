<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if( ! function_exists( 'simplexml_load_string' ) ){
    return false;
}

add_action( 'admin_notices', 'smms_plugin_fw_promo_notices', 15 );
add_action( 'admin_enqueue_scripts', 'smms_plugin_fw_notice_dismiss', 20 );

if( ! function_exists( 'smms_plugin_fw_promo_notices' ) ){
	function smms_plugin_fw_promo_notices(){
	    if( function_exists( 'current_user_can' ) && ! current_user_can( 'administrator' ) ){
	        return false;
        }

		$base_url                   = apply_filters( 'smms_plugin_fw_promo_base_url', SMM_CORE_PLUGIN_PATH . '/lib/promo/' );
		$xml                        = apply_filters( 'smms_plugin_fw_promo_xml_url', $base_url . 'smms-promo.xml' );
		$transient                  = "smms_promo_message";
		$remote_data                = get_site_transient( $transient );
		$regenerate_promo_transient = isset( $_GET['smms_regenerate_promo_transient'] ) && 'yes' == $_GET['smms_regenerate_promo_transient'] ? sanitize_text_field($_GET['smms_regenerate_promo_transient']) : '';
		$promo_data                 = false;
		$create_transient           = false;

		if( false === $remote_data || apply_filters( 'smms_plugin_fw_force_regenerate_promo_transient', false ) || 'yes' == $regenerate_promo_transient ){
			$remote_data      = wp_remote_get( $xml );
			$create_transient = true;
		}

		if ( ! is_wp_error( $remote_data ) && ! empty( $remote_data ) ) {
			$promo_data = @simplexml_load_string( $remote_data );
			if( true === $create_transient ){
				$is_membership_user = false;
				$license            = function_exists( 'SMMS_Plugin_Licence' ) ? SMMS_Plugin_Licence()->get_licence() : array();
				$xml_expiry_date    = '';

				if( is_array( $license ) && apply_filters( 'smms_plugin_fw_check_for_membership_user', true ) ){
				    /* === Check is the user have the SMMS Club === */
					foreach( $license as $plugin => $data ){
						if( ! empty( $data['is_membership'] ) ){
							$is_membership_user = true;
							$xml_expiry_date    = $data['licence_expires'];
							$remote_data = $promo_data = array();
							break;
						}
					}
                }

				if( empty( $is_membership_user ) && ! empty( $promo_data->expiry_date ) ){
				    $xml_expiry_date = $promo_data->expiry_date;
                }

				//Set Site Transient
				set_site_transient( $transient, $remote_data, smms_plugin_fw_get_promo_transient_expiry_date( $xml_expiry_date ) );
			}

			if ( $promo_data && ! empty( $promo_data->promo ) ) {
				$now = strtotime( current_time( 'mysql' ) );

				foreach ($promo_data->promo as $promo ){
					$show_promo = true;
					/* === Check for Special Promo === */
				    if( ! empty( $promo->show_promo_in ) ){
				        $show_promo_in = explode( ',', $promo->show_promo_in );
					    $show_promo_in = array_map( 'trim', $show_promo_in );
					    if( ! empty( $show_promo_in ) ){
					        $show_promo = false;
						    foreach( $show_promo_in as $plugin ){
						        $plugin_slug = constant( $plugin );
						        $plugin_is_activated = ! empty( $license[ $plugin_slug ]['activated'] );
							    if( defined( $plugin ) && ! apply_filters( 'smms_plugin_fw_promo_plugin_is_activated', $plugin_is_activated ) ){
                                    $show_promo = true;
                                    break;
							    }
						    }
                        }
                    }

					$start_date = isset( $promo->start_date ) ? $promo->start_date : '';
					$end_date   = isset( $promo->end_date ) ? $promo->end_date : '';

					if( $show_promo && ! empty( $start_date ) && ! empty( $end_date ) ){
						$start_date = strtotime( $start_date );
						$end_date   = strtotime( $end_date );

						if( $end_date >= $start_date && $now >= $start_date && $now <= $end_date ){
							//is valid promo
							$title            = isset( $promo->title ) ? $promo->title : '';
							$description      = isset( $promo->description ) ? $promo->description : '';
							$url              = isset( $promo->link->url ) ? $promo->link->url : '';
							$url_label        = isset( $promo->link->label ) ? $promo->link->label : '';
							$image_bg_color   = isset( $promo->style->image_bg_color ) ? $promo->style->image_bg_color : '';
							$border_color     = isset( $promo->style->border_color ) ? $promo->style->border_color : '';
							$background_color = isset( $promo->style->background_color ) ? $promo->style->background_color : '';
							$promo_id         = isset( $promo->promo_id ) ? $promo->promo_id : '';
							$banner           = isset( $promo->banner ) ? $promo->banner : '';
							$style = $link    = '';
							$show_notice      = false;

							if( ! empty( $border_color ) ){
								$style .= "border-left-color: {$border_color};";
							}

							if( ! empty( $background_color ) ){
								$style .= "background-color: {$background_color};";
							}

							if( ! empty( $image_bg_color ) ){
								$image_bg_color = "background-color: {$image_bg_color};";
							}

							if( ! empty( $title ) ) {
								$promo_id .= $title;
								$title = sprintf( '%s: ', $title );
								$show_notice = true;
							}

							if( ! empty( $description ) ) {
								$promo_id .= $description;
								$description = sprintf( '%s', $description );
								$show_notice = true;
							}

							if( ! empty( $url ) && ! empty( $url_label )) {
								$promo_id .= $url . $url_label;
								$link = sprintf( '<a href="%s" target="_blank">%s</a>', $url, $url_label );
								$show_notice = true;
							}

							if( ! empty( $banner ) ){
								$banner = sprintf( '<img src="%s" class="smms-promo-banner-image">', $base_url . $banner );

								if( ! empty( $url ) ){
									$banner = sprintf( '<a class="smms-promo-banner-image-link" href="%s" target="_blank" style="%s">%s</a>', $url, $image_bg_color, $banner);
								}
							}

							$unique_promo_id = "smms-notice-" . md5 ( $promo_id );

							if( ! empty( $_COOKIE[ 'hide_' . $unique_promo_id ] ) && 'yes' == $_COOKIE[ 'hide_' . $unique_promo_id ] ){
								$show_notice = false;
							}

							if ( true === $show_notice ) :
								wp_enqueue_script( 'smms-promo' );
								?>
                                <div id="<?php echo esc_attr( $unique_promo_id) ?>" class="smms-notice-is-dismissible notice notice-smms notice-alt is-dismissible" style="<?php echo esc_attr( $style)?>" data-expiry= <?php echo esc_attr( $promo->end_date) ?>>
                                    <p>
										<?php if( ! empty( $banner ) ) { printf( '%s', wp_kses($banner )); } ?>
										<?php printf( '%1$s %2$s %3$s', esc_html($title), esc_html($description), esc_html($link) ); ?>
                                    </p>
                                </div>
							<?php endif;
						}
					}
				}
			}
		}
	}
}

if( ! function_exists( 'smms_plugin_fw_notice_dismiss' ) ){
	function smms_plugin_fw_notice_dismiss(){
		$script_path = defined( 'SMM_CORE_PLUGIN_URL' ) ? SMM_CORE_PLUGIN_URL : get_template_directory_uri() . '/core/plugin-fw';
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'smms-promo', $script_path . '/assets/js/smms-promo' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
	}
}

if( ! function_exists( 'smms_plugin_fw_get_promo_transient_expiry_date' ) ){
	function smms_plugin_fw_get_promo_transient_expiry_date( $expiry_date ) {
		$xml_expiry_date = ! empty( $expiry_date ) ? $expiry_date : '+24 hours';
		$current     = strtotime( current_time( 'Y-m-d H:i:s' ) );
		$expiry_date = strtotime( $xml_expiry_date, $current );

		if( $expiry_date <= $current ){
			$expiry_date = strtotime( '+24 hours', $current );
		}

		return $expiry_date;
	}
}