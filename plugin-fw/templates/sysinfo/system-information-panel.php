<?php
/*
 * This file belongs to the SMM Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
$system_info        = get_option( 'smms_system_info' );
$recommended_memory = '128M';

?>
<div id="smms-sysinfo" class="wrap smms-system-info">
    <h1>
        <span class="smms-logo"><img src="<?php echo esc_url(smms_plugin_fw_get_default_logo()) ?>" /></span> <?php esc_html_e( 'SMMS System Information', 'smm-api' ) ?>
    </h1>

	<?php if ( ! isset( $_GET['smms-phpinfo'] ) || $_GET['smms-phpinfo'] != 'true' ): ?>

        <table class="widefat striped">
			<?php foreach ( $system_info['system_info'] as $key => $item ): ?>
				<?php
				$to_be_enabled = strpos( (string)$key, '_enabled' ) !== false;
				$has_errors    = isset( $item['errors'] );
				$has_warnings  = false;

				if ( $key == 'wp_memory_limit' && ! $has_errors ) {
					$has_warnings = $item['value'] < $recommended_memory;
				}

				?>
                <tr>
                    <th class="requirement-name">
						<?php  echo esc_html( $labels[ $key ]) ?>
                    </th>
                    <td class="requirement-value <?php  echo esc_attr( $has_errors ? 'has-errors' : '' ) ?> <?php echo esc_attr( $has_warnings ? 'has-warnings' : '' ) ?>">
                        <span class="dashicons dashicons-<?php echo esc_attr( $has_errors || $has_warnings ? 'warning' : 'yes' ) ?>"></span>

						<?php if ( $to_be_enabled ) {
							printf(/* translators: search here */ $item['value'] ? esc_html__( 'Enabled', 'smm-api' ) : esc_html__( 'Disabled', 'smm-api' ));
						} elseif ( $key == 'wp_memory_limit' ) {
							echo esc_html( size_format( $item['value'] ) );
						} else {
							echo esc_html( $item['value']);
						} ?>

                    </td>
                    <td class="requirement-messages">
						<?php if ( $has_errors ) : ?>
                            <ul>
								<?php foreach ( $item['errors'] as $plugin => $requirement ) : ?>
                                    <li>
										<?php if ( $to_be_enabled ) {
											printf(/* translators: search here */ esc_html__( '%1$s needs %2$s enabled', 'smm-api' ), '<b>' . esc_html($plugin) . '</b>', '<b>' . esc_html($labels[ $key ]) . '</b>' );
										} elseif ( $key == 'wp_memory_limit' ) {
											printf(/* translators: search here */ esc_html__( '%1$s needs at least %2$s of available memory', 'smm-api' ), '<b>' . esc_html($plugin) . '</b>', '<span class="error">' . esc_html( size_format( SMMS_System_Status()->memory_size_to_num( $requirement ) ) ) . '</span>' );
											echo '<br/>';
											printf(/* translators: search here */ esc_html__( 'For optimal functioning of our plugins, we suggest setting at least %s of available memory', 'smm-api' ), '<span class="error">' . esc_html( size_format( SMMS_System_Status()->memory_size_to_num( $recommended_memory ) ) ) . '</span>' );

										} else {
											printf(/* translators: search here */ esc_html__( '%1$s needs at least %2$s version', 'smm-api' ), '<b>' . esc_html($plugin) . '</b>', '<span class="error">' . esc_html($requirement) . '</span>' );
										} ?>
                                    </li>
								<?php endforeach; ?>
                            </ul>
							<?php switch ( $key ) {

								case 'min_wp_version':
								case 'min_wc_version':
									 printf(/* translators: search here */ 'Update it to the latest version in order to benefit of all new features and security updates.', 'smm-api' );
									break;
								case 'min_php_version':
								case 'min_tls_version':
								case 'imagick_version':
									 printf(/* translators: search here */ 'Contact your hosting company in order to update it.', 'smm-api' );
									break;
								case 'wp_cron_enabled':
									printf(/* translators: search here */ esc_html__( 'Remove %1$s from %2$s file', 'smm-api' ), '<code>define( \'DISABLE_WP_CRON\', true );</code>', '<b>wp-config.php</b>' );
									break;
								case 'mbstring_enabled':
								case 'simplexml_enabled':
								case 'gd_enabled':
								case 'opcache_enabled':
								case 'url_fopen_enabled':
									printf(/* translators: search here */ 'Contact your hosting company in order to enable it.', 'smm-api' );
									break;
								case 'wp_memory_limit':
									printf(/* translators: search here */ esc_html__( 'Read more %1$s here%2$s or contact your hosting company in order to increase it.', 'smm-api' ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">', '</a>' );
									break;
								default:
									echo esc_html(apply_filters( 'smms_system_generic_message', '', $item ));

							} ?>
						<?php endif; ?>

						<?php if ( $has_warnings ) : ?>
                            <ul>
                                <li>
									<?php printf(/* translators: search here */ esc_html__( 'For optimal functioning of our plugins, we suggest setting at least %s of available memory', 'smm-api' ), '<span class="warning">' . esc_html( size_format( SMMS_System_Status()->memory_size_to_num( $recommended_memory ) ) ) . '</span>' ) ?>
                                </li>
                            </ul>
							<?php printf(/* translators: search here */ esc_html__( 'Read more %1$s here%2$s or contact your hosting company in order to increase it.', 'smm-api' ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">', '</a>' )?>
						<?php endif; ?>
                    </td>
                </tr>
			<?php endforeach; ?>
        </table>

        <a href="<?php echo esc_url(add_query_arg( array( 'smms-phpinfo' => 'true' ) ) )?> "><?php esc_html_e( 'Show full PHPInfo', 'smm-api' ) ?></a>

	<?php else : ?>

        <a href="<?php echo esc_url(add_query_arg( array( 'smms-phpinfo' => 'false' ) ) )?> "><?php esc_html_e( 'Back to System panel', 'smm-api' ) ?></a>

		<?php

		ob_start();
		phpinfo( 61 );
		$pinfo = ob_get_contents();
		ob_end_clean();

		$pinfo = preg_replace( '%^.*<div class="center">(.*)</div>.*$%ms', '$1', $pinfo );
		$pinfo = preg_replace( '%(^.*)<a name=\".*\">(.*)</a>(.*$)%m', '$1$2$3', $pinfo );
		$pinfo = str_replace( '<table>', '<table class="widefat striped smms-phpinfo">', $pinfo );
		$pinfo = str_replace( '<td class="e">', '<th class="e">', $pinfo );
		echo wp_kses( $pinfo,wp_kses_allowed_html('post'));

		?>

        <a href="#smms-sysinfo"><?php esc_html_e( 'Back to top', 'smm-api' ) ?></a>

	<?php endif; ?>
</div>