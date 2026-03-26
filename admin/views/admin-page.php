<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$statuses = wc_get_order_statuses();
?>
<div class="wrap glint-reports-wrap">
    <h1>Order Source & Location Reports</h1>
    
    <div class="glint-reports-container">
        <div class="glint-reports-sidebar">
            <div class="postbox">
                <div class="inside">
                    <form id="glint-reports-form">
                        <p>
                            <label for="start_date">Start Date:</label><br/>
                            <input type="date" id="start_date" name="start_date" class="regular-text" style="width:100%;">
                        </p>
                        <p>
                            <label for="end_date">End Date:</label><br/>
                            <input type="date" id="end_date" name="end_date" class="regular-text" style="width:100%;">
                        </p>
                        <p>
                            <label for="statuses">Order Statuses:</label><br/>
                            <select id="statuses" name="statuses[]" multiple class="regular-text" style="width:100%; height: 150px;">
                                <?php foreach ( $statuses as $status_slug => $status_name ) : ?>
                                    <option value="<?php echo esc_attr( $status_slug ); ?>" selected><?php echo esc_html( $status_name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small>Hold Ctrl/Cmd to select multiple.</small>
                        </p>
                        <p>
                            <label for="report_type">Report Type:</label><br/>
                            <select id="report_type" name="report_type" class="regular-text" style="width:100%;">
                                <option value="source">Source Report</option>
                                <option value="location">Location Report</option>
                            </select>
                        </p>
                        <p id="location_by_container" style="display:none;">
                            <label for="location_by">Group Location By:</label><br/>
                            <select id="location_by" name="location_by" class="regular-text" style="width:100%;">
                                <option value="state">State</option>
                                <option value="suburb">Suburb / City</option>
                            </select>
                        </p>
                        <p>
                            <button type="submit" class="button button-primary" id="glint-generate-btn">Generate Report</button>
                            <span class="spinner" id="glint-reports-spinner"></span>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="glint-reports-main">
            <div class="postbox glint-chart-box">
                <div class="inside" style="position: relative; height: 400px; width: 100%;">
                    <canvas id="glintReportsChart"></canvas>
                </div>
            </div>
            
            <div class="postbox glint-table-box">
                <div class="inside">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody id="glint-reports-tbody">
                            <tr>
                                <td colspan="2">Generate a report to see data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
