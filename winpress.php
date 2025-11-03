<?php
/**
 * Plugin Name: Winpress
 * Description: Winpress v1.1.1 - Lightweight performance optimizer (fixed, stable).
 * Version: 1.1.1
 * Author: Agus Kristanto
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WINPRESS_PATH', plugin_dir_path(__FILE__));
define('WINPRESS_URL', plugin_dir_url(__FILE__));
define('WINPRESS_VERSION', '1.1.1');

// Load translations safely on init
add_action('init', function() {
    load_plugin_textdomain('winpress', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

require_once WINPRESS_PATH . 'includes/helpers.php';
require_once WINPRESS_PATH . 'includes/class-cache.php';
require_once WINPRESS_PATH . 'includes/class-optimizer.php';
require_once WINPRESS_PATH . 'includes/class-lazyload.php';
require_once WINPRESS_PATH . 'includes/class-image-optim.php';
require_once WINPRESS_PATH . 'admin/class-admin-page.php';

// Activation defaults
register_activation_hook(__FILE__, function(){
    $defaults = array(
        'defer_js' => '1',
        'defer_exclude' => 'jquery',
        'delay_css' => '1',
        'auto_preload_fonts' => '1',
        'manual_preload_urls' => '',
        'image_opt' => '0',
        'mobile_mode' => '1',
        'auto_branding' => '0',
    );
    if (!get_option('winpress_settings')) {
        add_option('winpress_settings', $defaults);
    } else {
        $opts = get_option('winpress_settings', array());
        $opts = array_merge($defaults, $opts);
        update_option('winpress_settings', $opts);
    }
});

// Initialize components on init (after translations)
add_action('init', function(){
    error_log('Winpress: Initializing plugin components');
    if (is_admin()) {
        error_log('Winpress: Initializing admin class');
        new Winpress_Admin();
    }
    error_log('Winpress: Initializing cache class');
    new Winpress_Cache();
    error_log('Winpress: Initializing optimizer class');
    new Winpress_Optimizer();
    error_log('Winpress: Initializing lazyload class');
    new Winpress_LazyLoad();
    error_log('Winpress: Initializing image optim class');
    new Winpress_Image_Optim();
    error_log('Winpress: Plugin initialization complete');
}, 20);
