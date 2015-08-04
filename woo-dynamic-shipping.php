<?php

/**
 * Plugin Name: Woocommerce Dynamic Shipping
 * Plugin URI: http://www.test.com/
 * Description: A woocommerce add on to support dynamic shipping options as per users requirement
 * Version: 1.0
 * Author: Mehul Kaklotar
 * Author URI: http://mehulkaklotar.branded.me
 * Requires at least: 4.1
 * Tested up to: 4.2
 *
 * Text Domain: wdshipping
 *
 * @package WooCommerce_Dynamic_Shipping
 * @category Core
 * @author mehulkaklotar
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'WooCommerce_Dynamic_Shipping' ) ) {

	/**
	 * Main WooCommerce Dynamic Shipping Class
	 *
	 * @class WooCommerce_Dynamic_Shipping
	 * @version	0.1
	 */
	final class WooCommerce_Dynamic_Shipping {

		/**
		 * @var string
		 */
		public $version = '1.0';
		/**
		 * @var WooCommerce_Dynamic_Shipping The single instance of the class
		 * @since 2.1
		 */
		protected static $_instance = null;

		/**
		 * Main WooCommerce_Dynamic_Shipping Instance
		 *
		 * Ensures only one instance of WooCommerce_Dynamic_Shipping is loaded or can be loaded.
		 *
		 * @since 0.1
		 * @static
		 * @see WDShipping()
		 * @return WooCommerce_Dynamic_Shipping - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 * @since 0.1
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wdshipping' ), '0.1' );
		}
		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 2.1
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wdshipping' ), '0.1' );
		}

		/**
		 * WooCommerce_Custom_Emails Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->init_hooks();
		}

		/**
		 * Hook into actions and filters
		 * @since  0.1
		 */
		private function init_hooks() {
			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Define WCE Constants
		 */
		private function define_constants() {
			$this->define( 'WDShipping_PLUGIN_FILE', __FILE__ );
			$this->define( 'WDShipping_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WDShipping_VERSION', $this->version );
			$this->define( 'WDShipping_TEXT_DOMAIN', 'wdshipping' );
			$this->define( 'WDShipping_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'WDShipping_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			include_once( 'admin/class-wdshipping-admin.php' );
		}

		/**
		 * Init WooCommerce when WordPress Initialises.
		 */
		public function init() {
			$this->includes();
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

	}

}

/**
 * Returns the main instance of WDShipping to prevent the need to use globals.
 *
 * @since  0.1
 * @return WooCommerce_Dynamic_Shipping
 */
function WDShipping() {
	return WooCommerce_Dynamic_Shipping::instance();
}
WDShipping();
