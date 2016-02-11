<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WDShipping_Admin' ) ) {

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

			add_action( 'admin_menu', array( $this, 'wdshipping_settings_menu' ) );

			add_action( 'admin_init', array( $this, 'wdshipping_actions_details' ) );

			$this->wdshipping_generate_shipping_classes_init();

		}

		function wdshipping_settings_menu() {

			add_options_page( __( 'WooCommerce Dynamic Shipping', WDShipping_TEXT_DOMAIN ), 'WooCommerce Dynamic Shipping', 'manage_options', 'wdshipping-settings', array( $this, 'wdshipping_settings_callback' ) );

		}

		function wdshipping_settings_callback() {

			?>
			<div class="wrap">
				<h2><?php _e( 'Woocommerce Dynamic Shipping Settings', WDShipping_TEXT_DOMAIN ); ?></h2>
				<?php
				if ( ! isset( $_REQUEST['type'] ) ) {
					$type = 'today';
				} else {
					$type = $_REQUEST['type'];
				}
				$all_types = array( 'add-shipping', 'view-shipping' );
				if ( ! in_array( $type, $all_types ) ) {
					$type = 'add-shipping';
				}
				?>
				<ul class="subsubsub">
					<li class="today"><a class ="<?php echo ( 'add-shipping' == $type ) ? 'current' : ''; ?>" href="<?php echo add_query_arg( array( 'type' => 'add-shipping' ), admin_url( 'admin.php?page=wdshipping-settings' ) ); ?>"><?php _e( 'Add Shipping', WDShipping_TEXT_DOMAIN ); ?></a> |</li>
					<li class="today"><a class ="<?php echo ( 'view-shipping' == $type ) ? 'current' : ''; ?>" href="<?php echo add_query_arg( array( 'type' => 'view-shipping' ), admin_url( 'admin.php?page=wdshipping-settings' ) ); ?>"><?php _e( 'View Your Shippings', WDShipping_TEXT_DOMAIN ); ?></a></li>
				</ul>
				<?php $this->wdshipping_render_sections( $type ); ?>
			</div>
			<?php

		}

		function wdshipping_render_sections( $type ) {

			if ( 'add-shipping' == $type ) {
				$this->wdshipping_render_add_shipping_section();
			} else if ( 'view-shipping' == $type ) {
				$this->wdshipping_render_view_shipping_section();
			} else {
				$this->wdshipping_render_add_shipping_section();
			}

		}

		function wdshipping_render_add_shipping_section() {

			$wdshipping_detail = array();
			if ( isset( $_REQUEST['wdshipping_edit'] ) ) {
				$wdshipping_shipping_details = get_option( 'wdshipping_shipping_details', array() );
				if ( ! empty( $wdshipping_shipping_details ) ) {
					foreach ( $wdshipping_shipping_details as $key => $details ) {
						if ( $_REQUEST['wdshipping_edit'] == $key ) {
							$wdshipping_detail = $details;
						}
					}
				}
			}

			?>
			<form method="post" action="">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<?php _e( 'Title', WDShipping_TEXT_DOMAIN ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Title of Shipping. )' ); ?>
								</span>
						</th>
						<td>
							<input name="wdshipping_title" id="wdshipping_title" type="text" required value="<?php echo isset( $wdshipping_detail['title'] ) ? $wdshipping_detail['title'] : ''; ?>" placeholder="<?php _e( 'Title', WDShipping_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Description', WDShipping_TEXT_DOMAIN ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Description of shipping method. )' ); ?>
								</span>
						</th>
						<td>
							<input name="wdshipping_description" id="wdshipping_description" required type="text" value="<?php echo isset( $wdshipping_detail['description'] ) ? $wdshipping_detail['description'] : ''; ?>" placeholder="<?php _e( 'Description', WDShipping_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Enable?', WCEmails_TEXT_DOMAIN ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Enable this shipping here. )' ); ?>
								</span>
						</th>
						<td>
							<input name="wdshipping_enable" id="wdshipping_enable" type="checkbox" <?php echo ( isset( $wdshipping_detail['enable'] ) && 'on' == $wdshipping_detail['enable'] ) ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" name="wdshipping_submit" id="wdshipping_submit" class="button button-primary" value="Save Changes">
				</p>
				<?php
				if ( isset( $_REQUEST['wdshipping_edit'] ) ) {
					?>
					<input type="hidden" name="wdshipping_update" id="wdshipping_update" value="<?php echo $_REQUEST['wdshipping_edit']; ?>" />
					<?php
				}
				?>
			</form>
			<?php

		}

		function wdshipping_render_view_shipping_section() {

			?>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Title', WDShipping_TEXT_DOMAIN ); ?></th>
					<th><?php _e( 'Description', WDShipping_TEXT_DOMAIN ); ?></th>
					<th><?php _e( 'Enable', WDShipping_TEXT_DOMAIN ); ?></th>
					<th><?php _e( 'Action', WDShipping_TEXT_DOMAIN ); ?></th>
				</tr>
				<?php
				$wdshipping_shipping_details = get_option( 'wdshipping_shipping_details', array() );
				if ( ! empty( $wdshipping_shipping_details ) ) {
					foreach ( $wdshipping_shipping_details as $key => $details ) {
						?>
						<tr>
							<td><?php echo $details['title']; ?></td>
							<td><?php echo $details['description']; ?></td>
							<td><?php echo 'on' == $details['enable'] ? 'Yes' : 'No'; ?></td>
							<td>
								<a href="<?php echo add_query_arg( array( 'type' => 'add-shipping', 'wdshipping_edit' => $key ), admin_url( 'admin.php?page=wdshipping-settings' ) ); ?>" data-key="<?php echo $key; ?>"><?php _e( 'Edit', WDShipping_TEXT_DOMAIN ); ?></a>
								<a href="<?php echo add_query_arg( array( 'type' => 'view-shipping', 'wdshipping_delete' => $key ), admin_url( 'admin.php?page=wdshipping-settings' ) ); ?>" class="wdshipping_delete" data-key="<?php echo $key; ?>"><?php _e( 'Delete', WDShipping_TEXT_DOMAIN ); ?></a>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</table>
			<?php

		}

		function wdshipping_actions_details() {

			if ( isset( $_POST['wdshipping_submit'] ) ) {

				$title = filter_input( INPUT_POST, 'wdshipping_title',FILTER_SANITIZE_STRING );
				$description = filter_input( INPUT_POST, 'wdshipping_description',FILTER_SANITIZE_STRING );
				$enable = filter_input( INPUT_POST, 'wdshipping_enable',FILTER_SANITIZE_STRING );
				$enable = empty( $enable ) ? 'off' : $enable;

				$wdshipping_shipping_details = get_option( 'wdshipping_shipping_details', array() );

				$data = array(
					'title' => $title,
					'description' => $description,
					'enable' => $enable,
				);

				if ( isset( $_POST['wdshipping_update'] ) ) {
					if ( ! empty( $wdshipping_shipping_details ) ) {
						foreach ( $wdshipping_shipping_details as $key => $details ) {
							if ( $key == $_POST['wdshipping_update'] ) {
								$wdshipping_shipping_details[ $key ] = $data;
							}
						}
					}
				} else {
					array_push( $wdshipping_shipping_details, $data );
				}

				update_option( 'wdshipping_shipping_details', $wdshipping_shipping_details );

				add_settings_error( 'wdshipping-settings', 'error_code', $title.' is saved and if you have enabled it then you can see it in Woocommerce Shipping Settings Now', 'success' );

			} else if ( isset( $_REQUEST['wdshipping_delete'] ) ) {

				$wdshipping_shipping_details = get_option( 'wdshipping_shipping_details', array() );

				$delete_key = $_REQUEST['wdshipping_delete'];

				if ( ! empty( $wdshipping_shipping_details ) ) {
					foreach ( $wdshipping_shipping_details as $key => $details ) {
						if ( $key == $delete_key ) {
							unset( $wdshipping_shipping_details[ $key ] );
						}
					}
				}

				update_option( 'wdshipping_shipping_details', $wdshipping_shipping_details );

				add_settings_error( 'wdshipping-settings', 'error_code', 'Shipping settings deleted!', 'success' );

			}

		}

		function wdshipping_generate_shipping_classes_init() {

			add_action( 'woocommerce_load_shipping_methods', array( $this, 'wdshipping_generate_shipping_classes' ) );

		}

		function wdshipping_generate_shipping_classes() {

			$wdshipping_shipping_details = get_option( 'wdshipping_shipping_details', array() );

			if ( ! empty( $wdshipping_shipping_details ) ) {
				foreach ( $wdshipping_shipping_details as $key => $method ) {

					$enable = $method['enable'];

					if ( 'on' == $enable ) {

						$title          = $method['title'];
						$description    = $method['description'];

						$id = str_replace( ' ', '_', $title );

						$wdshipping_instance = new WDShipping_Instance( $id, $title, $description );
						woocommerce_register_shipping_method( $wdshipping_instance );
					}
				}
			}

		}

	}

}

/**
 * Returns the main instance of WDShipping_Admin to prevent the need to use globals.
 *
 * @since  0.1
 * @return WDShipping_Admin
 */
function woo_dynamic_shipping_admin() {
	return WDShipping_Admin::instance();
}
woo_dynamic_shipping_admin();
