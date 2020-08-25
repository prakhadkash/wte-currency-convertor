<?php
/**
 * Class WPTECC_FIXER_API
 */

class WPTECC_FIXER_API {

	protected $access_key;

	public $is_premium;

	public $base_url = 'data.fixer.io/api/';

	public $base;

	public $response;

	private $rates;

	protected static $instance = null;

	public $error_type;

	public function __construct( $access_key ) {
		$this->access_key = $access_key;
		$this->validate();
	}

	public static function instance( $access_key ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $access_key );
		}
		return self::$instance;
	}

	public function get_endpoint( $is_premium = false ) {
		if ( $is_premium ) {
			return 'https://' . $this->base_url;
		} else {
			return 'http://' . $this->base_url;
		}
	}

	public function request( $is_premium = false, $base = null ) {
		if ( ! function_exists( 'curl_init' ) ) {
			return;
		}

		$endpoint = $this->get_endpoint( $is_premium ) . 'latest?access_key=' . $this->access_key;

		if ( ! is_null( $base ) ) {
			$endpoint .= "&base={$base}";
		}

		$endpoint .= "&format=1";

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

		return json_decode( $response );
	}

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

	public function validate() {
		$data = $this->request( true );

		if ( ! $data->success && 105 === $data->error->code ) {
			$this->is_premium = false;
		} else {
			$this->is_premium = true;
		}

		$this->get();
	}

	public function get_rates() {
		return $this->response->rates;
	}

	public function get_rate( $from = 'EUR', $to = 'EUR' ) {
		if ( $this->base === $from ) {
			return $this->response->rates->{$to};
		}

		// For free subscription.
		return $this->response->rates->{$to} / $this->response->rates->{$from};
	}

	public function get_error_type() {
		return $this->error_type;
	}

	public function has_error() {
		return isset( $this->response->success ) && ! $this->response->success;
	}

	public function get_response() {
		return $this->response;
	}
}
