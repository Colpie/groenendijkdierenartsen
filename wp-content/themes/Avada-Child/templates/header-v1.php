<?php
/**
 * Header-v1 template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}
?>
<div class="fusion-header">
    <div class="fusion-row">
        <div class="menu-column col-lg-4">
            <?php
                $menu_left = wp_nav_menu(array('menu' => 'Menu Left'));
            ?>
        </div>
        <div class="logo-column col-lg-3 col-md-8 col-7">
            <?php avada_logo(); ?>
        </div>
        <div class="menu-column last-menu-column col-lg-4">
            <?php
            $menu_right = wp_nav_menu(array('menu' => 'Menu Right'));
            ?>
        </div>

        <div class="main-mobile-menu col-md-4 col-5">
            <?php avada_main_menu(); ?>
        </div>
    </div>
</div>
