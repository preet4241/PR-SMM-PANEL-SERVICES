<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
/**
 * @var array  $field
 * @var string $description
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
$default_field = array(
    'id'    => '',
    'title' => isset( $field[ 'name' ] ) ? $field[ 'name' ] : '',
    'desc'  => '',
);
$field         = wp_parse_args( $field, $default_field );

$display_row = !in_array( $field[ 'type' ], array( 'hidden', 'html', 'sep', 'simple-text', 'title', 'list-table' ) );
$display_row = isset( $field[ 'smms-display-row' ] ) ? !!$field[ 'smms-display-row' ] : $display_row;

$extra_row_classes = apply_filters( 'smms_plugin_fw_panel_wc_extra_row_classes', array(), $field );
$extra_row_classes = is_array( $extra_row_classes ) ? implode( ' ', $extra_row_classes ) : '';

?>
<tr valign="top" class="smms-plugin-fw-panel-wc-row <?php echo esc_attr( $field[ 'type' ]) ?> <?php echo esc_attr( $extra_row_classes )?>" <?php esc_attr( smms_field_deps_data( $field ) )?>>
    <?php if ( $display_row ) : ?>
        <th scope="row" class="titledesc">
            <label for="<?php echo esc_attr( $field[ 'id' ] ) ?>"><?php echo esc_html( $field[ 'title' ] )?></label>
        </th>
        <td class="forminp forminp-<?php echo esc_attr(sanitize_title( $field[ 'type' ] )) ?>">
            <?php smms_plugin_fw_get_field( $field, true ); ?>
            <?php echo '<span class="description">' . wp_kses_post( $field[ 'desc' ] ) . '</span>' ?>
        </td>
    <?php else: ?>
        <td colspan="2">
            <?php smms_plugin_fw_get_field( $field, true ); ?>
        </td>
    <?php endif; ?>
</tr>