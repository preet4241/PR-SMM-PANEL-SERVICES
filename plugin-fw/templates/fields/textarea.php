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

$class = isset( $class ) ? $class : 'smms-plugin-fw-textarea';
$rows = isset( $rows ) ? $rows : 5;
$cols = isset( $cols ) ? $cols : 50;
?>
<textarea id="<?php echo esc_attr( $id) ?>"
          name="<?php echo esc_attr( $name) ?>"
          class="<?php echo esc_attr( $class) ?>"
          rows="<?php echo esc_attr( $rows) ?>" cols="<?php echo esc_attr( $cols) ?>" <?php if ( isset( $std ) ) : ?>data-std="<?php echo esc_attr( $std) ?>"<?php endif ?>
    <?php echo esc_attr( $custom_attributes) ?>
    <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>><?php echo esc_html( $value) ?></textarea>