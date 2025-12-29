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

$class      = isset( $class ) ? $class : 'smms-plugin-fw-select-images';
$wrapper_id = $id . '-wrapper';
?>
<div id="<?php echo esc_attr( $wrapper_id) ?>" class="smms-plugin-fw-select-images__wrapper">

    <select id="<?php echo esc_attr( $id) ?>"
            name="<?php echo esc_attr( $name) ?>"
            class="<?php  echo esc_attr( $class) ?>"
            style="display: none"
        <?php echo esc_attr( $custom_attributes) ?>
        <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>>
        <?php foreach ( $options as $key => $item ) :
            $label = !empty( $item[ 'label' ] ) ? $item[ 'label' ] : $key;
            ?>
            <option value="<?php echo esc_attr( $key  )?>" <?php selected( $key, $value ); ?> ><?php echo esc_html( $label) ?></option>
        <?php endforeach; ?>
    </select>

    <ul class="smms-plugin-fw-select-images__list">
        <?php foreach ( $options as $key => $item ) :
            $label = !empty( $item[ 'label' ] ) ? $item[ 'label' ] : $key;
            $image = !empty( $item[ 'image' ] ) ? $item[ 'image' ] : '';
            if ( $image ) :
                $selected_class = 'smms-plugin-fw-select-images__item--selected';
                $current_class = $key === $value ? $selected_class : '';
                ?>
                <li class="smms-plugin-fw-select-images__item <?php echo esc_attr( $current_class) ?>" data-key="<?php echo esc_attr( $key) ?>">
                    <?php if ( $label ) : ?>
                        <div class="smms-plugin-fw-select-images__item__label"><?php echo esc_html( $label) ?></div>
                    <?php endif; ?>
                    <img src="<?php echo esc_url( $image) ?>">
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>