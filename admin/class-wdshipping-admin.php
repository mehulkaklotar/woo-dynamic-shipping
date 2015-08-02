<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'WDShipping_Admin' ) ) {

	/**
	 * Admin WooCommerce Dynamic Shipping Class
	 *
	 * @class WDShipping_Admin
	 * @version	0.1
	 */
	class WDShipping_Admin {

		/**
		 * @var WDShipping_Admin The single instance of the class
		 * @since 0.1
		 */
		protected static $_instance = null;

		/**
		 * Main WDShipping_Admin Instance
		 *
		 * Ensures only one instance of WDShipping_Admin is loaded or can be loaded.
		 *
		 * @since 0.1
		 * @static
		 * @return WDShipping_Admin - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

		}

	}

}

/**
 * Returns the main instance of WDShipping_Admin to prevent the need to use globals.
 *
 * @since  0.1
 * @return WDShipping_Admin
 */
function WDShipping_Admin() {
	return WDShipping_Admin::instance();
}
WDShipping_Admin();
