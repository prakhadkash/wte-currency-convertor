# WP Travel Engine Currency Convertor - A Test Project
This is an addon which provides multiple currency functionality for WP Travel Engine, built using [Fixer API](https://fixer.io/).

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
