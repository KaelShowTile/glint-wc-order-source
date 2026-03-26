<?php
/**
 * Plugin Name: ST WC Order Source Reports
 * Description: Generates reports based on WooCommerce order sources and locations.
 * Version: 1.0.0
 * Author: Kael
 * Text Domain: glint-wc-order-source
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('GLINT_WC_OSR_VERSION', '1.0.0');
define('GLINT_WC_OSR_DIR', plugin_dir_path(__FILE__));
define('GLINT_WC_OSR_URL', plugin_dir_url(__FILE__));

if (is_admin()) {
    require_once GLINT_WC_OSR_DIR . 'admin/class-glint-reports-admin.php';
    new Glint_WC_Reports_Admin();
}
