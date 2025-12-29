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
$panel_content_class = apply_filters( 'smm_admin_panel_content_class', 'smm-admin-panel-content-wrap' );
?>

<div id="<?php esc_html( $this->settings[ 'page' ]) ?>_<?php esc_html( $this->get_current_tab()) ?>" class="smms-plugin-fw  smm-admin-panel-container">
    <?php do_action( 'smm_framework_before_print_wc_panel_content', $current_tab ); ?>
    <div class="<?php echo esc_attr( $panel_content_class)?>">
        <form id="plugin-fw-wc" method="post">
            <?php $this->add_fields() ?>
            <?php wp_nonce_field( 'smm_panel_wc_options_' . $this->settings[ 'page' ], 'smm_panel_wc_options_nonce' ); ?>
            <input style="float: left; margin-right: 10px;" class="button-primary" type="submit" value="<?php esc_html_e( 'Save Changes', 'smm-api' ) ?>"/>
        </form>
        <form id="plugin-fw-wc-reset" method="post">
            <?php $warning = __( 'If you continue with this action, you will reset all options in this page.', 'smm-api' ) ?>
            <input type="hidden" name="smm-action" value="wc-options-reset"/>
            <?php wp_nonce_field( 'smms_wc_reset_options_' . $this->settings[ 'page' ], 'smms_wc_reset_options_nonce' ); ?>
            <input type="submit" name="smm-reset" class="button-secondary" value="<?php /* translators: search here */ esc_attr_e( 'Reset Defaults', 'smm-api' ) ?>"
                   onclick="return confirm('<?php printf(esc_attr($warning)). '\n' .  'Are you sure?' ?>');"/>
        </form>
    </div>
    <?php do_action( 'smm_framework_after_print_wc_panel_content', $current_tab ); ?>
</div>