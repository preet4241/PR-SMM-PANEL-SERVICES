<?php
/**
 * This file belongs to the SMM Plugin Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 * Author: Yith
 * @var array $field
 */

/** @since 3.0.13 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract( $field );

$class = isset( $class ) ? $class : '';
$class = 'smms-plugin-fw-radio ' . $class;

?>
<div class="<?php echo esc_attr( $class) ?>" id="<?php echo esc_attr( $id ) ?>"
    <?php echo esc_attr( $custom_attributes) ?>
    <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?> value="<?php echo esc_attr( $value) ?>">
    <?php foreach ( $options as $key => $label ) :
        $radio_id = sanitize_key( $id . '-' . $key );
        ?>
        <div class="smms-plugin-fw-radio__row">
            <input type="radio" id="<?php echo esc_attr( $radio_id) ?>" name="<?php echo esc_attr( $name) ?>" value="<?php echo esc_attr( $key ) ?>" <?php checked( $key, $value ); ?> />
            <label for="<?php echo esc_attr( $radio_id) ?>"><?php echo esc_html( $label) ?></label>
        </div>
    <?php endforeach; ?>
</div>

