<?php

/**
 * Plugin Name: Ovesio
 * Description: Get instant translations & contentn generator in over 27 languages, powered by the most advanced artificial intelligence technologies.
 * Version: 1.2.0
 * Author: Ovesio
 * Text Domain: ovesio
 * Author URI: https://ovesio.com
 * Tags: Ovesio, AI Translation, multilingual, translation, content generator, woocommerce product translations, automated translations
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OVESIO_PLUGIN_VERSION', '1.2.0');
define('OVESIO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OVESIO_ADMIN_DIR', OVESIO_PLUGIN_DIR . 'admin/');

// Composer files
require_once __DIR__ . '/vendor/autoload.php';

// Helper functions
require_once OVESIO_PLUGIN_DIR . 'functions.php';
require_once OVESIO_PLUGIN_DIR . 'callback.php';

// Action Buttons
require_once OVESIO_ADMIN_DIR . 'buttons.php';

// Views
require_once OVESIO_ADMIN_DIR . 'views/settings-page-header.php';
require_once OVESIO_ADMIN_DIR . 'views/settings-api-tab.php';
require_once OVESIO_ADMIN_DIR . 'views/settings-translation-tab.php';
require_once OVESIO_ADMIN_DIR . 'views/requests-list-tab.php';


add_action('admin_notices', function() {
    if ($message = get_transient('ovesio_error')) {
        echo '<div class="notice notice-error"><p>' . wp_kses_post($message) . '</p></div>';

        delete_transient('ovesio_error');
    }
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ovesio_plugin_action_links');
function ovesio_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=ovesio') . '">' . __('Settings', 'ovesio') . '</a>';
    array_unshift($links, $settings_link);

    return $links;
}

register_activation_hook(__FILE__, 'ovesio_create_table');
function ovesio_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'ovesio_list'; // tabel cu prefixul WP (ex: wp_ovesio_list)
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        resource VARCHAR(50) NOT NULL,
        resource_id INT(11) NOT NULL,
        content_id INT(11) DEFAULT NULL,
        lang VARCHAR(50) DEFAULT NULL,
        generate_description_id INT(11) DEFAULT NULL,
        generate_description_hash VARCHAR(50) DEFAULT NULL,
        generate_description_date DATETIME DEFAULT NULL,
        generate_description_status INT(11) DEFAULT '0',
        metatags_id INT(11) DEFAULT NULL,
        metatags_hash VARCHAR(50) DEFAULT NULL,
        metatags_date DATETIME DEFAULT NULL,
        metatags_status INT(11) DEFAULT '0',
        translate_id INT(11) DEFAULT NULL,
        translate_hash VARCHAR(50) DEFAULT NULL,
        translate_date DATETIME DEFAULT NULL,
        translate_status INT(11) DEFAULT '0',
        link VARCHAR(250) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        INDEX resource (resource),
        INDEX resource_id (resource_id),
        INDEX content_id (content_id),
        INDEX lang (lang),
        INDEX generate_description_id (generate_description_id),
        INDEX generate_description_hash (generate_description_hash),
        INDEX generate_description_date (generate_description_date),
        INDEX generate_description_status (generate_description_status),
        INDEX metatags_id (metatags_id),
        INDEX metatags_hash (metatags_hash),
        INDEX metatags_date (metatags_date),
        INDEX metatags_status (metatags_status),
        INDEX translate_id (translate_id),
        INDEX translate_hash (translate_hash),
        INDEX translate_date (translate_date),
        INDEX translate_status (translate_status),
        INDEX created_at (created_at)
  ) $charset_collate;";

    dbDelta($sql);
}


add_action('admin_menu', 'ovesio_admin_menu');
function ovesio_admin_menu()
{
    add_menu_page(
        __('Ovesio', 'ovesio'),
        __('Ovesio', 'ovesio'),
        'manage_options',
        'ovesio',
        'ovesio_settings_tabs',
        'dashicons-admin-site-alt3',
 );

    add_submenu_page(
        'ovesio',
        __('Settings', 'ovesio'),
        __('Settings', 'ovesio'),
        'manage_options',
        'ovesio_settings',
        'ovesio_settings_tabs'
 );

    add_submenu_page(
        'ovesio',
        __('Requests List', 'ovesio'),
        __('Requests List', 'ovesio'),
        'manage_options',
        'ovesio_requests',
        'ovesio_requests_list_page',
 );

    // Elimină submeniul automat adăugat
    remove_submenu_page('ovesio', 'ovesio');
}

// Register settings
add_action('admin_init', 'ovesio_register_settings');
function ovesio_register_settings()
{
    if (!function_exists('pll_languages_list')) {
        // Deactivate the plugin
        deactivate_plugins(plugin_basename(__FILE__));

        set_transient('ovesio_error', '<strong>Ovesio</strong> requires <a href="https://wordpress.org/plugins/polylang/" target="_blank"><b>Polylang</b></a>. Works with both Pro or Free version', 120);

        if(isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        return;
    }

    register_setting('ovesio_api', 'ovesio_api_settings', 'ovesio_sanitize_api_options');
    register_setting('ovesio_settings', 'ovesio_options', 'ovesio_sanitize_options');
}

// Register Assets
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script(
        'ovesio-script',
        plugin_dir_url(__FILE__) . 'assets/js/admin.js',
        ['jquery'],
        OVESIO_PLUGIN_VERSION,
        true
    );

    wp_enqueue_style(
        'ovesio-style',
        plugin_dir_url(__FILE__) . 'assets/css/admin.css',
        [],
        OVESIO_PLUGIN_VERSION
    );
});

// Add page loader
if (is_admin()) {
    add_action('admin_footer', function () {
        echo "<div class=\"ovesio-loader-overlay-container\" style=\"display:none;\">
            <svg class=\"loader-overlay\" width=\"60\" height=\"60\" viewBox=\"0 0 50 50\">
                <defs>
                    <linearGradient id=\"gradient\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\">
                        <stop offset=\"0%\" stop-color=\"#000\"></stop>
                        <stop offset=\"100%\" stop-color=\"#000\"></stop>
                    </linearGradient>
                </defs>
                <circle cx=\"25\" cy=\"25\" r=\"20\" fill=\"none\" stroke=\"url(#gradient)\" stroke-width=\"4\" stroke-dasharray=\"31.4 31.4\">
                    <animateTransform attributeName=\"transform\" type=\"rotate\" from=\"0 25 25\" to=\"360 25 25\" dur=\"1s\" repeatCount=\"indefinite\"></animateTransform>
                </circle>
                <text x=\"25\" y=\"28\" text-anchor=\"middle\" font-size=\"10\" fill=\"#000\" font-weight=\"bold\"></text>
            </svg>
        </div>";
    });
}