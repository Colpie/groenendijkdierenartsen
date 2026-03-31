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
                 'plugins-script' => '/assets/js/plugins.js',
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

add_action('admin_menu', 'ae_404_admin_menu');
function ae_404_admin_menu()
{
    add_submenu_page(
        'options-general.php',
        '404 Pagina',
        '404 Pagina',
        'manage_options',
        'ae-404-settings',
        'ae_404_settings_page'
    );
}

add_action('admin_init', 'ae_404_register_settings');
function ae_404_register_settings()
{
    register_setting('ae_404_group', 'ae_404_bg_id', [
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 0,
    ]);

    register_setting('ae_404_group', 'ae_404_title', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Pagina niet gevonden',
    ]);
}

function ae_404_settings_page()
{
    $image_id = (int) get_option('ae_404_bg_id', 0);
    $title    = (string) get_option('ae_404_title', 'Pagina niet gevonden');
    $img_url  = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
    ?>
    <div class="wrap">
        <h1>404 Pagina instellingen</h1>

        <form method="post" action="options.php">
            <?php settings_fields('ae_404_group'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">Titel</th>
                    <td>
                        <input type="text" name="ae_404_title" class="regular-text" value="<?php echo esc_attr($title); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row">Banner afbeelding</th>
                    <td>
                        <div style="display:flex; gap:12px; align-items:center;">
                            <div>
                                <img id="ae-404-preview"
                                     src="<?php echo esc_url($img_url); ?>"
                                     alt=""
                                     style="width:150px; height:auto; border:1px solid #ddd; background:#f3f3f3; <?php echo $img_url ? '' : 'display:none;'; ?>" />
                            </div>

                            <div>
                                <input type="hidden" id="ae_404_bg_id" name="ae_404_bg_id" value="<?php echo esc_attr($image_id); ?>" />

                                <button type="button" class="button" id="ae-404-upload">
                                    Upload / kies afbeelding
                                </button>

                                <button type="button" class="button" id="ae-404-remove" <?php echo $img_url ? '' : 'style="display:none;"'; ?>>
                                    Verwijder
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <?php submit_button('Opslaan'); ?>
        </form>
    </div>
    <?php
}

add_action('admin_enqueue_scripts', 'ae_404_admin_scripts');
function ae_404_admin_scripts($hook)
{
    if ($hook !== 'settings_page_ae-404-settings') {
        return;
    }

    wp_enqueue_media();

    wp_add_inline_script('jquery', "
        jQuery(function($){
            let frame;

            $('#ae-404-upload').on('click', function(e){
                e.preventDefault();

                if(frame){
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Kies banner afbeelding',
                    button: { text: 'Gebruik deze afbeelding' },
                    multiple: false
                });

                frame.on('select', function(){
                    const attachment = frame.state().get('selection').first().toJSON();
                    $('#ae_404_bg_id').val(attachment.id);

                    const url = (attachment.sizes && attachment.sizes.large) ? attachment.sizes.large.url : attachment.url;
                    $('#ae-404-preview').attr('src', url).show();
                    $('#ae-404-remove').show();
                });

                frame.open();
            });

            $('#ae-404-remove').on('click', function(e){
                e.preventDefault();
                $('#ae_404_bg_id').val('');
                $('#ae-404-preview').hide().attr('src', '');
                $(this).hide();
            });
        });
    ");
}