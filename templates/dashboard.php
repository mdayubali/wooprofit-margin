<?php
$plugin_dir = plugin_dir_path( __FILE__ );

$file_to_include = $plugin_dir . '../wooprofit.php';
if ( file_exists( $file_to_include ) ) {
	include_once( $file_to_include );
} else {
	echo 'File not found: ' . esc_html( $file_to_include );
}
?>
<div class="wrap bg-dark">
    <div class="page-header">
        <h2 class="text-white title">WooProfit Details</h2>
    </div>
    <div class="flex gap-5">
        <div class="col-4 card">
            <div class="card-header flex">
                <h2 class="text-white title">
                    Total Stock
                    <i class="fas fa-info-circle tooltip" data-tooltip="Total Stock refers to the total number of all items currently available in the store."></i>
                </h2>
            </div>
            <div class="card-body">
                <p class="text-white">
		            <?php echo esc_html( $this->total_stock() ); ?>
                </p>
            </div>
        </div>
        <div class="col-4 card">
            <div class="card-header flex">
                <h2 class="text-white title">
                    Total Price
                    <i class="fas fa-info-circle tooltip" data-tooltip="Total Price is represents the combined retail price of all products currently in stock."></i>
                </h2>
            </div>
            <div class="card-body">
                <p class="text-white">
		            <?php echo esc_html( get_woocommerce_currency_symbol() ) . esc_html( number_format( $this->total_price(), 2 ) ); ?>
                </p>
            </div>
        </div>
        <div class="col-4 card">
            <div class="card-header flex">
                <h2 class="text-white title">
                    Total Cost
                    <i class="fas fa-info-circle tooltip" data-tooltip="Total Cost is refers to the cumulative cost of all products currently in stock."></i>
                </h2>
            </div>
            <div class="card-body">
                <p class="text-white">
		            <?php echo esc_html( get_woocommerce_currency_symbol() ) . esc_html( number_format( $this->total_cost(), 2 ) ); ?>
                </p>
            </div>
        </div>
        <div class="col-4 card">
            <div class="card-header flex">
                <h2 class="text-white title">
                    Total Potential Profit
                    <i class="fas fa-info-circle tooltip" data-tooltip="Total Potential Profit is refers to the estimated profit that can be made from selling all items currently in stock at their listed prices."></i>
                </h2>
            </div>
            <div class="card-body">
	            <?php
	            if ( $this->total_profit() > 0 ) { ?>
                    <p class="profit-positive">
			            <?php echo esc_html( get_woocommerce_currency_symbol() ) . esc_html( number_format( $this->total_profit(), 2 ) ); ?>
                    </p>
		            <?php
	            } else {
		            ?>
                    <p class="profit-negative">
			            <?php echo esc_html( get_woocommerce_currency_symbol() ) . esc_html( number_format( $this->total_profit(), 2 ) ); ?>
                    </p> <?php
	            }
	            ?>
            </div>
        </div>
    </div>
    <div class="date-range-picker">
		<?php include_once plugin_dir_path( __FILE__ ) . 'report-by-date.php'; ?>
    </div>
</div>