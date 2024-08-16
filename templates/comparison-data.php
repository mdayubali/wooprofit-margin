<?php
class ComparisonData {

	// ... existing methods

	public function __construct() {
		// ... existing constructor code

		//add_action( 'wp_ajax_wooprofit_compare_monthly_orders', [ $this, 'wooprofit_compare_monthly_orders' ] );
		//add_action( 'wp_ajax_nopriv_wooprofit_compare_monthly_orders', [ $this, 'wooprofit_compare_monthly_orders' ] );
	}

	// ... existing methods

	private function wooprofit_get_orders_for_month( $year, $month ) {
		$start_date = "{$year}-{$month}-01";
		$end_date = date( "Y-m-t", strtotime( $start_date ) ); // Get the last day of the month

		$args = array(
			'limit'        => -1,
			'status'       => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
			'date_created' => "{$start_date}...{$end_date}",
		);

		return wc_get_orders( $args );
	}

	public function wooprofit_compare_monthly_orders() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html( 'You do not have sufficient permissions to access this page.', 'wooprofit' ) );
		}

		$current_year = date( 'Y' );
		$current_month = date( 'm' );

		$previous_year = $current_month == 1 ? $current_year - 1 : $current_year;
		$previous_month = $current_month == 1 ? 12 : $current_month - 1;

		$current_month_orders = $this->wooprofit_get_orders_for_month( $current_year, $current_month );
		$previous_month_orders = $this->wooprofit_get_orders_for_month( $previous_year, $previous_month );

		$current_month_data = $this->wooprofit_calculate_order_data( $current_month_orders );
		$previous_month_data = $this->wooprofit_calculate_order_data( $previous_month_orders );

		echo json_encode( array(
			'current_month'  => $current_month_data,
			'previous_month' => $previous_month_data,
		) );

		wp_die();
	}

	private function wooprofit_calculate_order_data( $orders ) {
		$total_orders = count( $orders );
		$total_sales = 0;
		$total_cost = 0;

		foreach ( $orders as $order ) {
			$total_sales += $order->get_total();

			foreach ( $order->get_items() as $item ) {
				$product_id = $item->get_product_id();
				$product_cost = get_post_meta( $product_id, '_woo_product_cost', true );
				$product_cost = $product_cost ? floatval( $product_cost ) : 0;
				$total_cost += $product_cost * $item->get_quantity();
			}
		}

		$total_profit = $total_sales - $total_cost;
		$profit_percentage = $total_sales ? ( $total_profit / $total_sales ) * 100 : 0;

		return array(
			'total_orders'      => $total_orders,
			'total_sales'       => wc_price( $total_sales ),
			'total_cost'        => wc_price( $total_cost ),
			'profit'            => wc_price( $total_profit ),
			'profit_percentage' => round( $profit_percentage, 2 ) . '%',
		);
	}
}

// ... rest of the plugin code

new ComparisonData();
