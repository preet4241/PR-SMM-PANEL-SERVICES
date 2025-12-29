<?php
/*
 * This file belongs to the SMM Framework.
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



wp_enqueue_style( 'font-awesome' );
extract( $field );

$filter_icons      = !empty( $field[ 'filter_icons' ] ) ? $field[ 'filter_icons' ] : '';
$default_icon_text = isset( $std ) ? $std : false;
$default_icon_data = SMM_Icons()->get_icon_data( $default_icon_text, $filter_icons );

$current_icon_data = SMM_Icons()->get_icon_data( $value, $filter_icons );
$current_icon_text = $value;

$smm_icons = SMM_Icons()->get_icons( $filter_icons );
?>

<div id="smm-icons-manager-wrapper-<?php echo esc_attr( $id ) ?>" class="smm-icons-manager-wrapper">

    <div class="smm-icons-manager-text">
        <div class="smm-icons-manager-icon-preview" <?php echo esc_attr( $current_icon_data) ?>></div>
        <input class="smm-icons-manager-icon-text" type="text" id="<?php echo esc_attr( $id ) ?>" name="<?php echo esc_attr( $name) ?>" value="<?php echo esc_attr( $current_icon_text) ?>"/>
        <div class="clear"></div>
    </div>


    <div class="smm-icons-manager-list-wrapper">
        <ul class="smm-icons-manager-list">
            <?php foreach ( $smm_icons as $font => $icons ):
                foreach ( $icons as $key => $icon_name ):
                    $icon_text = $font . ':' . $icon_name;
                    $icon_class = $icon_text == $current_icon_text ? 'active' : '';
                    $icon_class .= $icon_text == $default_icon_text ? ' default' : '';
                    $data_icon = str_replace( '\\', '&#x', $key );
                    ?>
                    <li class="<?php echo esc_attr( $icon_class) ?>" data-font="<?php echo esc_attr( $font) ?>" data-icon="<?php echo esc_attr( $data_icon) ?>" data-key="<?php echo esc_attr( $key) ?>"
                        data-name="<?php echo esc_attr( $icon_name) ?>"></li>
                    <?php
                endforeach;
            endforeach; ?>
        </ul>
    </div>

    <div class="smm-icons-manager-actions">
        <?php if ( $default_icon_text ): ?>
            <div class="smm-icons-manager-action-set-default button"><?php printf( esc_attr__( 'Set Default', 'smm-api' ) )?>
            <i class="smm-icons-manager-default-icon-preview" <?php echo esc_attr( $default_icon_data) ?>></i>
            </div>
        <?php endif ?>
    </div>

</div>
