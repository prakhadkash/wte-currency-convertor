<?php
/**
 * Helper Functions for the plugin.
 *
 * @package wptecc
 */

/**
 * Formated menu locations data.
 *
 * @return [] Menu locations.
 */
function wptecc_get_theme_locations() {
	$locations = array();
	$menus     = get_nav_menu_locations();
	if ( ! empty( $menus ) ) {
		$menu_slugs = array_keys( $menus );
		foreach ( $menu_slugs as $menu_slug ) {
			$title                   = ucfirst( str_replace( array( '-', '_' ), ' ', $menu_slug ) );
			$locations[ $menu_slug ] = $title;
		}
	}
	return $locations;
}

/**
 * Currency Selector HTML
 *
 * @return string HTML Content.
 */
function wptecc_get_currency_selector() {
	wp_enqueue_script( 'wptecc-view' );
	$settings      = get_option( 'wp_travel_engine_settings', true );
	$base_currency = isset( $settings['currency_code'] ) ? $settings['currency_code'] : 'USD';
	$currencies    = ! empty( $settings['currency_convertor']['conversion_currencies'] ) ? $settings['currency_convertor']['conversion_currencies'] : array();
	if ( empty( $currencies ) ) {
		return '';
	}
	ob_start();
	array_unshift( $currencies, $base_currency );
	ob_start();
	?>
	<form id="wptecc_currency_selector">
		<select name="convert_to" id="wptecc_convertto" data-wpte-currency="">
			<?php
			foreach ( $currencies as $currency ) {
				$current_currency = isset( $_COOKIE['wptecc-user-currency'] ) ? wp_unslash( $_COOKIE['wptecc-user-currency'] ) : $base_currency; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$selected         = selected( $currency, $current_currency, false );
				echo wp_kses(
					"<option value=\"{$currency}\" {$selected}>{$currency}</option>",
					array(
						'option' => array(
							'value'    => array(),
							'selected' => array(),
						),
					)
				);
			}
			?>
		</select>
		<?php
		wp_nonce_field( 'wptecc_selector', '_nonce' );
		?>
	</form>
	<?php
	return ob_get_clean();
}

/**
 * Look for the currently active currency if any.
 *
 * @param string $default Currency Code.
 * @return string Currency Code.
 */
function wtecc_get_active_currency( $default = 'USD' ) {
	if ( isset( $_COOKIE['wptecc-user-currency'] ) ) {
		return wp_unslash( $_COOKIE['wptecc-user-currency'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
	$settings = get_option( 'wp_travel_engine_settings', true );
	return isset( $settings['currency_code'] ) ? $settings['currency_code'] : $default;
}

/**
 * Generates and returns currency conversion rate.
 *
 * @param string $from Currency to convert from.
 * @param string $to Currency to convert to.
 * @return number Conversion rate.
 */
function wtecc_get_conversion_rate( $from, $to ) {
	$settings         = get_option( 'wp_travel_engine_settings', true );
	$refresh_duration = 4 * 60 * 60;
	if ( ! empty( $settings['currency_convertor']['auto_update_duration'] ) && (float) $settings['currency_convertor']['auto_update_duration'] > 0 ) {
		$refresh_duration = (float) $settings['currency_convertor']['auto_update_duration'] * 60 * 60; // in seconds.
	}
	$response = get_transient( 'wptecc_fixer_response' );
	if ( empty( $response ) ) {
		$instance = WPTECC_FIXER_API::instance( WPTECC_API_FIXER_KEY );
		if ( ! $instance->has_error() ) {
			set_transient( 'wptecc_fixer_response', $instance->get_response(), $refresh_duration );
			$response = $instance->get_response();
		} else {
			return 1;
		}
	}

	if ( $response->base === $from ) {
		return $response->rates->{$to};
	} else { // For Premium Subscription.
		$instance = WPTECC_FIXER_API::instance( WPTECC_API_FIXER_KEY );
		if ( $instance->is_premium ) {
			$instance->get( $from );
			if ( ! $instance->has_error() ) {
				set_transient( 'wptecc_fixer_response', $instance->get_response(), $refresh_duration );
				return $instance->get_response()->rates->{$to};
			} else {
				return 0;
			}
		}
	}

	// For free subscription.
	return $response->rates->{$to} / $response->rates->{$from};
}

/**
 * Convert the amount from one currency to other.
 *
 * @param float  $amount Amount to be converted.
 * @param string $base_currency Base Currency.
 * @param string $active_currency Active Currency.
 * @return number converted amount.
 */
function wtecc_get_converted_price( $amount, $base_currency = null, $active_currency = null ) {
	if ( is_null( $base_currency ) ) {
		$settings      = get_option( 'wp_travel_engine_settings', true );
		$base_currency = isset( $settings['currency_code'] ) ? $settings['currency_code'] : 'USD';
	}
	if ( is_null( $active_currency ) ) {
		$active_currency = wtecc_get_active_currency();
	}

	$rate = wtecc_get_conversion_rate( $base_currency, $active_currency );

	return $amount * $rate;
}
