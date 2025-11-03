<?php
if (!defined('ABSPATH')) exit;

function winpress_get_option($key, $default = null) {
    $opts = get_option('winpress_settings', array());
    if (isset($opts[$key])) return $opts[$key];
    return $default;
}

function winpress_is_mobile() {
    return wp_is_mobile();
}
