<?php
/**
 * @since 1.0.0
 */
class GF_Automizy_Feed_Free extends GF_Automizy_Feed {
	private static $_instance = null;
	public static function get_instance() {
		if ( self::$_instance == null ) {
		   self::$_instance = new self;
	   }

	   return self::$_instance;
   }
	/**
	 * Plugin starting point. Handles hooks, loading of language files and PayPal delayed payment support.
	 */
	public function init() {
		parent::init();
	}

	/**
	 *
	 * Get the Automizy fields as a field map for Gravity forms
	 *
	 * @return array
	 */

	public function get_custom_fields_as_field_map() {
		if ( ! $this->initialize_api() ) {
			return array();
		}
		$field_map = array(
			array(
				'name'     => 'email',
				'label'    => 'Email',
				'required' => true,
			),
			array(
				'name'  => 'firstname',
				'label' => 'First Name',
			),
			array(
				'name'  => 'lastname',
				'label' => 'Last Name',
			),
			array(
				'name'  => 'company',
				'label' => 'Company',
			),
			array(
				'name'  => 'phone',
				'label' => 'Phone',
			),

		);
		return $field_map;
	}

}
