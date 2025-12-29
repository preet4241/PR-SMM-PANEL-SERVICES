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

$min = isset( $option[ 'min' ] ) ? $option[ 'min' ] : 0;
$max = isset( $option[ 'max' ] ) ? $option[ 'max' ] : 100;
?>
<div class="smms-plugin-fw-slider-container">
    <div class="ui-slider">
        <span class="minCaption"><?php echo esc_html( $min) ?></span>
        <div id="<?php echo esc_attr( $id) ?>-div" data-step="<?php echo esc_attr( isset( $step ) ? $step : 1 )?>" data-min="<?php echo esc_attr( $min) ?>" data-max="<?php echo esc_attr( $max) ?>" data-val="<?php echo esc_attr( $value) ?>" class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all">
            <input id="<?php echo esc_attr( $id) ?>" type="hidden" name="<?php echo esc_attr( $name) ?>" value="<?php echo esc_attr( $value ) ?>"/>
        </div>
        <span class="maxCaption"><?php echo esc_html( $max) ?></span>
    </div>
</div>