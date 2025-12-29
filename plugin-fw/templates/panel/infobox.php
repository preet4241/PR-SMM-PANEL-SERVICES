<?php
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 *        'section_general_settings_boxinfo'         => array(
 *            'name' => __( 'General information', 'smm-api' ),
 *            'type' => 'boxinfo',
 *            'default' => array(
 *                'plugin_name' => __( 'Plugin Name', 'smm-api' ),
 *                'buy_url' => 'http://www.softnwords.com',
 *                'demo_url' => 'http://plugins.softnwords.com/demo-url/'
 *            ),
 *            'id'   => 'smms_wcas_general_boxinfo'
 *        ),
 */
?>
<div id="<?php echo esc_attr( $id) ?>" class="meta-box-sortables">
    <div id="<?php echo esc_attr( $id) ?>-content-panel" class="postbox " style="display: block;">
        <h3><?php echo esc_attr( $name) ?></h3>
        <div class="inside">
            <p>welcome... </p>
            <p class="submit"><a href="<?php echo esc_url( $default['buy_url']) ?>" class="button-primary">Buy Plugin</a></p>
        </div>
    </div>
</div>