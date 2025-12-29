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

extract( $field );

$layout        = !isset( $value[ 'layout' ] ) ? 'sidebar-no' : $value[ 'layout' ];
$sidebar_left  = !isset( $value[ 'sidebar-left' ] ) ? '-1' : $value[ 'sidebar-left' ];
$sidebar_right = !isset( $value[ 'sidebar-right' ] ) ? '-1' : $value[ 'sidebar-right' ];
?>
<div class="smms-plugin-fw-sidebar-layout">
    <div class="option">
        <input type="radio" name="<?php echo esc_attr( $name) ?>[layout]" id="<?php echo esc_attr( $id) . '-left' ?>" value="sidebar-left" <?php checked( $layout, 'sidebar-left' ) ?> />
        <img src="<?php echo esc_url( SMM_CORE_PLUGIN_URL) ?>/assets/images/sidebar-left.png" title="<?php esc_html_e( 'Left sidebar', 'smm-api' ) ?>" alt="<?php esc_html_e( 'Left sidebar', 'smm-api' ) ?>" class="<?php echo esc_attr( $id) . '-left' ?>" data-type="left"/>

        <input type="radio" name="<?php echo esc_attr($name) ?>[layout]" id="<?php echo esc_attr( $id) . '-right' ?>" value="sidebar-right" <?php checked( $layout, 'sidebar-right' ) ?> />
        <img src="<?php echo esc_url( SMM_CORE_PLUGIN_URL) ?>/assets/images/sidebar-right.png" title="<?php esc_html_e( 'Right sidebar', 'smm-api' ) ?>" alt="<?php esc_html_e( 'Right sidebar', 'smm-api' ) ?>" class="<?php echo esc_attr( $id) . '-right' ?>" data-type="right"/>

        <input type="radio" name="<?php echo esc_attr( $name) ?>[layout]" id="<?php echo esc_attr( $id) . '-double' ?>" value="sidebar-double" <?php checked( $layout, 'sidebar-double' ) ?> />
        <img src="<?php echo esc_url( SMM_CORE_PLUGIN_URL) ?>/assets/images/double-sidebar.png" title="<?php esc_html_e( 'No sidebar', 'smm-api' ) ?>" alt="<?php esc_html_e( 'No sidebar', 'smm-api' ) ?>" class="<?php echo esc_attr( $id) . '-double' ?>" data-type="double"/>

        <input type="radio" name="<?php echo esc_attr( $name) ?>[layout]" id="<?php echo esc_attr( $id) . '-no' ?>" value="sidebar-no" <?php checked( $layout, 'sidebar-no' ) ?> />
        <img src="<?php echo esc_url( SMM_CORE_PLUGIN_URL) ?>/assets/images/no-sidebar.png" title="<?php esc_html_e( 'No sidebar', 'smm-api' ) ?>" alt="<?php esc_html_e( 'No sidebar', 'smm-api' ) ?>" class="<?php echo esc_attr( $id) . '-no' ?>" data-type="none"/>
    </div>
    <div class="clearfix"></div>
    <div class="option" id="choose-sidebars">
        <div class="side">
            <div <?php if ( $layout != 'sidebar-double' && $layout != 'sidebar-left' ) {
                echo esc_attr( 'style="display:none"');
            } ?> class="smms-plugin-fw-sidebar-layout-sidebar-left-container select-mask">
                <label for="<?php echo esc_attr( $id) ?>-sidebar-left"><?php esc_html_e( 'Left Sidebar', 'smm-api' ) ?></label>
                <select class="smms-plugin-fw-select" name="<?php echo esc_attr( $name) ?>[sidebar-left]" id="<?php echo esc_attr( $id) ?>-sidebar-left">
                    <option value="-1"><?php esc_html_e( 'Choose a sidebar', 'smm-api' ) ?></option>
                    <?php foreach ( smm_registered_sidebars() as $val => $option ) { ?>
                        <option value="<?php echo esc_attr( $val ) ?>" <?php selected( $sidebar_left, $val ) ?>><?php echo esc_html( $option) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="side" style="clear: both">
            <div <?php if ( $layout != 'sidebar-double' && $layout != 'sidebar-right' ) {
                echo esc_attr( 'style="display:none"');
            } ?> class="smms-plugin-fw-sidebar-layout-sidebar-right-container select-mask">
                <label for="<?php echo esc_attr( $id) ?>-sidebar-right"><?php esc_html_e( 'Right Sidebar', 'smm-api' ) ?></label>
                <select class="smms-plugin-fw-select" name="<?php echo esc_attr( $name) ?>[sidebar-right]" id="<?php echo esc_attr( $id) ?>-sidebar-right">
                    <option value="-1"><?php esc_html_e( 'Choose a sidebar', 'smm-api' ) ?></option>
                    <?php foreach ( smm_registered_sidebars() as $val => $option ) { ?>
                        <option value="<?php  echo esc_attr( $val ) ?>" <?php selected( $sidebar_right, $val ) ?>><?php echo esc_html( $option) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
</div>
