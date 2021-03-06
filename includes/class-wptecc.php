<?php
/**
 * WPTECC
 *
 * @package wptecc
 */

/**
 * The Main Class
 */
class WPTECC {
	/**+
	 * Plugin Version.
	 *
	 * @var string Version.
	 */
	public $version = '1.0.0';

	/**
	 * Only instance for the class.
	 *
	 * @var object WPTECC.
	 */
	protected static $instance = null;

	/**
	 * Plugins instance.
	 *
	 * Provides single instance of the class.
	 *
	 * @return object WPTECC
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Includes required files to run the plugin.
	 *
	 * @return void
	 */
	private function includes() {
		require_once 'class-wptecc-fixer-api.php';
		require_once 'functions.php';
	}

	/**
	 * Hook into hooks and filters.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Define constants.
	 *
	 * @return void
	 */
	private function define_constants() {
		$settings = get_option( 'wp_travel_engine_settings', true );
		if ( ! defined( 'WPTECC_API_FIXER_KEY' ) ) {
			$api_key = isset( $settings['fixer_api_key'] ) ? $settings['fixer_api_key'] : '';
			define( 'WPTECC_API_FIXER_KEY', $api_key );
		}
	}

	/**
	 * Hook into init action.
	 *
	 * @return void
	 */
	public function init() {

		/**
		 * Shortcode hook for currency Selector.
		 */
		add_shortcode(
			'WPTECC_CURRENCY_SELECTOR',
			function() {
				return wptecc_get_currency_selector();
			}
		);

		/**
		 * Adds tab content.
		 */
		add_filter(
			'wpte_get_global_extensions_tab',
			function( $tabs ) {
				return array_merge(
					$tabs,
					array(
						'multiple-currency' => array(
							'label'        => __( 'Multiple Currency Settings', 'wptecc' ),
							'content_path' => plugin_dir_path( WPTECC_FILE ) . 'backend/settings/misc/currency-convertor.php',
							'current'      => true,
						),
					)
				);
			}
		);

		/**
		 * Update options with data.
		 */
		add_action(
			'wpte_after_save_global_settings_data',
			function( $post_data ) {
				$settings = get_option( 'wp_travel_engine_settings', true );
				if ( isset( $post_data['currency_convertor'] ) ) {
					$settings['currency_convertor'] = wp_unslash( $post_data['currency_convertor'] );
				}
				if ( isset( $post_data['fixer_api_key'] ) ) {
					$settings['fixer_api_key'] = wp_unslash( $post_data['fixer_api_key'] );
				}
				update_option( 'wp_travel_engine_settings', $settings );
			}
		);

		// Ajax Hooks.
		add_action( 'wp_ajax_wtecc_test', array( $this, 'validate_access_key' ) );
		add_action( 'wp_ajax_nopriv_wtecc_test', array( $this, 'validate_access_key' ) );
		add_action( 'wp_ajax_wtecc_purge', array( $this, 'purge_cache' ) );
		add_action( 'wp_ajax_nopriv_wtecc_purge', array( $this, 'purge_cache' ) );

		/**
		 * Filters Menus to add selectors.
		 */
		add_filter(
			'wp_nav_menu_items',
			function( $items, $args ) {
				if ( $this->is_disabled() ) {
					return $items;
				}
				$settings          = get_option( 'wp_travel_engine_settings', true );
				$display_locations = isset( $settings['currency_convertor']['menu_locations'] ) && is_array( $settings['currency_convertor']['menu_locations'] ) ? $settings['currency_convertor']['menu_locations'] : array();
				if ( ! in_array( $args->theme_location, $display_locations, true ) || empty( $settings['currency_convertor']['conversion_currencies'] ) ) {
					return $items;
				}
				$content = wptecc_get_currency_selector();
				$items  .= "<li>{$content}</li>";
				return $items;
			},
			10,
			2
		);

		/**
		 * Enqueues Scripts hooks.
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/**
		 * Currency Code Filter.
		 */
		add_filter(
			'wp_travel_engine_currency_code',
			function( $code ) {
				if ( $this->is_disabled() ) {
					return $code;
				}
				if ( is_admin() ) {
					global $post;
					$screen = get_current_screen();
					if ( isset( $screen->id ) && 'booking' === $screen->id ) {
						$cart_currency = get_post_meta( $post->ID, 'wp_travel_engine_booking_setting' );
						return is_array( $cart_currency[0] ) && empty( $cart_currency[0]['cart_currency'] ) ? $code : $cart_currency[0]['cart_currency'];
					}
					return $code;
				}
				return wtecc_get_active_currency( $code );
			}
		);

		/**
		 * Filters Post Meta data.
		 */
		add_filter(
			'get_post_metadata',
			function( $value, $object_id, $meta_key, $single ) {

				if ( $this->is_disabled() ) {
					return $value;
				}

				if ( ! in_array(
					$meta_key,
					array(
						'wp_travel_engine_setting',
						'wp_travel_engine_setting_trip_price',
						'wp_travel_engine_setting_trip_prev_price',
					),
					true
				) ) {
					return $value;
				}

				$meta_cache = wp_cache_get( $object_id, 'post_meta' );

				if ( ! $meta_cache ) {
					$meta_cache = update_meta_cache( 'post', array( $object_id ) );
					if ( isset( $meta_cache[ $object_id ] ) ) {
						$meta_cache = $meta_cache[ $object_id ];
					} else {
						$meta_cache = null;
					}
				}

				if ( ! $meta_key ) {
					return $meta_cache;
				}

				if ( isset( $meta_cache[ $meta_key ] ) ) {

					$meta_cache_value = array_map( 'maybe_unserialize', $meta_cache[ $meta_key ] );

					switch ( $meta_key ) {
						case 'wp_travel_engine_setting':
							$new_pricings = array();
							$pricings     = isset( $meta_cache_value[0]['multiple_pricing'] ) ? $meta_cache_value[0]['multiple_pricing'] : array();

							if ( is_array( $pricings ) ) {
								foreach ( $pricings as $key => $pricing ) {
									$new_pricings[ $key ] = $pricing;
									if ( ! empty( $pricing['sale_price'] ) ) {
										$new_pricings[ $key ]['sale_price'] = $this->get_converted_price( (float) $pricing['sale_price'] );
									}
									if ( ! empty( $pricing['price'] ) ) {
										$new_pricings[ $key ]['price'] = $this->get_converted_price( (float) $pricing['price'] );
									}
								}
							}

							$meta_cache_value[0]['multiple_pricing'] = $new_pricings;
							if ( isset( $meta_cache_value[0]['trip_price'] ) ) {
								$meta_cache_value[0]['trip_price'] = $this->get_converted_price( (float) $meta_cache_value[0]['trip_price'] );
							}
							if ( isset( $meta_cache_value[0]['trip_prev_price'] ) ) {
								$meta_cache_value[0]['trip_prev_price'] = $this->get_converted_price( (float) $meta_cache_value[0]['trip_prev_price'] );
							}
							break;
						case 'wp_travel_engine_setting_trip_price':
						case 'wp_travel_engine_setting_trip_prev_price':
							$meta_cache_value = $this->get_converted_price( (float) $meta_cache_value );
							break;
					}

					return $meta_cache_value;
				}

				return null;

			},
			11,
			4
		);

		/**
		 * Filters Cart Items.
		 */
		add_filter(
			'wte_cart_items',
			function( $items ) {
				if ( $this->is_disabled() ) {
					return $items;
				}
				global $wte_cart;
				if ( is_array( $items ) ) {
					return array_map(
						function( $item ) use ( $wte_cart ) {
							$currency = $wte_cart->get_attribute( 'cart_currency' ) ? $wte_cart->get_attribute( 'cart_currency' ) : null;
							if ( isset( $item['trip_price'] ) ) {
								$item['trip_price'] = $this->get_converted_price( (float) $item['trip_price'], $currency );
							}
							if ( isset( $item['pax_cost'] ) ) {
								foreach ( $item['pax_cost'] as $key => $value ) {
									$item['pax_cost'][ $key ] = $this->get_converted_price( (float) $value, $currency );
								}
							}
							return $item;
						},
						$items
					);
				}
			}
		);

		/**
		 * Filters Carts Totals.
		 */
		add_filter(
			'wp_travel_engine_cart_get_total_fields',
			function( $totals ) {
				if ( is_array( $totals ) ) {
					global $wte_cart;
					$items    = $wte_cart->getItems();
					$item     = array_shift( $items );
					$currency = $wte_cart->get_attribute( 'cart_currency' ) ? $wte_cart->get_attribute( 'cart_currency' ) : null;
					return array_map(
						function( $total ) use ( $currency ) {
							return $this->get_converted_price( $total, $currency );
						},
						$totals
					);
				}
				return $totals;
			}
		);

		// Adds an attribute to cart.
		add_action(
			'wp_travel_engine_after_trip_add_to_cart',
			function( $trip_id ) {
				if ( $this->is_disabled() ) {
					return;
				}
				global $wte_cart;

				if ( isset( $_COOKIE['wptecc-user-currency'] ) ) {
					// phpcs:ignore
					$currency = wp_unslash( $_COOKIE['wptecc-user-currency'] );
				} else {
					$settings = get_option( 'wp_travel_engine_settings', false );
					if ( is_array( $settings ) && isset( $settings['currency_code'] ) ) {
						$currency = $settings['currency_code'];
					}
				}

				$wte_cart->set_attribute( 'cart_currency', $currency );
			}
		);

		/**
		 * Filters to save additonal meta fields.
		 */
		add_filter(
			'wte_before_booking_meta_save',
			function( $order_metas, $booking_id ) {
				global $wte_cart;
				$cart_currency = $wte_cart->get_attribute( 'cart_currency' );
				if ( $cart_currency ) {
					$order_metas['cart_currency'] = $cart_currency;
				}
				return $order_metas;
			},
			11,
			2
		);

	}

	/**
	 * Purge cache data when requested.
	 *
	 * @return void
	 */
	public function purge_cache() {
		if ( isset( $_GET['_nonce'] ) && wp_verify_nonce( wp_unslash( $_GET['_nonce'] ), 'wptecc_purge_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( delete_transient( 'wptecc_fixer_response' ) ) {
				wp_send_json_success( array( 'message' => __( 'Data Purged Successfully.', 'wptecc' ) ) );
				wp_die();
			}
		}
		wp_send_json_error( array( 'message' => __( 'No Data found to Purge.', 'wptecc' ) ) );
		wp_die();
	}

	/**
	 * Checks if currency convertor is disabled.
	 *
	 * @return boolean
	 */
	private function is_disabled() {
		return ! $this->is_enabled();
	}

	/**
	 * Checks if currency convertor is enabled.
	 *
	 * @return boolean
	 */
	private function is_enabled() {
		$settings = get_option( 'wp_travel_engine_settings', true );
		return isset( $settings['currency_convertor']['enable'] ) && 'yes' === $settings['currency_convertor']['enable'];
	}

	/**
	 * Gets Converted Price.
	 *
	 * @param float  $amount Amount to convert.
	 * @param string $base_currency Base Currency.
	 * @return float Converted amount.
	 */
	private function get_converted_price( float $amount, $base_currency = null ) {
		if ( class_exists( 'Wte_Trip_Currency_Converter_Init' ) || is_admin() ) { // Don't want to mess with original convertor.
			return $amount;
		}

		$settings = get_option( 'wp_travel_engine_settings' );
		if ( ! isset( $settings['currency_convertor']['enable'] ) || 'yes' !== $settings['currency_convertor']['enable'] ) {
			return $amount;
		}

		if ( is_null( $base_currency ) ) {
			$base_currency = isset( $settings['currency_code'] ) ? $settings['currency_code'] : 'USD';
		}

		$active_currency = wtecc_get_active_currency();
		if ( empty( $active_currency ) ) {
			$active_currency = $base_currency;
		}

		return wtecc_get_converted_price( $amount, $base_currency, $active_currency );

	}

	/**
	 * Enqueues Scripts from here.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( is_admin() ) {
			wp_enqueue_script( 'wptecc', plugin_dir_url( WPTECC_FILE ) . 'assets/js/admin.js', array(), wp_rand(), true );
		}
		wp_register_script( 'wptecc-view', plugin_dir_url( WPTECC_FILE ) . 'assets/js/view.js', array(), wp_rand(), true );
	}

	/**
	 * Validate the request.
	 *
	 * @return void
	 */
	public function validate_access_key() {
		// phpcs:ignore
		$access_key = $_GET['access_key'];

		$instance = WPTECC_FIXER_API::instance( $access_key );

		$response = $instance->get_response();
		if ( isset( $response->success ) && $response->success ) {
			wp_send_json_success( $instance->get_response() );
		} else {
			$code = $response->error->type;
			wp_send_json_error(
				array(
					'code'    => $code,
					'message' => $response->error->info,
				)
			);
		}
		wp_die();
	}
}
