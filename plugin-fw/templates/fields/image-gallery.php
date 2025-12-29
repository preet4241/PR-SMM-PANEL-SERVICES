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
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract( $field );
$array_id = array();
if ( !empty( $value ) ) {
    $array_id = array_filter( explode( ',', $value ) );
}
?>
<ul id="<?php echo esc_attr( $id) ?>-extra-images" class="slides-wrapper extra-images ui-sortable clearfix">
    <?php if ( !empty( $array_id ) ) : ?>
        <?php foreach ( $array_id as $image_id ) : ?>
            <li class="image" data-attachment_id= <?php echo esc_attr( $image_id ) ?>>
                <a href="#">
                    <?php
                    if ( function_exists( 'smm_image' ) ) :
                        smm_image( "id=$image_id&size=admin-post-type-thumbnails" );
                    else:
                        echo esc_html( wp_get_attachment_image( $image_id, array( 80, 80 ) ));
                    endif; ?>
                </a>
                <ul class="actions">
                    <li><a href="#" class="delete" title="<?php esc_html_e( 'Delete image', 'smm-api' ); ?>">x</a></li>
                </ul>
            </li>
        <?php endforeach; endif; ?>
</ul>
<input type="button" data-choose="<?php echo_html_e( 'Add Images to Gallery', 'smm-api' ); ?>" data-update="<?php esc_html_e( 'Add to gallery', 'smm-api' ); ?>" value="<?php esc_html_e( 'Add images', 'smm-api' ) ?>" data-delete="<?php esc_html_e( 'Delete image', 'smm-api' ); ?>" data-text="<?php esc_html_e( 'Delete', 'smm-api' ) ?>" id="<?php echo esc_attr( $id) ?>-button" class="image-gallery-button button"/>
<input type="hidden" class="image_gallery_ids" id="image_gallery_ids" name="<?php echo esc_attr( $name) ?>" value="<?php echo esc_attr( $value ) ?>"/>
