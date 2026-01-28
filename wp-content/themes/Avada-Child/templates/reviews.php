<?php
/**
 * CPT: Reviews + Shortcode: Swiper (thumb + body) + per-review HEADER image (media upload)
 * Doel: elke review krijgt een "Header afbeelding" (via WP media library) en bij slide change
 *       wordt de afbeelding in de aparte kolom erboven automatisch mee aangepast.
 *
 * Gebruik:
 * 1) In WP admin -> Reviews -> bewerk een review:
 *    - Uitgelichte afbeelding = thumb (in slide)
 *    - Metabox "Review header afbeelding" = afbeelding die in de bovenste kolom moet wisselen
 *
 * 2) In Avada:
 *    - Geef jouw bovenste Image Element (die kolom erboven) een EXTRA CSS class:
 *      reviews-top-image
 *      (Avada Image Element -> "CSS Class" veld)
 *
 * 3) Plaats shortcode: [reviews_swiper]
 *
 * Opmerking:
 * - We updaten bij wissel de <img src> én proberen srcset leeg te maken zodat WP srcset niet “tegenwerkt”.
 */

if (!defined('ABSPATH')) exit;

/**
 * 1) Register Custom Post Type: reviews
 */
add_action('init', function () {

    $labels = [
        'name' => __('Reviews', 'Avada'),
        'singular_name' => __('Review', 'Avada'),
        'menu_name' => __('Reviews', 'Avada'),
        'name_admin_bar' => __('Review', 'Avada'),
        'add_new' => __('Nieuwe Review', 'Avada'),
        'add_new_item' => __('Nieuwe Review toevoegen', 'Avada'),
        'new_item' => __('Nieuwe Review', 'Avada'),
        'edit_item' => __('Review bewerken', 'Avada'),
        'view_item' => __('Review bekijken', 'Avada'),
        'all_items' => __('Alle Reviews', 'Avada'),
        'search_items' => __('Reviews zoeken', 'Avada'),
        'not_found' => __('Geen Reviews gevonden', 'Avada'),
        'not_found_in_trash' => __('Geen Reviews in prullenmand', 'Avada'),
    ];

    register_post_type('reviews', [
        'labels' => $labels,
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'reviews'],
        'menu_icon' => 'dashicons-format-quote',
        'supports' => ['title', 'editor', 'thumbnail'],
    ]);
});

/**
 * 2) Per-review HEADER image metabox (WP media library)
 * Dit is de afbeelding die in de bovenste kolom moet wisselen.
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'review_header_image',
        __('Review header afbeelding (bovenste kolom)', 'Avada'),
        'review_header_image_metabox_render',
        'reviews',
        'side',
        'default'
    );
});

function review_header_image_metabox_render($post) {
    wp_nonce_field('review_header_image_save', 'review_header_image_nonce');

    $image_id = (int) get_post_meta($post->ID, '_review_header_image_id', true);
    $img_url  = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>
    <div>
        <input type="hidden" id="review_header_image_id" name="review_header_image_id" value="<?php echo esc_attr($image_id); ?>" />

        <div id="review_header_preview" style="margin-bottom:10px;">
            <?php if ($img_url): ?>
                <img src="<?php echo esc_url($img_url); ?>" style="max-width:100%;height:auto;border:1px solid #ddd;padding:4px;background:#fff;" />
            <?php endif; ?>
        </div>

        <button type="button" class="button" id="review_header_upload">
            <?php esc_html_e('Kies / upload header', 'Avada'); ?>
        </button>

        <button type="button" class="button" id="review_header_remove" <?php echo $image_id ? '' : 'style="display:none;"'; ?> style="margin-top:8px;">
            <?php esc_html_e('Verwijderen', 'Avada'); ?>
        </button>

        <p class="description" style="margin-top:10px;">
            <?php esc_html_e('Deze afbeelding verschijnt in de aparte kolom erboven en wisselt per slide.', 'Avada'); ?>
        </p>
    </div>
    <?php
}

add_action('save_post_reviews', function ($post_id) {
    if (!isset($_POST['review_header_image_nonce']) || !wp_verify_nonce($_POST['review_header_image_nonce'], 'review_header_image_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $image_id = isset($_POST['review_header_image_id']) ? absint($_POST['review_header_image_id']) : 0;

    if ($image_id) {
        update_post_meta($post_id, '_review_header_image_id', $image_id);
    } else {
        delete_post_meta($post_id, '_review_header_image_id');
    }
});

/**
 * Admin JS media uploader voor header image
 */
add_action('admin_enqueue_scripts', function ($hook) {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->post_type !== 'reviews') return;
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) return;

    wp_enqueue_media();

    wp_add_inline_script('jquery', "
        jQuery(function($){
            let frame;

            $('#review_header_upload').on('click', function(e){
                e.preventDefault();

                if(frame){ frame.open(); return; }

                frame = wp.media({
                    title: 'Kies header afbeelding',
                    button: { text: 'Gebruik deze afbeelding' },
                    multiple: false
                });

                frame.on('select', function(){
                    const attachment = frame.state().get('selection').first().toJSON();
                    $('#review_header_image_id').val(attachment.id);

                    const url = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
                    $('#review_header_preview').html('<img src=\"'+url+'\" style=\"max-width:100%;height:auto;border:1px solid #ddd;padding:4px;background:#fff;\" />');

                    $('#review_header_remove').show();
                });

                frame.open();
            });

            $('#review_header_remove').on('click', function(e){
                e.preventDefault();
                $('#review_header_image_id').val('0');
                $('#review_header_preview').html('');
                $(this).hide();
            });
        });
    ");
});

/**
 * 3) Shortcode: [reviews_swiper]
 * Output: thumb + body (editor) + title
 * Extra: per slide data-header met URL voor bovenste kolom.
 */
add_shortcode('reviews_swiper', function ($atts) {

    $atts = shortcode_atts([
        'limit' => 10,
        'order' => 'DESC',
        'orderby' => 'date',
        'class' => '',
    ], $atts, 'reviews_swiper');

    $q = new WP_Query([
        'post_type' => 'reviews',
        'post_status' => 'publish',
        'posts_per_page' => (int) $atts['limit'],
        'orderby' => sanitize_text_field($atts['orderby']),
        'order' => sanitize_text_field($atts['order']),
        'no_found_rows' => true,
    ]);

    if (!$q->have_posts()) return '';

    $extra_class = trim(sanitize_html_class($atts['class']));
    $wrapper_class = 'reviews-swiper swiper' . ($extra_class ? ' ' . $extra_class : '');

    ob_start();
    ?>
    <div class="<?php echo esc_attr($wrapper_class); ?>">
        <div class="swiper-wrapper">
            <?php while ($q->have_posts()) :
                $q->the_post();

                $header_id  = (int) get_post_meta(get_the_ID(), '_review_header_image_id', true);
                $header_url = $header_id ? wp_get_attachment_image_url($header_id, 'full') : '';
                // fallback: als geen header ingesteld is, gebruik featured image van de pagina (of thumbnail)
                if (!$header_url) {
                    $thumb_id = get_post_thumbnail_id(get_the_ID());
                    if ($thumb_id) {
                        $header_url = wp_get_attachment_image_url($thumb_id, 'full');
                    }
                }
                ?>
                <div class="swiper-slide review-slide" data-header="<?php echo esc_attr($header_url); ?>">
                    <div class="reviews-thumb">
                        <?php the_post_thumbnail('full', ['class' => 'img-fluid']); ?>
                    </div>

                    <div class="reviews-body">
                        <?php echo apply_filters('the_content', get_the_content()); ?>

                        <div class="reviews-author">
                            <p><strong><?php echo esc_html(get_the_title()); ?></strong></p>
                        </div>
                    </div>
                </div>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>

        <div class="swiper-navigation custom-navigation">
            <span class="swiper-button-prev">
                <img src="/wp-content/themes/Avada-Child/assets/images/icon/pijltje_wit.gif" alt="arrow left">
            </span>
            <span class="swiper-button-next">
                <img src="/wp-content/themes/Avada-Child/assets/images/icon/pijltje_wit.gif" alt="arrow right">
            </span>
        </div>
    </div>
    <?php
    return ob_get_clean();
});


/**
 * 4) Frontend JS: top image laten wisselen op basis van actieve slide
 *
 * BELANGRIJK:
 * - Geef de afbeelding in de bovenste kolom (Avada Image Element) een extra class: reviews-top-image
 *   Dan vinden we die als: .reviews-top-image img
 *
 * Als je Avada geen class kan geven aan image element zelf, zet class op de container, maar dan moet je selector aanpassen.
 */
add_action('wp_enqueue_scripts', function () {

    // We injecteren alleen JS als jQuery bestaat (Avada heeft dat meestal)
    if (!wp_script_is('jquery', 'registered') && !wp_script_is('jquery', 'enqueued')) {
        return;
    }

    $js = <<<JS
jQuery(function($){

  function setTopReviewImage(url){
    if(!url) return;

    // JOUW bovenste kolom image: geef die Avada image element de class "reviews-top-image"
    var \$img = $('.reviews-top-image img').first();
    if(!\$img.length) return;

    var current = \$img.attr('src') || '';
    if(current === url) return;

    // swap src
    \$img.attr('src', url);

    // srcset kan WP/Avada soms terug overrulen => leegmaken helpt
    \$img.removeAttr('srcset');
    \$img.removeAttr('sizes');

    // optioneel: kleine fade
    if(window.gsap){
      gsap.killTweensOf(\$img[0]);
      gsap.fromTo(\$img[0], {autoAlpha: 0}, {autoAlpha: 1, duration: 0.5, overwrite: true});
    }
  }

  function updateTopFromSwiper(sw){
    if(!sw || !sw.slides || typeof sw.activeIndex === 'undefined') return;
    var slide = sw.slides[sw.activeIndex];
    if(!slide) return;

    var url = slide.getAttribute('data-header');
    if(url) setTopReviewImage(url);
  }

  // Als Swiper al init door jouw script, luisteren we mee via DOM events is lastig.
  // Daarom: we initialiseren hier NIET opnieuw, we “haken” in op bestaande instances door per element te checken.
  // Best practice: zet je Swiper init in 1 plek. Maar jij init al via je eigen JS.
  // -> Oplossing: we observeren .reviews-swiper en proberen swipers te vinden via element.swiper.
  function attachWatcher(){
    $('.reviews-swiper').each(function(){
      var el = this;

      // Swiper instance zit vaak op el.swiper (Swiper v6+)
      if(!el.swiper) return;

      // meteen zetten
      updateTopFromSwiper(el.swiper);

      // events
      el.swiper.on('slideChangeTransitionStart', function(){
        updateTopFromSwiper(this);
      });

      el.swiper.on('init', function(){
        updateTopFromSwiper(this);
      });
    });
  }

  // probeer direct + na load (soms wordt swiper later geïnitialiseerd)
  attachWatcher();
  $(window).on('load', function(){ attachWatcher(); });

});
JS;

    wp_add_inline_script('jquery', $js, 'after');
});

/**
 * Shortcode: [reviews_header_image]
 * Toont een IMG-tag die door JS geüpdatet wordt.
 * Fallback: pakt de eerste review met een header image.
 */
add_shortcode('reviews_header_image', function($atts){

    $atts = shortcode_atts([
        'class' => 'reviews-top-image',
        'size'  => 'full',
        'alt'   => '',
    ], $atts, 'reviews_header_image');

    // Zoek eerste review met header image als fallback
    $q = new WP_Query([
        'post_type'      => 'reviews',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'meta_query'     => [
            [
                'key'     => '_review_header_image_id',
                'compare' => 'EXISTS',
            ]
        ],
    ]);

    $fallback_url = '';
    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            $header_id = (int) get_post_meta(get_the_ID(), '_review_header_image_id', true);
            if ($header_id) {
                $fallback_url = wp_get_attachment_image_url($header_id, $atts['size']);
                break;
            }
        }
        wp_reset_postdata();
    }

    if (!$fallback_url) {
        // geen header images gevonden => toon niets (of zet hier een default)
        return '';
    }

    $class = trim(sanitize_html_class($atts['class']));
    $alt   = esc_attr($atts['alt']);

    // data-reviews-header = hook voor JS
    return '<div class="fusion-image-element"><div class="fusion-imageframe"><img class="'.esc_attr($class).'" data-reviews-header="1" src="'.esc_url($fallback_url).'" alt="'.$alt.'" decoding="async" loading="lazy" /></div></div>';
});