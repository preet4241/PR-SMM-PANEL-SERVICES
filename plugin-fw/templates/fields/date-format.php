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

/** @since 3.1.30 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract( $field );

$class = isset( $class ) ? $class : '';
$js    = isset( $js ) ? $js : false;
$class = 'smms-plugin-fw-radio ' . $class;

$options = smms_get_date_format( $js );
$custom  = true;
?>
<div class="<?php echo esc_attr( $class) ?> smms-plugin-fw-date-format" id="<?php echo esc_attr( $id) ?>"
	<?php echo esc_attr( $custom_attributes) ?>
	<?php if ( isset( $data ) ) {
		echo esc_attr( smms_plugin_fw_html_data_to_string( $data ));
	} ?> value="<?php echo esc_attr( $value) ?>">
	<?php foreach ( $options as $key => $label ) :
		$checked = '';
		$radio_id = sanitize_key( $id . '-' . $key );
		if ( $value === $key ) { // checked() uses "==" rather than "==="
			$checked = " checked='checked'";
			$custom  = false;
		}
		?>
        <div class="smms-plugin-fw-radio__row">
            <input type="radio" id="<?php echo esc_attr( $radio_id ) ?>" name="<?php echo esc_attr( $name) ?>"
                   value="<?php echo esc_attr( esc_attr( $key )) ?>" <?php echo esc_attr( $checked) ?> />
            <label for="<?php echo esc_attr( esc_attr( $radio_id )) ?>"><?php echo esc_html( date_i18n( $label ) )?>
                <code><?php echo esc_html( $key ) ?></code></label>
        </div>
	<?php endforeach; ?>
	<?php $radio_id = sanitize_key( $id . '-custom' ); ?>
    <div class="smms-plugin-fw-radio__row">
        <input type="radio" id="<?php echo esc_attr( $radio_id ) ?>" name="<?php echo esc_attr( $name ) ?>"
               value="\c\u\s\t\o\m" <?php echo esc_attr(checked( $custom )); ?> />
        <label for="<?php echo  esc_attr( $radio_id ) ?>"> <?php printf( esc_html__( 'Custom:', 'smm-api' )) ?></label>
        <input type="text" name="<?php echo esc_attr( $name . '_text'  )?>"
               id="<?php echo esc_attr( $radio_id ) ?>_text" value="<?php echo esc_attr( $value ) ?>"
               class="small-text"/>
    </div>

</div>