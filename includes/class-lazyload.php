<?php
if (!defined('ABSPATH')) exit;

class Winpress_LazyLoad {
    public function __construct() {
        error_log('Winpress LazyLoad: Constructor called, adding filters and actions');
        add_filter('the_content', array($this, 'apply_lazy_to_content'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_script'));
    }

    public function apply_lazy_to_content($content) {
        error_log('Winpress LazyLoad: Processing content for lazy loading');
        // add loading=lazy and data-src for images that don't already have loading attribute
        $original_content = $content;
        $content = preg_replace('/<img(?![^>]*loading=)([^>]*?)src=("|\')(.*?)\2([^>]*?)>/i', '<img loading="lazy" data-src="$3" $1 $4>', $content);
        $images_modified = ($content !== $original_content);
        error_log('Winpress LazyLoad: Images processed, modified: ' . ($images_modified ? 'yes' : 'no'));

        // handle iframes (youtube etc.)
        $original_content = $content;
        $content = preg_replace('/<iframe(.*?)src=("|\')(.*?)\2(.*?)>/i', '<iframe data-src="$3" $1 $4>', $content);
        $iframes_modified = ($content !== $original_content);
        error_log('Winpress LazyLoad: Iframes processed, modified: ' . ($iframes_modified ? 'yes' : 'no'));

        return $content;
    }

    public function enqueue_script() {
        error_log('Winpress LazyLoad: Enqueuing lazyload script');
        wp_enqueue_script('winpress-lazy', WINPRESS_URL . 'admin/assets/lazyload.js', array(), WINPRESS_VERSION, true);
    }
}
