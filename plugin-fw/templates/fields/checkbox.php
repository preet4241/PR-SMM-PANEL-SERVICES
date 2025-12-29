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
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract( $field );
?>
<input type="checkbox" id="<?php echo esc_attr( $id) ?>"
       name="<?php echo esc_attr( $name ) ?>" value="1"
    <?php echo esc_attr( !empty( $class ) ? "class='$class'" : '') ?>
       <?php if ( isset( $std ) ) : ?>data-std="<?php echo esc_attr( $std) ?>" <?php endif; ?>
    <?php checked( true, smms_plugin_fw_is_true( $value ) ) ?>
    <?php echo esc_attr( $custom_attributes) ?>
    <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>/>
<?php
if ( isset( $field[ 'desc-inline' ] ) )
    echo  '<span class="description inline">' . esc_html($field[ 'desc-inline' ]) . '</span>';

?>
