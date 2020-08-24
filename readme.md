# WP Travel Engine Currency Convertor - A Test Project
Supports: WP Travel Engine `4.1.0`

This is an addon which provides multiple currency functionality for WP Travel Engine, built using [Fixer API](https://fixer.io/). It works with both free and premium subscription of Fixer. Get FIXER API KEY [here](https://fixer.io/product).

## Recommended
This addon requires a filter hook to work correctly on `cart` and `checkout` page which is not present on current version `4.1.0` of WP Travel Engine.
- Either download the modified version of WP Travel Engine from [here](https://github.com/prakhadkash/wte-currency-convertor/raw/master/build/wpte-currency-convertor.zip)
- or replace the `WTE_Cart::getItems` method with the function below.
```php
	public function getItems() {
		return apply_filters( 'wte_cart_items', $this->items ); // +
		// return $this->items; // -
	}
```
As the addon is tested alone with WP Travel Engine `4.1.0`, In case of encountered issues, deactivate the other addons if any used.

## Settings
It has settings/options UI under `WP Travel Engine > Settings > Extensions > Multiple Currency Settings`
[View Settings UI](https://d.pr/free/i/gs3Lnz)

### Settings API
Option Name|Description
-|-
Enable Currency Convertor|The addon will have control only when enabled.
Fixer API Access Key|Access Key
Refresh Rates (in Hrs)|Default `4` Hrs
Base Currency|`Payment Currency` Value `WP Travel Engine > Settings > Miscellaneous > Currency Settings`
Display Location | WP Menu location to display Currency Selector, supports multiple location.
Currencies|Currencies options for clients.
Usage|Shortcode `[WPTECC_CURRENCY_SELECTOR]`

## What to Expect
After setting configuration, you will see the [currency selector](https://d.pr/free/i/E0z7yS) on the selected menu location and location where shortcode used.

After selecting a currency from dropdown, the page will reloads and the `price` and `Currency Symbol` will be changed accordingly.
