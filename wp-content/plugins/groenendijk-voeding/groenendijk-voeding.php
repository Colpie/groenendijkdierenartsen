<?php
/**
 * Plugin Name: Groenendijk Voeding
 * Description: Custom Post Type "Voeding" met titel, tekst en 2 afbeeldingen. Shortcode [gda_voeding].
 * Version: 1.0.0
 * Author: Groenendijk
 */

if (!defined('ABSPATH')) exit;

define('GDA_VOEDING_VERSION', '1.0.0');
define('GDA_VOEDING_URL', plugin_dir_url(__FILE__));
define('GDA_VOEDING_PATH', plugin_dir_path(__FILE__));

/**
 * CPT registreren
 */
function gda_register_voeding_cpt() {

    $labels = array(
        'name'               => 'Voeding',
        'singular_name'      => 'Voeding item',
        'menu_name'          => 'Voeding',
        'name_admin_bar'     => 'Voeding item',
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
        'menu_icon'          => 'dashicons-carrot',
        'supports'           => array('title', 'editor', 'page-attributes'),
        'rewrite'            => array('slug' => 'voeding'),
    );

    register_post_type('gda_voeding', $args);
}
add_action('init', 'gda_register_voeding_cpt');


/**
 * Metaboxen: 2 afbeeldingen + anchor id
 * Tip: gebruik Afbeelding 1 als "logo/merk" (links), Afbeelding 2 optioneel (extra visual).
 */
function gda_voeding_add_metaboxes() {

    add_meta_box(
        'gda_voeding_images_box',
        'Afbeeldingen',
        'gda_voeding_images_metabox_render',
        'gda_voeding',
        'normal',
        'high'
    );

    add_meta_box(
        'gda_voeding_anchor_id_box',
        'Anchor ID',
        'gda_voeding_anchor_id_metabox_render',
        'gda_voeding',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'gda_voeding_add_metaboxes');

function gda_voeding_images_metabox_render($post) {
    wp_nonce_field('gda_voeding_images_save', 'gda_voeding_images_nonce');

    $img1_id = (int) get_post_meta($post->ID, '_gda_voeding_image_1', true);
    $img1_url = $img1_id ? wp_get_attachment_image_url($img1_id, 'medium') : '';

    ?>
    <div style="margin-bottom:16px;">
        <strong>Afbeelding 1 (bv. logo links)</strong>
        <div style="margin:10px 0;">
            <img id="gda-voeding-preview-1" src="<?php echo esc_url($img1_url); ?>"
                 style="<?php echo $img1_url ? '' : 'display:none;'; ?>max-width:320px;height:auto;border:1px solid #ddd;border-radius:6px;" />
        </div>

        <input type="hidden" id="gda-voeding-image-1" name="gda_voeding_image_1" value="<?php echo esc_attr($img1_id); ?>" />
        <button type="button" class="button" id="gda-voeding-pick-1">Kies afbeelding</button>
        <button type="button" class="button" id="gda-voeding-remove-1" style="<?php echo $img1_url ? '' : 'display:none;'; ?>">Verwijderen</button>
    </div>
    <?php
}

function gda_voeding_anchor_id_metabox_render($post) {
    wp_nonce_field('gda_voeding_anchor_id_save', 'gda_voeding_anchor_id_nonce');

    $val = get_post_meta($post->ID, '_gda_voeding_anchor_id', true);

    echo '<p style="margin-top:0;">Unieke ID voor anchors (zonder #). Voorbeeld: <code>alpapro</code></p>';
    echo '<input type="text" name="gda_voeding_anchor_id" value="' . esc_attr($val) . '" style="width:100%;" placeholder="bv. alpapro" />';
}

/**
 * Opslaan metaboxen
 */
function gda_voeding_images_save($post_id) {
    if (!isset($_POST['gda_voeding_images_nonce']) || !wp_verify_nonce($_POST['gda_voeding_images_nonce'], 'gda_voeding_images_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $img1 = isset($_POST['gda_voeding_image_1']) ? (int) $_POST['gda_voeding_image_1'] : 0;

    if ($img1 > 0) update_post_meta($post_id, '_gda_voeding_image_1', $img1);
    else delete_post_meta($post_id, '_gda_voeding_image_1');
}
add_action('save_post_gda_voeding', 'gda_voeding_images_save');

function gda_voeding_anchor_id_save($post_id) {
    if (!isset($_POST['gda_voeding_anchor_id_nonce']) || !wp_verify_nonce($_POST['gda_voeding_anchor_id_nonce'], 'gda_voeding_anchor_id_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $val = isset($_POST['gda_voeding_anchor_id']) ? sanitize_title($_POST['gda_voeding_anchor_id']) : '';

    if ($val === '') {
        delete_post_meta($post_id, '_gda_voeding_anchor_id');
        return;
    }

    update_post_meta($post_id, '_gda_voeding_anchor_id', $val);
}
add_action('save_post_gda_voeding', 'gda_voeding_anchor_id_save');


/**
 * Admin assets (media uploader)
 */
function gda_voeding_admin_assets($hook) {
    global $post_type;

    if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'gda_voeding') {
        wp_enqueue_media();
        wp_enqueue_script(
            'gda-voeding-admin',
            GDA_VOEDING_URL . 'assets/admin-voeding.js',
            array('jquery'),
            GDA_VOEDING_VERSION,
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'gda_voeding_admin_assets');


/**
 * Front assets (accordion)
 */
function gda_voeding_front_assets() {
    wp_enqueue_script(
        'gda-voeding-toggles',
        GDA_VOEDING_URL . 'assets/voeding-toggles.js',
        array('jquery'),
        GDA_VOEDING_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'gda_voeding_front_assets');


/**
 * Shortcode: [gda_voeding]
 */
function gda_voeding_shortcode($atts) {
    $atts = shortcode_atts(array(
        'posts_per_page' => -1,
    ), $atts, 'gda_voeding');

    $q = new WP_Query(array(
        'post_type'      => 'gda_voeding',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby'        => array('menu_order' => 'DESC', 'date' => 'ASC'),
    ));

    if (!$q->have_posts()) return '';

    ob_start();
    ?>
    <div class="gda-voeding-toggles">
        <?php while ($q->have_posts()) : $q->the_post(); ?>
            <?php
            $anchor_id = get_post_meta(get_the_ID(), '_gda_voeding_anchor_id', true);
            $anchor_attr = $anchor_id ? ' id="' . esc_attr($anchor_id) . '"' : '';

            $img1_id = (int) get_post_meta(get_the_ID(), '_gda_voeding_image_1', true);
            ?>
            <div class="gda-voeding-toggle"<?php echo $anchor_attr; ?>>
                <a class="gda-voeding-toggle__header" aria-expanded="false">
                    <span class="gda-voeding-toggle__title"><?php echo esc_html(get_the_title()); ?></span>
                    <span class="gda-voeding-toggle__chev" aria-hidden="true">
                    <img src="/wp-content/themes/Avada-Child/assets/images/icon/toggle.gif" alt="">
                </span>
                </a>

                <div class="gda-voeding-toggle__panel" style="display:none;">
                    <div class="gda-voeding-toggle__inner">

                        <?php if ($img1_id) : ?>
                            <div class="gda-voeding__logo">
                                <?php echo wp_get_attachment_image($img1_id, 'large'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="gda-voeding__body">
                            <?php the_content(); ?>
                        </div>

                    </div>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('gda_voeding', 'gda_voeding_shortcode');