<?php
$plugin_dir = plugin_dir_path( __FILE__ );

$file_to_include = $plugin_dir . '../wooprofit.php';
if ( file_exists( $file_to_include ) ) {
	include_once( $file_to_include );
} else {
	echo 'File not found: ' . esc_html( $file_to_include );
}
?>

<div class="wrap">
    <div class="page-header">
        <h2 class="text-white title">Date Range</h2>
    </div>
    <div class="card w-100 neg-margin p-0">
        <form name="custom-date-range-form" id="custom-date-range-form" method="post">
            <div class="flex gap-10">
                <select id="date-range-select" class="nice-select">
                    <option value="today"><?php esc_html_e( 'Today', 'wooprofit' ); ?></option>
                    <option value="yesterday"><?php esc_html_e( 'Yesterday', 'wooprofit' ); ?></option>
                    <option value="last-7-days"><?php esc_html_e( 'Last 7 Days', 'wooprofit' ); ?></option>
                    <option value="last-14-days"><?php esc_html_e( 'Last 14 Days', 'wooprofit' ); ?></option>
                    <option value="this-month"><?php esc_html_e( 'Month to date', 'wooprofit' ); ?></option>
                    <option value="last-month"><?php esc_html_e( 'Last Month', 'wooprofit' ); ?></option>
                </select>
                <input type="text" id="start_date" name="start_date" autocomplete="off" placeholder="Start Date">
                <input type="text" id="end_date" name="end_date" autocomplete="off" placeholder="End Date">
            </div>
        </form>
        <!--comparison tab-->
        <div class="flex">

            <form action="" method="post" id="previous-date-range-form" class="flex">
                <h4 class="compare-text title">compare to</h4>
                <select id="previous-date-range-select" class="nice-select">
                    <option value="no-comparison" selected><?php esc_html_e('No comparison', 'wooprofit'); ?></option>
                    <option value="previous-month"><?php esc_html_e('Previous Month', 'wooprofit'); ?><small class="small"> </small></option>
                    <option value="previous-period"><?php esc_html_e('Previous Period', 'wooprofit'); ?><small class="small"> </small></option>
                    <option value="previous-quarter"><?php esc_html_e('Previous Quarter', 'wooprofit'); ?><small class="small"> </small></option>
                </select>

                <div id="custom-date-range">
                    <input type="text" id="prev_start_date" name="prev_start_date" autocomplete="off" placeholder="Start Date">
                    <input type="text" id="prev_end_date" name="prev_end_date" autocomplete="off" placeholder="End Date">
                </div>
            </form>

        </div>
        <!--end comparison tab-->
    </div>
</div>

<div class="flex gap-5 w-100 justify-center">
    <div class="col-3">
        <div class="card">
            <div class="card-header flex space-between">
                <h2 class="text-white title">Order</h2>
                <span class="badge" id="order-percentage-change"></span>
            </div>
            <div class="card-body flex space-between">
                <div id="orders-list"></div>
                <div id="pre-orders-list" class="text-off-white"></div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-header flex space-between">
                <h2 class="text-white title">Total Sales</h2>
                <span class="badge" id="total-sales-percentage-change"></span>
            </div>
            <div class="card-body flex space-between">
                <div id="total-sales"></div>
                <div id="pre-total-sales" class="text-off-white"></div>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-header flex space-between">
                <h2 class="text-white title">Cost</h2>
                <span class="badge" id="total-cost-percentage-change"></span>
            </div>
            <div class="card-body flex space-between">
                <div id="total-cost"></div>
                <div id="pre-total-cost" class="text-off-white"></div>
            </div>
        </div>
    </div>
</div>
<div class="flex gap-5">
    <div class="col-3">
        <div class="card">
            <div class="card-header flex space-between">
                <h2 class="text-white title">Average Daily Profit </h2>
                <span class="badge" id="average-daily-profit-percentage-change"></span>
            </div>
            <div class="card-body flex space-between">
                <div id="average-profit"></div>
                <div id="pre-average-daily-profit" class="text-off-white"></div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-header flex space-between">
                <h2 class="text-white title">Average Order profit</h2>
                <span class="badge" id="average-order-profit-percentage-change"></span>
            </div>
            <div class="card-body flex space-between">
                <div id="average-order-profit"></div>
                <div id="pre-average-order-profit" class="text-off-white"></div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-header flex space-between">
                <h2 class="text-white title">Total Profit</h2>
                <span class="badge" id="total-profit-percentage-change"></span>
            </div>
            <div class="card-body flex space-between">
                <div class="flex aligncenter gap-5">
                    <div id="profit"></div>
                    <div id="profit-percentage"></div>
                </div>
                <div id="pre-profit" class="text-off-white"></div>
            </div>
        </div>
    </div>
</div>



