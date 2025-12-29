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

$size = isset( $size ) ? " style=\"width:{$size}px;\"" : '';
?>
<table class="smms-plugin-fw-text-array-table">
    <?php foreach ( $fields as $field_name => $field_label ) : ?>
        <tr>
            <td><?php echo esc_html( $field_label) ?></td>
            <td>
                <input type="text" name="<?php echo esc_attr( $name) ?>[<?php esc_attr( $field_name) ?>]" id="<?php echo esc_attr( $id) ?>_<?php echo esc_attr( $field_name) ?>" value="<?php echo esc_attr( isset( $value[ $field_name ] ) ?  $value[ $field_name ] : '') ?>"<?php echo esc_attr( $size) ?> />
            </td>
        </tr>
    <?php endforeach ?>
</table>
