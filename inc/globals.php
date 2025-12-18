<?php
/**
 * Global helpers and optimization gates
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Detect if Beaver Builder editor/preview is active
if (!function_exists('p5m_is_bb_active')) {
    function p5m_is_bb_active() {
        if (isset($_GET['fl_builder']) || isset($_GET['fl_builder_ui'])) return true;
        if (class_exists('FLBuilderModel')) {
            if (method_exists('FLBuilderModel', 'is_builder_active') && FLBuilderModel::is_builder_active()) return true;
            if (method_exists('FLBuilderModel', 'is_preview') && FLBuilderModel::is_preview()) return true;
        }
        return false;
    }
}

// Central gate: should theme optimizations run?
// Disable when: admin area, logged-in users (admin/editing mode), BB editor/preview,
// Customizer preview, or WP preview. Only run for public visitors.
if (!function_exists('p5m_should_optimize')) {
    function p5m_should_optimize() {
        if (is_admin()) return false;
        if (is_user_logged_in()) return false;
        if (p5m_is_bb_active()) return false;
        if (function_exists('is_customize_preview') && is_customize_preview()) return false;
        if (isset($_GET['preview'])) return false;
        return true;
    }
}

// Temporary debug helper (logs only when WP_DEBUG=true)
if (!function_exists('p5m_debug')) {
    function p5m_debug($msg) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (is_array($msg) || is_object($msg)) {
                error_log('[P5M] ' . print_r($msg, true));
            } else {
                error_log('[P5M] ' . $msg);
            }
        }
    }
}
