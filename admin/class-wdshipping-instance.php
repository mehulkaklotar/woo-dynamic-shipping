<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'WDShipping_Instance' ) ) {

	/**
	 * Shipping Method
	 *
	 * A simple shipping method
	 *
	 * @class        WDShipping_Instance
	 * @version        1.0
	 * @package        woo-dynamic-shipping
	 * @author        Mehul <mehul.kaklotar@gmail.com>
	 */
	class WDShipping_Instance extends WC_Shipping_Method {

		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 */
		public function __construct( $id, $title, $description ) {
			$this->id                 = $id; // Id for your shipping method. Should be uunique.
			$this->method_title       = __( $title );  // Title shown in admin
			$this->method_description = __( $description ); // Description shown in admin

			$this->enabled = "yes"; // This can be added as an setting but for this example its forced enabled
			$this->title   = $title; // This can be added as an setting but for this example its forced.

			$this->init();
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		function init() {
			// Load the settings API
			$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
			$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

			// Save settings in admin if you have any defined
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 *
		 * @param mixed $package
		 *
		 * @return void
		 */
		public function calculate_shipping( $package ) {
			$rate = array(
				'id'       => $this->id,
				'label'    => $this->title,
				'cost'     => '10.99',
				'calc_tax' => 'per_item'
			);

			// Register the rate
			$this->add_rate( $rate );
		}

	}

}

