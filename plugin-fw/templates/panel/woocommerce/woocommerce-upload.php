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
 * Upload Plugin Admin View
 *
 * @package    SMMS
 * @author     sam softnwords
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
$hidden_val = get_option($id . "-smms-attachment-id", 0);

?>

<tr valign="top">
    <th scope="row" class="image_upload">
        <label for="<?php echo esc_attr( $id )?>"><?php echo esc_html( $name )?></label>
    </th>
    <td class="forminp forminp-color plugin-option">

        <div id="<?php echo esc_attr( $id )?>-container" class="smm_options rm_option rm_input rm_text rm_upload"
             <?php if (isset($option['deps'])): ?>data-field="<?php echo esc_attr( $id) ?>"
             data-dep="<?php echo esc_attr( $this->get_id_field($option['deps']['ids'])) ?>"
             data-value="<?php echo esc_attr( $option['deps']['values']) ?>" <?php endif ?>>
            <div class="option">
                <input type="text" name="<?php echo esc_attr( $id )?>" id="<?php echo esc_attr( $id) ?>"
                       value="<?php echo esc_attr( $value == '1' ? '' : $value) ?>" class="smms-plugin-fw-upload-img-url"/>
                <input type="hidden" name="<?php echo esc_attr( $id )?>-smms-attachment-id" id="<?php echo esc_attr( $id) ?>-smms-attachment-id" value="<?php esc_attr( $hidden_val) ?>" />
                <input type="button" value="<?php echo esc_attr('Upload') ?>" id="<?php echo esc_attr( $id )?>-button"
                       class="smms-plugin-fw-upload-button button"/>
            </div>
            <div class="clear"></div>
            <span class="description"><?php echo esc_html( $desc) ?></span>

            <div class="smms-plugin-fw-upload-img-preview" style="margin-top:10px;">
                <?php
                $file = $value;
                if (preg_match('/(jpg|jpeg|png|gif|ico)$/', $file)) {
                    echo "<img src=\"" . esc_url(SMM_CORE_PLUGIN_URL) . "/assets/images/sleep.png\" data-src=\"";
					echo esc_html($file);
					echo '" />';
                }
                ?>
            </div>
        </div>


    </td>
</tr>

