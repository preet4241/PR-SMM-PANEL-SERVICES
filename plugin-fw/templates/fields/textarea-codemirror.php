<?php
/**
 * This file belongs to the SMM Plugin Framework.
 * Author: Yith
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wp_enqueue_script( 'wp-codemirror' );
wp_enqueue_script( 'codemirror-javascript' );
wp_enqueue_style( 'codemirror' );

extract( $field );

$class = isset( $class ) ? $class : 'codemirror';
?>
<textarea id="<?php echo esc_attr( $id) ?>"
          name="<?php echo esc_attr( $name) ?>"
          class="<?php echo esc_attr( $class) ?>"
          rows="8" cols="50" <?php echo esc_attr( $custom_attributes) ?>
    <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>><?php echo esc_html( $value) ?></textarea>