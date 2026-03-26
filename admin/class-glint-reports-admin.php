<?php
if (!defined('ABSPATH')) {
    exit;
}

class Glint_WC_Reports_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_glint_generate_reports', array($this, 'generate_reports_ajax'));
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            'woocommerce',
            'Order Source & Location Reports',
            'Order Source Reports',
            'manage_woocommerce',
            'glint-order-reports',
            array($this, 'render_admin_page')
        );
    }

    public function enqueue_scripts($hook)
    {
        if ('woocommerce_page_glint-order-reports' !== $hook) {
            return;
        }

        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        wp_enqueue_style('glint-reports-css', GLINT_WC_OSR_URL . 'assets/css/admin-reports.css', array(), GLINT_WC_OSR_VERSION);
        wp_enqueue_script('glint-reports-js', GLINT_WC_OSR_URL . 'assets/js/admin-reports.js', array('jquery', 'chart-js'), GLINT_WC_OSR_VERSION, true);

        wp_localize_script('glint-reports-js', 'glint_reports_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('glint_reports_nonce')
        ));
    }

    public function render_admin_page()
    {
        include_once GLINT_WC_OSR_DIR . 'admin/views/admin-page.php';
    }

    public function generate_reports_ajax()
    {
        check_ajax_referer('glint_reports_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permission denied');
        }

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $statuses = isset($_POST['statuses']) ? array_map('sanitize_text_field', $_POST['statuses']) : array();
        $report_type = isset($_POST['report_type']) ? sanitize_text_field($_POST['report_type']) : '';
        $location_by = isset($_POST['location_by']) ? sanitize_text_field($_POST['location_by']) : '';

        // Add wc- prefix if missing for statuses
        $status_values = array();
        foreach ($statuses as $status) {
            $status_values[] = 'wc-' . ltrim($status, 'wc-');
        }

        $order_args = array(
            'status' => $status_values,
            'limit' => -1,
            'return' => 'ids',
        );

        if ($start_date && $end_date) {
            $order_args['date_created'] = $start_date . '...' . $end_date;
        }

        $order_ids = wc_get_orders($order_args);
        $data = array();

        if ($report_type === 'source') {
            foreach ($order_ids as $order_id) {
                $order = wc_get_order($order_id);

                $source_type = $order->get_meta('source_type');
                if (empty($source_type)) {
                    $source_type = $order->get_meta('_wc_order_attribution_source_type');
                }

                $source = $order->get_meta('utm_source');
                if (empty($source)) {
                    $source = $order->get_meta('_wc_order_attribution_utm_source');
                }

                $campaign = $order->get_meta('utm_campaign');
                if (empty($campaign)) {
                    $campaign = $order->get_meta('_wc_order_attribution_utm_campaign');
                }

                $prefix = array();
                if ($source_type) {
                    $prefix[] = $source_type;
                }
                if ($source) {
                    $prefix[] = $source;
                }

                $key = "Unknown";
                if (!empty($prefix)) {
                    $key = implode(': ', $prefix);
                    if ($campaign) {
                        $key .= ' - ' . $campaign;
                    }
                }

                if (!isset($data[$key])) {
                    $data[$key] = 0;
                }
                $data[$key]++;
            }
        }
        elseif ($report_type === 'location') {
            foreach ($order_ids as $order_id) {
                $order = wc_get_order($order_id);

                $state = $order->get_shipping_state();
                if (empty($state)) {
                    $state = $order->get_billing_state();
                }

                $city = $order->get_shipping_city();
                if (empty($city)) {
                    $city = $order->get_billing_city();
                }

                if ($location_by === 'suburb') {
                    $key = $city ? $city : 'Unknown';
                }
                else {
                    $key = $state ? $state : 'Unknown';
                }

                if (!isset($data[$key])) {
                    $data[$key] = 0;
                }
                $data[$key]++;
            }
        }

        arsort($data); // Sort by values descending
        $sorted_labels = array_keys($data);
        $sorted_values = array_values($data);

        wp_send_json_success(array(
            'labels' => $sorted_labels,
            'values' => $sorted_values,
        ));
    }
}
