<?php
if (!defined('ABSPATH')) exit;

class Winpress_Admin {
    public function __construct() {
        error_log('Winpress Admin: Constructor called, adding admin hooks');
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('wp_ajax_winpress_clear_cache', array($this, 'ajax_clear_cache'));
    }

    public function menu() {
        error_log('Winpress Admin: Adding admin menu page');
        add_options_page('Winpress', 'Winpress', 'manage_options', 'winpress', array($this, 'page'));
    }

    public function register_settings() {
        error_log('Winpress Admin: Registering settings');
        register_setting('winpress_group', 'winpress_settings');
    }

    public function assets($hook) {
        error_log('Winpress Admin: Checking assets for hook: ' . $hook);
        if ($hook !== 'settings_page_winpress') {
            error_log('Winpress Admin: Skipping assets for non-winpress page');
            return;
        }
        error_log('Winpress Admin: Enqueuing admin assets');
        wp_enqueue_script('winpress-admin', WINPRESS_URL . 'admin/assets/admin.js', array('jquery'), WINPRESS_VERSION, true);
        wp_localize_script('winpress-admin', 'winpressAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('winpress-clear')
        ));
        wp_enqueue_style('winpress-admin-css', WINPRESS_URL . 'admin/assets/admin.css', array(), WINPRESS_VERSION);
    }

    public function page() {
        $opts = get_option('winpress_settings', array());
        $defer_js = isset($opts['defer_js']) ? $opts['defer_js'] : '1';
        $defer_exclude = isset($opts['defer_exclude']) ? $opts['defer_exclude'] : 'jquery';
        $delay_css = isset($opts['delay_css']) ? $opts['delay_css'] : '1';
        $auto_pre = isset($opts['auto_preload_fonts']) ? $opts['auto_preload_fonts'] : '1';
        $manual_pre = isset($opts['manual_preload_urls']) ? $opts['manual_preload_urls'] : '';
        $image_opt = isset($opts['image_opt']) ? $opts['image_opt'] : '0';
        $mobile_mode = isset($opts['mobile_mode']) ? $opts['mobile_mode'] : '1';
        $branding = isset($opts['auto_branding']) ? $opts['auto_branding'] : '0';
        ?>
        <div class="wrap">
            <h1>Winpress Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('winpress_group'); ?>
                <table class="form-table">
                    <tr>
                        <th>Defer JS</th>
                        <td>
                            <input type="checkbox" name="winpress_settings[defer_js]" value="1" <?php checked($defer_js, '1'); ?> />
                            <p class="description">Add <code>defer</code> to scripts. Exclude handles separated by comma.</p>
                            <input type="text" name="winpress_settings[defer_exclude]" value="<?php echo esc_attr($defer_exclude); ?>" style="width:60%;" />
                        </td>
                    </tr>
                    <tr>
                        <th>Delay non-critical CSS</th>
                        <td>
                            <input type="checkbox" name="winpress_settings[delay_css]" value="1" <?php checked($delay_css, '1'); ?> />
                            <p class="description">Delay loading non-critical CSS using media=print onload trick.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Preload Fonts</th>
                        <td>
                            <label><input type="checkbox" name="winpress_settings[auto_preload_fonts]" value="1" <?php checked($auto_pre, '1'); ?> /> Auto-detect & preload common fonts</label>
                            <p>Or add manual font/CSS URLs (one per line):</p>
                            <textarea name="winpress_settings[manual_preload_urls]" rows="4" style="width:60%;"><?php echo esc_textarea($manual_pre); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th>Image Optimization</th>
                        <td>
                            <input type="checkbox" name="winpress_settings[image_opt]" value="1" <?php checked($image_opt, '1'); ?> />
                            <p class="description">Enable placeholder image optimization (requires server support for advanced ops).</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Mobile Mode</th>
                        <td>
                            <input type="checkbox" name="winpress_settings[mobile_mode]" value="1" <?php checked($mobile_mode, '1'); ?> />
                            <p class="description">Enable mobile-specific optimizations (more aggressive deferring).</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <h2>Cache</h2>
            <p>Clear plugin cache:</p>
            <button id="winpress-clear-cache" class="button button-primary">Clear Winpress Cache</button>
            <span id="winpress-clear-result" style="margin-left:10px;"></span>
        </div>
        <?php
    }

    public function ajax_clear_cache() {
        error_log('Winpress Admin: Processing AJAX cache clear request');
        check_ajax_referer('winpress-clear', 'nonce');
        if (!current_user_can('manage_options')) {
            error_log('Winpress Admin: AJAX cache clear - user not authorized');
            wp_send_json_error('Unauthorized');
            return;
        }
        error_log('Winpress Admin: Clearing cache via AJAX');
        Winpress_Cache::clear_cache();
        error_log('Winpress Admin: Cache cleared successfully via AJAX');
        wp_send_json_success('Cache cleared');
    }
}
