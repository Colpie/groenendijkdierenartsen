<?php
/**
 * Uninstall Headers Security Advanced & HSTS WP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'hsts_max_age' );
delete_option( 'hsts_include_subdomains' );
delete_option( 'hsts_preload' );
delete_option( 'hsts_csp' );
delete_option( 'hsts_pp' );
delete_option( 'hsts_x_frame_options_url_field' );
delete_option( 'hsts_x_frame_options' );

delete_option( 'hsts_csp_report_uri' );
delete_option( 'disable_hsts_header' );
delete_option( 'disable_csp_header' );
delete_option( 'disable_x_content_type_options_header' );
delete_option( 'disable_x_frame_options_header' );



$pro_key = get_option( 'hsts_pro_license_key', '' );
if ( ! empty( $pro_key ) ) {
    
    $site_url = get_site_url();
    $salt     = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'hsts_pro_fallback_' . DB_NAME;
    $instance = substr( hash( 'sha256', $site_url . '|' . $salt ), 0, 32 );

    wp_remote_post( 'https://api.lemonsqueezy.com/v1/licenses/deactivate', array(
        'timeout' => 10,
        'headers' => array(
            'Accept'       => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body'    => array(
            'license_key' => $pro_key,
            'instance_id' => $instance,
        ),
    ) );
}

// Pro options cleanup
$pro_options = array(
    'hsts_pro_license_key', 'hsts_pro_license_status', 'hsts_pro_license_expiry',
    'hsts_pro_license_token', 'hsts_pro_license_domain', 'hsts_pro_license_fail_count',
    'hsts_pro_license_last_check', 'hsts_pro_files_hash',
    'hsts_pro_scan_schedule', 'hsts_pro_alert_email', 'hsts_pro_last_scan',
    'hsts_pro_security_score', 'hsts_pro_scan_history', 'hsts_pro_csp_violations',
    'hsts_pro_previous_score', 'hsts_pro_previous_headers',
    'hsts_pro_generated_csp', 'hsts_pro_generated_csp_report_only',
    'hsts_pro_discovered_resources', 'hsts_pro_csp_report_only_active',
    'hsts_pro_csp_report_only_since',
    'hsts_pro_webhook_slack_url', 'hsts_pro_webhook_discord_url',
    'hsts_pro_webhook_teams_url', 'hsts_pro_webhook_custom_url',
    'hsts_pro_webhook_notify_on',
);
foreach ( $pro_options as $opt ) {
    delete_option( $opt );
}
wp_clear_scheduled_hook( 'hsts_pro_weekly_scan' );