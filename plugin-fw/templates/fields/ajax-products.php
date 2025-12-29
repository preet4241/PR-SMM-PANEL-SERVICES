<?php
/**
 * This file belongs to the SMM Plugin Framework.
 * Author: Yith
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit;// Exit if accessed directly

$field[ 'type' ] = 'ajax-posts';
$field_data      = array(
    'post_type'   => 'product',
    'placeholder' => __( 'Search Product', 'smm-api' ),
    'action'      => 'smms_plugin_fw_json_search_products',
);
if ( isset( $field[ 'data' ] ) )
    $field_data = wp_parse_args( $field[ 'data' ], $field_data );

$field[ 'data' ] = $field_data;

smms_plugin_fw_get_field( $field, true );