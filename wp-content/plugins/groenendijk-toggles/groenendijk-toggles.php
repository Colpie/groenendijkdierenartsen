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

    $img1_id = (int) get_post_meta($post->ID, '_gda_ug_image_1', true);
    $img2_id = (int) get_post_meta($post->ID, '_gda_ug_image_2', true);

    $img1_url = $img1_id ? wp_get_attachment_image_url($img1_id, 'medium') : '';
    $img2_url = $img2_id ? wp_get_attachment_image_url($img2_id, 'medium') : '';

    ?>
    <div class="gda-ug-image-field" style="margin-bottom:16px;">
        <strong>Afbeelding 1</strong>
        <div style="margin:10px 0;">
            <img
                    id="gda-ug-preview-1"
                    src="<?php echo esc_url($img1_url); ?>"
                    style="<?php echo $img1_url ? '' : 'display:none;'; ?>max-width:320px;height:auto;border:1px solid #ddd;border-radius:6px;"
            />
        </div>

        <input type="hidden" id="gda-ug-image-1" name="gda_ug_image_1" value="<?php echo esc_attr($img1_id); ?>" />
        <button type="button" class="button" id="gda-ug-pick-1">Kies afbeelding</button>
        <button
                type="button"
                class="button"
                id="gda-ug-remove-1"
                style="<?php echo $img1_url ? '' : 'display:none;'; ?>"
        >Verwijderen</button>
    </div>

    <hr style="margin:18px 0;">

    <div class="gda-ug-image-field">
        <strong>Afbeelding 2</strong>
        <div style="margin:10px 0;">
            <img
                    id="gda-ug-preview-2"
                    src="<?php echo esc_url($img2_url); ?>"
                    style="<?php echo $img2_url ? '' : 'display:none;'; ?>max-width:320px;height:auto;border:1px solid #ddd;border-radius:6px;"
            />
        </div>

        <input type="hidden" id="gda-ug-image-2" name="gda_ug_image_2" value="<?php echo esc_attr($img2_id); ?>" />
        <button type="button" class="button" id="gda-ug-pick-2">Kies afbeelding</button>
        <button
                type="button"
                class="button"
                id="gda-ug-remove-2"
                style="<?php echo $img2_url ? '' : 'display:none;'; ?>"
        >Verwijderen</button>
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

    $img1 = isset($_POST['gda_ug_image_1']) ? (int) $_POST['gda_ug_image_1'] : 0;
    $img2 = isset($_POST['gda_ug_image_2']) ? (int) $_POST['gda_ug_image_2'] : 0;

    if ($img1 > 0) update_post_meta($post_id, '_gda_ug_image_1', $img1);
    else delete_post_meta($post_id, '_gda_ug_image_1');

    if ($img2 > 0) update_post_meta($post_id, '_gda_ug_image_2', $img2);
    else delete_post_meta($post_id, '_gda_ug_image_2');
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

            $img1_id = (int) get_post_meta(get_the_ID(), '_gda_ug_image_1', true);
            $img2_id = (int) get_post_meta(get_the_ID(), '_gda_ug_image_2', true);
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

                        <?php if ($img1_id || $img2_id) : ?>
                            <div class="gda-toggle__images">
                                <?php if ($img1_id) : ?>
                                    <div class="gda-toggle__image gda-toggle__image--1">
                                        <?php echo wp_get_attachment_image($img1_id, 'large'); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($img2_id) : ?>
                                    <div class="gda-toggle__image gda-toggle__image--2">
                                        <?php echo wp_get_attachment_image($img2_id, 'large'); ?>
                                    </div>
                                <?php endif; ?>
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