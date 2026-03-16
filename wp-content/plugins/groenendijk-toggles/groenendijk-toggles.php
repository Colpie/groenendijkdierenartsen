<?php
/**
 * Plugin Name: Groenendijk Uiergezondheid
 * Description: Custom Post Type "Uiergezondheid" met titel, tekst en 1 afbeelding. Inclusief shortcode [gda_uiergezondheid].
 * Version: 2.0.1
 * Author: Groenendijk
 */

if (!defined('ABSPATH')) exit;

define('GDA_UG_VERSION', '2.0.1');
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
        'labels'       => $labels,
        'public'       => true,
        'show_in_rest' => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-heart',
        'supports'     => array('title', 'editor', 'page-attributes'),
        'rewrite'      => array('slug' => 'uiergezondheid'),
    );

    register_post_type('gda_uiergezondheid', $args);
}
add_action('init', 'gda_register_uiergezondheid_cpt');

/**
 * Metaboxen: 1 afbeelding + anchor id
 */
function gda_ug_add_metaboxes() {

    add_meta_box(
        'gda_ug_image_box',
        'Afbeelding',
        'gda_ug_image_metabox_render',
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

function gda_ug_image_metabox_render($post) {
    wp_nonce_field('gda_ug_image_save', 'gda_ug_image_nonce');

    $image_id = (int) get_post_meta($post->ID, '_gda_ug_image', true);

    // backwards compat uit oude velden
    if (!$image_id) {
        $legacy_1 = (int) get_post_meta($post->ID, '_gda_ug_image_1', true);
        $legacy_2 = (int) get_post_meta($post->ID, '_gda_ug_image_2', true);

        if ($legacy_1) {
            $image_id = $legacy_1;
        } elseif ($legacy_2) {
            $image_id = $legacy_2;
        }
    }

    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>
    <div class="gda-ug-image-field">
        <p style="margin-top:0;">
            <strong>1 afbeelding per item</strong>
        </p>

        <div style="margin:10px 0;">
            <img
                    id="gda-ug-preview"
                    src="<?php echo esc_url($image_url); ?>"
                    style="<?php echo $image_url ? '' : 'display:none;'; ?>max-width:320px;height:auto;border:1px solid #ddd;border-radius:6px;"
            />
        </div>

        <input type="hidden" id="gda-ug-image" name="gda_ug_image" value="<?php echo esc_attr($image_id); ?>" />

        <button type="button" class="button button-primary" id="gda-ug-pick">Kies afbeelding</button>
        <button
                type="button"
                class="button"
                id="gda-ug-remove"
                style="<?php echo $image_url ? '' : 'display:none;'; ?>"
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
 * Opslaan afbeelding
 */
function gda_ug_image_save($post_id) {
    if (!isset($_POST['gda_ug_image_nonce']) || !wp_verify_nonce($_POST['gda_ug_image_nonce'], 'gda_ug_image_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $image_id = isset($_POST['gda_ug_image']) ? (int) $_POST['gda_ug_image'] : 0;

    if ($image_id > 0) {
        update_post_meta($post_id, '_gda_ug_image', $image_id);
    } else {
        delete_post_meta($post_id, '_gda_ug_image');
    }

    // oude meta opruimen / sync
    delete_post_meta($post_id, '_gda_ug_images');
    delete_post_meta($post_id, '_gda_ug_image_1');
    delete_post_meta($post_id, '_gda_ug_image_2');
}
add_action('save_post_gda_uiergezondheid', 'gda_ug_image_save');

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
 * Admin assets
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
 * Front assets
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
            $post_id = get_the_ID();

            $anchor_id = get_post_meta($post_id, '_gda_ug_anchor_id', true);
            $anchor_attr = $anchor_id ? ' id="' . esc_attr($anchor_id) . '"' : '';

            $image_id = (int) get_post_meta($post_id, '_gda_ug_image', true);

            if (!$image_id) {
                $legacy_1 = (int) get_post_meta($post_id, '_gda_ug_image_1', true);
                $legacy_2 = (int) get_post_meta($post_id, '_gda_ug_image_2', true);

                if ($legacy_1) {
                    $image_id = $legacy_1;
                } elseif ($legacy_2) {
                    $image_id = $legacy_2;
                }
            }
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

                        <?php if ($image_id) : ?>
                            <div class="gda-toggle__images gda-toggle__images--single">
                                <div class="gda-toggle__image gda-toggle__image--single">
                                    <?php echo wp_get_attachment_image($image_id, 'large'); ?>
                                </div>
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
 * Backwards compatibility
 */
add_shortcode('gda_toggles', 'gda_uiergezondheid_shortcode');