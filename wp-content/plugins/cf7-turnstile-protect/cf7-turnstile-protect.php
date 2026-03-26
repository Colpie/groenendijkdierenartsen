<?php
/**
 * Plugin Name: CF7 Turnstile Protect
 * Description: Voegt Cloudflare Turnstile toe aan Contact Form 7 formulieren, met per formulier instellingen zoals managed, non-interactive of invisible.
 * Version: 1.1.1
 * Author: Thomas Colpaert
 * Text Domain: cf7-turnstile-protect
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF7_Turnstile_Protect {

    private $option_name = 'cf7_turnstile_protect_settings';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        add_filter('wpcf7_form_elements', array($this, 'inject_turnstile_into_cf7_form'));
        add_filter('wpcf7_validate', array($this, 'validate_turnstile'), 20, 2);
        add_filter('wpcf7_validate_*', array($this, 'validate_turnstile'), 20, 2);
    }

    public function get_settings() {
        $defaults = array(
            'site_key'          => '',
            'secret_key'        => '',
            'theme'             => 'light',
            'size'              => 'normal',
            'enable_for_logged' => '1',
            'error_message'     => __('Bevestig dat je geen robot bent.', 'cf7-turnstile-protect'),
            'forms_config'      => array(),
        );

        $settings = get_option($this->option_name, array());

        if (!isset($settings['forms_config']) || !is_array($settings['forms_config'])) {
            $settings['forms_config'] = array();
        }

        return wp_parse_args($settings, $defaults);
    }

    public function add_admin_menu() {
        add_options_page(
            __('CF7 Turnstile Protect', 'cf7-turnstile-protect'),
            __('CF7 Turnstile Protect', 'cf7-turnstile-protect'),
            'manage_options',
            'cf7-turnstile-protect',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting(
            'cf7_turnstile_protect_group',
            $this->option_name,
            array($this, 'sanitize_settings')
        );

        add_settings_section(
            'cf7_turnstile_protect_main_section',
            __('Cloudflare Turnstile instellingen', 'cf7-turnstile-protect'),
            '__return_false',
            'cf7-turnstile-protect'
        );

        add_settings_field(
            'site_key',
            __('Site Key', 'cf7-turnstile-protect'),
            array($this, 'render_site_key_field'),
            'cf7-turnstile-protect',
            'cf7_turnstile_protect_main_section'
        );

        add_settings_field(
            'secret_key',
            __('Secret Key', 'cf7-turnstile-protect'),
            array($this, 'render_secret_key_field'),
            'cf7-turnstile-protect',
            'cf7_turnstile_protect_main_section'
        );

        add_settings_field(
            'theme',
            __('Globale theme', 'cf7-turnstile-protect'),
            array($this, 'render_theme_field'),
            'cf7-turnstile-protect',
            'cf7_turnstile_protect_main_section'
        );

        add_settings_field(
            'size',
            __('Globale size', 'cf7-turnstile-protect'),
            array($this, 'render_size_field'),
            'cf7-turnstile-protect',
            'cf7_turnstile_protect_main_section'
        );

        add_settings_field(
            'enable_for_logged',
            __('Ook voor ingelogde gebruikers', 'cf7-turnstile-protect'),
            array($this, 'render_enable_for_logged_field'),
            'cf7-turnstile-protect',
            'cf7_turnstile_protect_main_section'
        );

        add_settings_field(
            'error_message',
            __('Foutmelding', 'cf7-turnstile-protect'),
            array($this, 'render_error_message_field'),
            'cf7-turnstile-protect',
            'cf7_turnstile_protect_main_section'
        );

        add_settings_field(
            'forms_config',
            __('Per formulier instellingen', 'cf7-turnstile-protect'),
            array($this, 'render_forms_config_field'),
            'cf7-turnstile-protect',
            'cf7_turnstile_protect_main_section'
        );
    }

    public function sanitize_settings($input) {
        $output = array();

        $output['site_key'] = isset($input['site_key']) ? sanitize_text_field($input['site_key']) : '';
        $output['secret_key'] = isset($input['secret_key']) ? sanitize_text_field($input['secret_key']) : '';

        $allowed_themes = array('light', 'dark', 'auto');
        $output['theme'] = (isset($input['theme']) && in_array($input['theme'], $allowed_themes, true)) ? $input['theme'] : 'light';

        $allowed_sizes = array('normal', 'compact', 'flexible');
        $output['size'] = (isset($input['size']) && in_array($input['size'], $allowed_sizes, true)) ? $input['size'] : 'normal';

        $output['enable_for_logged'] = !empty($input['enable_for_logged']) ? '1' : '0';

        $output['error_message'] = isset($input['error_message']) && $input['error_message'] !== ''
            ? sanitize_text_field($input['error_message'])
            : __('Bevestig dat je geen robot bent.', 'cf7-turnstile-protect');

        $output['forms_config'] = array();

        if (!empty($input['forms_config']) && is_array($input['forms_config'])) {
            foreach ($input['forms_config'] as $form_id => $config) {
                $form_id = intval($form_id);

                if ($form_id <= 0 || !is_array($config)) {
                    continue;
                }

                $enabled = !empty($config['enabled']) ? '1' : '0';

                $allowed_modes = array('managed', 'non-interactive', 'invisible');
                $mode = (isset($config['mode']) && in_array($config['mode'], $allowed_modes, true))
                    ? $config['mode']
                    : 'managed';

                $output['forms_config'][$form_id] = array(
                    'enabled' => $enabled,
                    'mode'    => $mode,
                );
            }
        }

        return $output;
    }

    public function render_site_key_field() {
        $settings = $this->get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr($this->option_name); ?>[site_key]" value="<?php echo esc_attr($settings['site_key']); ?>" class="regular-text" />
        <?php
    }

    public function render_secret_key_field() {
        $settings = $this->get_settings();
        ?>
        <input type="password" name="<?php echo esc_attr($this->option_name); ?>[secret_key]" value="<?php echo esc_attr($settings['secret_key']); ?>" class="regular-text" />
        <?php
    }

    public function render_theme_field() {
        $settings = $this->get_settings();
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[theme]">
            <option value="light" <?php selected($settings['theme'], 'light'); ?>>Light</option>
            <option value="dark" <?php selected($settings['theme'], 'dark'); ?>>Dark</option>
            <option value="auto" <?php selected($settings['theme'], 'auto'); ?>>Auto</option>
        </select>
        <p class="description">Globale weergave voor zichtbare widgets.</p>
        <?php
    }

    public function render_size_field() {
        $settings = $this->get_settings();
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[size]">
            <option value="normal" <?php selected($settings['size'], 'normal'); ?>>Normal</option>
            <option value="compact" <?php selected($settings['size'], 'compact'); ?>>Compact</option>
            <option value="flexible" <?php selected($settings['size'], 'flexible'); ?>>Flexible</option>
        </select>
        <p class="description">Globale grootte voor zichtbare widgets.</p>
        <?php
    }

    public function render_enable_for_logged_field() {
        $settings = $this->get_settings();
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_for_logged]" value="1" <?php checked($settings['enable_for_logged'], '1'); ?> />
            <?php esc_html_e('Bescherm ook formulieren van ingelogde gebruikers', 'cf7-turnstile-protect'); ?>
        </label>
        <?php
    }

    public function render_error_message_field() {
        $settings = $this->get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr($this->option_name); ?>[error_message]" value="<?php echo esc_attr($settings['error_message']); ?>" class="regular-text" />
        <?php
    }

    private function get_cf7_forms() {
        if (!class_exists('WPCF7_ContactForm')) {
            return array();
        }

        return WPCF7_ContactForm::find(array(
            'posts_per_page' => -1,
        ));
    }

    public function render_forms_config_field() {
        $settings = $this->get_settings();
        $forms = $this->get_cf7_forms();

        if (empty($forms)) {
            echo '<p>Geen Contact Form 7 formulieren gevonden.</p>';
            return;
        }

        echo '<table class="widefat striped" style="max-width: 1000px;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th style="width: 80px;">Actief</th>';
        echo '<th>Formulier</th>';
        echo '<th style="width: 220px;">Mode</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($forms as $form) {
            $form_id = $form->id();
            $form_title = $form->title();

            $form_config = isset($settings['forms_config'][$form_id]) ? $settings['forms_config'][$form_id] : array(
                'enabled' => '0',
                'mode'    => 'managed',
            );

            $enabled = !empty($form_config['enabled']) ? '1' : '0';
            $mode = !empty($form_config['mode']) ? $form_config['mode'] : 'managed';

            echo '<tr>';

            echo '<td>';
            echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[forms_config][' . esc_attr($form_id) . '][enabled]" value="1" ' . checked($enabled, '1', false) . ' />';
            echo '</td>';

            echo '<td>';
            echo '<strong>' . esc_html($form_title) . '</strong><br>';
            echo '<small>ID: ' . esc_html($form_id) . '</small>';
            echo '</td>';

            echo '<td>';
            echo '<select name="' . esc_attr($this->option_name) . '[forms_config][' . esc_attr($form_id) . '][mode]">';
            echo '<option value="managed" ' . selected($mode, 'managed', false) . '>Managed (zichtbaar)</option>';
            echo '<option value="non-interactive" ' . selected($mode, 'non-interactive', false) . '>Non-interactive (subtiel)</option>';
            echo '<option value="invisible" ' . selected($mode, 'invisible', false) . '>Invisible (niet zichtbaar)</option>';
            echo '</select>';
            echo '</td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        echo '<p class="description" style="margin-top:10px;">';
        echo 'Gebruik <strong>Invisible</strong> voor bijvoorbeeld een nieuwsbrief in de footer. ';
        echo 'Gebruik <strong>Managed</strong> voor gewone contactformulieren waar zichtbare bescherming oké is.';
        echo '</p>';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('CF7 Turnstile Protect', 'cf7-turnstile-protect'); ?></h1>

            <p>
                <?php esc_html_e('Vul hier je Cloudflare Turnstile Site Key en Secret Key in. Daarna kan je per Contact Form 7 formulier instellen of Turnstile actief is en welke mode gebruikt wordt.', 'cf7-turnstile-protect'); ?>
            </p>

            <form method="post" action="options.php">
                <?php
                settings_fields('cf7_turnstile_protect_group');
                do_settings_sections('cf7-turnstile-protect');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_frontend_assets() {
        if (is_admin()) {
            return;
        }

        if (!class_exists('WPCF7')) {
            return;
        }

        $settings = $this->get_settings();

        if (empty($settings['site_key']) || empty($settings['secret_key'])) {
            return;
        }

        if (is_user_logged_in() && $settings['enable_for_logged'] !== '1') {
            return;
        }

        if (!$this->has_enabled_forms()) {
            return;
        }

        wp_enqueue_script(
            'cf-turnstile-api',
            'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
            array(),
            null,
            true
        );

        wp_enqueue_script(
            'cf7-turnstile-protect',
            plugin_dir_url(__FILE__) . 'assets/js/cf7-turnstile.js',
            array('cf-turnstile-api'),
            '1.1.0',
            true
        );

        wp_localize_script(
            'cf7-turnstile-protect',
            'cf7TurnstileProtect',
            array(
                'siteKey'    => $settings['site_key'],
                'theme'      => $settings['theme'],
                'size'       => $settings['size'],
                'formsConfig' => $this->get_enabled_forms_frontend_config(),
            )
        );
    }

    private function has_enabled_forms() {
        $settings = $this->get_settings();

        if (empty($settings['forms_config']) || !is_array($settings['forms_config'])) {
            return false;
        }

        foreach ($settings['forms_config'] as $form_config) {
            if (!empty($form_config['enabled']) && $form_config['enabled'] === '1') {
                return true;
            }
        }

        return false;
    }

    private function get_enabled_forms_frontend_config() {
        $settings = $this->get_settings();
        $frontend_config = array();

        if (empty($settings['forms_config']) || !is_array($settings['forms_config'])) {
            return $frontend_config;
        }

        foreach ($settings['forms_config'] as $form_id => $form_config) {
            if (empty($form_config['enabled']) || $form_config['enabled'] !== '1') {
                continue;
            }

            $frontend_config[(string) $form_id] = array(
                'enabled' => true,
                'mode'    => !empty($form_config['mode']) ? $form_config['mode'] : 'managed',
            );
        }

        return $frontend_config;
    }

    private function is_form_protected($form_id) {
        $settings = $this->get_settings();

        if (empty($settings['forms_config'][$form_id])) {
            return false;
        }

        return !empty($settings['forms_config'][$form_id]['enabled']) && $settings['forms_config'][$form_id]['enabled'] === '1';
    }

    private function get_form_mode($form_id) {
        $settings = $this->get_settings();

        if (empty($settings['forms_config'][$form_id]['mode'])) {
            return 'managed';
        }

        return $settings['forms_config'][$form_id]['mode'];
    }

    public function inject_turnstile_into_cf7_form($form) {
        $settings = $this->get_settings();

        if (empty($settings['site_key']) || empty($settings['secret_key'])) {
            return $form;
        }

        if (is_user_logged_in() && $settings['enable_for_logged'] !== '1') {
            return $form;
        }

        if (!class_exists('WPCF7_ContactForm')) {
            return $form;
        }

        $current_form = WPCF7_ContactForm::get_current();

        if (!$current_form) {
            return $form;
        }

        $form_id = (int) $current_form->id();

        if (!$this->is_form_protected($form_id)) {
            return $form;
        }

        if (strpos($form, 'cf7-turnstile-wrap') !== false) {
            return $form;
        }

        $mode = $this->get_form_mode($form_id);
        $visible_class = $mode === 'invisible' ? 'cf7-turnstile-wrap is-invisible' : 'cf7-turnstile-wrap';

        $turnstile_html = '
            <div class="' . esc_attr($visible_class) . '" data-turnstile-form-id="' . esc_attr($form_id) . '" data-turnstile-mode="' . esc_attr($mode) . '" style="margin: 15px 0;">
                <div class="cf7-turnstile-widget"></div>
            </div>
        ';

        if (preg_match('/(<input[^>]*type=["\']submit["\'][^>]*>)/i', $form)) {
            $form = preg_replace(
                '/(<input[^>]*type=["\']submit["\'][^>]*>)/i',
                $turnstile_html . '$1',
                $form,
                1
            );
        } elseif (preg_match('/(<button[^>]*type=["\']submit["\'][^>]*>.*?<\/button>)/is', $form)) {
            $form = preg_replace(
                '/(<button[^>]*type=["\']submit["\'][^>]*>.*?<\/button>)/is',
                $turnstile_html . '$1',
                $form,
                1
            );
        } else {
            $form .= $turnstile_html;
        }

        return $form;
    }

    public function validate_turnstile($result, $tags) {
        $settings = $this->get_settings();

        if (empty($settings['site_key']) || empty($settings['secret_key'])) {
            return $result;
        }

        if (is_user_logged_in() && $settings['enable_for_logged'] !== '1') {
            return $result;
        }

        if (!class_exists('WPCF7_Submission')) {
            return $result;
        }

        $submission = WPCF7_Submission::get_instance();

        if (!$submission) {
            return $result;
        }

        $contact_form = $submission->get_contact_form();

        if (!$contact_form) {
            return $result;
        }

        $form_id = (int) $contact_form->id();

        if (!$this->is_form_protected($form_id)) {
            return $result;
        }

        $posted_data = $submission->get_posted_data();
        $token = isset($posted_data['cf-turnstile-response']) ? sanitize_text_field($posted_data['cf-turnstile-response']) : '';

        if (empty($token)) {
            $result->invalidate('', $settings['error_message']);
            return $result;
        }

        $remote_ip = $this->get_user_ip();

        $response = wp_remote_post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            array(
                'timeout' => 15,
                'body'    => array(
                    'secret'   => $settings['secret_key'],
                    'response' => $token,
                    'remoteip' => $remote_ip,
                ),
            )
        );

        if (is_wp_error($response)) {
            $result->invalidate('', __('Turnstile verificatie mislukt. Probeer opnieuw.', 'cf7-turnstile-protect'));
            return $result;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['success'])) {
            $result->invalidate('', $settings['error_message']);
            return $result;
        }

        return $result;
    }

    private function get_user_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        );

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = sanitize_text_field(wp_unslash($_SERVER[$key]));

                if ($key === 'HTTP_X_FORWARDED_FOR' && strpos($ip, ',') !== false) {
                    $parts = explode(',', $ip);
                    $ip = trim($parts[0]);
                }

                return $ip;
            }
        }

        return '';
    }
}

new CF7_Turnstile_Protect();