<?php
/**
 * Plugin Name: Groenendijk Toggles
 * Description: Custom Post Type "Toggles" met titel, body en onbeperkte list items. Inclusief shortcode [gda_toggles].
 * Version: 1.0.0
 * Author: Groenendijk
 */

if (!defined('ABSPATH')) exit;

define('GDA_TOGGLES_VERSION', '1.0.0');
define('GDA_TOGGLES_URL', plugin_dir_url(__FILE__));
define('GDA_TOGGLES_PATH', plugin_dir_path(__FILE__));

/**
 * CPT registreren
 */
function gda_register_toggle_cpt() {

    $labels = array(
        'name'               => 'Toggles',
        'singular_name'      => 'Toggle',
        'menu_name'          => 'Toggles',
        'name_admin_bar'     => 'Toggle',
        'add_new'            => 'Nieuwe toggle',
        'add_new_item'       => 'Nieuwe toggle toevoegen',
        'new_item'           => 'Nieuwe toggle',
        'edit_item'          => 'Toggle bewerken',
        'view_item'          => 'Toggle bekijken',
        'all_items'          => 'Alle toggles',
        'search_items'       => 'Zoek toggles',
        'not_found'          => 'Geen toggles gevonden',
        'not_found_in_trash' => 'Geen toggles in prullenbak',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_rest'       => true,
        'has_archive'        => false,
        'menu_icon'          => 'dashicons-editor-ul',
        'supports'           => array('title', 'editor', 'page-attributes'), // page-attributes => menu_order
        'rewrite'            => array('slug' => 'toggle'),
    );

    register_post_type('gda_toggle', $args);
}
add_action('init', 'gda_register_toggle_cpt');

/**
 * Metabox: list items (repeatable)
 */
function gda_toggle_add_metaboxes() {

    add_meta_box(
        'gda_toggle_items_box',
        'List items',
        'gda_toggle_items_metabox_render',
        'gda_toggle',
        'normal',
        'high'
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

add_action('add_meta_boxes', 'gda_toggle_add_metaboxes');

function gda_toggle_items_metabox_render($post) {
    wp_nonce_field('gda_toggle_items_save', 'gda_toggle_items_nonce');

    $items = get_post_meta($post->ID, '_gda_toggle_items', true);
    if (!is_array($items)) $items = array();

    echo '<p style="margin:0 0 12px 0;">Voeg hieronder zoveel list items toe als je wil.</p>';

    echo '<div id="gda-toggle-items-wrap">';

    if (empty($items)) {
        $items = array(array('text' => ''));
    }

    foreach ($items as $index => $row) {
        $text = isset($row['text']) ? $row['text'] : '';
        ?>
        <div class="gda-toggle-item-row" style="display:flex; gap:10px; align-items:flex-start; margin-bottom:10px;">
            <input
                type="text"
                name="gda_toggle_items[<?php echo esc_attr($index); ?>][text]"
                value="<?php echo esc_attr($text); ?>"
                placeholder="Bijv. Drachtcontrole vanaf 30 dagen..."
                style="width:100%;"
            />
            <button type="button" class="button gda-remove-item" style="white-space:nowrap;">Verwijderen</button>
        </div>
        <?php
    }

    echo '</div>';

    echo '<button type="button" class="button button-primary" id="gda-add-item">+ Item toevoegen</button>';

    // Template voor JS
    ?>
    <script type="text/html" id="tmpl-gda-toggle-item-row">
        <div class="gda-toggle-item-row" style="display:flex; gap:10px; align-items:flex-start; margin-bottom:10px;">
            <input
                type="text"
                name="gda_toggle_items[{{INDEX}}][text]"
                value=""
                placeholder="Bijv. Controle van verse/open koeien"
                style="width:100%;"
            />
            <button type="button" class="button gda-remove-item" style="white-space:nowrap;">Verwijderen</button>
        </div>
    </script>
    <?php
}

function gda_toggle_anchor_id_metabox_render($post) {
    // zelfde nonce gebruiken mag, maar ik hou het apart (duidelijker)
    wp_nonce_field('gda_toggle_anchor_id_save', 'gda_toggle_anchor_id_nonce');

    $val = get_post_meta($post->ID, '_gda_toggle_anchor_id', true);

    echo '<p style="margin-top:0;">Unieke ID voor anchors (zonder #). Voorbeeld: <code>vruchtbaarheid</code></p>';
    echo '<input type="text" name="gda_toggle_anchor_id" value="' . esc_attr($val) . '" style="width:100%;" placeholder="bv. vruchtbaarheid" />';
    echo '<p style="color:#666; margin-bottom:0;">Gebruik in link: <code>#' . esc_html($val ? $val : 'vruchtbaarheid') . '</code></p>';
}

/**
 * Opslaan metabox
 */
function gda_toggle_items_save($post_id) {
    if (!isset($_POST['gda_toggle_items_nonce']) || !wp_verify_nonce($_POST['gda_toggle_items_nonce'], 'gda_toggle_items_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $raw = isset($_POST['gda_toggle_items']) ? $_POST['gda_toggle_items'] : array();
    $items = array();

    if (is_array($raw)) {
        foreach ($raw as $row) {
            $text = isset($row['text']) ? sanitize_text_field($row['text']) : '';
            if ($text !== '') {
                $items[] = array('text' => $text);
            }
        }
    }

    update_post_meta($post_id, '_gda_toggle_items', $items);
}
add_action('save_post_gda_toggle', 'gda_toggle_items_save');

function gda_toggle_anchor_id_save($post_id) {
    if (!isset($_POST['gda_toggle_anchor_id_nonce']) || !wp_verify_nonce($_POST['gda_toggle_anchor_id_nonce'], 'gda_toggle_anchor_id_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $val = isset($_POST['gda_toggle_anchor_id']) ? sanitize_title($_POST['gda_toggle_anchor_id']) : '';

    // Leeg = meta verwijderen
    if ($val === '') {
        delete_post_meta($post_id, '_gda_toggle_anchor_id');
        return;
    }

    update_post_meta($post_id, '_gda_toggle_anchor_id', $val);
}
add_action('save_post_gda_toggle', 'gda_toggle_anchor_id_save');

/**
 * Admin assets (repeatable UI)
 */
function gda_toggle_admin_assets($hook) {
    global $post_type;

    if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'gda_toggle') {
        wp_enqueue_script(
            'gda-toggle-admin',
            GDA_TOGGLES_URL . 'assets/admin-toggles.js',
            array('jquery'),
            GDA_TOGGLES_VERSION,
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'gda_toggle_admin_assets');

/**
 * Front assets
 */
function gda_toggle_front_assets() {
    wp_enqueue_style(
        'gda-toggles',
        GDA_TOGGLES_URL . 'assets/toggles.css',
        array(),
        GDA_TOGGLES_VERSION
    );

    wp_enqueue_script(
        'gda-toggles',
        GDA_TOGGLES_URL . 'assets/toggles.js',
        array('jquery'),
        GDA_TOGGLES_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'gda_toggle_front_assets');

/**
 * Shortcode: [gda_toggles]
 * Opties:
 * - posts_per_page (default -1)
 */
function gda_toggles_shortcode($atts) {
    $atts = shortcode_atts(array(
        'posts_per_page' => -1,
    ), $atts, 'gda_toggles');

    $q = new WP_Query(array(
        'post_type'      => 'gda_toggle',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby'        => array('menu_order' => 'DESC', 'date' => 'ASC'),
    ));

    if (!$q->have_posts()) return '';

    ob_start();
    ?>
    <div class="gda-toggles">
        <?php while ($q->have_posts()) : $q->the_post(); ?>
            <?php
            $items = get_post_meta(get_the_ID(), '_gda_toggle_items', true);
            if (!is_array($items)) $items = array();
            ?>
            <?php
            $anchor_id = get_post_meta(get_the_ID(), '_gda_toggle_anchor_id', true);
            $anchor_attr = $anchor_id ? ' id="' . esc_attr($anchor_id) . '"' : '';
            ?>
            <div class="gda-toggle"<?php echo $anchor_attr; ?>>
                <a class="gda-toggle__header" aria-expanded="false">
                    <span class="gda-toggle__title"><?php echo esc_html(get_the_title()); ?></span>
                    <span class="gda-toggle__chev" aria-hidden="true">
                        <img src="/wp-content/themes/Avada-Child/assets/images/icon/toggle.gif">
                    </span>
                </a>

                <div class="gda-toggle__panel" hidden>
                    <div class="gda-toggle__inner">
                        <div class="gda-toggle__body">
                            <?php the_content(); ?>
                        </div>

                        <?php if (!empty($items)) : ?>
                            <ul class="gda-toggle__list">
                                <?php foreach ($items as $row) :
                                    $text = isset($row['text']) ? $row['text'] : '';
                                    if ($text === '') continue;
                                    ?>
                                    <li class="gda-toggle__listitem">
                                        <span class="gda-toggle__check" aria-hidden="true">
                                            <img src="/wp-content/themes/Avada-Child/assets/images/icon/incl.png">
                                        </span>
                                        <span class="gda-toggle__text"><?php echo esc_html($text); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
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
