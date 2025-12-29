<?php
/**
 * This file belongs to the SMM Plugin Framework.
 * Author: Yith
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @var array $field
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;// Exit if accessed directly

extract( $field );

$field[ 'labels' ] = array(
    'plural_name'    => 'Tabs',
    'singular_name'  => 'Tab',
    'item_name_sing' => 'Tab',
    'item_name_plur' => 'Tabs',
);
$value             = is_array( $value ) ? $value : array();
?>
<div id="smm_custom_tabs" class="panel wc-metaboxes-wrapper" style="display: block;">
    <p class="toolbar">
        <a href="#" class="close_all"><?php esc_html_e( 'Close all', 'smm-api' ) ?></a><a href="#" class="expand_all"><?php esc_html_e( 'Expand all', 'smm-api' ) ?></a>
    </p>

    <div class="smm_custom_tabs wc-metaboxes ui-sortable" style="">

        <?php if ( !empty( $value ) ): ?>
            <?php foreach ( $value as $i => $tab ): ?>
                <div class="smm_custom_tab wc-metabox closed" rel="0">
                    <h3>
                        <button type="button" class="remove_row button"><?php esc_html_e( 'Remove', 'smm-api' ) ?></button>
                        <div class="handlediv" title="Click to toggle"></div>
                        <strong class="attribute_name"><?php echo esc_html( $tab[ 'name' ]) ?></strong>
                    </h3>

                    <table cellpadding="0" cellspacing="0" class="woocommerce_attribute_data wc-metabox-content" style="display: table;">
                        <tbody>
                        <tr>
                            <td class="attribute_name">
                                <label><?php esc_html_e( 'Name', 'smm-api' ) ?>:</label>
                                <input type="text" class="attribute_name" name="<?php echo esc_attr( $name) ?>[<?php echo esc_attr( $i) ?>][name]" value="<?php echo esc_attr( $tab[ 'name' ]  )?>">
                                <input type="hidden" name="<?php echo esc_attr( $name) ?>[<?php echo esc_attr( $i) ?>][position]" class="attribute_position" value="<?php echo esc_attr( $i) ?>">
                            </td>

                            <td rowspan="3">
                                <label><?php esc_html_e( 'Value', 'smm-api' ) ?>:</label>
                                <textarea name="<?php echo esc_attr( $name) ?>[<?php echo esc_attr( $i )?>][value]" cols="5" rows="5" placeholder="<?php esc_html_e( 'Content of the tab. (HTML is supported)', 'smm-api' ) ?>"><?php echo esc_html( $tab[ 'value' ]) ?></textarea>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            <?php endforeach ?>
        <?php endif ?>
    </div>

    <p class="toolbar">
        <button type="button" class="button button-primary add_custom_tab"><?php esc_html_e( 'Add custom product tab', 'smm-api' ) ?></button>
    </p>

    <div class="clear"></div>
</div>

<script>
    jQuery( document ).ready( function ( $ ) {
        // Add rows
        $( 'button.add_custom_tab' ).on( 'click', function () {

            var size = $( '.smm_custom_tabs .smm_custom_tab' ).size() + 1;

            // Add custom attribute row
            $( '.smm_custom_tabs' ).append( '<div class="smm_custom_tab wc-metabox">\
						<h3>\
							<button type="button" class="remove_row button"><?php esc_html_e( 'Remove', 'smm-api' ) ?></button>\
							<div class="handlediv" title="Click to toggle"></div>\
							<strong class="attribute_name"></strong>\
						</h3>\
						<table cellpadding="0" cellspacing="0" class="woocommerce_attribute_data">\
							<tbody>\
								<tr>\
									<td class="attribute_name">\
										<label><?php esc_html_e( 'Name', 'smm-api' ) ?>:</label>\
										<input type="text" class="attribute_name" name="<?php echo esc_attr( $name) ?>[' + size + '][name]" />\
										<input type="hidden" name="<?php echo esc_attr( $name) ?>[' + size + '][position]" class="attribute_position" value="' + size + '" />\
									</td>\
									<td rowspan="3">\
										<label><?php esc_html_e( 'Value', 'smm-api' ) ?>:</label>\
										<textarea name="<?php echo esc_attr( $name) ?>[' + size + '][value]" cols="5" rows="5" placeholder="<?php printf(/* translators: search here */ '%s'. esc_html(addslashes( esc_html__( 'Content of the tab. (HTML is supported)', 'smm-api' ) ))) ?>"></textarea>\
									</td>\
								</tr>\
							</tbody>\
						</table>\
					</div>' );

        } );


        $( '.smm_custom_tabs' ).on( 'click', 'button.remove_row', function () {
            var answer = confirm( "<?php esc_html_e( 'Do you want to remove the custom tab?', 'smm-api' ) ?>" );
            if ( answer ) {
                var $parent = $( this ).parent().parent();

                $parent.remove();
                attribute_row_indexes();
            }
            return false;
        } );

        // Attribute ordering
        $( '.smm_custom_tabs' ).sortable( {
                                              items               : '.smm_custom_tab',
                                              cursor              : 'move',
                                              axis                : 'y',
                                              handle              : 'h3',
                                              scrollSensitivity   : 40,
                                              forcePlaceholderSize: true,
                                              helper              : 'clone',
                                              opacity             : 0.65,
                                              placeholder         : 'wc-metabox-sortable-placeholder',
                                              start               : function ( event, ui ) {
                                                  ui.item.css( 'background-color', '#f6f6f6' );
                                              },
                                              stop                : function ( event, ui ) {
                                                  ui.item.removeAttr( 'style' );
                                                  attribute_row_indexes();
                                              }
                                          } );

        function attribute_row_indexes() {
            $( '.smm_custom_tabs .smm_custom_tab' ).each( function ( index, el ) {
                var newVal = '[' + $( el ).index( '.smm_custom_tabs .smm_custom_tab' ) + ']';
                var oldVal = '[' + $( '.attribute_position', el ).val() + ']';

                $( ':input:not(button)', el ).each( function () {
                    var name = $( this ).attr( 'name' );
                    $( this ).attr( 'name', name.replace( oldVal, newVal ) );
                } );

                $( '.attribute_position', el ).val( $( el ).index( '.smm_custom_tabs .smm_custom_tab' ) );
            } );
        }

    } );
</script>