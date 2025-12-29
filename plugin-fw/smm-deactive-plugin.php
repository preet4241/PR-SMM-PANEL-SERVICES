<?php
/*
 * This file belongs to the SMM Framework.
 * Author: Yith
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
if ( ! function_exists( 'smm_deactive_free_version' ) ) {
    function smm_deactive_free_version( $to_deactive, $to_active ) {

        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        if ( defined( $to_deactive ) && is_plugin_active( constant( $to_deactive ) ) ) {
            deactivate_plugins( constant( $to_deactive ) );// phpcs:ignore
            
             if( ! function_exists( 'wp_create_nonce' ) ){
                header( 'Location: plugins.php');
                exit();
            }


            global $status, $page, $s;
            $redirect    = 'plugins.php?action=activate&plugin=' . $to_active . '&plugin_status=' . $status . '&paged=' . $page . '&s=' . $s;
            $redirect    = esc_url_raw( add_query_arg( '_wpnonce', wp_create_nonce( 'activate-plugin_' . $to_active ), $redirect ) );

            header( 'Location: ' . $redirect );
            exit();
        }
    }
}