<?php
if (!defined('ABSPATH')) exit;

class Winpress_Cache {
    private $dir;
    public function __construct() {
        $this->dir = WP_CONTENT_DIR . '/cache/winpress/';
        error_log('Winpress Cache: Constructor called, cache dir: ' . $this->dir);
        add_action('init', array($this, 'ensure_dir'));
        add_action('template_redirect', array($this, 'maybe_start_buffer'), 0);
        add_action('save_post', array($this, 'clear_cache_on_save'));
    }

    public function ensure_dir() {
        error_log('Winpress Cache: Ensuring cache directory exists: ' . $this->dir);
        if (!file_exists($this->dir)) {
            wp_mkdir_p($this->dir);
            error_log('Winpress Cache: Created cache directory');
        } else {
            error_log('Winpress Cache: Cache directory already exists');
        }
    }

    public function maybe_start_buffer() {
        error_log('Winpress Cache: Checking if should start buffer - is_user_logged_in: ' . (is_user_logged_in() ? 'yes' : 'no') . ', is_admin: ' . (is_admin() ? 'yes' : 'no'));
        if (is_user_logged_in() || is_admin()) {
            error_log('Winpress Cache: Skipping buffer start for logged in user or admin');
            return;
        }
        error_log('Winpress Cache: Starting output buffer for caching');
        ob_start(array($this, 'save_cache'));
    }

    public function save_cache($html) {
        $this->ensure_dir();
        $key = md5($_SERVER['REQUEST_URI']);
        $file = $this->dir . $key . '.html';
        error_log('Winpress Cache: Saving cache for URI: ' . $_SERVER['REQUEST_URI'] . ' to file: ' . $file . ', size: ' . strlen($html) . ' bytes');
        $result = @file_put_contents($file, $html);
        if ($result === false) {
            error_log('Winpress Cache: Failed to write cache file: ' . $file);
        } else {
            error_log('Winpress Cache: Cache saved successfully');
        }
        return $html;
    }

    public static function clear_cache() {
        $dir = WP_CONTENT_DIR . '/cache/winpress/';
        error_log('Winpress Cache: Clearing cache from directory: ' . $dir);
        if (!is_dir($dir)) {
            error_log('Winpress Cache: Cache directory does not exist');
            return;
        }
        $files = glob($dir . '*.html');
        if ($files) {
            error_log('Winpress Cache: Found ' . count($files) . ' cache files to delete');
            foreach ($files as $f) {
                if (@unlink($f)) {
                    error_log('Winpress Cache: Deleted cache file: ' . $f);
                } else {
                    error_log('Winpress Cache: Failed to delete cache file: ' . $f);
                }
            }
        } else {
            error_log('Winpress Cache: No cache files found');
        }
    }

    public function clear_cache_on_save() {
        error_log('Winpress Cache: Clearing cache on post save');
        self::clear_cache();
    }
}
