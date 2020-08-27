<?php
/**
 * Currency Convertor Settings content.
 *
 * @package wptecc
 */

$settings              = get_option( 'wp_travel_engine_settings', true );
$enable                = isset( $settings['currency_convertor']['enable'] ) ? $settings['currency_convertor']['enable'] : 'no';
$conversion_currencies = isset( $settings['currency_convertor']['conversion_currencies'] ) && is_array( $settings['currency_convertor']['conversion_currencies'] ) ? $settings['currency_convertor']['conversion_currencies'] : array();
$fixer_api_key         = isset( $settings['fixer_api_key'] ) ? $settings['fixer_api_key'] : '';
$display_option        = empty( $fixer_api_key ) ? ' style="display:none;" ' : '';
$base_currency         = isset( $settings['currency_code'] ) ? $settings['currency_code'] : 'USD';
$menu_locations        = isset( $settings['currency_convertor']['menu_locations'] ) && is_array( $settings['currency_convertor']['menu_locations'] ) ? $settings['currency_convertor']['menu_locations'] : array();
$auto_update_duration  = isset( $settings['currency_convertor']['auto_update_duration'] ) ? $settings['currency_convertor']['auto_update_duration'] : 4;

$obj        = new \Wp_Travel_Engine_Functions();
$currencies = $obj->wp_travel_engine_currencies();
?>
<div class="wpte-form-block-wrap" id="wptecc-block">
	<div class="wpte-form-block">
		<div class="wpte-form-content">
			<div class="wpte-field wpte-checkbox advance-checkbox">
				<label
					class="wpte-field-label"
					for="wp_travel_engine_settings[currency_convertor][enable]"
				><?php esc_html_e( 'Enable Currency Convertor', 'wptecc' ); ?></label>
				<div class="wpte-checkbox-wrap">
					<input
						type="hidden"
						value="no"
						name="wp_travel_engine_settings[currency_convertor][enable]"
					>
					<input
						type="checkbox"
						id="wp_travel_engine_settings[currency_convertor][enable]"
						name="wp_travel_engine_settings[currency_convertor][enable]"
						value="yes"
						<?php checked( $enable, 'yes' ); ?>
					>
					<label for="wp_travel_engine_settings[currency_convertor][enable]"></label>
				</div>
				<span class="wpte-tooltip"><?php esc_html_e( 'Check this option to enable currency convertor which allows user to choose the currency.', 'wptecc' ); ?></span>
			</div>
			<div class="wpte-field wpte-floated">
				<label class="wpte-field-label" for="wp_travel_engine_settings__fixer_api_key"><?php esc_html_e( 'Fixer API Access Key', 'wptecc' ); ?>
				</label>
				<input type="text" name="wp_travel_engine_settings[fixer_api_key]" data-fixer-api="" id="wp_travel_engine_settings__fixer_api_key" value="<?php echo esc_attr( $fixer_api_key ); ?>">
				<span class="wpte-tooltip wptecc-message" style="color: red"></span>
				<?php /* translators: %1$s: Starting Anchor tag %2$s : Ending ANchor Tag. */ ?>
				<span class="wpte-tooltip"> <?php echo sprintf( esc_html__( 'Get your fixer api key %1$shere%2$s. The free subscription provides exchage rates for Euro \'EUR\' to other currencies only, The rates are used to generate rates for other base currency which may not result expected rates. It is recommended to use Premium Subscription of %1$sFIXER API%2$s, if not using \'EUR\' as base currency', 'wptecc' ), '<a target="new" href="https://fixer.io/product">', '</a>' ); ?></span>
			</div>
			<div class="wpte-field wpte-floated">
				<label class="wpte-field-label" for="wp_travel_engine_settings__auto_update_duration"><?php esc_html_e( 'Refresh Rates (in Hrs)', 'wptecc' ); ?>
				</label>
				<input type="number" step="0.1" name="wp_travel_engine_settings[currency_convertor][auto_update_duration]" id="wp_travel_engine_settings__auto_update_duration" min="0" value="<?php echo esc_attr( $auto_update_duration ); ?>">
				<span class="wpte-tooltip"> <?php echo sprintf( wp_kses_post( 'It is the duration (in Hrs) the exchange rates data will be cached for. "0" will fetch data every time.', 'wptecc' ) ); ?> <a href="" id="wptecc-purge" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wptecc_purge_nonce' ) ); ?>"><?php esc_html_e( 'Purge Cache.', 'wptecc' ); ?></a></span>
			</div>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --safe ?>
			<div class="wpte-field wpte-floated wptecc-option" <?php echo $display_option; ?>>
				<label class="wpte-field-label" for="wp_travel_engine_setting__base_currency"><?php esc_html_e( 'Base Currency', 'wptecc' ); ?>
				</label>
				<input type="text" readonly data-fixer-api="" id="wp_travel_engine_setting__base_currency" value="<?php echo esc_attr( $currencies[ $base_currency ] . '( ' . $obj->wp_travel_engine_currencies_symbol( $base_currency ) . ' )' ); ?>">
			</div>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --safe ?>
			<div class="wpte-field wpte-select wpte-floated wptecc-option" <?php echo $display_option; ?>>
				<label class="wpte-field-label" for="wptecc_display_location"><?php esc_html_e( 'Display Location', 'wptecc' ); ?>
				</label>
				<?php
				$locations = wptecc_get_theme_locations();
				?>
				<select name="wp_travel_engine_settings[currency_convertor][menu_locations]" multiple id="wptecc_display_location" class="wpte-enhanced-select">
				<?php
				foreach ( $locations as $slug => $location ) {
					$is_selected = in_array( $slug, $menu_locations, true );
					$selected    = selected( $is_selected, true );
					echo wp_kses(
						"<option value=\"{$slug}\" {$selected}>{$location}</option>",
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
			</div>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --safe ?>
			<div class="wpte-field wpte-select wpte-floated wptecc-option" <?php echo $display_option; ?>>
				<label class="wpte-field-label" for="wp_travel_engine_settings[conversion_currencies]"><?php esc_html_e( 'Currencies', 'wptecc' ); ?>
				</label>
				<select data-selected-currencies="" id="wp_travel_engine_settings[conversion_currencies]" multiple name="wp_travel_engine_settings[currency_convertor][conversion_currencies]" data-placeholder="<?php esc_attr_e( 'Choose a currency&hellip;', 'wptecc' ); ?>" class="wpte-enhanced-select">
					<option value=""><?php esc_html_e( 'Choose a currency&hellip;', 'wptecc' ); ?></option>
					<?php
					$code = 'USD';
					if ( isset( $settings['currency_convertor']['conversion_currencies'] ) && '' !== $settings['currency_convertor']['conversion_currencies'] ) {
						$code = $settings['currency_convertor']['conversion_currencies'];
					}
					$currency = $obj->wp_travel_engine_currencies_symbol( $code );
					foreach ( $currencies as $key => $name ) {
						if ( $key === $base_currency ) {
							continue;
						}
						$code = in_array( $key, $conversion_currencies, true ) ? 1 : 0;
						echo '<option value="' . ( ! empty( $key ) ? esc_attr( $key ) : 'USD' ) . '" ' . selected( $code, 1, false ) . '>' . esc_html( $name . ' (' . $obj->wp_travel_engine_currencies_symbol( $key ) . ')' ) . '</option>';
					}
					?>
				</select>
				<span class="wpte-tooltip"> <?php esc_html_e( 'Select currencies to display on the frontend.', 'wptecc' ); ?></span>
			</div>
			<div class="wpte-field wpte-floated">
				<label class="wpte-field-label" for="wp_travel_engine_settings__usage"><?php esc_html_e( 'Usage', 'wptecc' ); ?>
				</label>
				<input type="text" redonly value="[WPTECC_CURRENCY_SELECTOR]">
				<span class="wpte-tooltip"> <?php esc_html_e( 'Use shortcode to display currency selector.', 'wptecc' ); ?></span>
			</div>
		</div>
	</div>
</div>
