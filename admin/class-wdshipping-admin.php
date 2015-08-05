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

			add_action( 'admin_init', array( $this, 'wdshipping_email_actions_details' ) );

			add_action( 'admin_init', array( $this, 'wdshipping_generate_shipping_classes_init' ) );

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

		function wdshipping_email_actions_details() {

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

			add_action( 'woocommerce_shipping_init', array( $this, 'wdshipping_generate_shipping_classes' ) );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'wdshipping_add_ezway_shipping_method' ) );

		}

		function wdshipping_add_ezway_shipping_method( $shipping_methods ) {

			$wdshipping_shipping_details = get_option( 'wdshipping_shipping_details', array() );

			if ( ! empty( $wdshipping_shipping_details ) ) {
				foreach ( $wdshipping_shipping_details as $key => $method ) {

					$enable = $method['enable'];

					if ( 'on' == $enable ) {

						$title          = $method['title'];
						$id = str_replace( ' ', '_', $title );

						$shipping_methods[] = 'WC_' . $id . '_Shipping';

					}
				}
			}

			return $shipping_methods;
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

						eval('
                        class WC_'.$id."_Shipping extends WC_Shipping_Method {

							/**
							 * __construct function.
							 *
							 * @access public
							 * @return void
							 */
							function __construct() {
								\$this->id 						= '".$id."';
								\$this->method_title 			= __( '".$title."', 'woocommerce' );
								\$this->flat_rate_option 		= 'woocommerce_".$id."';
								\$this->method_description 		= __( '".$description."', 'woocommerce' );

								add_action( 'woocommerce_update_options_shipping_' . \$this->id, array( \$this, 'process_admin_options' ) );
								add_action( 'woocommerce_update_options_shipping_' . \$this->id, array( \$this, 'process_flat_rates' ) );
								add_filter( 'woocommerce_settings_api_sanitized_fields_' . \$this->id, array( \$this, 'save_default_costs' ) );

								\$this->init();
							}

							/**
							 * init function.
							 *
							 * @access public
							 * @return void
							 */
							function init() {

								// Load the settings.
								\$this->init_form_fields();
								\$this->init_settings();

								// Define user set variables
								\$this->title 		  = \$this->get_option( 'title' );
								\$this->availability   = \$this->get_option( 'availability' );
								\$this->countries 	  = \$this->get_option( 'countries' );
								\$this->type 		  = \$this->get_option( 'type' );
								\$this->tax_status	  = \$this->get_option( 'tax_status' );
								\$this->cost 		  = \$this->get_option( 'cost' );
								\$this->cost_per_order = \$this->get_option( 'cost_per_order' );
								\$this->fee 			  = \$this->get_option( 'fee' );
								\$this->minimum_fee 	  = \$this->get_option( 'minimum_fee' );
								\$this->options 		  = (array) explode( '\n', \$this->get_option( 'options' ) );

								// Load Flat rates
								\$this->get_flat_rates();
							}


							/**
							 * Initialise Gateway Settings Form Fields
							 *
							 * @access public
							 * @return void
							 */
							function init_form_fields() {

								\$this->form_fields = array(
									'enabled' => array(
													'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
													'type' 			=> 'checkbox',
													'label' 		=> __( 'Enable this shipping method', 'woocommerce' ),
													'default' 		=> 'no',
												),
									'title' => array(
													'title' 		=> __( 'Method Title', 'woocommerce' ),
													'type' 			=> 'text',
													'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
													'default'		=> __( 'Flat Rate', 'woocommerce' ),
													'desc_tip'		=> true
												),
									'availability' => array(
													'title' 		=> __( 'Availability', 'woocommerce' ),
													'type' 			=> 'select',
													'default' 		=> 'all',
													'class'			=> 'availability',
													'options'		=> array(
														'all' 		=> __( 'All allowed countries', 'woocommerce' ),
														'specific' 	=> __( 'Specific Countries', 'woocommerce' ),
													),
												),
									'countries' => array(
													'title' 		=> __( 'Specific Countries', 'woocommerce' ),
													'type' 			=> 'multiselect',
													'class'			=> 'chosen_select',
													'css'			=> 'width: 450px;',
													'default' 		=> '',
													'options'		=> WC()->countries->get_shipping_countries(),
													'custom_attributes' => array(
														'data-placeholder' => __( 'Select some countries', 'woocommerce' )
													)
												),
									'tax_status' => array(
													'title' 		=> __( 'Tax Status', 'woocommerce' ),
													'type' 			=> 'select',
													'default' 		=> 'taxable',
													'options'		=> array(
														'taxable' 	=> __( 'Taxable', 'woocommerce' ),
														'none' 		=> _x( 'None', 'Tax status', 'woocommerce' )
													),
												),
									'cost_per_order' => array(
													'title' 		=> __( 'Cost per order', 'woocommerce' ),
													'type' 			=> 'price',
													'placeholder'	=> wc_format_localized_price( 0 ),
													'description'	=> __( 'Enter a cost (excluding tax) per order, e.g. 5.00. Default is 0.', 'woocommerce' ),
													'default'		=> '',
													'desc_tip'		=> true
												),
									'options' => array(
													'title' 		=> __( 'Additional Rates', 'woocommerce' ),
													'type' 			=> 'textarea',
													'description'	=> __( 'Optional extra shipping options with additional costs (one per line): Option Name | Additional Cost [+- Percents] | Per Cost Type (order, class, or item) Example: <code>Priority Mail | 6.95 [+ 0.2%] | order</code>.', 'woocommerce' ),
													'default'		=> '',
													'desc_tip'		=> true,
													'placeholder'	=> __( 'Option Name | Additional Cost [+- Percents%] | Per Cost Type (order, class, or item)', 'woocommerce' )
												),
									'additional_costs' => array(
													'title'			=> __( 'Additional Costs', 'woocommerce' ),
													'type'			=> 'title',
													'description'   => __( 'Additional costs can be added below - these will all be added to the per-order cost above.', 'woocommerce' )
												),
									'type' => array(
													'title' 		=> __( 'Costs Added...', 'woocommerce' ),
													'type' 			=> 'select',
													'default' 		=> 'order',
													'options' 		=> array(
														'order' 	=> __( 'Per Order - charge shipping for the entire order as a whole', 'woocommerce' ),
														'item' 		=> __( 'Per Item - charge shipping for each item individually', 'woocommerce' ),
														'class' 	=> __( 'Per Class - charge shipping for each shipping class in an order', 'woocommerce' ),
													),
												),
									'additional_costs_table' => array(
												'type'				=> 'additional_costs_table'
												),
									'minimum_fee' => array(
													'title' 		=> __( 'Minimum Handling Fee', 'woocommerce' ),
													'type' 			=> 'price',
													'placeholder'	=> wc_format_localized_price( 0 ),
													'description'	=> __( 'Enter a minimum fee amount. Fee\'s less than this will be increased. Leave blank to disable.', 'woocommerce' ),
													'default'		=> '',
													'desc_tip'		=> true
												),
									);

							}


							function calculate_shipping( \$package = array() ) {

								\$this->rates 		= array();
								\$cost_per_order 	= ( isset( \$this->cost_per_order ) && ! empty( \$this->cost_per_order ) ) ? \$this->cost_per_order : 0;

								if ( \$this->type == 'order' ) {

									\$shipping_total = \$this->order_shipping( \$package );

									if ( ! is_null( \$shipping_total ) || \$cost_per_order > 0 ) {
										\$rate = array(
											'id' 	=> \$this->id,
											'label' => \$this->title,
											'cost' 	=> \$shipping_total + \$cost_per_order,
										);
									}

								} elseif ( \$this->type == 'class' ) {

									\$shipping_total = \$this->class_shipping( \$package );

									if ( ! is_null( \$shipping_total ) || \$cost_per_order > 0 ) {
										\$rate = array(
											'id' 	=> \$this->id,
											'label' => \$this->title,
											'cost' 	=> \$shipping_total + \$cost_per_order,
										);
									}

								} elseif ( \$this->type == 'item' ) {

									\$costs = \$this->item_shipping( \$package );

									if ( ! is_null( \$costs ) || \$cost_per_order > 0 ) {

										if ( ! is_array( \$costs ) ) {
											\$costs = array();
										}

										\$costs['order'] = \$cost_per_order;

										\$rate = array(
											'id' 		=> \$this->id,
											'label' 	=> \$this->title,
											'cost' 		=> \$costs,
											'calc_tax' 	=> 'per_item',
										);

									}
								}

								if ( isset( \$rate ) ) {
									\$this->add_rate( \$rate );
								}

								// Add any extra rates
								if ( sizeof( \$this->options ) > 0) {

									if ( ! isset( \$rate ) ) {
										\$rate = array(
											'id' 	=> \$this->id,
											'label' => \$this->title,
											'cost' 	=> 0,
										);
									}

									// Get item qty
									\$total_quantity = 0;

									foreach ( \$package['contents'] as \$item_id => \$values ) {
										if ( \$values['quantity'] > 0 && \$values['data']->needs_shipping() ) {
											\$total_quantity += \$values['quantity'];
										}
									}

									// Loop options
									foreach ( \$this->options as \$option ) {

										\$this_option = array_map( 'trim', explode( WC_DELIMITER, \$option ) );

										if ( sizeof( \$this_option ) !== 3 ) continue;

										\$extra_rate = \$rate;

										\$extra_rate['id']    = \$this->id . ':' . urldecode( sanitize_title( \$this_option[0] ) );
										\$extra_rate['label'] = \$this_option[0];
										\$this_cost           = \$this_option[1];
										\$this_cost_percents  = '';

										\$pattern =
											'/' .           // start regex
											'(\d+\.?\d*)' . // capture digits, optionally capture a `.` and more digits
											'\s*' .         // match whitespace
											'(\+|-)' .      // capture the operand
											'\s*'.          // match whitespace
											'(\d+\.?\d*)'.  // capture digits, optionally capture a `.` and more digits
											'\%/';          // match the percent sign & end regex
										if ( preg_match( \$pattern, \$this_cost, \$this_cost_matches ) ) {
											\$this_cost_mathop = \$this_cost_matches[2];
											\$this_cost_percents = \$this_cost_matches[3] / 100;
											\$this_cost = \$this_cost_matches[1];
											unset( \$this_cost_matches );
										}

										// Backwards compat with yes and no
										if ( \$this_option[2] == 'yes' ) {
											\$this_type = 'order';
										} elseif ( \$this_option[2] == 'no' ) {
											\$this_type = \$this->type;
										} else {
											\$this_type = \$this_option[2];
										}

										switch ( \$this_type ) {
											case 'class' :
												\$this_cost = \$this_cost * sizeof( \$this->find_shipping_classes( \$package ) );

												if ( \$this_cost_percents ) {
													foreach ( \$this->find_shipping_classes( \$package ) as \$shipping_class => \$items ){
														foreach ( \$items as \$item_id => \$values ) {
															\$this_cost = \$this->calc_percentage_adjustment( \$this_cost, \$this_cost_percents, \$this_cost_mathop, \$values['line_total'] );
														}
													}
												}
											break;
											case 'item' :
												\$this_cost = \$this_cost * \$total_quantity;

												if ( \$this_cost_percents ) {
													foreach ( \$package['contents'] as \$item_id => \$values ) {
														\$this_cost = \$this->calc_percentage_adjustment( \$this_cost, \$this_cost_percents, \$this_cost_mathop, \$values['line_total'] );
													}
												}
											break;
											case  'order' :
												if ( \$this_cost_percents ) {
													\$this_cost = \$this->calc_percentage_adjustment( \$this_cost, \$this_cost_percents, \$this_cost_mathop, \$package['contents_cost'] );
												}
											break;
										}

										// Per item rates
										if ( is_array( \$extra_rate['cost'] ) ) \$extra_rate['cost']['order'] = \$extra_rate['cost']['order'] + \$this_cost;

										// Per order or class rates
										else \$extra_rate['cost'] = \$extra_rate['cost'] + \$this_cost;

										\$this->add_rate( \$extra_rate );
									}
								}
							}


							function calc_percentage_adjustment( \$cost, \$percent_adjustment, \$percent_operator, \$base_price ) {
								if ( '+' == \$percent_operator ) {
									\$cost += \$percent_adjustment * \$base_price;
								} else {
									\$cost -= \$percent_adjustment * \$base_price;
								}

								return \$cost;
							}


							function order_shipping( \$package ) {
								\$cost 	= null;
								\$fee 	= null;

								if ( sizeof( \$this->flat_rates ) > 0 ) {

									\$found_shipping_classes = \$this->find_shipping_classes( \$package );

									// Find most expensive class (if found)
									foreach ( \$found_shipping_classes as \$shipping_class => \$products ) {
										if ( isset( \$this->flat_rates[ \$shipping_class ] ) ) {
											if ( \$this->flat_rates[ \$shipping_class ]['cost'] > \$cost ) {
												\$cost 	= \$this->flat_rates[ \$shipping_class ]['cost'];
												\$fee	= \$this->flat_rates[ \$shipping_class ]['fee'];
											}
										} else {
											// No matching classes so use defaults
											if ( ! empty( \$this->cost ) && \$this->cost > \$cost ) {
												\$cost 	= \$this->cost;
												\$fee	= \$this->fee;
											}
										}
									}

								}

								// Default rates if set
								if ( is_null( \$cost ) && \$this->cost !== '' ) {
									\$cost 	= \$this->cost;
									\$fee 	= \$this->fee;
								} elseif ( is_null( \$cost ) ) {
									// Set rates to 0 if nothing is set by the user
									\$cost 	= 0;
									\$fee 	= 0;
								}

								// Shipping for whole order
								return \$cost + \$this->get_fee( \$fee, \$package['contents_cost'] );
							}


							function class_shipping( \$package ) {
								\$cost 	= null;
								\$fee 	= null;
								\$matched = false;

								if ( sizeof( \$this->flat_rates ) > 0 || \$this->cost !== '' ) {

									// Find shipping classes for products in the cart.
									\$found_shipping_classes = \$this->find_shipping_classes( \$package );

									//  Store prices too, so we can calc a fee for the class.
									\$found_shipping_classes_values = array();

									foreach ( \$found_shipping_classes as \$shipping_class => \$products ) {
										if ( ! isset( \$found_shipping_classes_values[ \$shipping_class ] ) ) {
											\$found_shipping_classes_values[ \$shipping_class ] = 0;
										}

										foreach ( \$products as \$product ) {
											\$found_shipping_classes_values[ \$shipping_class ] += \$product['data']->get_price() * \$product['quantity'];
										}
									}

									// For each found class, add up the costs and fees
									foreach ( \$found_shipping_classes_values as \$shipping_class => \$class_price ) {
										if ( isset( \$this->flat_rates[ \$shipping_class ] ) ) {
											\$cost 	+= \$this->flat_rates[ \$shipping_class ]['cost'];
											\$fee	+= \$this->get_fee( \$this->flat_rates[ \$shipping_class ]['fee'], \$class_price );
											\$matched = true;
										} elseif ( \$this->cost !== '' ) {
											// Class not set so we use default rate if its set
											\$cost 	+= \$this->cost;
											\$fee	+= \$this->get_fee( \$this->fee, \$class_price );
											\$matched = true;
										}
									}
								}

								// Total
								if ( \$matched ) {
									return \$cost + \$fee;
								} else {
									return null;
								}
							}


							function item_shipping( \$package ) {
								// Per item shipping so we pass an array of costs (per item) instead of a single value
								\$costs = array();

								\$matched = false;

								// Shipping per item
								foreach ( \$package['contents'] as \$item_id => \$values ) {
									\$_product = \$values['data'];

									if ( \$values['quantity'] > 0 && \$_product->needs_shipping() ) {
										\$shipping_class = \$_product->get_shipping_class();

										\$fee = \$cost = 0;

										if ( isset( \$this->flat_rates[ \$shipping_class ] ) ) {
											\$cost 	= \$this->flat_rates[ \$shipping_class ]['cost'];
											\$fee	= \$this->get_fee( \$this->flat_rates[ \$shipping_class ]['fee'], \$_product->get_price() );
											\$matched = true;
										} elseif ( \$this->cost !== '' ) {
											\$cost 	= \$this->cost;
											\$fee	= \$this->get_fee( \$this->fee, \$_product->get_price() );
											\$matched = true;
										}

										\$costs[ \$item_id ] = ( ( \$cost + \$fee ) * \$values['quantity'] );
									}
								}

								if ( \$matched ) {
									return \$costs;
								} else {
									return null;
								}
							}

							public function find_shipping_classes( \$package ) {
								\$found_shipping_classes = array();

								// Find shipping classes for products in the cart
								if ( sizeof( \$package['contents'] ) > 0 ) {
									foreach ( \$package['contents'] as \$item_id => \$values ) {
										if ( \$values['data']->needs_shipping() ) {
											\$found_class = \$values['data']->get_shipping_class();
											if ( ! isset( \$found_shipping_classes[ \$found_class ] ) ) {
												\$found_shipping_classes[ \$found_class ] = array();
											}

											\$found_shipping_classes[ \$found_class ][ \$item_id ] = \$values;
										}
									}
								}

								return \$found_shipping_classes;
							}

							function validate_additional_costs_table_field( \$key ) {
								return false;
							}

							/**
							 * generate_additional_costs_html function.
							 *
							 * @access public
							 * @return string
							 */
							function generate_additional_costs_table_html() {
								ob_start();
								?>
								<tr valign='top'>
									<th scope='row' class='titledesc'><?php _e( 'Costs', 'woocommerce' ); ?>:</th>
									<td class='forminp' id='<?php echo \$this->id; ?>_flat_rates'>
										<table class='shippingrows widefat' cellspacing='0'>
											<thead>
												<tr>
													<th class='check-column'><input type='checkbox'></th>
													<th class='shipping_class'><?php _e( 'Shipping Class', 'woocommerce' ); ?></th>
													<th><?php _e( 'Cost', 'woocommerce' ); ?> <a class='tips' data-tip='<?php _e( 'Cost, excluding tax.', 'woocommerce' ); ?>'>[?]</a></th>
													<th><?php _e( 'Handling Fee', 'woocommerce' ); ?> <a class='tips' data-tip='<?php _e( 'Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%.', 'woocommerce' ); ?>'>[?]</a></th>
												</tr>
											</thead>
											<tbody class='flat_rates'>
												<tr>
													<td></td>
													<td class='flat_rate_class'><?php _e( 'Any class', 'woocommerce' ); ?></td>
													<td><input type='text' value='<?php echo esc_attr( wc_format_localized_price( \$this->cost ) ); ?>' name='default_cost' placeholder='<?php _e( 'N/A', 'woocommerce' ); ?>' size='4' class='wc_input_price' /></td>
													<td><input type='text' value='<?php echo esc_attr( wc_format_localized_price( \$this->fee ) ); ?>' name='default_fee' placeholder='<?php _e( 'N/A', 'woocommerce' ); ?>' size='4' class='wc_input_price' /></td>
												</tr>
												<?php
												\$i = -1;
												if ( \$this->flat_rates ) {
													foreach ( \$this->flat_rates as \$class => \$rate ) {
														\$i++;

														echo '<tr class=\"flat_rate\">
															<th class=\"check-column\"><input type=\"checkbox\" name=\"select\" /></th>
															<td class=\"flat_rate_class\">
																	<select name=\"' . esc_attr( \$this->id . '_class[' . \$i . ']' ) . '\" class=\"select\">';

														if ( WC()->shipping->get_shipping_classes() ) {
															foreach ( WC()->shipping->get_shipping_classes() as \$shipping_class ) {
																echo '<option value=\"' . esc_attr( \$shipping_class->slug ) . '\" '.selected(\$shipping_class->slug, \$class, false).'>'.\$shipping_class->name.'</option>';
															}
														} else {
															echo '<option value=\"\">'.__( 'Select a class&hellip;', 'woocommerce' ).'</option>';
														}

														echo '</select>
													        </td>
															<td><input type=\"text\" value=\"' . esc_attr( \$rate['cost'] ) . '\" name=\"' . esc_attr( \$this->id .'_cost[' . \$i . ']' ) . '\" placeholder=\"' . wc_format_localized_price( 0 ) . '\" size=\"4\" class=\"wc_input_price\" /></td>
															<td><input type=\"text\" value=\"' . esc_attr( \$rate['fee'] ) . '\" name=\"' . esc_attr( \$this->id .'_fee[' . \$i . ']' ) . '\" placeholder=\"' . wc_format_localized_price( 0 ) . '\" size=\"4\" class=\"wc_input_price\" /></td>
														</tr>';
													}
												}
												?>
											</tbody>
											<tfoot>
												<tr>
													<th colspan=\"4\"><a href=\"#\" class=\"add button\"><?php _e( 'Add Cost', 'woocommerce' ); ?></a> <a href=\"#\" class=\"remove button\"><?php _e( 'Delete selected costs', 'woocommerce' ); ?></a></th>
												</tr>
											</tfoot>
										</table>
									    <script type=\"text/javascript\">
											jQuery(function() {

												jQuery('#<?php echo \$this->id; ?>_flat_rates').on( 'click', 'a.add', function(){

													var size = jQuery('#<?php echo \$this->id; ?>_flat_rates tbody .flat_rate').size();

													jQuery('<tr class=\"flat_rate\">\
														<th class=\"check-column\"><input type=\"checkbox\" name=\"select\" /></th>\
														<td class=\"flat_rate_class\">\
															<select name=\"<?php echo \$this->id; ?>_class[' + size + ']\" class=\"select\">\
												                <?php
												                if (WC()->shipping->get_shipping_classes()) :
																	foreach (WC()->shipping->get_shipping_classes() as \$class) :
																		echo '<option value=\"' . esc_attr( \$class->slug ) . '\">' . esc_js( \$class->name ) . '</option>';
																	endforeach;
																else :
																	echo '<option value=\"\">'.__( 'Select a class&hellip;', 'woocommerce' ).'</option>';
																endif;
												                ?>\
												            </select>\
												        </td>\
														<td><input type=\"text\" name=\"<?php echo \$this->id; ?>_cost[' + size + ']\" placeholder=\"<?php echo wc_format_localized_price( 0 ); ?>\" size=\"4\" class=\"wc_input_price\" /></td>\
														<td><input type=\"text\" name=\"<?php echo \$this->id; ?>_fee[' + size + ']\" placeholder=\"<?php echo wc_format_localized_price( 0 ); ?>\" size=\"4\" class=\"wc_input_price\" /></td>\
													</tr>').appendTo('#<?php echo \$this->id; ?>_flat_rates table tbody');

													return false;
												});

												// Remove row
												jQuery('#<?php echo \$this->id; ?>_flat_rates').on( 'click', 'a.remove', function(){
													var answer = confirm(\"<?php _e( 'Delete the selected rates?', 'woocommerce' ); ?>\");
													if (answer) {
														jQuery('#<?php echo \$this->id; ?>_flat_rates table tbody tr th.check-column input:checked').each(function(i, el){
															jQuery(el).closest('tr').remove();
														});
													}
													return false;
												});

											});
										</script>
									</td>
								</tr>
								<?php
								return ob_get_clean();
							}

							function process_flat_rates() {
								// Save the rates
								\$flat_rate_class = array();
								\$flat_rate_cost = array();
								\$flat_rate_fee = array();
								\$flat_rates = array();

								if ( isset( \$_POST[ \$this->id . '_class'] ) ) \$flat_rate_class = array_map( 'wc_clean', \$_POST[ \$this->id . '_class'] );
								if ( isset( \$_POST[ \$this->id . '_cost'] ) )  \$flat_rate_cost  = array_map( 'stripslashes', \$_POST[ \$this->id . '_cost'] );
								if ( isset( \$_POST[ \$this->id . '_fee'] ) )   \$flat_rate_fee   = array_map( 'stripslashes', \$_POST[ \$this->id . '_fee'] );

								// Get max key
								\$values = \$flat_rate_class;
								ksort( \$values );
								\$value = end( \$values );
								\$key = key( \$values );

								for ( \$i = 0; \$i <= \$key; \$i++ ) {
									if ( ! empty( \$flat_rate_class[ \$i ] ) && isset( \$flat_rate_cost[ \$i ] ) && isset( \$flat_rate_fee[ \$i ] ) ) {

										\$flat_rate_cost[ \$i ] = wc_format_decimal( \$flat_rate_cost[\$i] );

										if ( ! strstr( \$flat_rate_fee[\$i], '%' ) ) {
											\$flat_rate_fee[ \$i ] = wc_format_decimal( \$flat_rate_fee[\$i] );
										} else {
											\$flat_rate_fee[ \$i ] = wc_clean( \$flat_rate_fee[\$i] );
										}

										// Add to flat rates array
										\$flat_rates[ urldecode( sanitize_title( \$flat_rate_class[ \$i ] ) ) ] = array(
											'cost' => \$flat_rate_cost[ \$i ],
											'fee'  => \$flat_rate_fee[ \$i ],
										);
									}
								}

								update_option( \$this->flat_rate_option, \$flat_rates );

								\$this->get_flat_rates();
							}

							function save_default_costs( \$fields ) {
							    \$default_cost = ( \$_POST['default_cost'] === '' ) ? '' : wc_format_decimal( \$_POST['default_cost'] );

							    if ( ! strstr( \$_POST['default_fee'], '%' ) ) {
							        \$default_fee  = ( \$_POST['default_fee'] === '' ) ? '' : wc_format_decimal( \$_POST['default_fee'] );
							    } else {
							        \$default_fee = wc_clean( \$_POST['default_fee'] );
							    }

							    \$fields['cost'] = \$default_cost;
							    \$fields['fee']  = \$default_fee;

							    return \$fields;
							}


							/**
							 * get_flat_rates function.
							 *
							 * @access public
							 * @return void
							 */
							function get_flat_rates() {
								\$this->flat_rates = array_filter( (array) get_option( \$this->flat_rate_option ) );
							}

						}

						");

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
