<?php
/**
 * Template Name: Legal page
 * A full-width template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<?php get_header(); ?>
<section id="content" class="full-width">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php echo fusion_render_rich_snippets_for_pages(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php avada_singular_featured_image(); ?>
			<div class="post-content">
                <div class="fusion-row legal-page-content">
                    <div class="col-lg-3 p-0 grid-fix">
                        <div class="legal-menu">
                            <?php $legal_menu = wp_nav_menu(array(
                                'menu' => 'legal-menu',
                            )) ?>
                            <?php print $legal_menu; ?>
                        </div>
                    </div>
                    <div class="col-lg-9 p-0">
				        <?php the_content(); ?>
                    </div>
                </div>
			</div>
			<?php if ( ! post_password_required( $post->ID ) ) : ?>
				<?php if ( Avada()->settings->get( 'comments_pages' ) ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endwhile; ?>
</section>
<?php get_footer(); ?>
