<?php

/**
 * Insusite shortcodes
 *
 * insufeed-shortcodes.php
 */

function insusite_register_shortcodes()
{
    add_shortcode('insu_insufeed_partners', 'insusite_insufeed_partners');
    add_shortcode('news_items_shortcode', 'print_latest_news_item');
    add_shortcode('news_all_items_shortcode', 'print_all_news_item');
    add_shortcode('news_archive_items_shortcode', 'print_archive_news_item');
    add_shortcode('get_opnenings_shortcode', 'get_opnenings');
    add_shortcode('insu_insufeed_cijfers', 'insusite_cijfers_output');
    add_shortcode('tip_shortcode', 'print_tip_item');
    add_shortcode('print_kantoornieuws_archive', 'print_kantoornieuws_overview');
    add_shortcode('print_kantoornieuws_latest', 'print_kantoornieuws_latest');
}

add_action('init', 'insusite_register_shortcodes');

/**
 * Genereert alle partners, verander hier de layout indien nodig
 *
 */

function insusite_insufeed_partners()
{

    ob_start();

    $logos = array();
    $output_string = "";

    //alle bedrijven ophalen
    $companies = InsuDocumentcenter::getCompanies();

    //de geselecteerde bedrijven in een nieuwe array ($logos) steken
    foreach ($companies as $cid => $company) {
        if (in_array($cid, get_option('insufeed_documents_companies')) && $cid != 17) $logos[] = $company;
    }

    $chunked_logos = array_chunk($logos, 6);

    echo '<div class="insupartners">';

    foreach ($chunked_logos as $logo_group) {

        foreach ($logo_group as $logo) {
            echo '<div class="col-lg-3 col-md-3 col-sm-6 col-xs-6 partner" data-match-height="insu-partner"><div class="logo widget"><a><img src="' . $logo['logo'] . '" alt="' . $logo['title'] . '" title="' . $logo['title'] . '" width="50%" /></a></div></div>';
        }

    }
    echo '</div>';

    $output_string = ob_get_contents();

    ob_end_clean();

    return $output_string;

}

/**
 * Print laatste nieuwsbericht
 *
 */
function print_latest_news_item()
{
    global $post;
    $args = array(
        'posts_per_page' => 3,
        'offset' => 0,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'post_type' => 'insunews',
        'post_status' => 'publish',
    );
    $the_query = new WP_Query($args);
    ob_start();
    if ($the_query->have_posts()) :
        echo '<div class="row news-row">';
        while ($the_query->have_posts()) :
            $the_query->the_post(); ?>

            <div class="latest-news-item col-lg-4 col-md-4 col-sm-12">
                <div class="card">
                    <a href="<?php print get_the_permalink($post->ID) ?>">
                        <div class="card-img-top">
                            <?php echo get_the_post_thumbnail($post->ID, 'full') ?>
                        </div>
                        <div class="card-block">
                            <div class="title">
                                <?php print the_title() ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a class="fusion-button"
                               href="<?php print get_the_permalink($post->ID) ?>"><?php print __('Lees meer'); ?></a>
                        </div>
                    </a>
                </div>
            </div>
        <?php endwhile;
        echo '</div>';
        wp_reset_postdata();
        return ob_get_clean();
    endif;
}

/**
 * Print 9 nieuwsberichten
 *
 */
function print_all_news_item()
{
    global $post;
    global $wp_query;
    $ppp = 9;


    $args = array(
        'posts_per_page' => $ppp,
        'offset' => 0,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'post_type' => 'insunews',
        'post_status' => 'publish',
    );

    $the_query = new WP_Query($args);
    ob_start();
    if ($the_query->have_posts()) :
        echo '<div class="row news-row news-row-overview">';
        while ($the_query->have_posts()) :
            $the_query->the_post(); ?>

            <div class="latest-news-item col-lg-4 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-img-top">
                        <?php echo get_the_post_thumbnail($post->ID, 'full') ?>
                        <a class="fusion-button news-read-more"
                           href="<?php print get_the_permalink($post->ID) ?>"><?php print __('Lees meer'); ?></a>
                    </div>
                    <div class="card-block">
                        <div class="title">
                            <?php print the_title() ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile;
        echo ' <div class="col-md-12 archive-button"><a class="fusion-button light-green" href="/nuttige-info/nieuws-archief/">Alle nieuwsberichten</a></div>';
        echo '</div>';
        wp_reset_postdata();
        return ob_get_clean();
    endif;
}

/**
 * Print alle nieuwsberichten
 *
 */
function print_archive_news_item()
{
    global $post;
    global $wp_query;
    $ppp = -1;


    $args = array(
        'posts_per_page' => $ppp,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'post_type' => 'insunews',
        'post_status' => 'publish',
    );

    $the_query = new WP_Query($args);
    ob_start();
    if ($the_query->have_posts()) :
        echo '<div class="row news-row news-row-overview">';
        while ($the_query->have_posts()) :
            $the_query->the_post(); ?>

            <div class="latest-news-item col-lg-4 col-md-4 col-sm-12">
                <div class="card">
                    <a href="<?php print get_the_permalink($post->ID) ?>">
                        <div class="card-img-top">
                            <?php echo get_the_post_thumbnail($post->ID, 'full') ?>
                            <a class="fusion-button news-read-more"
                               href="<?php print get_the_permalink($post->ID) ?>"><?php print __('Lees meer'); ?></a>
                        </div>
                        <div class="card-block">
                            <div class="title">
                                <?php print the_title() ?>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php endwhile;
        echo '</div>';
        wp_reset_postdata();
        return ob_get_clean();
    endif;
}

/**
 * Print openingsuren
 *
 */
function get_opnenings()
{
    ob_start();
    ?>
    <div class="openings">
        <h4><?php print __('Openingsuren') ?></h4>
        <div class="working-day">
            <div class="day"><?php print __('Maandag') ?></div>
            <div class="day_vm"><?php print get_option('ma_vm'); ?></div>
            <div class="day_nm"><?php print get_option('ma_nm'); ?></div>
        </div>

        <div class="working-day">
            <div class="day"><?php print __('Dinsdag') ?></div>
            <div class="day_vm"><?php print get_option('di_vm'); ?></div>
            <div class="day_nm"><?php print get_option('di_nm'); ?></div>
        </div>

        <div class="working-day">
            <div class="day"><?php print __('Woensdag') ?></div>
            <div class="day_vm"><?php print get_option('wo_vm'); ?></div>
            <div class="day_nm"><?php print get_option('wo_nm'); ?></div>
        </div>

        <div class="working-day">
            <div class="day"><?php print __('Donderdag') ?></div>
            <div class="day_vm"><?php print get_option('do_vm'); ?></div>
            <div class="day_nm"><?php print get_option('do_nm'); ?></div>
        </div>

        <div class="working-day">
            <div class="day"><?php print __('Vrijdag') ?></div>
            <div class="day_vm"><?php print get_option('vr_vm'); ?></div>
            <div class="day_nm"><?php print get_option('vr_nm'); ?></div>
        </div>
    </div>
    <?php return ob_get_clean();
}

/**
 * Genereert de template voor de Insusite Cijfers
 *
 */
function insusite_cijfers_output()
{
    ob_start();

    $cijfersData = InsuCijfers::getCijfers();
    foreach ($cijfersData as $item) {
        echo '<div class="cijfers-container insufeed-data-container fusion-row">';
        echo '<div class="cijfers-category insufeed-category clearfix">';
        echo '<div class="insufeed-category-title col-lg-8 col-md-8 col-sm-8 col-xs-12"><h2>' . __($item->title, 'insusite') . '</h2></div>';
        echo '<div class="insufeed-category-link col-lg-4 col-md-4 col-sm-4 col-xs-12"><a nohref class="main-link btn-main fusion-button">' . __('Bekijk cijfers', 'insusite') . '</a></div>';
        echo '</div>';
        echo '<div class="cijfers-content-container insufeed-content-container col-md-12" style="display: none;">';
        echo '<div class="cijfers-content insufeed-content">';
        echo $item->body;
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    $output_string = ob_get_contents();

    ob_end_clean();
    return $output_string;
}

/**
 * Print Tip van de maand
 *
 */
function print_tip_item()
{
    global $post;
    $args = array(
        'posts_per_page' => 1,
        'offset' => 0,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'post_type' => 'insutips',
        'post_status' => 'publish',
    );
    $the_query = new WP_Query($args);
    ob_start();
    if ($the_query->have_posts()) :
        while ($the_query->have_posts()) :
            $the_query->the_post(); ?>

            <div class="latest-tip-item">
                <div class="tip-item-inner">
                    <a href="<?php print get_the_permalink($post->ID) ?>">
                        <div class="title">
                            <h2>
                                <?php print the_title() ?>
                            </h2>
                        </div>
                    </a>
                </div>
            </div>
        <?php endwhile;
        wp_reset_postdata();
        return ob_get_clean();
    endif;
}

/**
 * Print alle kantoor nieuwsberichten
 *
 */
function print_kantoornieuws_overview()
{
    global $post;
    $args = array(
        'posts_per_page' => -1,
        'offset' => 1,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'category_name' => 'kantoornieuws',
        'post_status' => 'publish',
    );
    $the_query = new WP_Query($args);
    ob_start();
    if ($the_query->have_posts()) :
        echo '<div class="row news">';
        while ($the_query->have_posts()) :
            $the_query->the_post(); ?>

            <div class="latest-news-item col-lg-4 col-md-4 col-sm-6">
                <div class="news-item-inner">
                    <a href="<?php print get_the_permalink($post->ID) ?>">
                        <div class="date-news">
                            <?php echo get_the_date("d <\p>M<\/\p>", $post->ID) ?>
                        </div>
                        <div class="image">
                            <?php echo get_the_post_thumbnail($post->ID, 'full') ?>
                        </div>
                        <div class="title">
                            <?php print the_title() ?>
                        </div>
                        <div class="content">
                            <p>
                                <?php
                                $excerpt = get_the_excerpt($post->ID);

                                $excerpt = substr($excerpt, 0, 150);
                                $excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));

                                print $excerpt ?>
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        <?php endwhile;
        echo '</div>';
        wp_reset_postdata();
        return ob_get_clean();
    endif;
}

/**
 * Print laatste kantoor nieuwsbericht
 *
 */
function print_kantoornieuws_latest()
{
    global $post;
    $args = array(
        'posts_per_page' => 1,
        'offset' => 0,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'category_name' => 'kantoornieuws',
        'post_status' => 'publish',
    );
    $the_query = new WP_Query($args);
    ob_start();
    if ($the_query->have_posts()) :
        echo '<div class="row news">';
        while ($the_query->have_posts()) :
            $the_query->the_post(); ?>

            <div class="latest-news-item">
                <div class="news-item-inner">
                    <a href="<?php print get_the_permalink($post->ID) ?>">
                        <div class="date-news">
                            <?php echo get_the_date("d <\p>M<\/\p>", $post->ID) ?>
                        </div>
                        <div class="image">
                            <?php echo get_the_post_thumbnail($post->ID, 'full') ?>
                        </div>
                        <div class="title">
                            <?php print the_title() ?>
                        </div>
                        <div class="content">
                            <p>
                                <?php
                                $excerpt = get_the_excerpt($post->ID);

                                $excerpt = substr($excerpt, 0, 150);
                                $excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));

                                print $excerpt ?>
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        <?php endwhile;
        echo '</div>';
        wp_reset_postdata();
        return ob_get_clean();
    endif;
}