<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
/**
 * @var array $sections
 */
 /**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>
<div class="wp-suggested-text">
    <?php do_action( 'smms_plugin_fw_privacy_guide_content_before' ); ?>

    <?php
    foreach ( $sections as $key => $section ) {
        $action  = "smms_plugin_fw_privacy_guide_content_{$key}";
        $content = apply_filters( 'smms_plugin_fw_privacy_guide_content', '', $key );

        if ( has_action( $action ) || !empty( $section[ 'tutorial' ] ) || !empty( $section[ 'description' ] ) || $content ) {
            if ( !empty( $section[ 'title' ] ) ) {
                printf( "<h2>%s</h2>",esc_html($section['title']));
            }

            if ( !empty( $section[ 'tutorial' ] ) ) {
                printf( "<p class='privacy-policy-tutorial'>%s</p>",esc_html($section['tutorial']));
            }

            if ( !empty( $section[ 'description' ] ) ) {
                printf("<p>%s</p>",esc_html($section['description']));
            }

            if ( !empty( $content ) ) {
               echo esc_html( $content);
            }
        }

        do_action( $action );
    }
    ?>

    <?php do_action( 'smms_plugin_fw_privacy_guide_content_after' ); ?>
</div>