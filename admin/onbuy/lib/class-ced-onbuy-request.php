<?php
if ( ! class_exists( 'Class_Ced_Onbuy_Request' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Onbuy_Integration_By_CedCommerce
	 * @subpackage Onbuy_Integration_By_CedCommerce/admin/onbuy/lib
	 */
	class Class_Ced_Onbuy_Request {

		/**
		 * The Instace of CED_onbuy_Manager.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_onbuy_Manager class.
		 */
		private static $_instance;

		public $new_id;
		/**
		 * CED_onbuy_Manager Instance.
		 *
		 * Ensures only one instance of CED_onbuy_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_onbuy_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public $marketplaceID   = 'onbuy';
		public $marketplaceName = 'onbuy';


		/**
		 * Constructor.
		 *
		 * Registering actions and hooks for OnBuy.
		 *
		 * @since 1.0.0
		 */
		public function __construct( $consumerKey = '', $secretKey = '' ) {
			$this->consumerKey = $consumerKey;
			$this->secretKey   = $secretKey;
			$this->baseUrl     = 'https://api.onbuy.com/v2/';
		}

		public function sendCurlPostMethod( $action = '', $access_token = '' ) {

			$action = $this->baseUrl . $action . '?secret_key=' . $this->secretKey . '&consumer_key=' . $this->consumerKey;

			$args          = array(
				'headers' => array(
					'Authorization'  => $access_token,
					'Content-Type'   => 'application/json',
					'Provider-Token' => 'E3A6AD7D569C44F28944DADD8E3FA567',
				),
				'method'  => 'POST',
			);
			$request_put   = wp_remote_request( $action, $args );
			$response_body = wp_remote_retrieve_body( $request_put );
			if ( isset( $response_body ) && ! empty( $response_body ) ) {
				$response = $this->parse_response( $response_body );
				return $response;
			}
		}

		// =========================== Check Winning Status ===========================
		public function ced_onbuy_get_method_check_win_price( $action = '', $query = '', $access_token = '', $parameters = '' ) {
			$query      = ! empty( $query ) ? '?' . $query : $query;
			$url        = $this->baseUrl . $action . $query;
			$parameters = array(
				'site_id' => '2000',
				'skus'    => $parameters,

			);
			$args        = array(
				'headers' => array(
					'Authorization'  => $access_token,
					'accept'         => 'application/json',
					'Provider-Token' => 'E3A6AD7D569C44F28944DADD8E3FA567',
				),
				'body'    => $parameters,
			);
			$request_get = wp_remote_get( $url, $args );

			$response_body = wp_remote_retrieve_body( $request_get );
			if ( isset( $response_body ) && ! empty( $response_body ) ) {

				$response = $this->parse_response( $response_body );
				return $response;

			}
		}
		// =======================================================


		public function ced_onbuy_get_method( $action = '', $query = '', $access_token = '' ) {
			$query         = ! empty( $query ) ? '?' . $query : $query;
			$url           = $this->baseUrl . $action . $query;
			$args          = array(
				'headers' => array(
					'Authorization'  => $access_token,
					'accept'         => 'application/json',
					'Provider-Token' => 'E3A6AD7D569C44F28944DADD8E3FA567',
				),
			);
			$request_get   = wp_remote_get( $url, $args );
			$response_body = wp_remote_retrieve_body( $request_get );
			if ( isset( $response_body ) && ! empty( $response_body ) ) {

				$response = $this->parse_response( $response_body );
				return $response;

			}
		}

		public function ced_onbuy_post_method( $action = '', $query = '', $access_token = '', $parameters = '' ) {
			$query         = ! empty( $query ) ? '?' . $query : $query;
			$url           = $this->baseUrl . $action . $query;
			$args          = array(
				'headers' => array(
					'Authorization'  => $access_token,
					'Content-Type'   => 'application/json',
					'Provider-Token' => 'E3A6AD7D569C44F28944DADD8E3FA567',
				),
				'body'    => $parameters,
			);
			$request_post  = wp_remote_post( $url, $args );
			$response_body = wp_remote_retrieve_body( $request_post );

			if ( isset( $response_body ) && ! empty( $response_body ) ) {
				$response = $this->parse_response( $response_body );
				return $response;
			}
		}

		public function ced_onbuy_put_method( $action = '', $query = '', $access_token = '', $parameters = '', $opc = '' ) {
			$query = ! empty( $query ) ? '?' . $query : $query;
			if ( ! empty( $opc ) ) {
				$url = $this->baseUrl . $action . $query . '/' . $opc;
			} else {
				$url = $this->baseUrl . $action . $query;
			}

			$args          = array(
				'headers' => array(
					'Authorization'  => $access_token,
					'Content-Type'   => 'application/json',
					'Provider-Token' => 'E3A6AD7D569C44F28944DADD8E3FA567',
				),
				'body'    => $parameters,
				'method'  => 'PUT',
			);
			$request_put   = wp_remote_request( $url, $args );
			$response_body = wp_remote_retrieve_body( $request_put );
			if ( isset( $response_body ) && ! empty( $response_body ) ) {
				$response = $this->parse_response( $response_body );
				return $response;
			}

		}

		public function ced_onbuy_delete_method( $action = '', $query = '', $access_token = '', $parameters = '' ) {
			$query = ! empty( $query ) ? '?' . $query : $query;
			$url   = $this->baseUrl . $action . $query;

			$args = array(
				'headers' => array(
					'Authorization'  => $access_token,
					'Content-Type'   => 'application/json',
					'Provider-Token' => 'E3A6AD7D569C44F28944DADD8E3FA567',
				),
				'body'    => $parameters,
				'method'  => 'DELETE',
			);

			$request_delete = wp_remote_request( $url, $args );
			$response_body  = wp_remote_retrieve_body( $request_delete );
			if ( isset( $response_body ) && ! empty( $response_body ) ) {
				$response = $this->parse_response( $response_body );
				return $response;
			}

		}

		/**
		 * Function for parse_response
		 *
		 * @since 1.0.0
		 * @param string $response Response from OnBuy.
		 */
		public function parse_response( $response ) {
			if ( ! empty( $response ) ) {
				return json_decode( $response, true );
			}
		}
	}
}

