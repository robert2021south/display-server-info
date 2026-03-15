<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$disi_option_names = array(
    'disi_site_uuid',
    'disi_dashboard_widget_enable',
    'disi_admin_bar_enable',
    'disi_footer_enable',
    'disi_shortcode_enable'
);

foreach ( $disi_option_names as $disi_option_name ) {
    delete_option( sanitize_key( $disi_option_name ) );
}
