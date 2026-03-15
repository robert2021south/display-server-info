<?php
/**
 * Plugin Name: Display Server Info
 * Description: This plugin including PHP, MySQL, server software,and OS details in the WordPress admin dashboard.It also provides options to show the information in the admin bar and footer.
 *
 * Version: 2.2.0
 * Author: RobertWP
 * Author URI: https://robertwp.com
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: display-server-info
 * Domain Path: /languages
 */
namespace RobertWP\DisplayServerInfo;

use Random\RandomException;

if ( !defined('ABSPATH') ) {
    exit;
}

class DisplayServerInfo {

    const VERSION = '2.2.0';
    private string $plugin_url;

    private string $apiBaseUrl = 'https://api.robertwp.com/api';

    public function __construct() {
        $this->plugin_url = plugin_dir_url(__FILE__);

        // Actions
        add_action('admin_enqueue_scripts', [$this, 'handle_css_js']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        add_action('admin_bar_menu', [$this, 'add_admin_bar_info'], 100);
        add_action('admin_footer', [$this, 'add_footer_info']);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('wp_ajax_disi_save_settings', [$this, 'handle_ajax_request']);
        add_action('wp_ajax_disi_get_phpinfo', [$this, 'phpinfo_ajax_handler']);
        add_action('wp_ajax_disi_send_feedback', [$this, 'handle_feedback_submission']);
        add_shortcode('disi_server_info', [$this, 'add_shortcode']);

        // Filters
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_action_links']);
        add_filter('plugin_row_meta', [$this, 'add_meta_links'], 10, 2);
        add_action('init', [$this, 'maybe_init_site_uuid']);
    }

    /**
     * Initialize site UUID if not exists (runs on every page load, but only creates once)
     * @throws RandomException
     */
    public function maybe_init_site_uuid(): void {
        // Just ensure UUID exists, no need to return anything
        $this->get_site_uuid();
    }

    function add_meta_links($links, $file) {
        if ($file === plugin_basename(__FILE__)) {
            $links[] = '<a href="http://ko-fi.com/robertsouth" target="_blank">❤</a>';
        }
        return $links;
    }

    public function handle_css_js($hook): void
    {
        wp_register_style('disi-common-style-min', $this->plugin_url . 'assets/css/disi-common-style.min.css', [], self::VERSION );
        wp_register_style('disi-dashboard-style-min', $this->plugin_url . 'assets/css/disi-dashboard-style.min.css', [], self::VERSION );
        wp_register_style('disi-more-style-min', $this->plugin_url . 'assets/css/disi-more-style.min.css', [], self::VERSION );

        wp_register_style('disi-bootstrap-min', $this->plugin_url.'assets/css/bootstrap.min.css', array(), '3.3.5', 'all');


        wp_register_script( 'disi-common-min', $this->plugin_url . 'assets/js/disi-common.min.js', array( 'jquery' ), self::VERSION, true );
        wp_register_script( 'disi-ajax-handle-min', $this->plugin_url . 'assets/js/disi-ajax-handle.min.js', array( 'jquery' ), self::VERSION, true );

        wp_register_script('disi-bootstrap-min', $this->plugin_url . 'assets/js/bootstrap.min.js', array('jquery'), '3.3.5', true);

        //
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script('disi-bootstrap-min');
        wp_enqueue_script( 'disi-common-min' );

        //
        // For Dashboard
        if ($hook === 'index.php') {
            wp_enqueue_style('disi-dashboard-style-min');
        }

        // For more page
        if ($hook === 'settings_page_display_server_info') {
            wp_enqueue_style('disi-bootstrap-min' );
            wp_enqueue_style( 'disi-more-style-min' );

            wp_enqueue_script( 'disi-ajax-handle-min' );
        }

        wp_enqueue_style( 'disi-common-style-min' );

        // Localize script for AJAX
        wp_localize_script('disi-ajax-handle-min', 'disiAjaxObject', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('disi_save_settings_nonce'),
            'disiLocalizeText' => $this->load_msg()
        ]);

    }

    public function add_dashboard_widget(): void
    {
        if (get_option('disi_dashboard_widget_enable', '1') === '1') {
            wp_add_dashboard_widget('disi_dashboard_widget', __('Server Information', 'display-server-info'), [$this, 'display_dashboard_widget']);
        }
    }

    public function display_dashboard_widget(): void
    {
        $nonce = wp_create_nonce('options_general_page_nonce');

        $server_info = $this->get_server_info();

        echo '<div class="disi-display-board"><ul>';
        $i=0;
        foreach ($server_info as $arr) {
            $class = $i%2==0 ? '' : 'class=disi-line-gray-bg';
            echo '<li '.esc_attr($class).'><span>' . esc_html($arr['text']) . ':</span> '.esc_html($arr['value']).'</li>';
            $i++;
        }

        echo '<li><a href="'.esc_url(admin_url( "options-general.php?page=display_server_info")).'">'.esc_html(__('More','display-server-info')).'</a></li></ul></div>';

        // Add action for extending the widget
        do_action('disi_dashboard_widget_after_content');
    }

    public function add_admin_bar_info($wp_admin_bar): void
    {
        if (!current_user_can('manage_options') || get_option('disi_admin_bar_enable', '0') !== '1') {
            return;
        }

        $server_info = $this->get_server_info();
        $info = sprintf('PHP: %s | MySQL: %s | Server: %s', $server_info['php_version']['value'], $server_info['mysql_version']['value'], $server_info['server_software']['value']);

        $wp_admin_bar->add_node([
            'id' => 'disi_display_server_info',
            'title' => esc_html($info),
            'href'  => false,
            'parent' => 'top-secondary',
            'meta' => ['class' => 'disi-server-info']
        ]);
    }

    public function add_footer_info(): void
    {
        if (get_option('disi_footer_enable', '0') === '1') {
            $server_info = $this->get_server_info();
            echo '<div class="disi-admin-footer-info">';
            echo sprintf(
                esc_html($server_info['php_version']['text']).": %s | ".esc_html($server_info['mysql_version']['text']).": %s | ".esc_html($server_info['server_software']['text']).": %s",
                esc_html($server_info['php_version']['value']),
                esc_html($server_info['mysql_version']['value']),
                esc_html($server_info['server_software']['value'])
            );
            echo '</div>';
        }
    }

    public function add_settings_page(): void
    {
        add_submenu_page(
                'options-general.php',
                __('More - Display Server Info', 'display-server-info'),
                __('Display Server Info', 'display-server-info'),
                'manage_options',
                'display_server_info',
                [$this, 'render_settings_page']
            );
    }

    public function render_settings_page(): void
    {

        if (!current_user_can('manage_options')) {
            wp_die(esc_html(__('Permission denied', 'display-server-info')));
        }

//        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'options_general_page_nonce')) {
//            wp_die(__('Nonce verification failed.', 'display-server-info'));
//        }

        include  plugin_dir_path(__FILE__) . 'templates/settings-page.php';
    }

    public function handle_ajax_request()
    {

        if (!(is_admin()
            && defined('DOING_AJAX')
            && DOING_AJAX
            && isset($_POST['action'])
            && sanitize_text_field(wp_unslash($_POST['action'])) === 'disi_save_settings')) {
            wp_send_json_error(__('Invalid request', 'display-server-info'), 400);
            wp_die();
        }

        if (!is_user_logged_in()) {
            wp_send_json_error(__('Your login has expired, please log in again!', 'display-server-info'), 401);
            wp_die();
        }

        if (!check_ajax_referer('disi_save_settings_nonce', 'nonce', false)) {
            wp_send_json_error(__('Illegal request', 'display-server-info'), 418);
            wp_die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'display-server-info'), 403);
            wp_die();
        }

        update_option('disi_admin_bar_enable', isset($_POST['disi_enable_admin_bar']) ? '1' : '0');
        update_option('disi_dashboard_widget_enable', isset($_POST['disi_enable_widget']) ? '1' : '0');
        update_option('disi_footer_enable', isset($_POST['disi_enable_footer']) ? '1' : '0');
        update_option('disi_shortcode_enable', isset($_POST['disi_enable_shortcode']) ? '1' : '0');


        wp_send_json_success(__('Settings saved successfully', 'display-server-info'));
        wp_die();
    }

    public function add_shortcode()
    {
        if (get_option('disi_shortcode_enable', '0') === '1') {

            $server_info = $this->get_server_info();

            $output = '<ul>';
            foreach ($server_info as $arr) {
                $output .= "<li><strong>{$arr['text']}:</strong> {$arr['value']}</li>";
            }
            $output .= '</ul>';

            return $output;

        }
    }

    public function phpinfo_ajax_handler(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'display-server-info')), 403);
        }
        ob_start();
        phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
        $phpinfo = ob_get_clean();

        wp_send_json_success(array('phpinfo' => $phpinfo));
    }

    public function add_action_links($links)
    {
        $settings_link = '<a href="' . admin_url('options-general.php?page=display_server_info') . '">' . __('Settings', 'display-server-info') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Handle feedback submission from the plugin settings page
     * @throws RandomException
     */
    public function handle_feedback_submission(): void
    {

        // Security checks
        if (!check_ajax_referer('disi_save_settings_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed', 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied', 403);
        }

        // Get access token first
        $token = $this->get_access_token();
        if (!$token) {
            wp_send_json_error('Unable to obtain access token. Please try again later.', 500);
            return;
        }

        // Get and validate input
        $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : 0;
        $feedback_type = isset($_POST['feedback_type']) ? sanitize_text_field(wp_unslash($_POST['feedback_type'])) : 'general';
        $feedback_message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
        $user_email = isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '';

        if ($rating === 0 && empty($feedback_message)) {
            wp_send_json_error('Please provide either a rating or a message', 400);
        }

        // Prepare data for API (now using site_uuid, not site_url)
        $data = [
            'rating' => $rating,
            'feedback_type' => $feedback_type,
            'message' => $feedback_message,
            'user_email' => $user_email,
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'locale' => get_locale(),
            'meta' => null
        ];

        // Send to Laravel API with token
        $response = wp_remote_post($this->apiBaseUrl . '/feedback', [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'body' => json_encode($data),
        ]);

        if (is_wp_error($response)) {
            //error_log('Feedback submission failed: ' . $response->get_error_message());
            wp_send_json_error('Failed to send feedback', 500);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code >= 200 && $response_code < 300) {
            wp_send_json_success('Thank you for your feedback!');
        } else {
            //error_log('Feedback API returned error: ' . $response_code . ' - ' . wp_remote_retrieve_body($response));
            wp_send_json_error('Feedback service error', 500);
        }
    }

    /* ===================  Private method  ====================== */

    private function get_server_info(): array
    {
        global $wpdb;

        return [
            'php_version' => [ 'text' => __('PHP Version', 'display-server-info') , 'value' => PHP_VERSION ],
            'mysql_version' => [ 'text' => __('MySQL Version', 'display-server-info') , 'value' => sanitize_text_field($wpdb->db_version()) ],
            'server_software' => [ 'text' => __('Server Software', 'display-server-info') , 'value' => (isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '') ],
            'server_ip' => [ 'text' => __('Server IP', 'display-server-info') , 'value' => (isset($_SERVER['SERVER_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR'])) : '') ],
            'server_hostname' => [ 'text' => __('Server Hostname', 'display-server-info') , 'value' => (function_exists( 'php_uname' ) ? sanitize_text_field(wp_unslash(php_uname( 'n' ))) : '') ],
            'operating_system' => [ 'text' => __('Operating System', 'display-server-info') , 'value' => PHP_OS ]
        ];
    }

    private function load_msg(): array
    {
        return [
            "settingsSavedText"  => __( 'Settings saved successfully', 'display-server-info' ),
            "errorOccurredText"  => __( 'An error occurred when saving the settings', 'display-server-info' ),
            "invalidRequestText"  => __( 'Invalid request', 'display-server-info' ),
            "loginTimeoutText"  => __( 'Login timeout, please log in again', 'display-server-info' ),
            "illegalRequestText"  => __( 'Illegal request', 'display-server-info' ),
            "permissionDeniedText"  => __( 'Permission denied', 'display-server-info' )
        ];
    }

    /**
     * Get access token from Laravel API
     *
     * @return string|null
     * @throws RandomException
     */
    private function get_access_token(): ?string
    {
        $siteUuid = $this->get_site_uuid();
        $token_option = 'disi_api_token_' . md5($siteUuid);

        // Check for cached token
        $cached_token = get_transient($token_option);
        if ($cached_token) {
            return $cached_token;
        }

        // Request new token
        $response = wp_remote_post($this->apiBaseUrl . '/auth/issue-feedback-token', [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'site_uuid' => $siteUuid,
                'plugin_slug' => 'display-server-info',
                'plugin_version' => self::VERSION,
            ]),
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['status']) && $body['status'] === 'success' && isset($body['data']['token'])) {
            $token = $body['data']['token'];
            set_transient($token_option, $token, MINUTE_IN_SECONDS * 4);
            return $token;
        }

        return null;
    }

    /**
     * Get or generate site UUID
     *
     * @return string
     * @throws RandomException
     */
    private function get_site_uuid(): string
    {
        $option_name = 'disi_site_uuid';
        $uuid = get_option($option_name);

        if (empty($uuid)) {
            $uuid = $this->generate_uuid_v4();
            update_option($option_name, $uuid, true);

            // 可选：记录日志便于调试
            //error_log('Display Server Info: New site UUID generated');
        }

        return $uuid;
    }

    /**
     * Generate a valid UUID v4
     *
     * @return string
     * @throws RandomException
     */
    private function generate_uuid_v4(): string
    {
        $data = random_bytes(16);

        // Set version to 0100 (UUID v4)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set variant to 10xx (RFC 4122)
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Format as UUID
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}

new DisplayServerInfo();
