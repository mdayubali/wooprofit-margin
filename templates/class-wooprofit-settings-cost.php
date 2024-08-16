<?php
/**
 * WooCommerce Cost Settings Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_Settings_Page' ) ) {
	class Wooprofit_Settings_Cost extends WC_Settings_Page {

		public function __construct() {
			$this->id    = 'wooprofit';
			$this->label = __( 'Wooprofit Margin', 'wooprofit' );

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		}

		public function get_sections() {
			$sections = array(
				'' => __( 'General', 'wooprofit' ),
			);

			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
		}

		public function get_settings( $current_section = '' ) {
			$settings = array();

			if ( $current_section == '' ) {
				$settings = array(
					'section_title' => array(
						'name' => __( 'Cost Settings', 'wooprofit' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'cost_settings_section_title'
					)
				);
			}

			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
		}

		public function output() {
			global $current_section;

			$settings = $this->get_settings( $current_section );
			WC_Admin_Settings::output_fields( $settings );
		}

		public function save() {
			global $current_section;

			$settings = $this->get_settings( $current_section );
			WC_Admin_Settings::save_fields( $settings );
		}
	}

	return new Wooprofit_Settings_Cost();
}
