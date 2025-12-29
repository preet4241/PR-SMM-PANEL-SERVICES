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

$settings = array(

    'privacy' => array(

            'privacy_settings'     => array(
                'name' => __( 'Privacy settings', 'smm-api' ),
                'type' => 'title',
                'id'   => 'smapi_privacy_settings'
            ),

            'erasure_request'   => array(
	            'name'      		=> __('Account erasure requests','smm-api'),
	            'desc-inline'       => __( 'Remove personal data from subscriptions', 'smm-api' ),
	            'desc'              => sprintf(
					/* translators: search here */ __( 'When handling an <a href="%s">
				account erasure request</a>, 
				should personal data within subscriptions be retained or removed?
				.<br>Note: All the subscriptions will change the status to 
				cancelled if the personal data will be removed.',
				 'smm-api' ),
				  esc_url( admin_url( 'tools.php?page=remove_personal_data' ) ) ),
	            'id'                => 'smapi_erasure_request',
	            'type'      		=> 'smms-field',
	            'smms-type' 		=> 'onoff',
	            'default'   		=> 'no'
            ),
		'cron_schedules'   => array(
	            'name'      		=> __('Cron Schedules','smm-api'),
	            'desc-inline'       => __( 'List of Active Cron', 'smm-api' ),
	            'desc'              => sprintf('Subscription Order Renew : %1s <br>Status : %2s',
				  esc_html( wp_next_scheduled( 'smapi_renew_orders' ) ? gmdate("Y-m-d H:i:s", wp_next_scheduled( 'smapi_renew_orders' )) :'N/A'),esc_html(get_option( 'smapi_cron_job' )) ),
	            'id'                => 'smapi_cron_job',
	            'type'      		=> 'smms-field',
	            'smms-type' 		=> 'onoff',
	            'default'   		=> 'no'
            ),
		'cron_price-update'   => array(
	            'name'      		=> __('Auto Price Update','smm-api'),
	            'desc-inline'       => sprintf('Server# %1s', esc_html(get_option( 'active_cron_servers' ) )),
	            'desc'              => sprintf('Scheduled at %1s  and Status : %2s',
				  esc_html( SMAPI_Subscription_Cron()->smm_cron['smm_auto_price'])
				  ,
				  esc_html(get_option( 'smapi_price_update' )) ),
	            'id'                => 'smapi_price_update',
	            'type'      		=> 'smms-field',
	            'smms-type' 		=> 'onoff',
	            'default'     		=> 'no',
            ),

            'section_end_privacy_settings'=> array(
	            'type'              => 'sectionend',
	            'id'                => 'smapi_section_end_privacy_settings'
            ),

            array(
	            'title' => __( 'Personal data retention', 'smm-api' ),
	            'desc'  => __( 'Choose how long to retain personal data when it\'s no longer 
				needed for processing. Leave the following options blank to retain 
				this data indefinitely.', 'smm-api' ),
	            'type'  => 'title',
	            'id'    => 'smapi_personal_data_retention',
            ),
            array(
	            'title'       => __( 'Retain pending subscriptions', 'smm-api' ),
	            'desc_tip'    => __( 'Pending subscriptions are unpaid and may have been abandoned 
				by the customer. They will be trashed after the specified duration.', 'smm-api' ),
	            'id'          => 'smapi_trash_pending_subscriptions',
	            'type'        => 'relative_date_selector',
	            'placeholder' => __( 'N/A', 'smm-api' ),
	            'default'     => '',
            ),
            array(
	            'title'       => __( 'Retain cancelled subscriptions', 'smm-api' ),
	            'desc_tip'    => __( 'Cancelled subscriptions are disable subscriptions 
				that can\'t be reactivated by the customer. They will be trashed after 
				the specified duration.', 'smm-api' ),
	            'id'          => 'smapi_trash_cancelled_subscriptions',
	            'type'        => 'relative_date_selector',
	            'placeholder' => __( 'N/A', 'smm-api' ),
	            'default'     => '',
            ),
		

            'section_end_privacy_retention_settings'=> array(
	            'type'              => 'sectionend',
	            'id'                => 'smapi_section_end_privacy_retention_settings'
            ),
			
    )
    
);

return apply_filters( 'smms_smapi_panel_privacy_settings_options', $settings );