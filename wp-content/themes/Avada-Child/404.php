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
?>
<?php get_header(); ?>
<section id="content" class="full-width">
	<div id="post-404page">
		<div class="post-content">
			<?php
			// Render the page titles.
			$subtitle = esc_html__('Deze pagina lijkt niet te bestaan!', 'Avada');
			Avada()->template->title_template($subtitle);
			?>
			<div class="fusion-clearfix"></div>
			<div class="error-page">
				<div class="fusion-error-page-404">
					<div class="error-message">404</div>
				</div>
				<div class="error-button fusion-button">
					<a href="<?php print get_site_url(); ?>" class="fusion-button classic">Terug naar de homepagina</a>
				</div>
			</div>
		</div>
	</div>
</section>
<?php get_footer(); ?>
