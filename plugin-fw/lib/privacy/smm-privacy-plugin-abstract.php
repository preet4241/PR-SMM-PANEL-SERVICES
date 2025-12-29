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

if ( !class_exists( 'SMMS_Privacy_Plugin_Abstract' ) ) {
    class SMMS_Privacy_Plugin_Abstract {
        private $_name;

        public function __construct( $name ) {
            $this->_name = $name;
            $this->init();
        }

        protected function init() {
            add_filter( 'smms_plugin_fw_privacy_guide_content', array( $this, 'add_message_in_section' ), 10, 2 );
        }

        public function add_message_in_section( $html, $section ) {
            if ( $message = $this->get_privacy_message( $section ) ) {
                $html .= "<p class='privacy-policy-tutorial'><strong>{$this->_name}</strong></p>";
                $html .= $message;
            }
            return $html;
        }

        public function get_privacy_message( $section ) {
            return '';
        }
    }
}