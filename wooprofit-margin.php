<?php
/*
Plugin Name: WooProfit Margin
Plugin URI: https://spider-themes.net/wooprofit/
Description: Effortlessly track and analyze product costs and profits in WooCommerce, empowering smarter financial decisions and enhanced profitability.
Version: 1.0.0
Requires at least: 5.7
Requires PHP: 7.4
Author: spider-themes
Author URI: https://spider-themes.net
Text Domain: wooprofit-margin
Domain Path: /languages
Copyright: © 2024 Spider Themes
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

const WOOPROFIT_COST_SETTINGS_PLUGIN_FILE = __FILE__;

class Wooprofit_Margin {

	public function __construct() {
		add_action( 'init', array( $this, 'wooprofit_init' ) );
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
	}

	public function activate(): void {
		flush_rewrite_rules();
	}

	public function wooprofit_init(): void {

		add_action( 'admin_menu', [ $this, 'wooprofit_admin_menu' ], 99 );
		/**
		 * Enqueue Assets
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'wooprofit_assetsloader' ] );

		/**
		 * add custom cost field
		 */
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_cost_field' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_cost_field' ) );
		add_filter( 'manage_product_posts_columns', array( $this, 'add_cost_and_profit_column_header' ), 20 );
		add_action( 'manage_product_posts_custom_column', array( $this, 'populate_cost_and_profit_column_content' ), 20, 2 );
		/**
		 * Make the custom columns sortable
		 */
		add_filter( 'manage_edit-product_sortable_columns', array( $this, 'make_cost_and_profit_column_sortable' ) );
		/**
		 * Handle sorting by custom columns
		 */
		add_action( 'pre_get_posts', array( $this, 'sort_cost_and_profit_columns' ) );
		/**
		 * Add custom tab inside the woocommerce setting menu
		 */
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_cost_tab_to_woocommerce_settings' ) );
		/**
		 * Settings filter
		 */
		add_filter( 'plugin_action_links_' . plugin_basename( WOOPROFIT_COST_SETTINGS_PLUGIN_FILE ), array( $this, 'wooprofit_settings_action_links' ) );

		/**
		 *Date range
		 */
		add_action( 'wp_ajax_get_orders_by_date_range', [ $this, 'custom_get_orders_by_date_range' ] );
		add_action( 'wp_ajax_nopriv_get_orders_by_date_range', [ $this, 'custom_get_orders_by_date_range' ] );

	}

	function wooprofit_settings_action_links( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=wooprofit' ) . '">' . __( 'Settings', 'wooprofit-margin' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Method for settings
	 */
	function add_cost_tab_to_woocommerce_settings( $settings ) {
		$settings[] = include( 'class-wooprofit-settings-cost.php' );

		return $settings;
	}

	function wooprofit_assetsloader( $hook ): void {
		$assets_dir = plugins_url( 'assets/', __FILE__ );

		wp_enqueue_style( 'wooprofit-style', $assets_dir . 'css/style.css' );
		wp_enqueue_style( 'wooprofit-nice', $assets_dir . 'css/nice-select.css' );
		if ( $hook == 'post.php' || $hook == 'post-new.php' ) {
			global $post_type;
			if ( $post_type == 'product' ) {
				wp_enqueue_script( 'wooprofit', $assets_dir . 'js/profit-show.js', array( 'jquery' ), '1.0', true );
			}
		}
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
		wp_enqueue_script( 'custom-date-range-script', $assets_dir . 'js/custom-date-range.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'nice-select', $assets_dir . 'js/jquery.nice-select.min.js', array(), '1.0', true );

		wp_localize_script( 'custom-date-range-script', 'ajax_params', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	function wooprofit_admin_menu(): void {
		add_submenu_page(
			'wc-admin&path=/analytics/overview',
			__( 'Profit', 'wooprofit-margin' ),
			__( 'Profit Margin', 'wooprofit-margin' ),
			'manage_woocommerce',
			'wc-analytics-profit',
			[ $this, 'wooprofit_page' ]
		);
	}

	function wooprofit_total_stock_amount(): ?int {
		$total_stock = 0;
		// Ensure WooCommerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			);

			$products = new WP_Query( $args );

			if ( $products->have_posts() ) {
				while ( $products->have_posts() ) {
					$products->the_post();
					$product     = wc_get_product( get_the_ID() );
					$total_stock += $product->get_stock_quantity();
				}
			}
			wp_reset_postdata();
		}

		return $total_stock;

	}

	function wooprofit_total_price_amount() {
		$total_price = 0;

		// Ensure WooCommerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			);

			$products = new WP_Query( $args );

			if ( $products->have_posts() ) {
				while ( $products->have_posts() ) {
					$products->the_post();
					$product     = wc_get_product( get_the_ID() );
					$total_price += (float) $product->get_price();
				}
			}
			wp_reset_postdata();
		}

		return $total_price;
	}

	function wooprofit_total_cost_amount(): float|int {
		$total_cost = 0;

		// Ensure WooCommerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
				'meta_key'       => '_product_cost',
				'meta_value'     => '',
				'meta_compare'   => '!='
			);

			$products = new WP_Query( $args );

			if ( $products->have_posts() ) {
				while ( $products->have_posts() ) {
					$products->the_post();
					$cost       = (float) get_post_meta( get_the_ID(), '_product_cost', true );
					$total_cost += $cost;
				}
			}
			wp_reset_postdata();
		}

		return $total_cost;
	}

	function wooprofit_total_profit_amount(): float|int {

		return $this->wooprofit_total_price_amount() - $this->wooprofit_total_cost_amount();
	}

	//  cost field
	function add_cost_field(): void {
		woocommerce_wp_text_input(
			array(
				'id'          => '_product_cost',
				'label'       => __( 'Cost', 'wooprofit-margin' ),
				'placeholder' => 'Enter the cost price',
				'desc_tip'    => 'true',
				'description' => __( 'Enter the cost price of the product.', 'wooprofit-margin' )
			)
		);
		echo '<p id="product_profit_display" class="form-field description">' . __( 'Profit: 0.00 (' . get_woocommerce_currency_symbol() . ' 0.00%)',
				'wooprofit' ) . '</p>';
	}

	// Save cost field
	function save_cost_field( $post_id ): void {
		$product_cost = isset( $_POST['_product_cost'] ) ? sanitize_text_field( $_POST['_product_cost'] ) : '';
		update_post_meta( $post_id, '_product_cost', $product_cost );
	}

	/**
	 * Add custom columns to the products admin page
	 **/
	function add_cost_and_profit_column_header( $columns ): array {
		// Remove the existing 'product_cost' and 'product_profit' columns if they already exist
		unset( $columns['product_cost'] );
		unset( $columns['product_profit'] );
		/**
		 *  Insert 'product_cost' and 'product_profit' after 'price' column
		 */

		$new_columns = array();
		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;
			if ( 'price' === $key ) {
				$new_columns['product_cost']   = __( 'Cost', 'wooprofit-margin' );
				$new_columns['product_profit'] = __( 'Profit', 'wooprofit-margin' );
			}
		}

		return $new_columns;
	}

	/*
	 * Populate custom columns with data
	 * */
	function populate_cost_and_profit_column_content( $column, $post_id ): void {
		if ( 'product_cost' === $column ) {
			$product_cost = get_post_meta( $post_id, '_product_cost', true );
			if ( $product_cost !== '' ) {
				echo esc_html( number_format( (float) $product_cost, 2 ) . get_woocommerce_currency_symbol() );
			} else {
				echo '-';
			}
		}

		if ( 'product_profit' === $column ) {
			$product_price = get_post_meta( $post_id, '_price', true );
			$product_cost  = get_post_meta( $post_id, '_product_cost', true );

			if ( $product_price && $product_cost !== '' ) {
				$profit            = $product_price - $product_cost;
				$profit_percentage = ( $product_cost > 0 ) ? ( $profit / $product_cost ) * 100 : 0;
				echo esc_html( number_format( (float) $profit, 2 ) . get_woocommerce_currency_symbol() . ' (' . number_format( $profit_percentage, 2 ) . '%)' );
			} else {
				echo '-';
			}
		}
	}

	function make_cost_and_profit_column_sortable( $columns ) {
		$columns['product_cost']   = 'product_cost';
		$columns['product_profit'] = 'product_profit';

		return $columns;
	}

	function sort_cost_and_profit_columns( $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'product_cost' === $orderby ) {
			$query->set( 'meta_key', '_product_cost' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ( 'product_profit' === $orderby ) {
			$query->set( 'meta_key', '_product_profit' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 *  WooProfit Margin Admin Page
	 */
	function wooprofit_page(): void {
		include_once plugin_dir_path( __FILE__ ) . 'templates/dashboard.php';
	}

	/* Date range */

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
			$average_order_value = $total_orders ? $total_sales / $total_orders : 0;

			echo json_encode( array(
				'total_orders'        => $total_orders,
				'total_sales'         => wc_price( $total_sales ),
				'net_sales'           => wc_price( $net_sales ),
				'total_cost'           => wc_price( $total_cost ),
				'average_order_value' => wc_price( $average_order_value ),
				'profit'              => wc_price( $total_profit ),
				'profit_class'        => $profit_class

			) );
		} else {
			echo json_encode( array(
				'total_orders'        => 0,
				'total_sales'         => wc_price( 0 ),
				'net_sales'           => wc_price( 0 ),
				'total_cost'           => wc_price( 0 ),
				'average_order_value' => wc_price( 0 ),
				'profit'              => wc_price( 0 ),
				'profit_class'        => 'profit-negative'
			) );
		}
		wp_reset_postdata();
		wp_die();
	}
}

new Wooprofit_Margin();
