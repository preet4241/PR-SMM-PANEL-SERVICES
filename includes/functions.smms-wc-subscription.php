<?php
if ( !defined( 'ABSPATH' ) || ! defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements helper functions for SMMS WooCommerce Subscription
 *
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */


if( !function_exists('smapi_get_price_per_string')){

	/**
	 * Return the days from timestamp
	 *
	 * @param $timestamp int
	 *
	 * @return int
	 * @since 1.0.0
	 */

	function smapi_get_price_per_string( $price_per, $time_option ) {
		$price_html = ( ( $price_per == 1 ) ? '' : $price_per ) . ' ';

		switch( $time_option ){
			case 'days':
				$price_html .= _n( 'day', 'days', $price_per, 'smm-api' );
				break;
			case 'weeks':
				$price_html .= _n( 'week', 'weeks', $price_per, 'smm-api' );
				break;
			case 'months':
				$price_html .= _n( 'month', 'months', $price_per, 'smm-api' );
				break;
			case 'years':
				$price_html .= _n( 'year', 'years', $price_per, 'smm-api' );
				break;
			default:
		}

		return $price_html;
	}

}

if( !function_exists('smapi_get_time_options')){

    /**
     * Return the list of time options to add in product editor panel
     *
     *
     * @return array
     * @since 1.0.0
     */

    function smapi_get_time_options(){
        $options = array(
            'days'   => __( 'days', 'smm-api' ),
            'weeks'  => __( 'weeks', 'smm-api' ),
            'months' => __( 'months', 'smm-api' ),
        );

        return apply_filters('smapi_time_options', $options);
    }
}

if( !function_exists('smapi_get_price_time_option_paypal')){

    /**
     * Return the list of time options to add in product editor panel
     *
     *
     * @return array
     * @since 1.0.0
     */

    function smapi_get_price_time_option_paypal( $time_option ){
        $options = array(
            'days'   => 'D',
            'weeks'   => 'W',
            'months' => 'M',
        );

        return isset( $options[ $time_option] ) ? $options[ $time_option] : '';
    }
}

if ( !function_exists( 'smms_smapi_locate_template' ) ) {
    /**
     * Locate the templates and return the path of the file found
     *
     * @param string $path
     * @param array  $var
     *
     * @return string
     * @since 1.0.0
     */
    function smms_smapi_locate_template( $path, $var = NULL ) {

        global $woocommerce;

        if ( function_exists( 'WC' ) ) {
            $woocommerce_base = WC()->template_path();
        }
        elseif ( defined( 'WC_TEMPLATE_PATH' ) ) {
            $woocommerce_base = WC_TEMPLATE_PATH;
        }
        else {
            $woocommerce_base = $woocommerce->plugin_path() . '/templates/';
        }

        $template_woocommerce_path = $woocommerce_base . $path;
        $template_path             = '/' . $path;
        $plugin_path               = SMMS_SMAPI_DIR . 'templates/' . $path;

        $located = locate_template( array(
            $template_woocommerce_path, // Search in <theme>/woocommerce/
            $template_path,             // Search in <theme>/
            $plugin_path                // Search in <plugin>/templates/
        ) );

        if ( !$located && file_exists( $plugin_path ) ) {
            return apply_filters( 'smms_smapi_locate_template', $plugin_path, $path );
        }

        return apply_filters( 'smms_smapi_locate_template', $located, $path );
    }
}

if ( ! function_exists( 'smapi_get_timestamp_from_option' ) ) {

	/**
	 * Add a date to a timestamp
	 *
	 * @param int $time_from
	 * @param int $qty
	 * @param string $time_opt
	 *
	 * @return string
	 * @since 1.0.0
	 */

	function smapi_get_timestamp_from_option( $time_from, $qty, $time_opt ) {

		$timestamp = 0;
		$time_from = (int) $time_from;
			switch ( $time_opt ) {
			case 'days':
				$timestamp = smapi_add_date( $time_from, intval( $qty ) );
				break;
			case 'weeks':
				$timestamp = smapi_add_date( $time_from, intval( $qty ) * 7 );
				break;
			case 'months':
				$timestamp = smapi_add_date( $time_from, 0, intval( $qty ) );
				break;
			case 'years':
				$timestamp = smapi_add_date( $time_from, 0, 0, intval( $qty ) );
				break;
			default:
		}

		return $timestamp;
	}
}

if ( ! function_exists( 'smapi_get_paypal_limit_options' ) ) {

    /**
     * Return the list of time options with the max value that paypal accept
     *
     *
     * @return array
     * @since 1.0.0
     */

    function smapi_get_paypal_limit_options() {
        $options = array(
            'days'   => 90,
            'weeks'  => 52,
            'months' => 24,
        );

        return apply_filters( 'smapi_paypal_limit_options', $options );
    }
}

if( !function_exists('smapi_get_price_per_string')){

    /**
     * Return the days from timestamp
     *
     * @param $price_per
     * @param $time_option
     *
     * @return int
     * @internal param int $timestamp
     *
     * @since    1.0.0
     */

    function smapi_get_price_per_string( $price_per, $time_option ) {
        $price_html = ( ( $price_per == 1 ) ? '' : $price_per ) . ' ';

        switch( $time_option ){
            case 'days':
                $price_html .= _n( 'day', 'days', $price_per, 'smm-api' );
                break;
            case 'weeks':
                $price_html .= _n( 'week', 'weeks', $price_per, 'smm-api' );
                break;
            case 'months':
                $price_html .= _n( 'month', 'months', $price_per, 'smm-api' );
                break;
            default:
        }

        return $price_html;
    }

}


if ( ! function_exists( 'smapi_get_max_length_period' ) ) {

    /**
     * Return the max length of period that can be accepted from paypal
     *
     *
     * @return string
     * @internal param int $time_from
     * @internal param int $qty
     * @since    1.0.0
     */

    function smapi_get_max_length_period() {

        $max_length = array(
            'days'   => 90,
            'weeks'  => 52,
            'months' => 24,
            'years'  => 5
        );

        return apply_filters( 'smapi_get_max_length_period', $max_length );

    }
}



if ( ! function_exists( 'smapi_validate_max_length' ) ) {

    /**
     * Return the max length of period that can be accepted from paypal
     *
     *
     * @param int    $max_length
     * @param string $time_opt
     *
     * @return int
     * @since    1.0.0
     */

    function smapi_validate_max_length( $max_length, $time_opt ) {

        $max_lengths = smapi_get_max_length_period();
        $max_length  = ( $max_length > $max_lengths[$time_opt] ) ? $max_lengths[$time_opt] : $max_length;

        return $max_length;
    }
}

if( !function_exists('smapi_get_price_per_string')){


	/**
	 * @param $price_per
	 * @param $time_option
	 *
	 * @return string
	 * @author sam softnwords
	 */
	function smapi_get_price_per_string( $price_per, $time_option ) {
		$price_html = ( ( $price_per == 1 ) ? '' : $price_per ) . ' ';

		switch( $time_option ){
			case 'days':
				$price_html .= _n( 'day', 'days', $price_per, 'smm-api' );
				break;
			case 'weeks':
				$price_html .= _n( 'week', 'weeks', $price_per, 'smm-api' );
				break;
			case 'months':
				$price_html .= _n( 'month', 'months', $price_per, 'smm-api' );
				break;
			case 'years':
				$price_html .= _n( 'year', 'years', $price_per, 'smm-api' );
				break;
			default:
		}

		return $price_html;
	}

}



if ( ! function_exists( 'smapi_add_date' ) ) {

	/**
	 * Add day, months or year to a date
	 *
	 * @param int $given_date
	 * @param int $day
	 * @param int $mth
	 * @param int $yr
	 *
	 * @return string
	 * @since 1.2.0
	 */

	function smapi_add_date( $given_date, $day = 0, $mth = 0, $yr = 0 ) {
		$new_date = $given_date;
		$new_date = strtotime( "+".$day." days", $new_date );
		$new_date = strtotime( "+".$mth." month", $new_date );
		$new_date = strtotime( "+".$yr." year", $new_date );
		return $new_date;
	}
}


if ( ! function_exists( 'smms_check_privacy_enabled' ) ) {

	/**
	 * Check if the tool for export and erase personal data are enabled
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	function smms_check_privacy_enabled( $wc = false) {
		global $wp_version;
		$enabled = $wc ? version_compare( WC()->version, '3.4.0', '>=' ) && version_compare( $wp_version, '4.9.5', '>' ) : version_compare( $wp_version, '4.9.5', '>' );
		return apply_filters('smms_check_privacy_enabled', $enabled, $wc );
	}
}

if ( ! function_exists( 'smapi_get_subscription' ) ) {

	/**
	 * Return the subscription object
	 *
	 * @param int $subscription_id
	 *
	 * @return SMAPI_Subscription
	 * @since 1.0.0
	 */

	function smapi_get_subscription( $subscription_id ) {
		return new SMAPI_Subscription( $subscription_id );
	}
}

if ( ! function_exists( 'smapi_get_status' ) ) {

	/**
	 * Return the list of status available
	 *
	 * @return array
	 * @since 1.0.0
	 */

	function smapi_get_status() {
		$options = array(
			'active'    => __( 'active', 'smm-api' ),
			'paused'    => __( 'paused', 'smm-api' ),
			'pending'   => __( 'pending', 'smm-api' ),
			'overdue'   => __( 'overdue', 'smm-api' ),
			'trial'     => __( 'trial', 'smm-api' ),
			'cancelled' => __( 'cancelled', 'smm-api' ),
			'expired'   => __( 'expired', 'smm-api' ),
			'suspended' => __( 'suspended', 'smm-api' ),
		);

		return apply_filters( 'smapi_status', $options );
	}
}

if ( ! function_exists( 'smapi_get_max_failed_attemps_list' ) ) {

	/**
	 * Return the list of max failed attempts for each compatible gateways
	 *
	 * @return array
	 */

	function smapi_get_max_failed_attemps_list() {
		$arg = array(
			'paypal'      => 3,
			'smms-stripe' => 4
		);

		return apply_filters( 'smapi_max_failed_attemps_list', $arg );
	}

}

if ( ! function_exists( 'smapi_get_num_of_days_between_attemps' ) ) {

	/**
	 * Return the list of max failed attemps for each compatible gateways
	 *
	 * @return array
	 */

	function smapi_get_num_of_days_between_attemps() {
		$arg = array(
			'paypal'      => 5,
			'smms-stripe' => 5
		);

		return apply_filters( 'smapi_get_num_of_days_between_attemps', $arg );
	}

}

if ( ! function_exists( 'smapi_is_an_order_with_subscription' ) ) {
	/**
	 * Checks if in the order there's a subscription product
	 * returns false if is not an order with subscription or
	 * returns the type of subscription order ( parent|renew )
	 *
	 * @param $order
	 *
	 * @return string|bool
	 * @since 1.2.0
	 */
	function smapi_is_an_order_with_subscription( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$order_subscription_type = false;
		$subscriptions           = smm_get_prop( $order, 'subscriptions' );
		$is_renew                = smm_get_prop( $order, 'is_renew' );

		if ( $subscriptions ) {
			$order_subscription_type = empty( $is_renew ) ? 'parent' : 'renew';
		}

		return $order_subscription_type;

	}
}


if ( ! function_exists( 'smapi_coupon_is_valid' ) ) {

	/**
	 * Check if a coupon is valid.
	 *
	 * @param $coupon WC_Coupon
	 * @param array $object
	 *
	 * @return bool|WP_Error
	 * @author sam softnwords
	 * @throws Exception
	 */
	function smapi_coupon_is_valid( $coupon, $object = array() ) {
		if ( version_compare( WC()->version, '3.2.0', '>=' ) ) {
			$wc_discounts = new WC_Discounts( $object );
			$valid        = $wc_discounts->is_coupon_valid( $coupon );
			$valid        = is_wp_error( $valid ) ? false : $valid;
		} else {
			$valid = $coupon->is_valid();
		}

		return $valid;
	}

}
if ( ! function_exists( 'smapi_get_var_sub' ) ) {

	/**
	 * Get Meta values as array
	 *
	 * @param $id POST ID
	 * @param $meta_key subscribed
	 *
	 * @return array
	 * @author sam softnwords
	 * @throws Exception
	 */
	function smapi_get_var_sub( $id, $meta_key ){ 
            $string_data = get_post_meta( $id, $meta_key, true );
            return json_decode($string_data,true);
            
            
        }

}
if ( ! function_exists( 'smapi_set_var_sub' ) ) {

	/**
	 * Set Meta values as string
	 *
	 * @param $id POST ID
	 * @param $meta_key subscribed
	 * @param $meta_arr array to string
	 * @return true
	 * @author sam softnwords
	 * @throws Exception
	 */
	 function smapi_set_var_sub($id, $meta_key, $meta_arr ){
            
            $meta_text = update_post_meta( $id, $meta_key, wp_json_encode($meta_arr));
            
        }

}