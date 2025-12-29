<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} 
// Exit if accessed directly<div class="wrap">
//License: GPLv3
//License URI: https://www.gnu.org/licenses/gpl-3.0.html


return array(
    'orders' => array(
        'home' => array(
            'type'   => 'custom_tab',
            'action' => 'smms_smapi_orders_tab',
            'hide_sidebar' => true
        )
    )
);