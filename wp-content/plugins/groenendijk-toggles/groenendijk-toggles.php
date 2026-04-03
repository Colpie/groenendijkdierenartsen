<?php
/**
 * Plugin Name: Groenendijk Toggles
 * Description: Custom Post Type "Groenendijk Toggles" met titel, tekst en 1 afbeelding. Inclusief shortcode [gda_toggles].
 * Version: 3.0.0
 * Author: Groenendijk
 */

if (!defined('ABSPATH')) exit;

define('GDA_TOGGLES_VERSION', '3.0.0');
define('GDA_TOGGLES_URL', plugin_dir_url(__FILE__));
define('GDA_TOGGLES_PATH', plugin_dir_path(__FILE__));

/**
 * CPT registreren
 */
function gda_register_toggles_cpt() {

    $labels = array(
        'name'               => 'Groenendijk Toggles',
        'singular_name'      => 'Toggle item',
        'menu_name'          => 'Groenendijk Toggles',
        'name_admin_bar'     => 'Toggle item',
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
        'menu_icon'    => 'dashicons-editor-ul',
        'supports'     => array('title', 'editor', 'page-attributes'),
        'rewrite'      => array('slug' => 'groenendijk-toggle'),
    );

    register_post_type('gda_toggle', $args);
}
add_action('init', 'gda_register_toggles_cpt');

/**
 * Taxonomy registreren
 */
function gda_register_toggles_taxonomy() {

    $labels = array(
        'name'              => 'Categorieën',
        'singular_name'     => 'Categorie',
        'search_items'      => 'Zoek categorieën',
        'all_items'         => 'Alle categorieën',
        'edit_item'         => 'Categorie bewerken',
        'update_item'       => 'Categorie updaten',
        'add_new_item'      => 'Nieuwe categorie toevoegen',
        'new_item_name'     => 'Nieuwe categorienaam',
        'menu_name'         => 'Categorieën',
    );

    $args = array(
        'labels'            => $labels,
        'public'            => true,
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array('slug' => 'groenendijk-toggle-categorie'),
    );

    register_taxonomy('gda_toggle_category', array('gda_toggle'), $args);
}
add_action('init', 'gda_register_toggles_taxonomy');

/**
 * Metaboxen: 1 afbeelding + anchor id + Icoon
 */
function gda_toggles_add_metaboxes() {

    add_meta_box(
        'gda_toggle_image_box',
        'Afbeelding',
        'gda_toggle_image_metabox_render',
        'gda_toggle',
        'normal',
        'high'
    );

    add_meta_box(
        'gda_toggle_icon_box',
        'Icoon instellingen',
        'gda_toggle_icon_metabox_render',
        'gda_toggle',
        'normal',
        'default'
    );

    add_meta_box(
        'gda_toggle_anchor_id_box',
        'Anchor ID',
        'gda_toggle_anchor_id_metabox_render',
        'gda_toggle',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'gda_toggles_add_metaboxes');

function gda_toggle_image_metabox_render($post) {
    wp_nonce_field('gda_toggle_image_save', 'gda_toggle_image_nonce');

    $image_id = (int) get_post_meta($post->ID, '_gda_toggle_image', true);

    // backwards compat uit oude velden
    if (!$image_id) {
        $legacy_1 = (int) get_post_meta($post->ID, '_gda_ug_image', true);
        $legacy_2 = (int) get_post_meta($post->ID, '_gda_ug_image_1', true);
        $legacy_3 = (int) get_post_meta($post->ID, '_gda_ug_image_2', true);

        if ($legacy_1) {
            $image_id = $legacy_1;
        } elseif ($legacy_2) {
            $image_id = $legacy_2;
        } elseif ($legacy_3) {
            $image_id = $legacy_3;
        }
    }

    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>
    <div class="gda-toggle-image-field">
        <p style="margin-top:0;">
            <strong>1 afbeelding per item</strong>
        </p>

        <div style="margin:10px 0;">
            <img
                    id="gda-toggle-preview"
                    src="<?php echo esc_url($image_url); ?>"
                    style="<?php echo $image_url ? '' : 'display:none;'; ?>max-width:320px;height:auto;border:1px solid #ddd;border-radius:6px;"
            />
        </div>

        <input type="hidden" id="gda-toggle-image" name="gda_toggle_image" value="<?php echo esc_attr($image_id); ?>" />

        <button type="button" class="button button-primary" id="gda-toggle-pick">Kies afbeelding</button>
        <button
                type="button"
                class="button"
                id="gda-toggle-remove"
                style="<?php echo $image_url ? '' : 'display:none;'; ?>"
        >Verwijderen</button>
    </div>
    <?php
}

function gda_toggle_icon_metabox_render($post) {
    wp_nonce_field('gda_toggle_icon_save', 'gda_toggle_icon_nonce');

    $icon_id     = (int) get_post_meta($post->ID, '_gda_toggle_icon', true);
    $icon_active = get_post_meta($post->ID, '_gda_toggle_icon_active', true);

    $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'medium') : '';
    ?>
    <div class="gda-toggle-icon-field">
        <p style="margin-top:0;">
            <label>
                <input type="checkbox" name="gda_toggle_icon_active" value="1" <?php checked($icon_active, '1'); ?>>
                Gebruik icoonlayout voor dit item
            </label>
        </p>

        <p style="color:#666;">
            Als dit actief is, wordt de gewone afbeelding op de voorkant genegeerd en tonen we links het icoon en rechts de content.
        </p>

        <div style="margin:10px 0;">
            <img
                    id="gda-toggle-icon-preview"
                    src="<?php echo esc_url($icon_url); ?>"
                    style="<?php echo $icon_url ? '' : 'display:none;'; ?>max-width:140px;height:auto;border:1px solid #ddd;border-radius:6px;background:#fff;padding:8px;"
            />
        </div>

        <input type="hidden" id="gda-toggle-icon" name="gda_toggle_icon" value="<?php echo esc_attr($icon_id); ?>" />

        <button type="button" class="button button-primary" id="gda-toggle-icon-pick">Kies icoon</button>
        <button
                type="button"
                class="button"
                id="gda-toggle-icon-remove"
                style="<?php echo $icon_url ? '' : 'display:none;'; ?>"
        >Verwijderen</button>
    </div>
    <?php
}

function gda_toggle_anchor_id_metabox_render($post) {
    wp_nonce_field('gda_toggle_anchor_id_save', 'gda_toggle_anchor_id_nonce');

    $val = get_post_meta($post->ID, '_gda_toggle_anchor_id', true);

    // backwards compat
    if (!$val) {
        $val = get_post_meta($post->ID, '_gda_ug_anchor_id', true);
    }

    echo '<p style="margin-top:0;">Unieke ID voor anchors (zonder #). Voorbeeld: <code>mastapro</code></p>';
    echo '<input type="text" name="gda_toggle_anchor_id" value="' . esc_attr($val) . '" style="width:100%;" placeholder="bv. mastapro" />';
    echo '<p style="color:#666; margin-bottom:0;">Gebruik in link: <code>#' . esc_html($val ? $val : 'mastapro') . '</code></p>';
}

/**
 * Opslaan afbeelding
 */
function gda_toggle_image_save($post_id) {
    if (!isset($_POST['gda_toggle_image_nonce']) || !wp_verify_nonce($_POST['gda_toggle_image_nonce'], 'gda_toggle_image_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $image_id = isset($_POST['gda_toggle_image']) ? (int) $_POST['gda_toggle_image'] : 0;

    if ($image_id > 0) {
        update_post_meta($post_id, '_gda_toggle_image', $image_id);

        // backwards compat sync
        update_post_meta($post_id, '_gda_ug_image', $image_id);
    } else {
        delete_post_meta($post_id, '_gda_toggle_image');
        delete_post_meta($post_id, '_gda_ug_image');
    }

    delete_post_meta($post_id, '_gda_ug_images');
    delete_post_meta($post_id, '_gda_ug_image_1');
    delete_post_meta($post_id, '_gda_ug_image_2');
}
add_action('save_post_gda_toggle', 'gda_toggle_image_save');

function gda_toggle_anchor_id_save($post_id) {
    if (!isset($_POST['gda_toggle_anchor_id_nonce']) || !wp_verify_nonce($_POST['gda_toggle_anchor_id_nonce'], 'gda_toggle_anchor_id_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $val = isset($_POST['gda_toggle_anchor_id']) ? sanitize_title($_POST['gda_toggle_anchor_id']) : '';

    if ($val === '') {
        delete_post_meta($post_id, '_gda_toggle_anchor_id');
        delete_post_meta($post_id, '_gda_ug_anchor_id');
        return;
    }

    update_post_meta($post_id, '_gda_toggle_anchor_id', $val);

    // backwards compat sync
    update_post_meta($post_id, '_gda_ug_anchor_id', $val);
}
add_action('save_post_gda_toggle', 'gda_toggle_anchor_id_save');

function gda_toggle_icon_save($post_id) {
    if (!isset($_POST['gda_toggle_icon_nonce']) || !wp_verify_nonce($_POST['gda_toggle_icon_nonce'], 'gda_toggle_icon_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $icon_id = isset($_POST['gda_toggle_icon']) ? (int) $_POST['gda_toggle_icon'] : 0;
    $icon_active = isset($_POST['gda_toggle_icon_active']) ? '1' : '0';

    if ($icon_id > 0) {
        update_post_meta($post_id, '_gda_toggle_icon', $icon_id);
    } else {
        delete_post_meta($post_id, '_gda_toggle_icon');
    }

    update_post_meta($post_id, '_gda_toggle_icon_active', $icon_active);
}
add_action('save_post_gda_toggle', 'gda_toggle_icon_save');

/**
 * Admin assets
 */
function gda_toggle_admin_assets($hook) {
    global $post_type;

    if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'gda_toggle') {
        wp_enqueue_media();

        wp_enqueue_script(
            'gda-toggle-admin',
            GDA_TOGGLES_URL . 'assets/admin-toggles.js',
            array('jquery'),
            GDA_TOGGLES_VERSION,
            true
        );

        wp_add_inline_script('gda-toggle-admin', "
    jQuery(document).ready(function($){
        var imageFrame;
        var iconFrame;

        $('#gda-toggle-pick').on('click', function(e){
            e.preventDefault();

            if (imageFrame) {
                imageFrame.open();
                return;
            }

            imageFrame = wp.media({
                title: 'Kies afbeelding',
                button: {
                    text: 'Gebruik deze afbeelding'
                },
                multiple: false
            });

            imageFrame.on('select', function(){
                var attachment = imageFrame.state().get('selection').first().toJSON();
                $('#gda-toggle-image').val(attachment.id);
                $('#gda-toggle-preview').attr('src', attachment.url).show();
                $('#gda-toggle-remove').show();
            });

            imageFrame.open();
        });

        $('#gda-toggle-remove').on('click', function(e){
            e.preventDefault();
            $('#gda-toggle-image').val('');
            $('#gda-toggle-preview').attr('src', '').hide();
            $(this).hide();
        });

        $('#gda-toggle-icon-pick').on('click', function(e){
            e.preventDefault();

            if (iconFrame) {
                iconFrame.open();
                return;
            }

            iconFrame = wp.media({
                title: 'Kies icoon',
                button: {
                    text: 'Gebruik dit icoon'
                },
                multiple: false
            });

            iconFrame.on('select', function(){
                var attachment = iconFrame.state().get('selection').first().toJSON();
                $('#gda-toggle-icon').val(attachment.id);
                $('#gda-toggle-icon-preview').attr('src', attachment.url).show();
                $('#gda-toggle-icon-remove').show();
            });

            iconFrame.open();
        });

        $('#gda-toggle-icon-remove').on('click', function(e){
            e.preventDefault();
            $('#gda-toggle-icon').val('');
            $('#gda-toggle-icon-preview').attr('src', '').hide();
            $(this).hide();
        });
    });
");
    }
}
add_action('admin_enqueue_scripts', 'gda_toggle_admin_assets');

/**
 * Front assets
 */
function gda_toggle_front_assets() {
    wp_enqueue_script(
        'gda-toggle-front',
        GDA_TOGGLES_URL . 'assets/toggles.js',
        array('jquery'),
        GDA_TOGGLES_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'gda_toggle_front_assets');

/**
 * Shortcode: [gda_toggles]
 * Voorbeelden:
 * [gda_toggles]
 * [gda_toggles category="uiergezondheid"]
 * [gda_toggles category="uiergezondheid,preventie"]
 * [gda_toggles posts_per_page="5"]
 */
function gda_toggles_shortcode($atts) {
    $atts = shortcode_atts(array(
        'posts_per_page' => -1,
        'category'       => '',
    ), $atts, 'gda_toggles');

    $args = array(
        'post_type'      => 'gda_toggle',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby'        => array(
            'menu_order' => 'DESC',
            'date'       => 'ASC',
        ),
    );

    if (!empty($atts['category'])) {
        $categories = array_filter(array_map('trim', explode(',', $atts['category'])));

        if (!empty($categories)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'gda_toggle_category',
                    'field'    => 'slug',
                    'terms'    => $categories,
                ),
            );
        }
    }

    $q = new WP_Query($args);

    if (!$q->have_posts()) {
        return '';
    }

    ob_start();
    ?>
    <div class="gda-toggles gda-groenendijk-toggles">
        <?php while ($q->have_posts()) : $q->the_post(); ?>
            <?php
            $post_id = get_the_ID();
            $icon_id = (int) get_post_meta($post_id, '_gda_toggle_icon', true);
            $icon_active = get_post_meta($post_id, '_gda_toggle_icon_active', true);

            $anchor_id = get_post_meta($post_id, '_gda_toggle_anchor_id', true);
            if (!$anchor_id) {
                $anchor_id = get_post_meta($post_id, '_gda_ug_anchor_id', true);
            }

            $anchor_attr = $anchor_id ? ' id="' . esc_attr($anchor_id) . '"' : '';

            $image_id = (int) get_post_meta($post_id, '_gda_toggle_image', true);

            if (!$image_id) {
                $legacy_1 = (int) get_post_meta($post_id, '_gda_ug_image', true);
                $legacy_2 = (int) get_post_meta($post_id, '_gda_ug_image_1', true);
                $legacy_3 = (int) get_post_meta($post_id, '_gda_ug_image_2', true);

                if ($legacy_1) {
                    $image_id = $legacy_1;
                } elseif ($legacy_2) {
                    $image_id = $legacy_2;
                } elseif ($legacy_3) {
                    $image_id = $legacy_3;
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
                    <div class="gda-toggle__inner <?php echo ($icon_active === '1' && $icon_id) ? 'gda-toggle__inner--icon-layout' : ''; ?>">

                        <?php if ($icon_active === '1' && $icon_id) : ?>
                            <div class="gda-toggle__icon-layout">
                                <div class="gda-toggle__icon-col">
                                    <div class="gda-toggle__icon-wrap">
                                        <?php echo wp_get_attachment_image($icon_id, 'medium', false, array('class' => 'gda-toggle__icon')); ?>
                                    </div>
                                </div>

                                <div class="gda-toggle__content-col">
                                    <div class="gda-toggle__body icon-body-content check-list">
                                        <?php the_content(); ?>
                                    </div>
                                    <?php if ($image_id) : ?>
                                        <div class="gda-toggle__images gda-toggle__images--single gda-toggle__images--icon-layout">
                                            <div class="gda-toggle__image gda-toggle__image--single">
                                                <?php echo wp_get_attachment_image($image_id, 'large'); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else : ?>
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
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('gda_toggles', 'gda_toggles_shortcode');

/**
 * Optioneel backwards compatibility met oude shortcode
 */
add_shortcode('gda_uiergezondheid', 'gda_toggles_shortcode');

/**
 * Flush rewrite rules bij activatie/deactivatie
 */
function gda_toggles_activate() {
    gda_register_toggles_cpt();
    gda_register_toggles_taxonomy();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'gda_toggles_activate');

function gda_toggles_deactivate() {
    flush_rewrite_rules();
}