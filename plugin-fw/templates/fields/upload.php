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
?>
<div class="smms-plugin-fw-upload-img-preview" style="margin-top:10px;">
	<?php
	$file = $value;
	if ( preg_match( '/(jpg|jpeg|png|gif|ico)$/', $file ) ) {
		printf( "<img src='%s' style='max-width:600px; max-height:300px;' />", esc_attr($file));
	}
	?>
</div>
<input type="text" id="<?php echo esc_attr( $id) ?>" name="<?php echo esc_attr( $name) ?>" value="<?php echo esc_attr( $value ) ?>" <?php if ( isset( $default ) ) : ?>data-std="<?php echo esc_attr( $default) ?>"<?php endif ?> class="smms-plugin-fw-upload-img-url"/>
<button class="button-secondary smms-plugin-fw-upload-button" id="<?php echo esc_attr($id) ?>-button"><?php esc_html_e( 'Upload', 'smm-api' ) ?></button>
<button type="button"  id="<?php echo esc_attr( $id) ?>-button-reset" class="smms-plugin-fw-upload-button-reset button"
        data-default="<?php echo esc_attr( isset( $default ) ? $default : '' )?>"><?php esc_html_e( 'Reset', 'smm-api' ) ?></button>
