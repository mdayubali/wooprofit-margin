<?php
namespace Wooprofit\Margin;

class Daterange{

	function custom_get_orders_by_date_range(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'wooprit-margin' ) );
		}

		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';

		if ( ! $start_date || ! $end_date ) {
			echo '<p>' . __( 'Please select a valid date range.', 'wooprit-margin' ) . '</p>';
			wp_die();
		}

		$args = array(
			'limit'        => - 1, // Retrieve all matching orders
			'status'       => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ), // Order statuses to include
			'date_created' => $start_date . '...' . $end_date, // Date range for order creation
		);

		$orders = wc_get_orders( $args );

		if ( ! empty( $orders ) ) {
			$total_orders = count( $orders );
			$total_sales  = 0;
			$net_sales    = 0;
			$total_cost   = 0;

			foreach ( $orders as $order ) {
				$total_sales += $order->get_total();
				$net_sales   += $order->get_total() - $order->get_total_tax();

				foreach ( $order->get_items() as $item ) {
					$product_id   = $item->get_product_id();
					$product_cost = get_post_meta( $product_id, '_product_cost', true );

					// Ensure that the product cost is a valid number
					if ( $product_cost === '' ) {
						$product_cost = 0; // Default to 0 if the meta key does not exist
					} else {
						$product_cost = floatval( $product_cost );
					}

					$total_cost += $product_cost * $item->get_quantity();
				}
				// Now you can calculate the profit based on the total cost and the order total
//				$total_profit = $net_sales - $total_cost;
			}

			$total_profit        = $net_sales - $total_cost;
			$profit_class        = $total_profit > 0 ? 'profit-positive' : 'profit-negative';
			$average_profit = $this->calculate_average_profit($total_profit, $total_orders);
			$average_order_value = $total_orders ? $total_sales / $total_orders : 0;
			$average_order_profit = $this->calculate_average_order_profit( $total_profit, $total_orders );

			echo json_encode( array(
				'total_orders'        => $total_orders,
				'total_sales'         => wc_price( $total_sales ),
				'net_sales'           => wc_price( $net_sales ),
				'total_cost'           => wc_price( $total_cost ),
				'average_order_value' => wc_price( $average_order_value ),
				'profit'              => wc_price( $total_profit ),
				'profit_class'        => $profit_class,
				'average_profit' => wc_price($average_profit),
				'average_order_profit'=> wc_price( $average_order_profit )

			) );
		} else {
			echo json_encode( array(
				'total_orders'        => 0,
				'total_sales'         => wc_price( 0 ),
				'net_sales'           => wc_price( 0 ),
				'total_cost'           => wc_price( 0 ),
				'average_order_value' => wc_price( 0 ),
				'profit'              => wc_price( 0 ),
				'profit_class'        => 'profit-negative',
				'average_profit' => wc_price(0),
				'average_order_profit'=> wc_price( 0 )
			) );
		}
		wp_reset_postdata();
		wp_die();
	}
}