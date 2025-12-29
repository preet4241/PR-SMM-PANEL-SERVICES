<?php
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Field Container for SMM Panel
 *
 * @package    SMMS
 * @author     sam
 * @since      3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$id   = $this->get_id_field( $option[ 'id' ] );
$name = $this->get_name_field( $option[ 'id' ] );
$type = $option[ 'type' ];

$field            = $option;
$field[ 'id' ]    = $id;
$field[ 'name' ]  = $name;
$field[ 'value' ] = $db_value;
if ( !empty( $custom_attributes ) )
    $field[ 'custom_attributes' ] = $custom_attributes;

?>
<div id="<?php echo esc_attr( $id) ?>-container" class="smm_options smms-plugin-fw-field-wrapper smms-plugin-fw-<?php echo esc_attr( $type) ?>-field-wrapper" <?php echo esc_attr( smms_panel_field_deps_data( $option, $this )) ?>>
    <div class="option">
        <?php smms_plugin_fw_get_field( $field, true, false ); ?>
    </div>
    <span class="description"><?php echo esc_html( $option[ 'desc' ]) ?></span>

    <div class="clear"></div>
</div>

