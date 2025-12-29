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

if ( !function_exists( 'WC' ) ) {
    return;
}

$changed_objects = array();

if ( !function_exists( 'smm_get_prop' ) ) {
    /**
     *
     */
    function smm_get_prop( $object, $key, $single = true, $context = 'view' ) {

        $prop_map   = smm_return_new_attribute_map();
        $is_wc_data = $object instanceof WC_Data;

        if ( $is_wc_data ) {
            $key = ( array_key_exists( $key, $prop_map ) ) ? $prop_map[ $key ] : $key;

            if ( ( $getter = "get{$key}" ) && method_exists( $object, $getter ) ) {
                return $object->$getter( $context );
            } elseif ( ( $getter = "get_{$key}" ) && method_exists( $object, $getter ) ) {
                return $object->$getter( $context );
            } else {
                return $object->get_meta( $key, $single );
            }
        } else {
            $key = ( in_array( $key, $prop_map ) ) ? array_search( $key, $prop_map ) : $key;

            if ( isset( $object->$key ) ) {
                return $object->$key;
            } elseif ( smm_wc_check_post_columns( $key ) ) {
                return $object->post->$key;
            } else {
                $getter = 'get_user_meta';
                !$object instanceof WC_Customer && $getter = 'get_post_meta';
                $object_old_id = isset( $object->id ) ? $object->id : '';
                $object_id = is_callable( array( $object, 'get_id' ) ) ? $object->get_id() : $object_old_id;

                return $getter( $object_id, $key, true );
            }
        }
    }
}

if ( !function_exists( 'smm_set_prop' ) ) {
    /**
     *
     */
    function smm_set_prop( $object, $arg1, $arg2 = false ) {

        if ( !is_array( $arg1 ) ) {
            $arg1 = array(
                $arg1 => $arg2
            );
        }

        $prop_map   = smm_return_new_attribute_map();
        $is_wc_data = $object instanceof WC_Data;

        foreach ( $arg1 as $key => $value ) {
            if ( $is_wc_data ) {
                $key = ( array_key_exists( $key, $prop_map ) ) ? $prop_map[ $key ] : $key;

                if ( ( $setter = "set{$key}" ) && method_exists( $object, $setter ) ) {
                    $object->$setter( $value );
                } elseif ( ( $setter = "set_{$key}" ) && method_exists( $object, $setter ) ) {
                    $object->$setter( $value );
                } else {
                    $object->update_meta_data( $key, $value );
                }
            } else {
                $key = ( in_array( $key, $prop_map ) ) ? array_search( $key, $prop_map ) : $key;
                ( strpos( (string)$key, '_' ) === 0 ) && $key = substr( $key, 1 );

                if ( smm_wc_check_post_columns( $key ) ) {
                    $object->post->$key = $value;
                } else {
                    $object->$key = $value;
                }
            }
        }
    }
}

if ( !function_exists( 'smm_save_prop' ) ) {
    /**
     *
     */
    function smm_save_prop( $object, $arg1, $arg2 = false, $force_update = false ) {
        if ( !is_array( $arg1 ) ) {
            $arg1 = array(
                $arg1 => $arg2
            );
        }

        $is_wc_data = $object instanceof WC_Data;

        foreach ( $arg1 as $key => $value ) {
            smm_set_prop( $object, $key, $value );

            if ( !$is_wc_data ) {

                if ( smm_wc_check_post_columns( $key ) ) {
                    smm_store_changes( $object->post, $key, $value );
                } else {
                    $object_id = is_callable( array( $object, 'get_id' ) ) ? $object->get_id() : $object->id;

                    update_post_meta( $object_id, $key, $value );
                }
            }
        }

        if ( $is_wc_data ) {
            $object->save();
        }
    }
}

if ( !function_exists( 'smm_delete_prop' ) ) {
    /**
     *
     */
    function smm_delete_prop( $object, $key, $value = '' ) {
        $prop_map   = smm_return_new_attribute_map();
        $is_wc_data = $object instanceof WC_Data;

        if ( $is_wc_data ) {
            $key = ( array_key_exists( $key, $prop_map ) ) ? $prop_map[ $key ] : $key;

            if ( ( $setter = "set{$key}" ) && ( $getter = "get{$key}" ) && method_exists( $object, $setter ) && ( !$value || $object->$getter == $value ) ) {
                $object->$setter( '' );
            } elseif ( ( $setter = "set_{$key}" ) && ( $getter = "get_{$key}" ) && method_exists( $object, $setter ) && ( !$value || $object->$getter == $value ) ) {
                $object->$setter( '' );
            } elseif ( ( !$value || $object->get_meta( $key ) == $value ) ) {
                $object->delete_meta_data( $key, $value );
            }

            $object->save();
        } else {
            if ( smm_wc_check_post_columns( $key ) && ( !$value || $object->post->$key == $value ) ) {
                smm_store_changes( $object->post, $key, '' );
            } else {
                $object_id = is_callable( array( $object, 'get_id' ) ) ? $object->get_id() : $object->id;

                delete_post_meta( $object_id, $key, $value );
            }
        }
    }
}

if ( !function_exists( 'smm_return_new_attribute_map' ) ) {
    function smm_return_new_attribute_map() {
        return array(
            'post_parent'                => 'parent_id',
            'post_title'                 => 'name',
            'post_status'                => 'status',
            'post_content'               => 'description',
            'post_excerpt'               => 'short_description',
            /* Orders */
            'paid_date'                  => 'date_paid',
            '_paid_date'                 => '_date_paid',
            'completed_date'             => 'date_completed',
            '_completed_date'            => '_date_completed',
            '_order_date'                => '_date_created',
            'order_date'                 => 'date_created',
            'order_total'                => 'total',
            'customer_user'              => 'customer_id',
            '_customer_user'             => 'customer_id',
            /* Products */
            'visibility'                 => 'catalog_visibility',
            '_visibility'                => '_catalog_visibility',
            'sale_price_dates_from'      => 'date_on_sale_from',
            '_sale_price_dates_from'     => '_date_on_sale_from',
            'sale_price_dates_to'        => 'date_on_sale_to',
            '_sale_price_dates_to'       => '_date_on_sale_to',
            'product_attributes'         => 'attributes',
            '_product_attributes'        => '_attributes',
            /*Coupons*/
            'coupon_amount'              => 'amount',
            'exclude_product_ids'        => 'excluded_product_ids',
            'exclude_product_categories' => 'excluded_product_categories',
            'customer_email'             => 'email_restrictions',
            'expiry_date'                => 'date_expires',
        );
    }
}

if ( !function_exists( 'smm_store_changes' ) ) {
    function smm_store_changes( $object, $key, $value = false ) {
        global $changed_objects;

        $is_wc_data = $object instanceof WC_Data;

        if ( $is_wc_data ) {
            /**
             * @var $object \WC_Data
             */
            $object_reference = $object->get_id();

            $changed_objects[ $object_reference ][ 'object' ]          = $object;
            $changed_objects[ $object_reference ][ 'changes' ][ $key ] = $value;



        } else {
            $changed_objects[ $object->ID ][ $key ] = $value;
        }
    }
}

if ( !function_exists( 'smm_send_changes_to_db' ) ) {
    function smm_send_changes_to_db() {
        global $changed_objects;

        if ( !empty( $changed_objects ) ) {
            foreach ( $changed_objects as $id => $data ) {
                if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
                    /**
                     * @var $object \WC_Data
                     */
                    $object = is_a( $data[ 'object' ], 'WC_Product' ) ? wc_get_product( $id ) : wc_get_order( $id );

                    smm_set_prop( $object, $data[ 'changes' ] );
                    $object->save();
                } else {
                    $data[ 'ID' ] = $id;
                    wp_update_post( $data );
                }
            }
        }
    }
}

if ( !function_exists( 'smm_get_orders' ) ) {
    /**
     *
     */
    function smm_get_orders( $args ) {
        if ( version_compare( WC()->version, '2.7', '<' ) ) {
            $args[ 'fields' ] = 'objects';
            $posts            = get_posts( $args );

            return array_map( 'wc_get_order', $posts );
        } else {
            return wc_get_orders( $args );
        }
    }
}

if ( !function_exists( 'smm_get_products' ) ) {
    /**
     *
     */
    function smm_get_products( $args ) {
        if ( version_compare( WC()->version, '2.7', '<' ) ) {
            $args[ 'fields' ] = 'objects';
            $posts            = get_posts( $args );

            return array_map( 'wc_get_product', $posts );
        } else {
            return wc_get_products( $args );
        }
    }
}

if ( !function_exists( 'smm_update_product_stock' ) ) {
    /**
     *
     */
    function smm_update_product_stock( $product, $stock_quantity = 1, $operation = 'set' ) {
        if ( function_exists( 'wc_update_product_stock' ) ) {
            $stock = wc_update_product_stock( $product, $stock_quantity, $operation );
        } else {
            switch ( $operation ) {
                case 'increase':
                    $stock = $product->increase_stock( $stock_quantity );
                    break;
                case 'decrease':
                    $stock = $product->reduce_stock( $stock_quantity );
                    break;
                case 'set':
                default:
                    $stock = $product->set_stock( $stock_quantity );
                    break;
            }
        }

        return $stock;
    }
}

if ( !function_exists( 'smm_wc_deprecated_filters' ) ) {
    /**
     *
     */
    function smm_wc_deprecated_filters() {
        return apply_filters( 'smm_wc_deprecated_filters', array(
            'woocommerce_email_order_schema_markup'      => 'woocommerce_structured_data_order',
            'woocommerce_product_width'                  => 'woocommerce_product_get_width',
            'woocommerce_product_height'                 => 'woocommerce_product_get_height',
            'woocommerce_product_length'                 => 'woocommerce_product_get_length',
            'woocommerce_product_weight'                 => 'woocommerce_product_get_weight',
            'woocommerce_get_sku'                        => 'woocommerce_product_get_sku',
            'woocommerce_get_price'                      => 'woocommerce_product_get_price',
            'woocommerce_get_price'                      => 'woocommerce_product_variation_get_price',
            'woocommerce_get_regular_price'              => 'woocommerce_product_get_regular_price',
            'woocommerce_get_sale_price'                 => 'woocommerce_product_get_sale_price',
            'woocommerce_product_tax_class'              => 'woocommerce_product_get_tax_class',
            'woocommerce_get_stock_quantity'             => 'woocommerce_product_get_stock_quantity',
            'woocommerce_get_product_attributes'         => 'woocommerce_product_get_attributes',
            'woocommerce_product_gallery_attachment_ids' => 'woocommerce_product_get_gallery_image_ids',
            'woocommerce_product_review_count'           => 'woocommerce_product_get_review_count',
            'woocommerce_product_files'                  => 'woocommerce_product_get_downloads',
            'woocommerce_get_currency'                   => 'woocommerce_order_get_currency',
            'woocommerce_order_amount_discount_total'    => 'woocommerce_order_get_discount_total',
            'woocommerce_order_amount_discount_tax'      => 'woocommerce_order_get_discount_tax',
            'woocommerce_order_amount_shipping_total'    => 'woocommerce_order_get_shipping_total',
            'woocommerce_order_amount_shipping_tax'      => 'woocommerce_order_get_shipping_tax',
            'woocommerce_order_amount_cart_tax'          => 'woocommerce_order_get_cart_tax',
            'woocommerce_order_amount_total'             => 'woocommerce_order_get_total',
            'woocommerce_order_amount_total_tax'         => 'woocommerce_order_get_total_tax',
            'woocommerce_order_amount_total_discount'    => 'woocommerce_order_get_total_discount',
            'woocommerce_order_amount_subtotal'          => 'woocommerce_order_get_subtotal',
            'woocommerce_order_tax_totals'               => 'woocommerce_order_get_tax_totals',
            'woocommerce_refund_amount'                  => 'woocommerce_get_order_refund_get_amount',
            'woocommerce_refund_reason'                  => 'woocommerce_get_order_refund_get_reason',
            'default_checkout_country'                   => 'default_checkout_billing_country',
            'default_checkout_state'                     => 'default_checkout_billing_state',
            'default_checkout_postcode'                  => 'default_checkout_billing_postcode',

        ) );
    }
}

if ( !function_exists( 'smm_fix_wc_deprecated_filters' ) ) {
    /**
     *
     */
    function smm_fix_wc_deprecated_filters() {

        if ( !version_compare( WC()->version, '2.7.0', '<' ) ) {
            return;
        }

        $deprecated_filters = smm_wc_deprecated_filters();
        foreach ( $deprecated_filters as $old => $new ) {
            add_filter( $old, 'smm_wc_deprecated_filter_mapping', 10, 100 );
        }
    }
}

if ( !function_exists( 'smm_wc_deprecated_filter_mapping' ) ) {
    /**
     *
     */
    function smm_wc_deprecated_filter_mapping() {
        $deprecated_filters = smm_wc_deprecated_filters();

        $filter = current_filter();
        $args   = func_get_args();
        $data   = $args[ 0 ];


        if ( isset( $deprecated_filters[ $filter ] ) ) {
            if ( has_filter( $deprecated_filters[ $filter ] ) ) {
                $data = apply_filters_ref_array( $deprecated_filters[ $filter ], $args );
            }
        }

        return $data;
    }
}

if ( !function_exists( 'smm_wc_check_post_columns' ) ) {
    /**
     *
     */
    function smm_wc_check_post_columns( $key ) {
        $columns = array(
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_title',
            'post_excerpt',
            'post_status',
            'comment_status',
            'ping_status',
            'post_password',
            'post_name',
            'to_ping',
            'pinged',
            'post_modified',
            'post_modified_gmt',
            'post_content_filtered',
            'post_parent',
            'guid',
            'menu_order',
            'post_type',
            'post_mime_type',
            'comment_count',
        );

        return in_array( $key, $columns );
    }
}


/*  Shortcuts for common functions   */

if ( !function_exists( 'smm_get_order_id' ) ) {
    /**
     * Retrieve the order id
     *
     * @param WC_Order $order
     *
     * @return mixed
     */
    function smm_get_order_id( $order ) {
        return smm_get_prop( $order, 'id' );
    }
}

if ( !function_exists( 'smm_get_product_id' ) ) {
    /**
     * Retrieve the product id
     *
     * @param WC_Product $product
     *
     * @return mixed
     */
    function smm_get_product_id( $product ) {
        return smm_get_prop( $product, 'id' );
    }
}

if ( !function_exists( 'smm_get_base_product_id' ) ) {
    /**
     * New way to retrieve the $product->id as it was before WC 2.7.
     *
     * Retrieve the parent product id for WC_Product_Variation instances
     * or the product id in the other cases.
     *
     * @param WC_Product $product
     *
     * @return mixed
     */
    function smm_get_base_product_id( $product ) {

        return $product instanceof WC_Data && $product->is_type( 'variation' ) ?
            smm_get_prop( $product, 'parent_id' ) :
            smm_get_prop( $product, 'id' );
    }
}

if ( !function_exists( 'smm_get_display_price' ) ) {
    /**
     * @param WC_Product $product
     * @param string     $price
     * @param int        $qty
     */
    function smm_get_display_price( $product, $price = '', $qty = 1 ) {

        if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

            $price = wc_get_price_to_display( $product, array( 'qty' => $qty, 'price' => $price ) );
        } else {

            $price = $product->get_display_price( $price, $qty );
        }

        return $price;
    }
}

if ( !function_exists( 'smm_get_price_excluding_tax' ) ) {
    /**
     * @param WC_Product $product
     * @param int        $qty
     * @param string     $price
     *
     * @return float|string
     */
    function smm_get_price_excluding_tax( $product, $qty = 1, $price = '' ) {

        if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

            $price = wc_get_price_excluding_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
        } else {

            $price = $product->get_price_excluding_tax( $qty, $price );
        }

        return $price;
    }
}

if ( !function_exists( 'smm_get_price_including_tax' ) ) {
    /**
     * @param WC_Product $product
     * @param int        $qty
     * @param string     $price
     *
     * @return float|string
     */
    function smm_get_price_including_tax( $product, $qty = 1, $price = '' ) {

        if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

            $price = wc_get_price_including_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
        } else {

            $price = $product->get_price_including_tax( $qty, $price );
        }

        return $price;
    }
}

if ( !function_exists( 'smm_get_product_image_id' ) ) {
    /**
     * get the attach image id
     *
     * @param WC_Product $product
     * @param string     $context ( view/edit )
     */
    function smm_get_product_image_id( $product, $context = 'view' ) {

        if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

            $image_id = $product->get_image_id( $context );
        } else {

            $image_id = $product->get_image_id();
        }

        return $image_id;
    }
}

if ( !function_exists( 'smm_get_refund_amount' ) ) {
    /**
     * @param $refund  \WC_Order_Refund
     * @param $context string
     *
     * @return float
     */
    function smm_get_refund_amount( $refund, $context = 'view' ) {
        $is_wc_data = $refund instanceof WC_Data;

        if ( $is_wc_data ) {
            return $refund->get_amount( $context );
        } else {
            return $refund->get_refund_amount();
        }
    }
}

if ( !function_exists( 'smm_set_refund_amount' ) ){
    /**
     * @param $refund \WC_Order_Refund
     * @param $amount float
     *
     * @return float
     */
    function smm_set_refund_amount( $refund, $amount ){
        $is_wc_data = $refund instanceof WC_Data;

        if( $is_wc_data ){
            $refund->set_amount( $amount );
        }
        else{
            $refund->refund_amount = $amount;
        }
    }
}

if ( !function_exists( 'smm_get_refund_reason' ) ){
    /**
     * @param $refund \WC_Order_Refund
     * @param $amount float
     *
     * @return float
     */
    function smm_get_refund_reason( $refund ){
        $is_wc_data = $refund instanceof WC_Data;

        if( $is_wc_data ){
            return $refund->get_reason();
        }
        else{
            return $refund->get_refund_reason();
        }
    }
}

if ( !function_exists( 'smm_add_select2_fields' ) ) {
    /**
     * Add select 2
     *
     * @param array $args
     */
    function smm_add_select2_fields( $args = array() ) {
        $default = array(
            'type'              => 'hidden',
            'class'             => '',
            'id'                => '',
            'name'              => '',
            'data-placeholder'  => '',
            'data-allow_clear'  => false,
            'data-selected'     => '',
            'data-multiple'     => false,
            'data-action'       => '',
            'value'             => '',
            'style'             => '',
            'custom-attributes' => array()
        );

        $args = wp_parse_args( $args, $default );

        $custom_attributes = array();
        foreach ( $args[ 'custom-attributes' ] as $attribute => $attribute_value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
        }
        $custom_attributes = implode( ' ', $custom_attributes );

        if ( version_compare( WC()->version, '2.7.0', '>=' ) ) :
            if ( $args[ 'data-multiple' ] === true && substr( $args[ 'name' ], -2 ) != '[]' ) {
                $args[ 'name' ] = $args[ 'name' ] . '[]';
            }
            ?>

            <select
                id="<?php echo esc_attr( $args[ 'id' ] )?>"
                class="<?php echo esc_attr( $args[ 'class' ]) ?>"
                name="<?php echo esc_attr( $args[ 'name' ]) ?>"
                data-placeholder="<?php echo esc_attr( $args[ 'data-placeholder' ] )?>"
                data-allow_clear="<?php echo esc_attr( $args[ 'data-allow_clear' ]) ?>"
                <?php echo esc_attr( !empty( $args[ 'data-action' ] ) ? 'data-action="' . $args[ 'data-action' ] . '"' : '') ?>
                <?php echo esc_attr( !empty( $args[ 'data-multiple' ] ) ? 'multiple="multiple"' : '') ?>
                style="<?php echo esc_attr( $args[ 'style' ]) ?>"
                <?php echo esc_attr( $custom_attributes )?>
            >

                <?php if ( !empty( $args[ 'value' ] ) ) {
                    $values = $args[ 'value' ];

                    if ( !is_array( $values ) ) {
                        $values = explode( ',', $values );
                    }

                    foreach ( $values as $value ): ?>
                        <option value="<?php echo esc_attr( $value) ?>" <?php selected( true, true, true ) ?> >
                            <?php echo esc_attr( $args[ 'data-selected' ][ $value ]) ?>
                        </option>
                    <?php endforeach;
                }
                ?>
            </select>
            <?php
        else :
            if ( $args[ 'data-multiple' ] === false && is_array( $args[ 'data-selected' ] ) ) {
                $args[ 'data-selected' ] = current( $args[ 'data-selected' ] );
            }

            ?>
            <input
                type="hidden"
                id="<?php echo esc_attr($args[ 'id' ]) ?>"
                class="<?php echo esc_attr( $args[ 'class' ] )?>"
                name="<?php echo esc_attr( $args[ 'name' ] )?>"
                data-placeholder="<?php echo esc_attr( $args[ 'data-placeholder' ]) ?>"
                data-allow_clear="<?php echo esc_attr( $args[ 'data-allow_clear' ]) ?>"
                data-selected="<?php echo esc_attr( is_array( $args[ 'data-selected' ] ) ? esc_attr( wp_json_encode( $args[ 'data-selected' ] ) ) : esc_attr( $args[ 'data-selected' ] )) ?>"
                data-multiple="<?php echo esc_attr( $args[ 'data-multiple' ] === true ? 'true' : 'false') ?>"
                <?php echo esc_attr( !empty( $args[ 'data-action' ]  ? 'data-action="' . $args[ 'data-action' ] . '"' : '' )) ?>
                value="<?php echo esc_attr( $args[ 'value' ]) ?>"
                style="<?php echo esc_attr( $args[ 'style' ]) ?>"
                <?php echo esc_attr($custom_attributes) ?>
            />
            <?php
        endif;
    }
}

if ( !function_exists( 'smm_product_visibility_meta' ) ) {
    function smm_product_visibility_meta( $args ) {
        if ( version_compare( WC()->version, '2.7.0', '<' ) ) {
            $args[ 'meta_query' ]   = isset( $args[ 'meta_query' ] ) ? $args[ 'meta_query' ] : array();
            $args[ 'meta_query' ][] = WC()->query->visibility_meta_query();
        }

        elseif( taxonomy_exists( 'product_visibility' ) ) {
            $product_visibility_term_ids = wc_get_product_visibility_term_ids();
            $args[ 'tax_query' ]         = isset( $args[ 'tax_query' ] ) ? $args[ 'tax_query' ] : array();
            $args[ 'tax_query' ][]       = array(
                'taxonomy' => 'product_visibility',
                'field'    => 'term_taxonomy_id',
                'terms'    => is_search() ? $product_visibility_term_ids[ 'exclude-from-search' ] : $product_visibility_term_ids[ 'exclude-from-catalog' ],
                'operator' => 'NOT IN',
            );
        }

        return $args;
    }
}

if ( !function_exists( 'smm_datetime_to_timestamp' ) ) {

    /**
     *
     */
    function smm_datetime_to_timestamp( $date ) {

        if ( !is_int( $date ) ) {
            $date = strtotime( $date );
        }

        return $date;
    }

}
if ( !function_exists( 'smm_checkpoint' ) ) {

    /**
     * for testing only
     */
    function smm_checkpoint($object, $key, $single = true, $context = 'view') {

        $check = "My Data";
		$prop_map   = smm_return_new_attribute_map();
        $is_wc_data = $object instanceof WC_Data;

        if ( $is_wc_data ) 
		$key = ( array_key_exists( $key, $prop_map ) ) ? $prop_map[ $key ] : $key;
        if ( ( $getter = "get{$key}" ) && method_exists( $object, $getter ) ) {
                return $object->$getter( $context );
				} elseif ( ( $getter = "get_{$key}" ) && method_exists( $object, $getter ) ) {
                return $object->$getter( $context );
            } else {
                return $key; // $object->get_meta( $key, $single );
            }
    }

}


smm_fix_wc_deprecated_filters();
add_action( 'shutdown', 'smm_send_changes_to_db' );