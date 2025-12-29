<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

 add_thickbox();
?>
<div class="wrap <?php echo esc_attr( $wrap_class)?>">
    <div id="icon-users" class="icon32"><br/></div>
	<?php do_action('smms_plugin_fw_before_woocommerce_panel', $page )?>
    <?php if( ! empty( $available_tabs ) ): ?>
        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <?php foreach( $available_tabs as $id => $label ):
	            $active_class = ( $current_tab == $id ) ? ' nav-tab-active' : '';
	            $active_class .= 'premium' == $id ? ' smms-premium ': '';
                ?>
                <a href="?page=<?php echo esc_attr( $page) ?>&tab=<?php echo esc_attr( $id) ?>" class="nav-tab <?php echo esc_attr( $active_class )?>"><?php echo esc_html( $label) ?></a>
            <?php endforeach; ?>
        </h2>
        <?php $this->print_panel_content() ?>
    <?php endif; ?>
</div>