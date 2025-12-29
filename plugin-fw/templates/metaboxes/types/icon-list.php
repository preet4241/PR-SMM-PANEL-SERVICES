<?php
/*
 * This file belongs to the SMM Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Awesome Icon Admin View
 *
 * @package    SMMS
 * @author sam softnwords
 * @since 1.0.0
 */

extract( $args );


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$current_options = wp_parse_args( $args[ 'value' ], $args[ 'std' ] );
$current_icon    = SMM_Icon()->get_icon_data( $current_options[ 'icon' ] );
$std_icon        = SMM_Icon()->get_icon_data( $std[ 'icon' ] );

$options[ 'icon' ] = SMM_Plugin_Common::get_icon_list();

?>


<div id="<?php echo esc_attr( $id) ?>-container" class="select_icon rm_option rm_input rm_text" <?php echo esc_attr( smms_field_deps_data( $args )) ?>>
    <div id="<?php echo esc_attr( $id) ?>-container" <?php echo esc_attr( smms_field_deps_data( $args )) ?>>

        <label for="<?php echo esc_attr( $id) ?>"><?php echo esc_html( $label) ?></label>

        <div class="option">
            <div class="select_wrapper icon_list_type clearfix">
                <select name="<?php echo esc_attr( $name) ?>[select]" id="<?php echo esc_attr( $id) ?>[select]" <?php if ( isset( $std[ 'select' ] ) ) : ?>data-std="<?php echo esc_attr( $std[ 'select' ]) ?>"<?php endif; ?>>
                    <?php foreach ( $options[ 'select' ] as $val => $option ) : ?>
                        <option value="<?php echo esc_attr( $val) ?>" <?php selected( $current_options[ 'select' ], $val ); ?> ><?php echo esc_html( $option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="icon-manager-wrapper">
                <div class="icon-manager-text">
                    <div class="icon-preview" <?php echo esc_attr( $current_icon) ?>></div>
                    <input type="text" id="<?php echo esc_attr( $id) ?>[icon]" class="icon-text" name="<?php echo esc_attr( $name) ?>[icon]" value="<?php echo esc_attr( $current_options[ 'icon' ]) ?>"/>
                </div>


                <div class="icon-manager">
                    <ul class="icon-list-wrapper">
                        <?php foreach ( $options[ 'icon' ] as $font => $icons ):
                            foreach ( $icons as $key => $icon ): ?>
                                <li data-font="<?php echo esc_attr( $font) ?>" data-icon="<?php echo esc_attr( ( strpos( (string)$key, '\\' ) === 0 ) ? '&#x' . substr( $key, 1 ) : $key) ?>" data-key="<?php echo esc_attr( $key) ?>" data-name="<?php echo esc_attr( $icon) ?>"></li>
                                <?php
                            endforeach;
                        endforeach; ?>
                    </ul>
                </div>
            </div>


            <div class="input_wrapper custom_icon_wrapper upload" style="clear:both;">
                <input type="text" name="<?php echo esc_attr( $name) ?>[custom]" id="<?php echo esc_attr( $id) ?>[custom]" value="<?php echo esc_attr( $current_options[ 'custom' ]) ?>" class="smms-plugin-fw-upload-img-url upload_custom_icon"/>
                <input type="button" value="<?php esc_html_e( 'Upload', 'smm-api' ) ?>" id="<?php echo esc_attr( $id) ?>-custom-button" class="smms-plugin-fw-upload-button button"/>

                <div class="smms-plugin-fw-upload-img-preview" style="margin-top:10px;">
                    <?php
                    $file = $current_options[ 'custom' ];
                    if ( preg_match( '/(jpg|jpeg|png|gif|ico)$/', $file ) ) {
                        printf(/* translators: search here */ esc_html__( 'Image preview', 'smm-api' ));
						echo ': ';
						echo "<img src=\"" . esc_url(SMM_CORE_ASSETS_URL) . "/images/sleep.png\" data-src=\"";
					    printf(esc_html($file));
						 echo "\" />";
                    }
                    ?>
                </div>
            </div>

        </div>

        <div class="clear"></div>


        <div class="description">
            <?php echo esc_html(  $desc )?>
            <?php if ( $std[ 'select' ] == 'custom' ) : ?>
                <?php printf(/* translators: search here */ esc_html__( '(Default: %1$s <img src="%2$s"/>)', 'smm-api' ), esc_attr($options[ 'select' ][ 'custom' ]), esc_attr($std[ 'custom' ] )) ?>
            <?php else: ?>
                <?php printf(/* translators: search here */ esc_html__( '(Default: <i %s></i> )', 'smm-api' ), esc_attr($std_icon) ) ?>
            <?php endif; ?>
        </div>

        <div class="clear"></div>

    </div>
</div>

<script>

    jQuery( document ).ready( function ( $ ) {

        $( '.select_wrapper.icon_list_type' ).on( 'change', function () {

            var t       = $( this );
            var parents = $( '#' + t.parents( 'div.select_icon' ).attr( 'id' ) );
            var option  = $( 'option:selected', this ).val();
            var to_show = option == 'none' ? '' : option == 'icon' ? '.icon-manager-wrapper' : '.custom_icon_wrapper';

            parents.find( '.option > div:not(.icon_list_type)' ).removeClass( 'show' ).addClass( 'hidden' );
            parents.find( to_show ).removeClass( 'hidden' ).addClass( 'show' );
        } );

        $( '.select_wrapper.icon_list_type' ).trigger( 'change' );

        var $icon_list    = $( '.select_icon' ).find( 'ul.icon-list-wrapper' ),
            $preview      = $( '.icon-preview' ),
            $element_list = $icon_list.find( 'li' ),
            $icon_text    = $( '.icon-text' );

        $element_list.on( "click", function () {
            var $t = $( this );
            $element_list.removeClass( 'active' );
            $t.addClass( 'active' );
            $preview.attr( 'data-font', $t.data( 'font' ) );
            $preview.attr( 'data-icon', $t.data( 'icon' ) );
            $preview.attr( 'data-name', $t.data( 'name' ) );
            $preview.attr( 'data-key', $t.data( 'key' ) );

            $icon_text.val( $t.data( 'font' ) + ':' + $t.data( 'name' ) );

        } );
    } );

</script>