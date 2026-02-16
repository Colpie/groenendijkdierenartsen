<?php

function theme_enqueue_styles()
{
    // Helperfunctie voor auto-versie
    function auto_version_child($relative_path)
    {
        $file = get_stylesheet_directory() . $relative_path;
        $uri = get_stylesheet_directory_uri() . $relative_path;
        $version = file_exists($file) ? filemtime($file) : null;
        return [$uri, $version];
    }

    // Parent theme style (geen auto-version nodig)
    wp_enqueue_style('avada-parent-stylesheet', get_template_directory_uri() . '/style.css');

    // Styles met auto-version
    foreach ([
                 'child-style' => '/css/child.css',
                 'splitting-style' => '/css/splitting-cells.css',
                 'swiper-style' => '/css/swiper-style.css',
                 'bootstrap' => '/css/bootstrap/bootstrap.min.css',
                 'font-awesome' => '/css/fontawesome/css/light.css',
             ] as $handle => $path) {
        [$uri, $version] = auto_version_child($path);
        wp_enqueue_style($handle, $uri, [], $version);
    }

    // Scripts met auto-version
    foreach ([
                 'child-script' => '/assets/js/child.js',
                 'scrollspy' => '/assets/js/scrollspy.js',
                 'splitting-script' => '/assets/js/splitting.js',
                 'wow' => '/assets/js/wow.min.js',
                 'swiper' => '/assets/js/swiper-bundle.min.js',
                 'forms-script' => '/assets/js/forms.js',
             ] as $handle => $path) {
        [$uri, $version] = auto_version_child($path);

        // jQuery dependency alleen voor wow
        $deps = ($handle === 'wow') ? ['jquery'] : [];
        wp_enqueue_script($handle, $uri, $deps, $version, true);
    }

    wp_localize_script('child-script', 'gdaNews', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
}

add_action('wp_enqueue_scripts', 'theme_enqueue_styles', 99);

// Requires
require_once 'assets/includes/insu_shortcodes.php';
require_once 'assets/includes/openings_small.php';
require_once 'templates/popup.php';
require_once 'templates/button.php';
require_once 'templates/reviews.php';

/**
 * Filter body classes
 */
// Add page slug as body class

function add_slug_body_class($classes)
{
    global $post;
    if (isset($post)) {
        $classes[] = $post->post_type . '-' . $post->post_name;
    }

    if (is_single()) {
        foreach ((get_the_category($post->ID)) as $category) {
            // add category slug to the $classes array
            $classes[] = $category->category_nicename;
        }
    }

    return $classes;
}

add_filter('body_class', 'add_slug_body_class');

function option_schade()
{
    ob_start();
    include "templates/schade.php";
    return ob_get_clean();
}

add_shortcode('print_option_schade', 'option_schade');

function redirecting_404_to_home()
{
    if (is_404()) {
        wp_safe_redirect(site_url());
        exit();
    }
}

;
add_action('template_redirect', 'redirecting_404_to_home');

function my_login_logo_one()
{
    ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(/wp-content/themes/Avada-Child/assets/images/login/artisteeq.gif);
            height: 194px;
            width: 250px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            padding-bottom: 30px;
        }
    </style>
    <?php
}

add_action('login_enqueue_scripts', 'my_login_logo_one');

function custom_login_page_background()
{
    echo '<style type="text/css">
        body.login {
            background-color: #FFBF00;
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>';
}

add_action('login_enqueue_scripts', 'custom_login_page_background');

/**
 * Shortcode: [print_latest_news]
 * Toont de 2 nieuwste WP posts.
 */
function print_latest_news_shortcode($atts)
{

    $atts = shortcode_atts([
        'posts' => 2,
        'cat' => '',   // optioneel: category ID(s) of slug(s)
        'excerpt' => 20,   // aantal woorden
    ], $atts, 'print_latest_news');

    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => intval($atts['posts']),
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ];

    // Optioneel filter op categorie (ID(s) of slug(s))
    if (!empty($atts['cat'])) {
        // Als het numeric is -> cat (IDs), anders -> category_name (slugs)
        if (is_numeric(str_replace(',', '', $atts['cat']))) {
            $args['cat'] = $atts['cat']; // bv "3" of "3,8"
        } else {
            $args['category_name'] = $atts['cat']; // bv "news" of "news,updates"
        }
    }

    $q = new WP_Query($args);

    if (!$q->have_posts()) {
        return '<div class="latest-news latest-news--empty">Geen nieuws gevonden.</div>';
    }

    ob_start();
    ?>
    <div class="latest-news fusion-row">
        <?php while ($q->have_posts()) : $q->the_post(); ?>
            <article class="latest-news__item">
                <div class="latest-news__item-inner">
                    <div class="latest-news__item-thumb">
                        <?php the_post_thumbnail('large', ['class' => 'img-fluid']); ?>
                    </div>
                    <div class="latest-news__item-content">
                        <h3 class="latest-news__title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <div class="latest-news__excerpt">
                            <?php
                            $excerpt = wp_trim_words(get_the_excerpt(), intval($atts['excerpt']), '…');
                            echo esc_html($excerpt);
                            ?>
                        </div>

                        <a class="latest-news__readmore fusion-button green-button" href="<?php the_permalink(); ?>">
                            <span class="fusion-button-text">
                                Lees meer
                            </span>
                            <span class="button-icon"><img
                                        src="/wp-content/themes/Avada-Child/assets/images/icon/pijltje_wit.gif"> </span>
                        </a>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('print_latest_news', 'print_latest_news_shortcode');

add_action('login_enqueue_scripts', 'custom_login_page_background');

/**
 * Shortcode: [print_latest_news]
 * Toont de 9 nieuwste WP posts.
 */
/**
 * Shortcode: [print_all_news]
 * Toont de 9 nieuwste WP posts + Load more via AJAX.
 */
function print_all_news_shortcode($atts)
{
    $atts = shortcode_atts([
        'posts'   => 4,
        'cat'     => '',   // optioneel: category ID(s) of slug(s)
        'excerpt' => 20,   // aantal woorden
    ], $atts, 'print_all_news');

    $per_page = intval($atts['posts']);
    $cat_raw  = (string) $atts['cat'];

    $args = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $per_page,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => false, // important: we need max_num_pages
        'paged'               => 1,
    ];

    // Optioneel filter op categorie (ID(s) of slug(s))
    if (!empty($cat_raw)) {
        if (is_numeric(str_replace(',', '', $cat_raw))) {
            $args['cat'] = $cat_raw; // bv "3" of "3,8"
        } else {
            $args['category_name'] = $cat_raw; // bv "news" of "news,updates"
        }
    }

    $q = new WP_Query($args);

    if (!$q->have_posts()) {
        return '<div class="latest-news latest-news--empty">Geen nieuws gevonden.</div>';
    }

    $nonce = wp_create_nonce('gda_load_more_news');

    ob_start();
    ?>
    <div
            class="latest-news-wrapper"
            data-per-page="<?php echo esc_attr($per_page); ?>"
            data-excerpt="<?php echo esc_attr(intval($atts['excerpt'])); ?>"
            data-cat="<?php echo esc_attr($cat_raw); ?>"
            data-nonce="<?php echo esc_attr($nonce); ?>"
            data-page="1"
            data-max-pages="<?php echo esc_attr((int) $q->max_num_pages); ?>"
    >
        <div class="latest-news fusion-row">
            <?php while ($q->have_posts()) : $q->the_post(); ?>
                <article class="latest-news__item">
                    <div class="latest-news__item-inner">
                        <div class="latest-news__item-thumb">
                            <?php the_post_thumbnail('large', ['class' => 'img-fluid']); ?>
                        </div>
                        <div class="latest-news__item-content">
                            <h3 class="latest-news__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>

                            <div class="latest-news__excerpt">
                                <?php
                                $excerpt = wp_trim_words(get_the_excerpt(), intval($atts['excerpt']), '…');
                                echo esc_html($excerpt);
                                ?>
                            </div>

                            <a class="latest-news__readmore fusion-button green-button" href="<?php the_permalink(); ?>">
                                <span class="fusion-button-text">Lees meer</span>
                                <span class="button-icon">
                                    <img src="/wp-content/themes/Avada-Child/assets/images/icon/pijltje_wit.gif">
                                </span>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php if ((int) $q->max_num_pages > 1) : ?>
            <div class="latest-news__actions" style="width:100%; text-align:center; margin-top:20px;">
                <a class="latest-news__loadmore fusion-button">
                    <span class="fusion-button-text">Laad meer</span>
                    <span class="button-icon">
                            <img src="/wp-content/themes/Avada-Child/assets/images/icon/pijltje_wit.gif">
                        </span>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('print_all_news', 'print_all_news_shortcode');

add_action('wp_ajax_gda_load_more_news', 'gda_load_more_news');
add_action('wp_ajax_nopriv_gda_load_more_news', 'gda_load_more_news');

function gda_load_more_news()
{
    check_ajax_referer('gda_load_more_news', 'nonce');

    $page     = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $per_page = isset($_POST['per_page']) ? max(1, intval($_POST['per_page'])) : 9;
    $excerpt  = isset($_POST['excerpt']) ? max(0, intval($_POST['excerpt'])) : 20;
    $cat_raw  = isset($_POST['cat']) ? sanitize_text_field(wp_unslash($_POST['cat'])) : '';

    $args = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $per_page,
        'ignore_sticky_posts' => true,
        'paged'               => $page,
        'no_found_rows'       => true,
    ];

    if (!empty($cat_raw)) {
        if (is_numeric(str_replace(',', '', $cat_raw))) {
            $args['cat'] = $cat_raw;
        } else {
            $args['category_name'] = $cat_raw;
        }
    }

    $q = new WP_Query($args);

    if (!$q->have_posts()) {
        wp_send_json_success([
            'html' => '',
            'has_more' => false,
        ]);
    }

    ob_start();
    while ($q->have_posts()) : $q->the_post(); ?>
        <article class="latest-news__item">
            <div class="latest-news__item-inner">
                <div class="latest-news__item-thumb">
                    <?php the_post_thumbnail('large', ['class' => 'img-fluid']); ?>
                </div>
                <div class="latest-news__item-content">
                    <h3 class="latest-news__title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>

                    <div class="latest-news__excerpt">
                        <?php
                        $ex = wp_trim_words(get_the_excerpt(), $excerpt, '…');
                        echo esc_html($ex);
                        ?>
                    </div>

                    <a class="latest-news__readmore fusion-button green-button" href="<?php the_permalink(); ?>">
                        <span class="fusion-button-text">Lees meer</span>
                        <span class="button-icon">
                            <img src="/wp-content/themes/Avada-Child/assets/images/icon/pijltje_wit.gif">
                        </span>
                    </a>
                </div>
            </div>
        </article>
    <?php endwhile;
    wp_reset_postdata();

    $html = ob_get_clean();

    // We weten "has_more" niet exact zonder found_rows; simpel: als we minder dan per_page terugkrijgen -> geen meer
    $has_more = ($q->post_count === $per_page);

    wp_send_json_success([
        'html' => $html,
        'has_more' => $has_more,
    ]);
}