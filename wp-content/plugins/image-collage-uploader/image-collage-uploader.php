<?php
/**
 * Plugin Name: Image Collage Uploader
 * Description: Admin page to add/remove unlimited images via WP Media Library and display as masonry collage via shortcode.
 * Version: 1.1.0
 */

if (!defined('ABSPATH')) exit;

class GDA_Image_Collage_Uploader {
    const OPTION_KEY = 'gda_image_collage_items';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'front_assets']);

        add_action('admin_post_gda_save_collage', [$this, 'handle_save']);

        add_shortcode('image_collage', [$this, 'shortcode']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Image Collage',
            'Image Collage',
            'manage_options',
            'gda-image-collage',
            [$this, 'render_admin_page'],
            'dashicons-format-gallery',
            26
        );
    }

    public function admin_assets($hook) {
        if ($hook !== 'toplevel_page_gda-image-collage') return;

        wp_enqueue_media();

        wp_enqueue_style(
            'gda-collage-admin',
            plugins_url('assets/admin.css', __FILE__),
            [],
            '1.1.0'
        );

        wp_enqueue_script(
            'gda-collage-admin',
            plugins_url('assets/admin.js', __FILE__),
            ['jquery'],
            '1.1.0',
            true
        );

        $items = get_option(self::OPTION_KEY, []);
        if (!is_array($items)) $items = [];

        $normalized = [];
        foreach ($items as $it) {
            if (!is_array($it)) continue;

            $id = isset($it['attachment_id']) ? absint($it['attachment_id']) : 0;
            if (!$id) continue;

            $thumb = wp_get_attachment_image_url($id, 'thumbnail');
            if (!$thumb) $thumb = wp_get_attachment_image_url($id, 'medium');
            if (!$thumb) $thumb = wp_get_attachment_url($id);

            $normalized[] = [
                'attachment_id' => $id,
                'thumb' => $thumb ? $thumb : '',
            ];
        }

        wp_localize_script('gda-collage-admin', 'GDA_COLLAGE', [
            'items' => $normalized,
            'nonce' => wp_create_nonce('gda_collage_save'),
        ]);

        wp_enqueue_script('jquery-ui-sortable');
    }

    public function front_assets() {
        wp_enqueue_style(
            'gda-collage-front',
            plugins_url('assets/front.css', __FILE__),
            [],
            '1.1.0'
        );

        wp_enqueue_script(
            'gda-collage-front',
            plugins_url('assets/front.js', __FILE__),
            [],
            '1.1.0',
            true
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) return;

        $items = get_option(self::OPTION_KEY, []);
        if (!is_array($items)) $items = [];

        ?>
        <div class="wrap gda-collage-wrap">
            <h1>Image Collage</h1>

            <p class="description">
                Voeg foto's toe (1 per keer) via de WP Media Library. Je kan ze vervangen of verwijderen.
                Gebruik op je pagina: <code>[image_collage]</code>
            </p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="gda_save_collage">
                <?php wp_nonce_field('gda_collage_save', 'gda_collage_nonce'); ?>

                <div class="gda-toolbar">
                    <button type="button" class="button button-primary" id="gda-add-item">+ Foto toevoegen</button>
                    <button type="submit" class="button button-secondary">Opslaan</button>
                    <span class="gda-shortcode">Shortcode: <code>[image_collage]</code></span>
                </div>

                <div class="gda-items" id="gda-items">
                    <!-- rows injected by JS -->
                </div>

                <div class="gda-footer">
                    <button type="submit" class="button button-primary">Opslaan</button>
                </div>
            </form>

            <hr />

            <h2>Preview</h2>
            <div class="gda-preview">
                <?php
                // Normalize for render
                $normalized = [];
                foreach ($items as $it) {
                    if (!is_array($it)) continue;
                    $id = isset($it['attachment_id']) ? absint($it['attachment_id']) : 0;
                    if ($id) $normalized[] = ['attachment_id' => $id];
                }
                echo $this->render_collage($normalized, [
                    'bg' => '#A7B72A',
                    'radius' => '22px',
                ]);
                ?>
            </div>
        </div>
        <?php
    }

    public function handle_save() {
        if (!current_user_can('manage_options')) wp_die('No permission');

        $nonce = isset($_POST['gda_collage_nonce']) ? sanitize_text_field($_POST['gda_collage_nonce']) : '';
        if (!wp_verify_nonce($nonce, 'gda_collage_save')) wp_die('Invalid nonce');

        $raw = isset($_POST['items']) ? (array) $_POST['items'] : [];

        $items = [];
        foreach ($raw as $row) {
            if (!is_array($row)) continue;
            $attachment_id = isset($row['attachment_id']) ? absint($row['attachment_id']) : 0;
            if (!$attachment_id) continue;

            $items[] = [
                'attachment_id' => $attachment_id,
            ];
        }

        update_option(self::OPTION_KEY, $items);

        wp_redirect(add_query_arg(['page' => 'gda-image-collage', 'updated' => 'true'], admin_url('admin.php')));
        exit;
    }

    public function shortcode($atts) {
        $atts = shortcode_atts([
            'bg' => '#A7B72A',
            'radius' => '22px',
            'class' => '',
            'cols' => '3', // masonry kolommen desktop
        ], $atts, 'image_collage');

        $items = get_option(self::OPTION_KEY, []);
        if (!is_array($items)) $items = [];

        $normalized = [];
        foreach ($items as $it) {
            if (!is_array($it)) continue;
            $id = isset($it['attachment_id']) ? absint($it['attachment_id']) : 0;
            if ($id) $normalized[] = ['attachment_id' => $id];
        }

        return $this->render_collage($normalized, $atts);
    }

    private function render_collage($items, $atts) {
        if (empty($items)) {
            return '<div class="gda-collage-empty">Nog geen foto’s ingesteld.</div>';
        }

        $bg = isset($atts['bg']) ? $atts['bg'] : '#A7B72A';
        $radius = isset($atts['radius']) ? $atts['radius'] : '22px';
        $extra_class = isset($atts['class']) ? $atts['class'] : '';
        $cols = isset($atts['cols']) ? max(1, min(6, (int)$atts['cols'])) : 3;

        ob_start();
        ?>
        <div class="gda-collage <?php echo esc_attr($extra_class); ?>"
             style="--gda-bg: <?php echo esc_attr($bg); ?>; --gda-radius: <?php echo esc_attr($radius); ?>; --gda-cols: <?php echo esc_attr((string)$cols); ?>;">
            <div class="gda-collage-inner">
                <?php foreach (array_slice($items, 0, 7) as $i => $item):
                    $attachment_id = isset($item['attachment_id']) ? absint($item['attachment_id']) : 0;
                    if (!$attachment_id) continue;

                    $thumb = wp_get_attachment_image_url($attachment_id, 'large');
                    if (!$thumb) $thumb = wp_get_attachment_url($attachment_id);
                    if (!$thumb) continue;
                    ?>
                    <figure class="gda-tile gda-pos-<?php echo $i + 1; ?>">
                        <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
                    </figure>
                <?php endforeach; ?>

            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new GDA_Image_Collage_Uploader();
