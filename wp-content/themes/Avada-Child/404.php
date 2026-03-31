<?php
/**
 * The template used for 404 pages.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}

$ae_404_image_id = (int)get_option('ae_404_bg_id', 0);
$ae_404_image_url = $ae_404_image_id ? wp_get_attachment_image_url($ae_404_image_id, 'full') : '';
$ae_404_title = get_option('ae_404_title', 'Pagina niet gevonden');
?>
<?php get_header(); ?>
    <section id="content" class="full-width">
        <div id="post-404page">
            <div class="post-content">
                <div class="banner-row default-banner position-relative fusion-fullwidth">
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

                    <div class="single-title fusion-layout-column">
                        <h1><?php echo esc_html($ae_404_title); ?></h1>
                    </div>
                </div>

                <div class="fusion-clearfix"></div>

                <div class="error-page">
                    <div class="fusion-row fusion-404">
                        <div class="fusion-error-page-404">
                            <div class="error-message">404</div>
                        </div>
                        <div class="error-button">
                            <?php
                            echo do_shortcode('[fusion_button link="' . esc_url(get_site_url()) . '" color="custom" size="medium"]Terug naar de homepagina[/fusion_button]');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php get_footer(); ?>