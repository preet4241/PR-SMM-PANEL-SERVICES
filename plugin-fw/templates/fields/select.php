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
$multiple      = isset( $multiple ) && $multiple;
$multiple_html = ( $multiple ) ? ' multiple' : '';
$placeholder   = isset( $placeholder ) ? ' data-placeholder = "' . $placeholder .'" ': '';
if ( $multiple && !is_array( $value ) )
    $value = array();

$class = isset( $class ) ? $class : 'smms-plugin-fw-select';
?>
    <select<?php echo esc_attr( $multiple_html) ?>
            id="<?php echo esc_attr( $id) ?>"
        name="<?php echo esc_attr( $name) ?><?php if ( $multiple ) echo esc_attr( "[]" )?>" <?php if ( isset( $std ) ) : ?>
        data-std="<?php echo esc_attr( ( $multiple ) ? implode( ' ,', $std ) : $std) ?>"<?php endif ?>

        class="<?php echo esc_attr( $class) ?>"
	    <?php echo esc_attr( $placeholder) ?>
        <?php echo esc_attr( $custom_attributes) ?>
        <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>>
        <?php foreach ( $options as $key => $item ) : ?>
            <option value="<?php echo  esc_attr( $key ) ?>" <?php if ( $multiple ): selected( true, in_array( $key, $value ) );
            else: selected( $key, $value ); endif; ?> ><?php echo esc_html( $item) ?></option>
        <?php endforeach; ?>
    </select>

<?php
/* --------- BUTTONS ----------- */
if ( isset( $buttons ) ) {
    $button_field = array(
        'type'    => 'buttons',
        'buttons' => $buttons
    );
    smms_plugin_fw_get_field( $button_field, true );
}
?>