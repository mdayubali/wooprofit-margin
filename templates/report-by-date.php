<?php
$plugin_dir = plugin_dir_path( __FILE__ );

$file_to_include = $plugin_dir . '../wooprofit-margin.php';
if ( file_exists( $file_to_include ) ) {
	include_once( $file_to_include );
} else {
	echo 'File not found: ' . esc_html( $file_to_include );
}
?>

<div class="wrap">
    <p><?php esc_html_e('Date Range', 'wooprofit-margin'); ?></p>
    <form name="custom-date-range-form" id="custom-date-range-form" method="post">
        <select id="date-range-select" class="nice-select">
<!--            <option value="custom">--><?php //esc_html_e('Custom', 'wooprofit-margin'); ?><!--</option>-->
            <option value="today"><?php esc_html_e('Today', 'wooprofit-margin'); ?></option>
            <option value="yesterday"><?php esc_html_e('Yesterday', 'wooprofit-margin'); ?></option>
            <option value="last-7-days"><?php esc_html_e('Last 7 Days', 'wooprofit-margin'); ?></option>
            <option value="last-14-days"><?php esc_html_e('Last 14 Days', 'wooprofit-margin'); ?></option>
            <option value="this-month"><?php esc_html_e('This Month', 'wooprofit-margin'); ?></option>
            <option value="last-month"><?php esc_html_e('Last Month', 'wooprofit-margin'); ?></option>
            <!-- Add more options as needed -->
        </select>

            <input class="" type="text" id="start_date" placeholder="Start Date">
            <input class="" type="text" id="end_date" placeholder="End Date">
            <button class="" type="submit" class="button button-primary">Filter</button>

    </form>
</div>
<div class="wrap">
    <ul class="woocommerce-summary has-4-items flex">
        <div class="woocommerce-summary__item-container col">
            <p>Order</p>
            <div id="orders-list"></div>
        </div>
        <div class="woocommerce-summary__item-container col">
            <p>Total Sales</p>
            <div id="total-sales"></div>
        </div>
        <div class="woocommerce-summary__item-container col">
            <p>Net Sales</p>
            <div id="net-sales"></div>
        </div>
        <div class="woocommerce-summary__item-container col">
            <p>Average Order Value</p>
            <div id="average-order-value"></div>
        </div>
        <div class="woocommerce-summary__item-container col">
            <p>Cost</p>
            <div id="total-cost"></div>
        </div>
        <div class="woocommerce-summary__item-container col">
            <p>Average Daily Profit </p>
            <div id="average-profit"></div>
        </div>
        <div class="woocommerce-summary__item-container col">
            <p>Average Order profit
            </p>
            <div id="average-order-profit"></div>
        </div>
        <div class="woocommerce-summary__item-container col">
            <p>Total Profit</p>
            <div id="profit"></div>
        </div>

    </ul>
</div>