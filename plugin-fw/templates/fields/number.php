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

$min_max_attr = $step_attr = '';

if ( isset( $min ) ) {
    $min_max_attr .= " min='{$min}'";
}

if ( isset( $max ) ) {
    $min_max_attr .= " max='{$max}'";
}

if ( isset( $step ) ) {
    $step_attr .= "step='{$step}'";
}
?>
<input type="number" id="<?php echo esc_attr( $id) ?>"
    <?php echo esc_attr( !empty( $class ) ? "class='$class'" : ''); ?>
       name="<?php echo esc_attr( $name) ?>" <?php echo esc_attr( $step_attr) ?> <?php echo esc_attr( $min_max_attr) ?>
       value="<?php echo esc_attr( $value  )?>" <?php if ( isset( $std ) ) : ?>data-std="<?php echo esc_attr( $std) ?>"<?php endif ?>
    <?php echo esc_attr( $custom_attributes) ?>
    <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>/>