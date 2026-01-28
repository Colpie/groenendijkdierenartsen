<?php
/**
 * Plugin Name: Simple Admin Popup (settings + shortcode)
 * Description: Admin popup settings with media uploader + frontend shortcode.
 */

if (!defined('ABSPATH')) exit;

/**
 * 1) Admin menu
 */
add_action('admin_menu', 'ae_popup_admin_menu');
function ae_popup_admin_menu()
{
    add_menu_page(
        'Popup',
        'Popup',
        'manage_options',
        'ae-popup-settings',
        'ae_popup_settings_page',
        'dashicons-warning',
        60
    );
}

/**
 * 2) Register settings
 */
add_action('admin_init', 'ae_popup_register_settings');
function ae_popup_register_settings()
{
    register_setting('ae_popup_group', 'ae_popup_active', [
        'type' => 'boolean',
        'sanitize_callback' => function ($v) {
            return $v ? 1 : 0;
        },
        'default' => 0,
    ]);

    register_setting('ae_popup_group', 'ae_popup_icon_id', [
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 0,
    ]);

    register_setting('ae_popup_group', 'ae_popup_title', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);

    // Body: laat basis HTML toe
    register_setting('ae_popup_group', 'ae_popup_body', [
        'type' => 'string',
        'sanitize_callback' => 'wp_kses_post',
        'default' => '',
    ]);
}

/**
 * 3) Enqueue media uploader + admin JS
 */
add_action('admin_enqueue_scripts', function($hook){
    if ($hook !== 'toplevel_page_ae-popup-settings') return;

    wp_enqueue_media();

    $src  = get_stylesheet_directory_uri() . '/assets/js/popup-admin.js';
    $path = get_stylesheet_directory() . '/assets/js/popup-admin.js';

    // voorkomt “<<” door 404 HTML
    if (!file_exists($path)) return;

    wp_enqueue_script('ae-popup-admin', $src, ['jquery'], filemtime($path), true);
});

/**
 * 4) Admin page markup
 */
function ae_popup_settings_page()
{
    if (!current_user_can('manage_options')) return;

    $active = (int)get_option('ae_popup_active', 0);
    $icon_id = (int)get_option('ae_popup_icon_id', 0);
    $title = (string)get_option('ae_popup_title', '');
    $body = (string)get_option('ae_popup_body', '');

    $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'medium') : '';

    ?>
    <div class="wrap">
        <h1>Popup instellingen</h1>

        <form method="post" action="options.php">
            <?php settings_fields('ae_popup_group'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">Activeer popup</th>
                    <td>
                        <label>
                            <input type="checkbox" name="ae_popup_active" value="1" <?php checked(1, $active); ?> />
                            Popup is actief
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Icoon / afbeelding</th>
                    <td>
                        <div style="display:flex; gap:12px; align-items:center;">
                            <div>
                                <img id="ae-popup-icon-preview"
                                     src="<?php echo esc_url($icon_url); ?>"
                                     alt=""
                                     style="width:64px; height:64px; object-fit:contain; background:#f3f3f3; border:1px solid #ddd; <?php echo $icon_url ? '' : 'display:none;'; ?>"/>
                            </div>

                            <div>
                                <input type="hidden" id="ae_popup_icon_id" name="ae_popup_icon_id"
                                       value="<?php echo esc_attr($icon_id); ?>"/>

                                <button type="button" class="button" id="ae-popup-upload">
                                    Upload / kies afbeelding
                                </button>

                                <button type="button" class="button"
                                        id="ae-popup-remove" <?php echo $icon_url ? '' : 'style="display:none;"'; ?>>
                                    Verwijder
                                </button>

                                <p class="description">Wordt gebruikt als trigger-icoon (of in je popup header).</p>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Titel</th>
                    <td>
                        <input type="text" class="regular-text" name="ae_popup_title"
                               value="<?php echo esc_attr($title); ?>"/>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Body</th>
                    <td>
                        <textarea name="ae_popup_body" rows="8" class="large-text"><?php echo esc_textarea($body); ?></textarea>
                        <p class="description">Je mag hier basic tekst/HTML zetten (wordt gesanitized).</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Opslaan'); ?>
        </form>

        <hr/>

        <h2>Shortcode</h2>

        <?php if ($active): ?>
            <p>Popup is actief. Gebruik deze shortcode op de frontend:</p>
            <code>[ae_popup]</code>
        <?php else: ?>
            <p>Popup is niet actief. Zet “Activeer popup” aan om de shortcode te gebruiken.</p>
            <code>[ae_popup]</code>
        <?php endif; ?>

    </div>
    <?php
}

/**
 * 5) Frontend shortcode
 * - Rendert enkel als actief
 */
add_shortcode('ae_popup', 'ae_popup_shortcode');
function ae_popup_shortcode($atts = [])
{
    $active = (int)get_option('ae_popup_active', 0);
    if (!$active) return '';

    $icon_id = (int)get_option('ae_popup_icon_id', 0);
    $title = (string)get_option('ae_popup_title', '');
    $body = (string)get_option('ae_popup_body', '');

    $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'medium') : '';

    ob_start(); ?>
    <div class="ae-popup">
        <div class="ae-popup-wrap">
            <div class="ae-popup-icon">
                <?php if ($icon_url): ?>
                    <img src="<?php echo esc_url($icon_url); ?>" alt=""/>
                <?php else: ?>
                    <span>!</span>
                <?php endif; ?>
            </div>
            <div class="ae-popup-content">
                <?php if ($title): ?>
                    <strong><?php echo esc_html($title); ?></strong>
                <?php endif; ?>

                <div class="ae-popup-body">
                    <?php echo wp_kses_post(wpautop($body)); ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
