<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} 
// Exit if accessed directly<div class="wrap">
//License: GPLv3
//License URI: https://www.gnu.org/licenses/gpl-3.0.html

$settings = array(

    'general' => array(

            'section_general_settings'     => array(
                'name' => __( 'General settings', 'smm-api' ),
                'type' => 'title',
                'id'   => 'smapi_section_general'
            ),

            'enabled' => array(
                'name'    =>  __( 'Enable Subscription', 'smm-api' ),
                'desc'    => '',
                'id'      => 'smapi_enabled',
                'type'      => 'smms-field',
                'smms-type' => 'onoff',
                'default' => 'yes'
            ),


            'section_end_form'=> array(
                'type'              => 'sectionend',
                'id'                => 'smapi_section_general_end_form'
            ),
        )

);

return apply_filters( 'smms_smapi_panel_settings_options', $settings );