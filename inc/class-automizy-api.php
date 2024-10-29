<?php

/**
 * Handle the API calls to the Automizy REST API
 *
 * @since 1.0.0
 */
class Automizy_API {


	/**
	 * Store the API Key
	 *
	 * @var string
	 */
	protected $api_key = null;

	/**
	 *
	 * Fire it up
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}


	/**
	 *
	 * Adds a contact to out list
	 *
	 * @param int    $list_id
	 * @param string $email
	 * @param array  $custom_fields
	 * @param string $tag
	 *
	 * @return bool|array
	 */
	public function add_contact( $list_id, $email, $custom_fields, $tag = '' ) {
		$endpoint = 'https://gateway.automizy.com/v2/smart-lists/' . $list_id . '/contacts';
		$body     = [
			'email'        => $email,
			'customFields' => $custom_fields,
			'tags'         => [
				$tag,
			],
		];
		$body     = json_encode( $body );
		$args     = [
			'body'    => $body,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Accepts'       => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
		];
		$request  = wp_remote_post( $endpoint, $args );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 201 ) {
			error_log( '[===ADD CUSTOMER TO LIST ===]' );
			error_log( wp_remote_retrieve_response_code( $request ) . wp_remote_retrieve_body( $request ) );
			return false;
		} else {
			$response              = wp_remote_retrieve_body( $request );
			$customer_list_details = json_decode( $response, true );
			return $customer_list_details;
		}
	}

	/**
	 *
	 * Grabs the contact lists
	 *
	 * @return array
	 */
	public function get_lists() {
	   $endpoint  = 'https://gateway.automizy.com/v2/smart-lists';
		$args     = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Accepts'       => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
		];
		$request  = wp_remote_get( $endpoint, $args );
		$response = wp_remote_retrieve_body( $request );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			error_log( '[===ADD CUSTOMER TO LIST ===]' );
			error_log( wp_remote_retrieve_response_code( $request ) . wp_remote_retrieve_body( $request ) );
			return false;
		} else {
			$response     = wp_remote_retrieve_body( $request );
			$list_details = json_decode( $response, true );
			return $list_details['smartLists'];
		}
	}

	/**
	 *
	 * Grabs the tags
	 *
	 * @return bool|array
	 */
	public function get_tags() {
		$endpoint = 'https://gateway.automizy.com/v2/contacts/tag-manager';
		$args     = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Accepts'       => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
		];
		$request  = wp_remote_get( $endpoint, $args );
		$response = wp_remote_retrieve_body( $request );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			error_log( '[===ERROR GETTING TAGS ===]' );
			error_log( wp_remote_retrieve_response_code( $request ) . wp_remote_retrieve_body( $request ) );
			return false;
		} else {
			$response = wp_remote_retrieve_body( $request );
			$tags     = json_decode( $response, true );
			return $tags['contactTags'];
		}
	}

	/**
	 *
	 * Grabs the custom fields
	 *
	 * @return bool|array
	 */
	public function get_custom_fields() {
	   $endpoint  = 'https://gateway.automizy.com/v2/custom-fields';
		$args     = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Accepts'       => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
		];
		$request  = wp_remote_get( $endpoint, $args );
		$response = wp_remote_retrieve_body( $request );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			error_log( '[===ERROR GETTING CUSTOM FIELDS ===]' );
			error_log( wp_remote_retrieve_response_code( $request ) . wp_remote_retrieve_body( $request ) );
			return false;
		} else {
			$response      = wp_remote_retrieve_body( $request );
			$custom_fields = json_decode( $response, true );
			return $custom_fields['customFields'];
		}
	}

	/**
	 *
	 * Checks to see if we can access the api by getting the lists
	 *
	 * @return bool
	 */
	public function check_api() {
	   $endpoint = 'https://gateway.automizy.com/v2/smart-lists';
		$args    = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Accepts'       => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
		];
		$request = wp_remote_get( $endpoint, $args );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * Get the list name from a given ID
	 *
	 * @param int $list_id
	 *
	 * @return bool|string
	 */
	public function get_list_name( $list_id ) {
		$endpoint = 'https://gateway.automizy.com/v2/smart-lists/' . $list_id;
		$args     = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Accepts'       => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
		];
		$request  = wp_remote_get( $endpoint, $args );
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			error_log( '[===GET LIST NAME===]' );
			error_log( wp_remote_retrieve_response_code( $request ) . wp_remote_retrieve_body( $request ) );

			return false;
		} else {
			$response     = wp_remote_retrieve_body( $request );
			$list_details = json_decode( $response, true );
			return $list_details['name'];
		}
	}
}
