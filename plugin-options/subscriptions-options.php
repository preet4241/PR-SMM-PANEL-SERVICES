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

$section = array(
	'subscription_section'     => array(
		'name' => '',
		'type' => 'title',
		'id'   => 'smapi_subscription_section'
	),
	'subscription_list_table' => array(
		'type'                 => 'smms-field',
		'smms-type'            => 'list-table',
		'post_type'            => 'smapi_subscription',
		'class'                => 'ywdpd_discount_table',
		'list_table_class'     => 'SMMS_SMAPI_Subscriptions_List_Table',
		'list_table_class_dir' => SMMS_SMAPI_INIT . 'admin/class.smapi-subscriptions-list-table.php',
		'title'                => esc_html__( 'Subscriptions', 'smm-api' ),
		'search_form'          => array( 'text' => 'Search rule', 'input_id' => 'rule' ),
		'id'                   => 'smapi_subscription_table',
	),
	'subscription_section_end' => array(
		'type' => 'sectionend',
		'id'   => 'smapi_subscription_section_end'
	)
);


return apply_filters( 'smapi_subscriptions_options', array( 'subscriptions' => $section ) );