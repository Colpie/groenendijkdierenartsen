<?php
/**
 * Template used for single posts and other post-types
 * that don't have a specific template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}
global $post;
?>
<?php get_header(); ?>

    <section id="content" style="<?php echo esc_attr(apply_filters('awb_content_tag_style', '')); ?>">
        <?php if (fusion_get_option('blog_pn_nav')) : ?>
            <div class="single-navigation clearfix">
                <?php previous_post_link('%link', esc_attr__('Previous', 'Avada')); ?>
                <?php next_post_link('%link', esc_attr__('Next', 'Avada')); ?>
            </div>
        <?php endif; ?>

        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('post'); ?>>
                <div class="banner-row default-banner position-relative fusion-fullwidth">
                    <div class="fusion-layout-column banner-image-column fusion-flex-column">
                        <div class="fusion-column-wrapper p-0">
                            <div class="fusion-image-element">
                                <div class="fusion-imageframe">
                                    <?php echo get_the_post_thumbnail($post->ID, 'full'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="single-title fusion-layout-column">
                        <h1><?php echo get_the_title(); ?></h1>
                    </div>
                </div>

                <div class="post-content">
                    <?php the_content(); ?>
                    <div class="fusion-row">
                        <a href="/news" class="fusion-button overview-button">
                            <span class="fusion-button-text">
                                <?php print __('Terug naar overzicht') ?>
                            </span>
                            <span class="button-icon">
                                    <img src="/wp-content/themes/Avada-Child/assets/images/icon/pijltje_wit.gif">
                                </span>
                        </a>
                    </div>
                    <?php fusion_link_pages(); ?>
                </div>
            </article>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    </section>
<?php do_action('avada_after_content'); ?>
<?php
get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
