<?php
/**
 * Plugin Name: Groenendijk Uiergezondheid
 * Description: Custom Post Type "Uiergezondheid" met titel, tekst en 2 afbeeldingen. Inclusief shortcode [gda_uiergezondheid].
 * Version: 2.0.0
 * Author: Groenendijk
 */

if (!defined('ABSPATH')) exit;

define('GDA_UG_VERSION', '2.0.0');
define('GDA_UG_URL', plugin_dir_url(__FILE__));
define('GDA_UG_PATH', plugin_dir_path(__FILE__));

/**
 * CPT registreren
 */
function gda_register_uiergezondheid_cpt() {

    $labels = array(
        'name'               => 'Uiergezondheid',
        'singular_name'      => 'Uiergezondheid item',
        'menu_name'          => 'Uiergezondheid',
        'name_admin_bar'     => 'Uiergezondheid item',
        'add_new'            => 'Nieuw item',
        'add_new_item'       => 'Nieuw item toevoegen',
        'new_item'           => 'Nieuw item',
        'edit_item'          => 'Item bewerken',
        'view_item'          => 'Item bekijken',
        'all_items'          => 'Alle items',
        'search_items'       => 'Zoek items',
        'not_found'          => 'Geen items gevonden',
        'not_found_in_trash' => 'Geen items in prullenbak',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_rest'       => true,
        'has_archive'        => false,
        'menu_icon'          => 'dashicons-heart',
        'supports'           => array('title', 'editor', 'page-attributes'), // page-attributes => menu_order
        'rewrite'            => array('slug' => 'uiergezondheid'),
    );

    register_post_type('gda_uiergezondheid', $args);
}
add_action('init', 'gda_register_uiergezondheid_cpt');


/**
 * Metaboxen: 2 afbeeldingen + anchor id
 */
function gda_ug_add_metaboxes() {

    add_meta_box(
        'gda_ug_images_box',
        'Afbeeldingen (2 stuks)',
        'gda_ug_images_metabox_render',
        'gda_uiergezondheid',
        'normal',
        'high'
    );

    add_meta_box(
        'gda_ug_anchor_id_box',
        'Anchor ID',
        'gda_ug_anchor_id_metabox_render',
        'gda_uiergezondheid',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'gda_ug_add_metaboxes');

function gda_ug_images_metabox_render($post) {
    wp_nonce_field('gda_ug_images_save', 'gda_ug_images_nonce');

    // Nieuw (multiple)
    $gallery_ids = get_post_meta($post->ID, '_gda_ug_images', true);

    // Backwards compat (als nog niet gezet, neem oude 1/2 over)
    if (empty($gallery_ids)) {
        $img1_id = (int) get_post_meta($post->ID, '_gda_ug_image_1', true);
        $img2_id = (int) get_post_meta($post->ID, '_gda_ug_image_2', true);
        $fallback = array_filter([$img1_id, $img2_id]);
        $gallery_ids = $fallback ? implode(',', $fallback) : '';
    }

    $ids = array_filter(array_map('intval', explode(',', (string) $gallery_ids)));

    ?>
    <div class="gda-ug-gallery-field">
        <p style="margin-top:0;">
            <strong>Afbeeldingen (meerdere mogelijk)</strong><br>
            Selecteer meerdere afbeeldingen. Volgorde kan je later uitbreiden met drag & drop als je wil.
        </p>

        <input type="hidden" id="gda-ug-gallery-ids" name="gda_ug_images" value="<?php echo esc_attr(implode(',', $ids)); ?>" />

        <div id="gda-ug-gallery-preview" style="display:flex;gap:10px;flex-wrap:wrap;margin:12px 0;">
            <?php foreach ($ids as $id) :
                $url = wp_get_attachment_image_url($id, 'thumbnail');
                if (!$url) continue;
                ?>
                <div class="gda-ug-thumb" data-id="<?php echo esc_attr($id); ?>" style="position:relative;">
                    <img src="<?php echo esc_url($url); ?>" style="width:110px;height:auto;border:1px solid #ddd;border-radius:6px;display:block;" />
                    <button type="button"
                            class="button gda-ug-thumb-remove"
                            style="position:absolute;top:6px;right:6px;line-height:1;padding:2px 6px;"
                            title="Verwijderen">×</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="button" id="gda-ug-gallery-pick">Kies afbeeldingen</button>
        <button type="button" class="button" id="gda-ug-gallery-addone">Voeg 1 foto toe</button>
        <button type="button" class="button" id="gda-ug-gallery-clear" style="<?php echo $ids ? '' : 'display:none;'; ?>">Alles verwijderen</button>
    </div>
    <?php
}
function gda_ug_anchor_id_metabox_render($post) {
    wp_nonce_field('gda_ug_anchor_id_save', 'gda_ug_anchor_id_nonce');

    $val = get_post_meta($post->ID, '_gda_ug_anchor_id', true);

    echo '<p style="margin-top:0;">Unieke ID voor anchors (zonder #). Voorbeeld: <code>mastapro</code></p>';
    echo '<input type="text" name="gda_ug_anchor_id" value="' . esc_attr($val) . '" style="width:100%;" placeholder="bv. mastapro" />';
    echo '<p style="color:#666; margin-bottom:0;">Gebruik in link: <code>#' . esc_html($val ? $val : 'mastapro') . '</code></p>';
}

/**
 * Opslaan metaboxen
 */
function gda_ug_images_save($post_id) {
    if (!isset($_POST['gda_ug_images_nonce']) || !wp_verify_nonce($_POST['gda_ug_images_nonce'], 'gda_ug_images_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Nieuw: multiple ids als comma string
    $raw = isset($_POST['gda_ug_images']) ? (string) $_POST['gda_ug_images'] : '';
    $ids = array_filter(array_map('intval', explode(',', $raw)));
    $ids = array_values(array_unique($ids));

    if (!empty($ids)) {
        update_post_meta($post_id, '_gda_ug_images', implode(',', $ids));

        // Backwards compat: eerste 2 ook blijven vullen
        update_post_meta($post_id, '_gda_ug_image_1', isset($ids[0]) ? (int) $ids[0] : 0);
        update_post_meta($post_id, '_gda_ug_image_2', isset($ids[1]) ? (int) $ids[1] : 0);
    } else {
        delete_post_meta($post_id, '_gda_ug_images');

        // Ook oude velden leegmaken
        delete_post_meta($post_id, '_gda_ug_image_1');
        delete_post_meta($post_id, '_gda_ug_image_2');
    }
}
add_action('save_post_gda_uiergezondheid', 'gda_ug_images_save');

function gda_ug_anchor_id_save($post_id) {
    if (!isset($_POST['gda_ug_anchor_id_nonce']) || !wp_verify_nonce($_POST['gda_ug_anchor_id_nonce'], 'gda_ug_anchor_id_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $val = isset($_POST['gda_ug_anchor_id']) ? sanitize_title($_POST['gda_ug_anchor_id']) : '';

    if ($val === '') {
        delete_post_meta($post_id, '_gda_ug_anchor_id');
        return;
    }

    update_post_meta($post_id, '_gda_ug_anchor_id', $val);
}
add_action('save_post_gda_uiergezondheid', 'gda_ug_anchor_id_save');


/**
 * Admin assets (media uploader voor 2 afbeeldingen)
 */
function gda_ug_admin_assets($hook) {
    global $post_type;

    if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'gda_uiergezondheid') {
        wp_enqueue_media();

        wp_enqueue_script(
            'gda-ug-admin',
            GDA_UG_URL . 'assets/admin-toggles.js',
            array('jquery'),
            GDA_UG_VERSION,
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'gda_ug_admin_assets');


/**
 * Front assets (accordion gedrag)
 */
function gda_ug_front_assets() {
    wp_enqueue_script(
        'gda-ug-toggles',
        GDA_UG_URL . 'assets/toggles.js',
        array('jquery'),
        GDA_UG_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'gda_ug_front_assets');


/**
 * Shortcode: [gda_uiergezondheid]
 * Opties:
 * - posts_per_page (default -1)
 */
function gda_uiergezondheid_shortcode($atts) {
    $atts = shortcode_atts(array(
        'posts_per_page' => -1,
    ), $atts, 'gda_uiergezondheid');

    $q = new WP_Query(array(
        'post_type'      => 'gda_uiergezondheid',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby'        => array('menu_order' => 'DESC', 'date' => 'ASC'),
    ));

    if (!$q->have_posts()) return '';

    ob_start();
    ?>
    <div class="gda-toggles gda-uiergezondheid">
        <?php while ($q->have_posts()) : $q->the_post(); ?>
            <?php
            $anchor_id = get_post_meta(get_the_ID(), '_gda_ug_anchor_id', true);
            $anchor_attr = $anchor_id ? ' id="' . esc_attr($anchor_id) . '"' : '';

            $gallery_ids = get_post_meta(get_the_ID(), '_gda_ug_images', true);

// Backwards compat indien leeg
            if (empty($gallery_ids)) {
                $img1_id = (int) get_post_meta(get_the_ID(), '_gda_ug_image_1', true);
                $img2_id = (int) get_post_meta(get_the_ID(), '_gda_ug_image_2', true);
                $fallback = array_filter([$img1_id, $img2_id]);
                $gallery_ids = $fallback ? implode(',', $fallback) : '';
            }

            $image_ids = array_filter(array_map('intval', explode(',', (string) $gallery_ids)));
            ?>
            <div class="gda-toggle"<?php echo $anchor_attr; ?>>
                <a class="gda-toggle__header" aria-expanded="false">
                    <span class="gda-toggle__title"><?php echo esc_html(get_the_title()); ?></span>
                    <span class="gda-toggle__chev" aria-hidden="true">
                        <img src="/wp-content/themes/Avada-Child/assets/images/icon/toggle.gif" alt="">
                    </span>
                </a>

                <div class="gda-toggle__panel" hidden>
                    <div class="gda-toggle__inner">

                        <div class="gda-toggle__body">
                            <?php the_content(); ?>
                        </div>

                        <?php if (!empty($image_ids)) : ?>
                            <?php
                            $count = count($image_ids);
                            $grid_class = 'gda-toggle__images--grid';
                            if ($count === 1) $grid_class .= ' is-1';
                            if ($count === 2) $grid_class .= ' is-2';
                            if ($count >= 3) $grid_class .= ' is-3plus';
                            ?>
                            <div class="gda-toggle__images <?php echo esc_attr($grid_class); ?>">
                                <?php foreach ($image_ids as $img_id) : ?>
                                    <div class="gda-toggle__image">
                                        <?php echo wp_get_attachment_image($img_id, 'large'); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('gda_uiergezondheid', 'gda_uiergezondheid_shortcode');

/**
 * Backwards compatibility (als je nog [gda_toggles] ergens hebt staan)
 */
add_shortcode('gda_toggles', 'gda_uiergezondheid_shortcode');