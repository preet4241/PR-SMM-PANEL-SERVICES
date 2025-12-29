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

if ( ! defined( 'ABSPATH' ) ) exit;// Exit if accessed directly

extract( $field );
$multiple_html = ( isset( $multiple ) && $multiple ) ? ' multiple' : '';
?>

<select<?php echo esc_attr( $multiple_html) ?>
    id="<?php echo esc_attr( $id) ?>"
    name="<?php echo esc_attr( $name) ?>" <?php if ( isset( $std ) ) : ?>data-std="<?php echo esc_attr( $std) ?>"<?php endif ?>
    class="smms-plugin-fw-select"
    <?php echo esc_attr( $custom_attributes) ?>
    <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>>
    <?php foreach ( $options as $key => $item ) : ?>
        <option value="<?php echo esc_attr( $key) ?>"<?php selected( $key, $value ) ?>><?php echo esc_html( $item) ?></option>
    <?php endforeach; ?>
</select>
<input type="button" class="button-secondary <?php echo esc_attr( isset( $class ) ? $class : '') ?>" value="<?php echo esc_attr( $button_name) ?>"/>
<span class="spinner"></span>
