<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$disi_option_names = array(
    'disi_site_uuid',
    'disi_install_date',
    'disi_dashboard_widget_enable',
    'disi_admin_bar_enable',
    'disi_footer_enable',
    'disi_shortcode_enable'
);

foreach ( $disi_option_names as $disi_option_name ) {
    delete_option( sanitize_key( $disi_option_name ) );
}

global $wpdb;

$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE %s 
            OR option_name LIKE %s",
        $wpdb->esc_like('_transient_disi_api_token_') . '%',
        $wpdb->esc_like('_transient_timeout_disi_api_token_') . '%'
    )
);