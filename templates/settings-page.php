<?php

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$disi_enabled_admin_bar = get_option('disi_admin_bar_enable', '0');
$disi_enabled_widget = get_option('disi_dashboard_widget_enable', '1');
$disi_enabled_footer = get_option('disi_footer_enable', '0');
$disi_enabled_shortcode = get_option('disi_shortcode_enable', '1');

global $wpdb;

$disi_clientVersion = null;
if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
    $disi_clientVersion = $wpdb->dbh->client_info;
}

if(empty($wpdb->collation)){
    $disi_collation = disi_get_database_collation($wpdb);
}else{
    $disi_collation = $wpdb->collation;
}

function disi_get_database_collation($wpdb) {

    $cache_key = 'disi_db_collation';
    $collation = wp_cache_get($cache_key, 'disi');

    if ($collation === false) {
        $collation = $wpdb->get_var($wpdb->prepare(
            "SELECT DEFAULT_COLLATION_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = %s",
            $wpdb->dbname
        ));

        wp_cache_set($cache_key, $collation, 'disi', 12 * HOUR_IN_SECONDS);
    }

    return $collation;
}

$disi_serverInfo = [
        ['text'  => __('Hosting Server Info','display-server-info'),
         'value' => [
                        ['text'=>__('Operating System','display-server-info'),'value'=>esc_html(PHP_OS)],
                        ['text'=>__('Hostname','display-server-info') ,'value' =>  esc_html(sanitize_text_field(wp_unslash(php_uname( 'n' ))))],
                        ['text'=>__('Server IP','display-server-info') ,'value' => (isset($_SERVER['SERVER_ADDR'])?esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR']))):'')],
                        ['text'=>__('Protocol','display-server-info')  ,'value'=> (isset($_SERVER['SERVER_PROTOCOL']) ? esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_PROTOCOL']))) : '')],
                        ['text'=>__('Server Software','display-server-info')  ,'value'=> (isset($_SERVER['SERVER_SOFTWARE']) ? esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']))):'')],
                        ['text'=>__('Web Port','display-server-info')  ,'value'=> (isset($_SERVER['SERVER_PORT'])? esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_PORT']))) :'')],
                        ['text'=>__('CGI Version','display-server-info')  ,'value'=> (isset($_SERVER['GATEWAY_INTERFACE']) ? esc_html(sanitize_text_field(wp_unslash($_SERVER['GATEWAY_INTERFACE']))) :'')]
                    ],
        ],

        ['text' => __('PHP Info','display-server-info'),
         'value' => [
                         ['text'=>__('PHP Version','display-server-info'),'value' => esc_html(PHP_VERSION)],
                         ['text'=>__('Memory Limit','display-server-info'),'value' => (function_exists('ini_get') ? ini_get( 'memory_limit' ) : '')],
                         ['text'=>__('Max Execution Time','display-server-info'),'value' => (function_exists('ini_get') ? ini_get('max_execution_time') : '')],
                         ['text'=>__('Upload Max Filesize','display-server-info'),'value' => (function_exists('ini_get') ? ini_get('upload_max_filesize') : '')],
                         ['text'=>__('Max File Uploads','display-server-info'),'value' => (function_exists('ini_get') ? ini_get('max_file_uploads') : '')]
                    ]
        ],

        ['text'=>__('Database Info','display-server-info'),
         'value'=>[
                        ['text'=>__('Server version','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->db_version()))],
                        ['text'=>__('Client version','display-server-info'),'value' => esc_html(sanitize_text_field($disi_clientVersion))],
                        ['text'=>__('Database host','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->dbhost))],
                        ['text'=>__('Database username','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->dbuser))],
                        ['text'=>__('Database name','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->dbname))],
                        ['text'=>__('Table prefix','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->prefix))],
                        ['text'=>__('Database charset','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->charset))],
                        ['text'=>__('Database collation','display-server-info'),'value' => esc_html(sanitize_text_field($disi_collation))]
                    ]
        ],
];
?>
<p>&nbsp;</p>
<div class="container-fluid">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <div class="tabbable" id="tabs-104416">
                <ul class="nav nav-tabs">
                    <li <?php echo isset($_SERVER['HTTP_REFERER']) && strpos(sanitize_text_field(sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER']))),'plugins.php')>0?'':'class="active"';?>>
                        <a href="#panel-280630" data-toggle="tab" class="glyphicon glyphicon-info-sign"><?php esc_html_e('Server Info','display-server-info')?></a>
                    </li>
                    <li <?php echo isset($_SERVER['HTTP_REFERER']) && strpos(sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])),'plugins.php')>0?'class="active"':'';?>>
                        <a href="#panel-81025" data-toggle="tab" class="glyphicon glyphicon-cog"><?php esc_html_e('Settings','display-server-info')?></a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane <?php echo isset($_SERVER['HTTP_REFERER']) && strpos(sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])),'plugins.php')>0?'':'active';?>" id="panel-280630">
                        <p>&nbsp;</p>
                        <p>
                            <?php esc_html_e('This page provides detailed information about the server environment, PHP configuration, and database setup. It includes essential data such as server specifications, PHP version and settings, and database connection details to help with troubleshooting and system optimization.', 'display-server-info');?>
                        </p>
                        <p>&nbsp;</p>

                        <div class="row clearfix">
                            <?php foreach ($disi_serverInfo as $disi_k=>$disi_data): ?>
                                <div class="col-md-4 column">
                                    <div class="list-group">
                                        <div class="list-group-item active"><?php echo esc_html($disi_data['text']); ?></div>
                                        <?php
                                        $disi_i = 0;
                                        foreach ($disi_data['value'] as $disi_value):
                                            $disi_bgClass = ($disi_i % 2 == 0) ? '' : 'disi-line-gray-bg';
                                            $disi_i++;
                                            ?>
                                            <div class="list-group-item <?php echo esc_attr($disi_bgClass); ?>">
                                                    <span><?php echo esc_html($disi_value['text']); ?></span> <?php echo esc_html($disi_value['value']); ?>&nbsp;
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if($disi_k==1):?>
                                            <div class="list-group-item">
                                                <button id="btn-phpinfo-output" class="btn btn-default btn-info btn-block" type="button">phpinfo()</button>
                                            </div>
                                        <?php endif;?>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="tab-pane <?php echo isset($_SERVER['HTTP_REFERER']) && strpos(sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])),'plugins.php')>0?'active':'';?>" id="panel-81025">
                        <p>&nbsp;</p>
                        <p>
                            <?php esc_html_e('This page allows you to configure the display of server information in specific locations on your WordPress site. You can easily choose whether to show server information in the following areas: - WordPress Dashboard - Admin Bar - Website Footer With flexible display options, you can customize how and where server information is presented to enhance convenience and manageability.','display-server-info');?>
                        </p>
                        <p>&nbsp;</p>
                        <div class="row clearfix">
                            <div class="col-md-12 column">
                                <form method="post" id="disi_setting_form"  action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                                    <?php wp_nonce_field('disi_save_settings'); ?>
                                    <fieldset>
                                        <legend></legend>
                                        <input type="hidden" name="action" value="disi_save_settings" />
                                        <div class="checkbox">
                                            <label class="switch">
                                                <input type="checkbox" id="disi_enable_admin_bar" name="disi_enable_admin_bar" value="1" <?php checked($disi_enabled_admin_bar, '1'); ?> />
                                                <span class="slider round"></span>
                                            </label>
                                            <?php esc_html_e('Show server info in admin bar','display-server-info');?>
                                        </div>

                                        <div class="checkbox disi-line-gray-bg">
                                            <label class="switch">
                                                <input type="checkbox" id="disi_enable_widget" name="disi_enable_widget" value="1"  <?php checked($disi_enabled_widget, '1'); ?> />
                                                <span class="slider round"></span>
                                            </label>
                                            <?php esc_html_e('Show server info as dashboard widget','display-server-info');?>
                                        </div>

                                        <div class="checkbox">
                                            <label class="switch">
                                                <input type="checkbox" id="disi_enable_footer" name="disi_enable_footer" value="1"  <?php checked($disi_enabled_footer, '1'); ?> />
                                                <span class="slider round"></span>
                                            </label>
                                            <?php esc_html_e('Show server info in footer','display-server-info');?>
                                        </div>

                                        <div class="checkbox disi-line-gray-bg">
                                            <label class="switch">
                                                <input type="checkbox" id="disi_enable_shortcode" name="disi_enable_shortcode" value="1"  <?php checked($disi_enabled_shortcode, '1'); ?> />
                                                <span class="slider round"></span>
                                            </label>
                                            <?php esc_html_e('Enable the Shortcode. Use the [disi_server_info] shortcode to show server information on a post or page.','display-server-info');?>
                                        </div>
                                    </fieldset>
                                </form>

                                <hr>

                                <h3><?php esc_html_e('Send Feedback', 'display-server-info'); ?></h3>

                                <p>
                                    <?php esc_html_e('Found a bug or have a suggestion? Send us feedback.', 'display-server-info'); ?>
                                </p>

                                <form id="disi_feedback_form">

                                    <div class="form-group">
                                        <label><?php esc_html_e('Rating', 'display-server-info'); ?></label>
                                        <div class="wp-rating" role="radiogroup" aria-label="Rate this item" id="rating-container">
                                            <button class="star" data-value="5" aria-label="5 stars" type="button"></button>
                                            <button class="star" data-value="4" aria-label="4 stars" type="button"></button>
                                            <button class="star" data-value="3" aria-label="3 stars" type="button"></button>
                                            <button class="star" data-value="2" aria-label="2 stars" type="button"></button>
                                            <button class="star" data-value="1" aria-label="1 star" type="button"></button>
                                            <input type="hidden" name="rating" id="rating-value" value="0">
                                        </div>
                                        <p class="description" style="margin-top: 5px;">
                                            <?php esc_html_e('Click a star to rate (half-star support: click left/right side)', 'display-server-info'); ?>
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <label><?php esc_html_e('Type', 'display-server-info'); ?></label>

                                        <select id="disi_feedback_type" class="form-control">
                                            <option value="bug">Bug</option>
                                            <option value="feature">Feature Request</option>
                                            <option value="improvement">Improvement</option>
                                            <option value="general" selected>General</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label><?php esc_html_e('Message', 'display-server-info'); ?></label>
                                        <textarea id="disi_feedback_message" class="form-control" rows="4"
                                                  placeholder="<?php esc_attr_e('What would make this plugin perfect for you?', 'display-server-info'); ?>"
                                        ></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label><?php esc_html_e('Email (optional)', 'display-server-info'); ?></label>
                                        <input type="email" id="disi_feedback_email" class="form-control"
                                               placeholder="<?php esc_attr_e('your@email.com', 'display-server-info'); ?>">

                                        <!-- 隐私说明 + 链接 -->
                                        <div class="feedback-privacy-notice" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-left: 3px solid #46b450;">
                                            <p style="margin: 0 0 5px 0;">
                                                <strong>🔒 <?php esc_html_e('Your privacy matters', 'display-server-info'); ?></strong>
                                            </p>
                                            <p style="margin: 0; font-size: 13px; color: #555;">
                                                <?php esc_html_e('We only use your email to respond to this specific feedback.', 'display-server-info'); ?>
                                                <?php esc_html_e('We never send marketing emails or share your information.', 'display-server-info'); ?>
                                                <a href="https://robertwp.com/privacy-policy" target="_blank" style="color: #2271b1;">
                                                    <?php esc_html_e('Learn more', 'display-server-info'); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>

                                    <button type="button" id="disi_send_feedback" class="button button-primary">
                                        <?php esc_html_e('Send Feedback', 'display-server-info'); ?>
                                    </button>

                                </form>

                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
<div class="disi-modal-overlay disi-hidden" id="spinnerModal">
    <div class="disi-loader"></div>
</div>



