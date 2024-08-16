<?php
/**
 * Plugin Name: WooProfit
 * Plugin URI: https://spider-themes.net/wooprofit/
 * Description: Effortlessly track and analyze product costs and profits in WooCommerce, empowering smarter financial decisions and enhanced profitability.
 * Version: 1.0.0
 * Requires at least: 5.7
 * Requires PHP: 7.4
 * Author: spider-themes
 * Author URI: https://spider-themes.net
 * Text Domain: wooprofit
 * Domain Path: /languages
 * Copyright: © 2024 Spider Themes
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins:  woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

const WOOPROFIT_COST_SETTINGS_PLUGIN_FILE = __FILE__;

class Wooprofit {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
	}

	public function activate(): void {
		flush_rewrite_rules();
	}

	public function init(): void {

		add_action( 'admin_menu', [ $this, 'admin_menu' ], 99 );
		/**
		 * Enqueue Assets
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'assets_load' ] );
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
		add_filter( 'plugin_action_links_' . plugin_basename( WOOPROFIT_COST_SETTINGS_PLUGIN_FILE ), array( $this, 'settings_action_links' ) );

		/**
		 * Date range
		 */
		add_action( 'wp_ajax_wooprofit_get_orders_by_date_range', [ $this, 'get_orders_by_date_range' ] );
		add_action( 'wp_ajax_nopriv_wooprofit_get_orders_by_date_range', [ $this, 'get_orders_by_date_range' ] );

		/**
		 * Order Comparison
		 */
		add_action( 'wp_ajax_wooprofit_compare_monthly_orders', [ $this, 'compare_monthly_orders' ] );
		add_action( 'wp_ajax_nopriv_wooprofit_compare_monthly_orders', [ $this, 'compare_monthly_orders' ] );

		/**
		 * Remove Default WordPress notice
		 */
		add_action( 'admin_head', [ $this, 'remove_wp_default_notifications' ] );

		/**
		 * add custom cost in Order
		 */
		add_action( 'woocommerce_admin_order_item_headers', [ $this, 'add_cost_column_header' ] );
		add_action( 'woocommerce_admin_order_item_values', [ $this, 'add_cost_column_value' ], 10, 3 );
		add_action( 'woocommerce_before_order_itemmeta_update', [ $this, 'save_custom_order_item_cost' ], 10, 1 );

		add_filter( 'manage_edit-shop_order_columns', [ $this, 'add_order_cost_column' ], 20 );
		add_action( 'manage_shop_order_posts_custom_column', [ $this, 'populate_order_cost_column' ], 10, 2 );

		/**
		 * meta box sidebar register for order items edit
		 */
		add_action( 'add_meta_boxes', [ $this, 'add_cost_profit_meta_box' ] );

		/**
		 * Remove third-party plugins notice
		 */
		add_action('admin_notices', [$this, 'remove_third_party_admin_notices'], 100);

		$this->total_profit();
		$this->total_stock();
	}

	/**
	 * remove default notification
	 * @return void
	 */
	function remove_wp_default_notifications(): void {
		$screen = get_current_screen();
		if ( $screen->id === 'toplevel_page_woo-profit' ) {
			remove_action( 'admin_notices', 'update_nag', 3 );
			// Remove admin footer text
			add_filter( 'admin_footer_text', '__return_empty_string', 11 );
			add_filter( 'update_footer', '__return_empty_string', 11 );
		}
	}

	/**
	 * Remove third-party notice
	 * @return void
	 *  remove third party notice
	 */
	public function remove_third_party_admin_notices(): void {
		// Check if we are on the specific plugin page
		$current_screen = get_current_screen();

		if ( $current_screen->id === 'toplevel_page_woo-profit') {
			remove_all_actions('admin_notices');
		}
	}

	/**
	 * Plugins setting setup
	 * @param $links
	 *
	 * @return mixed
	 */
	public function settings_action_links( $links ): mixed {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=wooprofit' ) ) . '">' . esc_html( 'Settings', 'wooprofit' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * add plugin setting option
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function add_cost_tab_to_woocommerce_settings( $settings ): mixed {
		$settings[] = include( 'templates/class-wooprofit-settings-cost.php' );

		return $settings;
	}

	/**
	 * Asset loading
	 *
	 * @param $hook
	 *
	 * @return void
	 */
	public function assets_load( $hook ): void {
		$screen         = get_current_screen();
		$plugin_data    = get_plugin_data( __FILE__ );
		$plugin_version = $plugin_data['Version'];
		$assets_dir     = plugins_url( 'assets/', __FILE__ );
		if ( $screen->id === 'wooprofit_page_woo-profit-bulk-discounts' ) {
			wp_enqueue_style( 'bulk-style', $assets_dir . 'css/bulk-dicount.css' );
		}

		/*if ( $screen->id === 'analytics_page_wc-analytics-profit' ) { */
		if ( $screen->id === 'toplevel_page_woo-profit' ) {
			wp_enqueue_style( 'wooprofit-style', $assets_dir . 'css/style.css' );
			wp_enqueue_style( 'wooprofit-nice', $assets_dir . 'css/nice-select.css' );
			wp_enqueue_style( 'google-font', '//fonts.googleapis.com/css?family=Montserrat' );
			wp_enqueue_style( 'font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
			wp_enqueue_script( 'tooltip', $assets_dir . 'js/tooltip.js', array( 'jquery' ), $plugin_version, [ 'in_footer' => true, 'strategy' => 'defer' ] );
			wp_enqueue_script( 'custom-date-range-script', $assets_dir . 'js/custom-date-range.js', array( 'jquery' ), $plugin_version,
				[ 'in_footer' => true, 'strategy' => 'defer' ] );
			wp_enqueue_script( 'nice-select', $assets_dir . 'js/jquery.nice-select.min.js', array(), $plugin_version,
				[ 'in_footer' => true, 'strategy' => 'defer' ] );
			wp_localize_script( 'custom-date-range-script', 'ajax_params', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wooprofit_compare_data' )
			) );
		}
		if ( $hook == 'post.php' || $hook == 'post-new.php' ) {
			global $post_type;
			if ( $post_type == 'product' ) {
				wp_enqueue_script( 'wooprofit', $assets_dir . 'js/profit-show.js', array( 'jquery' ), $plugin_version,
					[ 'in_footer' => true, 'strategy' => 'defer' ] );
			}
		}
	}

	/**
	 * Set Submenu to Woocommerce Analytics
	 * @return void
	 */
	/*public function admin_menu(): void {
		add_submenu_page(
			'wc-admin&path=/analytics/overview',
			esc_html( 'Profit', 'wooprofit' ),
			esc_html( 'Profit Margin', 'wooprofit' ),
			'manage_woocommerce',
			'wc-analytics-profit',
			[ $this, 'wooprofit_page' ]
		);
	}*/
	/**
	 * Set admin menu for wooprofit
	 * @return void
	 */
	public function admin_menu(): void {
		add_menu_page(
			__( 'WooProfit', 'woo-profit' ),
			__( 'WooProfit', 'woo-profit' ),
			'manage_woocommerce',
			'woo-profit',
			[ $this, 'wooprofit_page' ],
			'dashicons-money-alt',
			26
		);

		/**
		 * Rename the parent menu when displayed as a submenu
		 */
		add_submenu_page(
			'woo-profit',
			__( 'Overview', 'woo-profit' ),
			__( 'Overview', 'woo-profit' ),
			'manage_woocommerce',
			'woo-profit',
			[ $this, 'wooprofit_page' ]
		);

		/**
		 * Submenu: Bulk Discounts
		 */
		add_submenu_page(
			'woo-profit',
			__( 'Bulk Discount', 'woo-profit' ),
			__( 'Bulk Discount', 'woo-profit' ),
			'manage_woocommerce',
			'woo-profit-bulk-discounts',
			[ $this, 'bulk_discounts_page' ]
		);

		/**
		 * Submenu: Operation Cost
		 */
		add_submenu_page(
			'woo-profit',
			__( 'Cost of Operation', 'woo-profit' ),
			__( 'Cost of Operation', 'woo-profit' ),
			'manage_woocommerce',
			'woo-profit-operation-cost',
			[ $this, 'operation_cost_page' ]
		);

		/**
		 * Submenu: generate image
		 */
		add_submenu_page(
			'woo-profit',
			__( 'Generate Product Image', 'woo-profit' ),
			__( 'Generate Product Image', 'woo-profit' ),
			'manage_woocommerce',
			'woo-profit-image-generate',
			[ $this, 'image_generate_page' ]
		);
	}

	public function image_generate_page(): void {
		include_once plugin_dir_path( __FILE__ ) . 'templates/product-image-generate.php';
	}

	public function bulk_discounts_page(): void {
		include_once plugin_dir_path( __FILE__ ) . 'templates/bulk-discount.php';
	}

    public function operation_cost_page(): void {
	    include_once plugin_dir_path( __FILE__ ) . 'templates/cost-operation.php';
	}

	/**
	 * Total Cost Calculation
	 * @return int|null
	 */
	public function total_stock(): ?int {
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

	/**
	 * Total Price Calculation
	 * @return float|int
	 */
	public function total_price(): float|int {
		$total_price = 0;

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

	/**
	 * Total cost calculation
	 * @return float|int
	 */

	public function total_cost(): float|int {
		$total_cost = 0;

		if ( class_exists( 'WooCommerce' ) ) {
			// Check if the 'Cost of Goods for WooCommerce' plugin is activated
			$is_cog_activated = class_exists( 'Alg_WC_Cost_of_Goods_Core' );

			$meta_key = '_woo_product_cost';

			if ( $is_cog_activated ) {
				$meta_key = '_alg_wc_cog_cost';
			}

			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_key'       => $meta_key,
				'meta_value'     => '',
				'meta_compare'   => '!='
			);

			$products = new WP_Query( $args );

			if ( $products->have_posts() ) {
				while ( $products->have_posts() ) {
					$products->the_post();
					$cost = 0;

					// Check if '_woo_product_cost' is set
					$woo_cost = (float) get_post_meta( get_the_ID(), '_woo_product_cost', true );

					if ( $is_cog_activated ) {
						// If 'Cost of Goods for WooCommerce' is activated, use '_alg_wc_cog_cost'
						$cost = (float) get_post_meta( get_the_ID(), '_alg_wc_cog_cost', true );
					} elseif ( $woo_cost ) {
						// Otherwise, use '_woo_product_cost' if it's set
						$cost = $woo_cost;
					}

					$total_cost += $cost;
				}
			}
			wp_reset_postdata();
		}

		return $total_cost;
	}




	/**
	 * Total profit Calculate
	 * @return float|int
	 */
	public function total_profit(): float|int {
		return $this->total_price() - $this->total_cost();
	}

	/**
	 * add cost field for product page
	 * @return void
	 */
	public function add_cost_field(): void {
		woocommerce_wp_text_input(
			array(
				'id'          => '_woo_product_cost',
				'label'       => esc_html( 'Cost', 'wooprofit' ),
				'placeholder' => 'Enter the cost price',
				'desc_tip'    => 'true',
				'description' => esc_html( 'Enter the cost price of the product.', 'wooprofit' )
			)
		);
		echo '<p id="product_profit_display" class="form-field description">'
		     . esc_html( 'Profit: 0.00 (' . esc_html( get_woocommerce_currency_symbol() ) . ' 0.00%)', 'wooprofit' ) . '</p>';

	}

	/**
	 * Save cost field for product page
	 * @param $post_id
	 *
	 * @return void
	 */
	public function save_cost_field( $post_id ): void {
		$product_cost = isset( $_POST['_woo_product_cost'] ) ? sanitize_text_field( $_POST['_woo_product_cost'] ) : '';
		update_post_meta( $post_id, '_woo_product_cost', $product_cost );
	}

	/**
	 * Add custom columns to the products admin page
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_cost_and_profit_column_header( $columns ): array {
		// Remove the existing 'product_cost' and 'product_profit' columns if they already exist
		unset( $columns['product_cost'] );
		unset( $columns['product_profit'] );

		/**
		 * Insert 'product_cost' and 'product_profit' after 'price' column
		 */
		$new_columns = array();
		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;
			if ( 'price' === $key ) {
				$new_columns['product_cost']   = esc_html( 'Cost', 'wooprofit' );
				$new_columns['product_profit'] = esc_html( 'Profit', 'wooprofit' );
			}
		}

		return $new_columns;
	}

	/**
	 * Populate custom columns with data
	 * @param $column
	 * @param $post_id
	 *
	 * @return void
	 */
	public function populate_cost_and_profit_column_content( $column, $post_id ): void {
		if ( 'product_cost' === $column ) {
			$product_cost = get_post_meta( $post_id, '_woo_product_cost', true );
			if ( $product_cost !== '' ) {
				echo esc_html( number_format( (float) $product_cost, 2 ) . esc_html( get_woocommerce_currency_symbol() ) );
			} else {
				echo '-';
			}
		}

		if ( 'product_profit' === $column ) {
			$product_price = get_post_meta( $post_id, '_price', true );
			$product_cost  = get_post_meta( $post_id, '_woo_product_cost', true );

			if ( $product_price && $product_cost !== '' ) {
				$profit            = $product_price - $product_cost;
				$profit_percentage = ( $product_cost > 0 ) ? ( $profit / $product_cost ) * 100 : 0;
				echo esc_html( number_format( (float) $profit, 2 ) . esc_html( get_woocommerce_currency_symbol() ) . ' (' . number_format( $profit_percentage,
						2 ) . '%)' );
			} else {
				echo '-';
			}
		}
	}

	/**
	 * create cost and profit columns
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function make_cost_and_profit_column_sortable( $columns ): mixed {
		$columns['product_cost']   = 'product_cost';
		$columns['product_profit'] = 'product_profit';

		return $columns;
	}

	/**
	 * cost and profit columns sorting
	 * @param $query
	 *
	 * @return void
	 */
	public function sort_cost_and_profit_columns( $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		$orderby = $query->get( 'orderby' );

		if ( 'product_cost' === $orderby ) {
			$query->set( 'meta_key', '_woo_product_cost' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ( 'product_profit' === $orderby ) {
			$query->set( 'meta_key', '_product_profit' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * add Cost column in order items page
	 * @return void
	 */
	public function add_cost_column_header(): void {
		echo '<th class="cost">' . __( 'Wooprofit Cost', 'wooprofit' ) . '</th>';
	}

	/**
	 * Add cost column value
	 * @param $product
	 * @param $item
	 * @param $item_id
	 *
	 * @return void
	 */
	public function add_cost_column_value( $product, $item, $item_id ): void {
		// Ensure $product is a valid WC_Product object
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			echo '<td class="cost">' . __( 'N/A', 'wooprofit' ) . '</td>';

			return;
		}

		$product_id = $product->get_id();
		$cost       = get_post_meta( $product_id, '_woo_product_cost', true );
		echo '<td class="cost">' . wc_price( $cost ) . '</td>';
	}

	/**
	 * Save column order item cost
	 * @param $item_id
	 *
	 * @return void
	 * @throws Exception
	 */
	public function save_custom_order_item_cost( $item_id ): void {
		if ( isset( $_POST['order_item_cost'][ $item_id ] ) ) {
			$cost = wc_clean( $_POST['order_item_cost'][ $item_id ] );
			wc_update_order_item_meta( $item_id, '_woo_product_cost', $cost );
		}
	}

	/**
	 * Add order cost column
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function add_order_cost_column( $columns ): mixed {
		$columns['order_cost'] = __( 'NEW Cost', 'wooprofit' );

		return $columns;
	}

	/**
	 * Populate order cost column
	 * @param $column
	 * @param $post_id
	 *
	 * @return void
	 */
	public function populate_order_cost_column( $column, $post_id ): void {
		if ( $column === 'order_cost' ) {
			$order      = wc_get_order( $post_id );
			$items      = $order->get_items();
			$total_cost = 0;

			foreach ( $items as $item_id => $item ) {
				$product = $item->get_product();
				if ( $product ) {
					$cost       = get_post_meta( $product->get_id(), '_woo_product_cost', true );
					$total_cost += $cost * $item->get_quantity();
				}
			}

			echo wc_price( $total_cost );
		}
	}

	/**
	 * Date range function according to date range
	 * @return void
	 */

	public function get_orders_by_date_range(): void {

		global $wpdb;
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html( 'You do not have sufficient permissions to access this page.', 'wooprofit' ) );
		}

		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';

		if ( ! $start_date || ! $end_date ) {
			echo json_encode( [
				'error'   => true,
				'message' => esc_html( 'Please select a valid date range.', 'wooprofit' ),
			] );
			wp_die();
		}

		$args = array(
			'limit'        => -1,
			'status'       => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
			'date_created' => $start_date . '...' . $end_date,
		);

		$orders = wc_get_orders( $args );
		if ( ! empty( $orders ) ) {
			$total_orders = count( $orders );
			$total_sales  = 0;
			$total_cost   = 0;

			foreach ( $orders as $order ) {
				$total_sales += $order->get_total();

				foreach ( $order->get_items() as $item ) {
					$product_id = $item->get_product_id();

					// Default to using '_woo_product_cost'
					$product_cost = (float) get_post_meta( $product_id, '_woo_product_cost', true );

					// If 'Cost of Goods for WooCommerce' is activated, use '_alg_wc_cog_cost' instead
					if ( class_exists( 'Alg_WC_Cost_of_Goods' ) ) {
						$alg_cost = get_post_meta( $product_id, '_alg_wc_cog_cost', true );
						if ( ! empty( $alg_cost ) ) {
							$product_cost = (float) $alg_cost;
						}
					}

					$total_cost += $product_cost * $item->get_quantity();
				}
			}

			$currency_symbol = get_woocommerce_currency_symbol();

			$total_profit      = $total_sales - $total_cost;
			$profit_percentage = $total_sales ? ( $total_profit / $total_sales ) * 100 : 0;
			$profit_class      = $total_profit > 0 ? 'profit-positive' : 'profit-negative';

			$average_profit       = $total_profit / ( ( strtotime( $end_date ) - strtotime( $start_date ) ) / ( 60 * 60 * 24 ) + 1 );
			$average_order_profit = $total_orders ? $total_profit / $total_orders : 0;

			$chart_data = [
				'orders'  => [ $total_orders ],
				'profits' => [ $total_profit ],
				'sales'   => [ $total_sales ],
				'labels'  => [ $start_date, $end_date ]
			];

			echo json_encode( array(
				'total_orders'         => $total_orders,
				'total_sales'          => $currency_symbol . number_format( $total_sales, 2 ),
				'total_cost'           => $currency_symbol . number_format( $total_cost, 2 ),
				'profit'               => $currency_symbol . number_format( $total_profit, 2 ),
				'profit_percentage'    => round( $profit_percentage, 2 ) . '%',
				'profit_class'         => $profit_class,
				'average_profit'       => $currency_symbol . number_format( $average_profit, 2 ),
				'average_order_profit' => $currency_symbol . number_format( $average_order_profit, 2 ),
				'chart_data'           => $chart_data
			) );
		} else {
			$currency_symbol = get_woocommerce_currency_symbol();

			echo json_encode( array(
				'total_orders'         => '0',
				'total_sales'          => $currency_symbol . '0.00',
				'total_cost'           => $currency_symbol . '0.00',
				'profit'               => $currency_symbol . '0.00',
				'profit_percentage'    => '0%',
				'profit_class'         => 'profit-negative',
				'average_profit'       => $currency_symbol . '0.00',
				'average_order_profit' => $currency_symbol . '0.00',
				'chart_data'           => []
			) );
		}
		wp_reset_postdata();
		wp_die();
	}




	/**
	 * Comparison Query method
	 * @return void
	 */
	public function compare_monthly_orders(): void {
		check_ajax_referer('wooprofit_compare_data', 'nonce');

		// Retrieve date ranges from POST data
		$current_start_date = isset($_POST['current_start_date']) ? sanitize_text_field($_POST['current_start_date']) : date('Y-m-01');
		$current_end_date = isset($_POST['current_end_date']) ? sanitize_text_field($_POST['current_end_date']) : date('Y-m-t');
		$prev_start_date = sanitize_text_field($_POST['prev_start_date']);
		$prev_end_date = sanitize_text_field($_POST['prev_end_date']);

		if (!empty($prev_start_date) && !empty($prev_end_date)) {
			$current_data = $this->get_order_data($current_start_date, $current_end_date);
			$previous_data = $this->get_order_data($prev_start_date, $prev_end_date);

			$order_percentage_change = $this->calculate_percentage_change($current_data['total_orders'], $previous_data['total_orders']);
			$total_sales_percentage_change = $this->calculate_percentage_change($current_data['total_sales'], $previous_data['total_sales']);
			$total_cost_percentage_change = $this->calculate_percentage_change($current_data['total_cost'], $previous_data['total_cost']);
			$average_order_profit_percentage_change = $this->calculate_percentage_change($current_data['average_order_profit'], $previous_data['average_order_profit']);
			$total_profit_percentage_change = $this->calculate_percentage_change($current_data['total_profit'], $previous_data['total_profit']);
			$average_daily_profit_percentage_change = $this->calculate_percentage_change($current_data['average_daily_profit'], $previous_data['average_daily_profit']);

			$currency_symbol = get_woocommerce_currency_symbol();

			$result = [
				'success' => true,
				'data' => [
					'current_month' => $current_data,
					'previous_month' => $previous_data,
					'order_percentage_change' => $order_percentage_change,
					'total_sales_percentage_change' => $total_sales_percentage_change,
					'total_cost_percentage_change' => $total_cost_percentage_change,
					'average_order_profit_percentage_change' => $average_order_profit_percentage_change,
					'total_profit_percentage_change' => $total_profit_percentage_change,
					'average_daily_profit_percentage_change' => $average_daily_profit_percentage_change,
					'currency_symbol' => $currency_symbol
				]
			];

			wp_send_json($result);
		} else {
			wp_send_json([
				'success' => false,
				'message' => 'Invalid date ranges.'
			]);
		}
	}

	/**
	 * Calculate percentage change between two values
	 * @param $current_value
	 * @param $previous_value
	 *
	 * @return float|int
	 */
	private function calculate_percentage_change( $current_value, $previous_value ): float|int {
		if ( $previous_value == 0 ) {
			return $current_value > 0 ? 100 : 0; // Avoid division by zero, handle cases where previous value is zero
		}

		return ( ( $current_value - $previous_value ) / $previous_value ) * 100;
	}

	/**
	 * Get order data according to date range
	 * @param $start_date
	 * @param $end_date
	 *
	 * @return array
	 */
	private function get_order_data( $start_date, $end_date ): array {
		$orders = wc_get_orders( [
			'limit'        => - 1,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'date_created' => $start_date . '...' . $end_date,
		] );

		$total_sales  = 0;
		$total_cost   = 0;
		$total_profit = 0;
		$order_count  = count( $orders );

		foreach ( $orders as $order ) {
			$total  = $order->get_total();
			$cost   = $this->get_order_cost( $order );
			$profit = $total - $cost;

			$total_sales  += $total;
			$total_cost   += $cost;
			$total_profit += $profit;
		}

		$average_order_profit = $order_count > 0 ? $total_profit / $order_count : 0;
		$average_daily_profit = $total_profit / ( ( strtotime( $end_date ) - strtotime( $start_date ) ) / ( 60 * 60 * 24 ) + 1 );

		return [
			'total_orders'         => $order_count,
			'total_sales'          => $total_sales,
			'total_cost'           => $total_cost,
			'total_profit'         => $total_profit,
			'average_order_profit' => $average_order_profit,
			'average_daily_profit' => $average_daily_profit,
		];
	}

	/**
	 * Custom order cost function
	 * @param $order
	 *
	 * @return float|int
	 */
	public function get_order_cost( $order ): float|int {
		$cost = 0;
		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id = $item->get_product_id();
			$product    = wc_get_product( $product_id );
			if ( $product ) {
				$cost += $product->get_meta( '_woo_product_cost', true ) * $item->get_quantity();
			}
		}

		return $cost;
	}

	/**
	 * Meta box function
	 * @return void
	 */
	public function add_cost_profit_meta_box(): void {
		global $pagenow;
		$current_screen = get_current_screen();
		// Check if we are on the WooCommerce order edit page
		if ( $pagenow == 'admin.php' && $current_screen->id == 'woocommerce_page_wc-orders' ) {
			add_meta_box(
				'cost_profit_meta_box',
				__( 'Cost and Profit', 'wooprofit' ),
				[ $this, 'cost_profit_meta_box_html' ],
				'woocommerce_page_wc-orders',
				'side',
				'high'
			);
		}
	}

	/**
	 * Meta box output function
	 * @param $post
	 *
	 * @return void
	 */
	public function cost_profit_meta_box_html( $post ): void {

		// Get the order object
		$order = wc_get_order( $post->ID );
		if ( ! $order ) {
			error_log( 'Order not found' ); // Debug log

			return;
		}

		$total_cost = 0;

		// Loop through order items to calculate the total cost
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( $product ) {
				$quantity   = $item->get_quantity();
				$cost       = (float) get_post_meta( $product->get_id(), '_woo_product_cost', true ) * $quantity;
				$total_cost += $cost;
			}
		}

		// Calculate total sales and profit
		$total_sales  = $order->get_total();
		$total_profit = $total_sales - $total_cost;

		// Display cost and profit
		echo '<div>';
		echo '<p><strong>' . __( 'Cost:', 'wooprofit' ) . '</strong> ' . '<span style="color: #008000FF;">' . wc_price( $total_cost ) . '</span>' . '</p>';
		echo '<p><strong>' . __( 'Profit:', 'wooprofit' ) . '</strong> ' . '<span style="color: #FF0000FF;">' . wc_price( $total_profit ) . '</span>' . '</p>';
		echo '</div>';

	}

	/**
	 * WooProfit Admin Page
	 * @return void
	 */
	public function wooprofit_page(): void {
		include_once plugin_dir_path( __FILE__ ) . 'templates/dashboard.php';
	}

}

new Wooprofit();
