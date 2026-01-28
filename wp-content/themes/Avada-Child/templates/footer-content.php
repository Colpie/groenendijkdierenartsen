<?php
/**
 * Footer content template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.3.0
 */

$c_page_id = Avada()->fusion_library->get_page_id();
/**
 * Check if the footer widget area should be displayed.
 */
?>

    <div class="custom-footer">
        <div class="fusion-row">
            <div class="footer-column col-lg-5 col-md-12 col-12">
                <h4 class="white-color">Ons bereiken?</h4>
                <div class="footer-content-info">
                    <span class="info-content">
                        <img src="/wp-content/themes/Avada-Child/assets/images/icon/bellen.gif" alt="Bellen">
                        <p>
                            Bel ons op <?php echo do_shortcode('[office-phone-link]'); ?>
                        </p>
                    </span>
                    <span class="info-content">
                        <img src="/wp-content/themes/Avada-Child/assets/images/icon/mailen.gif" alt="Mail">
                        <p>
                            Of mail naar <?php echo do_shortcode('[office-email-link]'); ?>
                        </p>
                    </span>
                    <span class="info-content">
                        <img src="/wp-content/themes/Avada-Child/assets/images/icon/locatie.gif" alt="Locatie">
                        <p>
                            Kom langs <a
                                    href="https://www.google.com/maps/place/Groenendijk/@51.2472379,4.116828,693m/data=!3m1!1e3!4m15!1m8!3m7!1s0x47c389b36addb075:0xe7be5d8f25f3aacd!2sGroenendijkstraat+12,+9170+Sint-Gillis-Waas!3b1!8m2!3d51.2472379!4d4.1194083!16s%2Fg%2F11j_1ph3kv!3m5!1s0x47c3890053f274e1:0x4668b55b89583735!8m2!3d51.2472379!4d4.1194083!16s%2Fg%2F11x669hn3q?entry=ttu&g_ep=EgoyMDI2MDEyMS4wIKXMDSoASAFQAw%3D%3D"
                                    target="_blank">
                                <?php echo do_shortcode('[office-street]'); ?>, <?php echo do_shortcode('[office-city]'); ?>
                            </a>
                        </p>
                    </span>
                    <div class="contact-button">
                        <a href="/contact" class="fusion-button green-button">
                            <span class="fusion-button-text">
                                Neem contact op
                            </span>
                            <span class="button-icon">
                                <img src="/wp-content/themes/Avada-Child/assets/images/icon/pijltje_wit.gif">
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-column col-lg-4 col-md-8 col-12">
                <div class="footer-column-inner">
                    <strong>Open:</strong>
                    <span class="footer-text">
                        <p>Ma-vr: 8u30 tot 17u30<br>Za: 9u tot 12u30</p>
                    </span>
                    <strong>Inplannen afspraken:</strong>
                    <span class="footer-text">
                        <p>Ma-za: 8u tot 10u</p>
                    </span>
                    <strong>Spoedgevallen buiten openingsuren:</strong>
                    <span class="footer-text">
                        <p>24/7 telefonisch bereikbaar</p>
                    </span>
                </div>
            </div>
            <div class="footer-column footer-column-last col-lg-2 col-md-4 col-12">
                <?php avada_logo(); ?>
                <div class="social-links-footer">
                    <a href="<?php echo do_shortcode('[office-facebook]') ?>" target="_blank">
                        <img src="/wp-content/themes/Avada-Child/assets/images/icon/fb.gif" alt="Facebook">
                    </a>
                    <a href="<?php echo do_shortcode('[office-instagram]') ?>" target="_blank">
                        <img src="/wp-content/themes/Avada-Child/assets/images/icon/insta.gif" alt="Instagram">
                    </a>
                </div>
            </div>
        </div>
    </div>
<div class="footer-bottom-row">
    <div class="fusion-row">
        <div class="col-lg-12 col-md-12 col-12">
            <?php
                $footer_menu = wp_nav_menu(array('menu' => "footer-menu"));
                echo $footer_menu;
            ?>
        </div>
    </div>
</div>

<?php
// Displays WPML language switcher inside footer if parallax effect is used.
if ((defined('WPML_PLUGIN_FILE') || defined('ICL_PLUGIN_FILE')) && 'footer_parallax_effect' === Avada()->settings->get('footer_special_effects')) {
    global $wpml_language_switcher;
    $slot = $wpml_language_switcher->get_slot('statics', 'footer');
    if ($slot->is_enabled()) {
        echo $wpml_language_switcher->render($slot); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
