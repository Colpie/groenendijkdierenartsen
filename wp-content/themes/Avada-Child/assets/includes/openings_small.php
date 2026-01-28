<?php
function get_openings()
{
    ob_start();
    $today = date('D');

    switch ($today) {

        case "Mon":
            $vm = get_option('ma_vm');
            $nm = get_option('ma_nm');
            break;

        case "Tue":
            $vm = get_option('di_vm');
            $nm = get_option('di_nm');
            break;

        case "Wed":
            $vm = get_option('wo_vm');
            $nm = get_option('wo_nm');
            break;

        case "Thu":
            $vm = get_option('do_vm');
            $nm = get_option('do_nm');
            break;

        case "Fri":
            $vm = get_option('vr_vm');
            $nm = get_option('vr_nm');
            break;

        case "Sat":
            $vm = get_option('za_vm');
            $nm = get_option('za_nm');
            break;
    }

    ?>
        <div class="opening-houres">
            <div class="content">
                <p><strong><?php print __('Openingsuren vandaag:'); ?></strong></p>
            </div>
            <div class="houres">
                <?php if($vm && $nm) { print $vm ?> & <?php print $nm; } else { print __('gesloten'); } ?>
            </div>
        </div>
    <?php

    return ob_get_clean();
}

add_shortcode('print_openings', 'get_openings');