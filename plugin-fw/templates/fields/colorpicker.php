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

wp_enqueue_style( 'wp-color-picker' );

extract( $field );

$class = !empty( $class ) ? $class : 'smms-plugin-fw-colorpicker';
$default = isset( $default ) ?  $default : '';
if( isset($std) && !empty( $std) && empty($default) ){
	$default = $std;
}
?>

<input type="text" name="<?php echo esc_attr( $name) ?>"
       id="<?php echo esc_attr( $id ) ?>" value="<?php echo esc_attr( $value ) ?>"
       <?php if ( isset( $default ) ) : ?> data-default-color="<?php echo esc_attr( $default) ?>"<?php endif ?>
       class="<?php echo esc_attr( $class) ?>"
    <?php echo esc_attr( $custom_attributes) ?>
    <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>/>

