<?php
/**
 * The template used for 404 pages.
 *
 * @package Avada
 * @subpackage Templates
 */

if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}

$ae_404_image_id  = (int) get_option('ae_404_bg_id', 0);
$ae_404_image_url = $ae_404_image_id ? wp_get_attachment_image_url($ae_404_image_id, 'full') : '';
$ae_404_title     = get_option('ae_404_title', 'Pagina niet gevonden');
$ae_404_text      = get_option('ae_404_text', 'De pagina die je zoekt bestaat niet meer of werd verplaatst.');
?>

<?php get_header(); ?>

    <section id="content" class="full-width gda-404-page">

        <div class="banner-row default-banner position-relative fusion-fullwidth gda-404-banner">
            <div class="fusion-layout-column banner-image-column fusion-flex-column">
                <div class="fusion-column-wrapper p-0">
                    <div class="fusion-image-element">
                        <div class="fusion-imageframe">
                            <?php if ($ae_404_image_url) : ?>
                                <img
                                        src="<?php echo esc_url($ae_404_image_url); ?>"
                                        alt="<?php echo esc_attr($ae_404_title); ?>"
                                        class="img-fluid"
                                />
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="single-title fusion-layout-column gda-404-banner-title">
                <h1><?php echo esc_html($ae_404_title); ?></h1>
            </div>
        </div>

        <div class="fusion-row fusion-404 gda-404-content">
            <div class="gda-404-box">
                <p class="gda-404-text"><?php echo esc_html($ae_404_text); ?></p>

                <div class="gda-404-actions">
                    <?php
                    echo do_shortcode(
                        '[fusion_button link="' . esc_url(home_url('/')) . '" color="custom" size="medium" class="gda-404-button"]Terug naar de homepagina[/fusion_button]'
                    );
                    ?>
                </div>
            </div>
        </div>

    </section>

<?php get_footer(); ?>