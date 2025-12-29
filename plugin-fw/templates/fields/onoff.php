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
?>

<div class="smms-plugin-fw-onoff-container">
    <input type="checkbox" id="<?php echo esc_attr( $id) ?>" name="<?php echo esc_attr( $name) ?>" value="<?php echo esc_attr( $value ) ?>" <?php checked( smms_plugin_fw_is_true( $value ) ) ?> class="on_off" <?php if ( isset( $std ) ) : ?>data-std="<?php echo esc_attr( $std) ?>"<?php endif ?> />
    <span class="smms-plugin-fw-onoff"></span>
</div>
<?php
if ( isset( $field[ 'desc-inline' ] ) ) {
    echo "<span class='description inline'>" . esc_html($field[ 'desc-inline' ]) . "</span>";
}
?>
