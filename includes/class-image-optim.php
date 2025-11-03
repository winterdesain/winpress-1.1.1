<?php
if (!defined('ABSPATH')) exit;

class Winpress_Image_Optim {
    public function __construct() {
        add_filter('wp_generate_attachment_metadata', array($this, 'maybe_optimize'), 10, 2);
    }

    public function maybe_optimize($metadata, $attachment_id) {
        $opts = get_option('winpress_settings', array());
        if (empty($opts['image_opt']) || $opts['image_opt'] !== '1') return $metadata;
        // Placeholder for future image optimization (requires server libs)
        return $metadata;
    }
}
