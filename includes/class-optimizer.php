<?php
if (!defined('ABSPATH')) exit;

class Winpress_Optimizer {
    public function __construct() {
        error_log('Winpress Optimizer: Constructor called, adding filters and actions');
        add_filter('script_loader_tag', array($this, 'defer_scripts'), 10, 3);
        add_filter('style_loader_tag', array($this, 'delay_styles'), 10, 2);
        add_action('wp_head', array($this, 'print_preload_fonts'), 1);
        add_action('template_redirect', array($this, 'start_minify'), 100);
    }

    private function opts() {
        return get_option('winpress_settings', array());
    }

    public function defer_scripts($tag, $handle, $src) {
        error_log('Winpress Optimizer: Processing script defer for handle: ' . $handle);
        if (is_admin()) {
            error_log('Winpress Optimizer: Skipping defer for admin page');
            return $tag;
        }
        $opts = $this->opts();
        if (empty($opts['defer_js']) || $opts['defer_js'] !== '1') {
            error_log('Winpress Optimizer: Defer JS disabled in settings');
            return $tag;
        }
        $exclude = isset($opts['defer_exclude']) ? array_map('trim', explode(',', $opts['defer_exclude'])) : array('jquery');
        if (in_array($handle, $exclude)) {
            error_log('Winpress Optimizer: Script ' . $handle . ' is in exclude list');
            return $tag;
        }
        if (strpos($tag, 'defer') === false && strpos($tag, 'async') === false) {
            error_log('Winpress Optimizer: Adding defer to script: ' . $handle);
            // use concatenation operator for strings
            return '<script src="' . esc_url($src) . '" defer></script>';
        }
        error_log('Winpress Optimizer: Script already has defer/async: ' . $handle);
        return $tag;
    }

    public function delay_styles($html, $handle) {
        error_log('Winpress Optimizer: Processing style delay for handle: ' . $handle);
        if (is_admin()) {
            error_log('Winpress Optimizer: Skipping delay for admin page');
            return $html;
        }
        $opts = $this->opts();
        if (empty($opts['delay_css']) || $opts['delay_css'] !== '1') {
            error_log('Winpress Optimizer: Delay CSS disabled in settings');
            return $html;
        }
        if (strpos($html, 'media=') !== false || strpos($html, 'onload=') !== false) {
            error_log('Winpress Optimizer: Style already has media/onload: ' . $handle);
            return $html;
        }
        error_log('Winpress Optimizer: Adding delay to style: ' . $handle);
        return str_replace('rel="stylesheet"', 'rel="stylesheet" media="print" onload="this.media=\'all\'"', $html);
    }

    public function print_preload_fonts() {
        error_log('Winpress Optimizer: Processing font preloading');
        if (is_admin()) {
            error_log('Winpress Optimizer: Skipping font preload for admin page');
            return;
        }
        $opts = $this->opts();
        if (empty($opts['auto_preload_fonts']) && empty($opts['manual_preload_urls'])) {
            error_log('Winpress Optimizer: No font preloading configured');
            return;
        }
        if (!empty($opts['manual_preload_urls'])) {
            error_log('Winpress Optimizer: Processing manual font URLs');
            $lines = preg_split('/\r?\n/', $opts['manual_preload_urls']);
            foreach ($lines as $l) {
                $l = trim($l);
                if (empty($l)) continue;
                error_log('Winpress Optimizer: Adding manual font preload: ' . $l);
                echo '<link rel="preload" as="style" href="' . esc_url($l) . '" onload="this.rel=\'stylesheet\'">' . "\n";
            }
        }
        if (!empty($opts['auto_preload_fonts']) && $opts['auto_preload_fonts'] === '1') {
            error_log('Winpress Optimizer: Adding auto font preloads');
            $candidates = array(
                'https://fonts.googleapis.com/css2?family=Poppins&display=swap',
                'https://fonts.googleapis.com/css2?family=Roboto&display=swap',
                'https://fonts.googleapis.com/css2?family=Inter&display=swap'
            );
            foreach ($candidates as $url) {
                error_log('Winpress Optimizer: Adding auto font preload: ' . $url);
                echo '<link rel="preload" as="style" href="' . esc_url($url) . '" onload="this.rel=\'stylesheet\'">' . "\n";
            }
        }
    }

    public function start_minify() {
        error_log('Winpress Optimizer: Checking minify conditions - is_admin: ' . (is_admin() ? 'yes' : 'no') . ', is_user_logged_in: ' . (is_user_logged_in() ? 'yes' : 'no'));
        if (is_admin() || is_user_logged_in()) {
            error_log('Winpress Optimizer: Skipping minify for admin or logged in user');
            return;
        }
        error_log('Winpress Optimizer: Starting HTML minification buffer');
        ob_start(array($this, 'minify_html'));
    }

    public function minify_html($html) {
        $original_size = strlen($html);
        error_log('Winpress Optimizer: Minifying HTML, original size: ' . $original_size . ' bytes');
        $search = array('/\n+/', '/\s{2,}/', '/>\s+</');
        $replace = array('', ' ', '><');
        $html = preg_replace($search, $replace, $html);
        $html = preg_replace('/<!--(?!\s*\[if).*?-->/', '', $html);
        $minified_size = strlen($html);
        error_log('Winpress Optimizer: HTML minified, new size: ' . $minified_size . ' bytes, saved: ' . ($original_size - $minified_size) . ' bytes');
        return $html;
    }
}
