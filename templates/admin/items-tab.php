<div class="wrap">
<?php
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit;   
$this->cpt_obj_items->smm_item_form();
?> 
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        $this->cpt_obj_items->prepare_items();
                        // Search form
                        $this->cpt_obj_items->search_box('search', 'search_id');
                        $this->cpt_obj_items->display(); ?> 
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>