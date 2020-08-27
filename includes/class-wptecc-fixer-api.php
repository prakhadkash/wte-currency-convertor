<?php
/**
 * Class WPTECC_FIXER_API
 *
 * @package wptecc
 */

/**
 * Class for FIXER API.
 */
class WPTECC_FIXER_API {

	/**
	 * API Access Key.
	 *
	 * @var string
	 */
	protected $access_key;

	/**
	 * Is premium Subscription.
	 *
	 * @var bool
	 */
	public $is_premium;

	/**
	 * Base URL API Endpoint.
	 *
	 * @var string
	 */
	public $base_url = 'data.fixer.io/api/';

	/**
	 * Base Currency of response.
	 *
	 * @var string
	 */
	public $base;

	/**
	 * API Response.
	 *
	 * @var object
	 */
	public $response;

	/**
	 * Class Singleton instance.
	 *
	 * @var object WPTECC_FIXER_API
	 */
	protected static $instance = null;

	/**
	 * Active Error Type.
	 *
	 * @var string
	 */
	public $error_type;

	/**
	 * Class Constructor.
	 *
	 * @param string $access_key Access Key.
	 */
	public function __construct( $access_key ) {
		$this->access_key = $access_key;
		$this->validate();
	}

	/**
	 * Gets Class active instance.
	 *
	 * @param string $access_key Access key.
	 * @return object WPTECC_FIXER_API
	 */
	public static function instance( $access_key ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $access_key );
		}
		return self::$instance;
	}

	/**
	 * Generates Endpoint URL.
	 *
	 * @param boolean $is_premium if is premium.
	 * @return string Endpoint.
	 */
	public function get_endpoint( $is_premium = false ) {
		if ( $is_premium ) {
			return 'https://' . $this->base_url;
		} else {
			return 'http://' . $this->base_url;
		}
	}

	/**
	 * Requests are made from here.
	 *
	 * @param boolean $is_premium Is premium.
	 * @param string  $base Base Currency.
	 * @return void|object
	 */
	public function request( $is_premium = false, $base = null ) {
		if ( ! function_exists( 'curl_init' ) ) {
			return;
		}

		$endpoint = $this->get_endpoint( $is_premium ) . 'latest?access_key=' . $this->access_key;

		if ( ! is_null( $base ) ) {
			$endpoint .= "&base={$base}";
		}

		$endpoint .= '&format=1';
		// phpcs:disable
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $endpoint,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
			)
		);

		$response = curl_exec( $curl );

		curl_close( $curl );
		//phpcs:enable

		return json_decode( $response );
	}

	/**
	 * Gets Rates.
	 *
	 * @param string $base Currency code.
	 * @return void
	 */
	public function get( $base = null ) {
		$data = $this->request( $this->is_premium, $base );

		$this->response = $data;
		if ( $this->response->success ) {
			$this->base = $this->response->base;
		} else {
			$this->error_type = $this->response->error->type;
			$this->error_code = $this->response->error->code;
		}
	}

	/**
	 * Validates and Checks subscription type.
	 *
	 * @return void
	 */
	public function validate() {
		$data = $this->request( true );

		if ( ! $data->success && 105 === $data->error->code ) {
			$this->is_premium = false;
		} else {
			$this->is_premium = true;
		}

		$this->get();
	}

	/**
	 * Getter to get rates from outside.
	 *
	 * @return object
	 */
	public function get_rates() {
		return $this->response->rates;
	}

	/**
	 * Getter to get error type.
	 *
	 * @return string Error Type.
	 */
	public function get_error_type() {
		return $this->error_type;
	}

	/**
	 * Checks if has Error.
	 *
	 * @return boolean
	 */
	public function has_error() {
		return isset( $this->response->success ) && ! $this->response->success;
	}

	/**
	 * Getter to last Response.
	 *
	 * @return object The Last Response.
	 */
	public function get_response() {
		return $this->response;
	}
}
