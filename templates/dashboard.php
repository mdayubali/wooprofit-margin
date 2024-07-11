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

    <h1 class="">Profit Margin</h1>;
    <div class="wooprofit_row flex m-5">
        <div class="col col-4">
            <p class="text-center">Total Stock</p>
            <h2 class="text-center"><?php echo $this->wooprofit_total_stock_amount(); ?> </h2>
        </div>
        <div class="col col-4">
            <p class="text-center">Total Price</p>
            <h2 class="text-center"><?php echo get_woocommerce_currency_symbol() . number_format( $this->wooprofit_total_price_amount(), 2 ); ?> </h2>
        </div>
        <div class="col col-4">
            <p class="text-center">Total Cost</p>
            <h2 class="text-center"><?php echo get_woocommerce_currency_symbol() . number_format( $this->wooprofit_total_cost_amount(), 2 ); ?> </h2>
        </div>
        <div class="col col-4">
            <p class="text-center">Potential Profit</p>
	        <?php
            if($this->wooprofit_total_profit_amount() > 0){ ?>
                 <h2 class="text-center profit-positive">
                    <?php echo get_woocommerce_currency_symbol() . number_format( $this->wooprofit_total_profit_amount(), 2 ); ?>
                </h2>
                <?php
	        }else {
	        ?>
            <h2 class="text-center profit-negative">
		        <?php echo get_woocommerce_currency_symbol() . number_format( $this->wooprofit_total_profit_amount(), 2 ); ?>
            </h2> <?php
            }
            ?>

        </div>
    </div>
    <div class="date-range-picker">
		<?php include_once plugin_dir_path( __FILE__ ) . 'report-by-date.php'; ?>
    </div>
</div>
