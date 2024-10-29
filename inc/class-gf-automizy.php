<?php
/**
 * Base Class
 *
 * @since
 */
abstract class GF_Automizy_Feed extends GFFeedAddOn {
	protected $_version                  = GF_AUTOMIZY_VERSION;
	protected $_min_gravityforms_version = '1.9.16';
	protected $_slug                     = 'automizy-gravity-forms';
	protected $_path                     = 'automizy-gravity-forms/automizy-gravity-forms.php';
	protected $_full_path                = __FILE__;
	protected $_title                    = 'Gravity Forms Automizy Feed';
	protected $_short_title              = 'Automizy';
	protected $automizy                  = false;

	private static $_instance = null;
	abstract public function get_custom_fields_as_field_map();

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

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Subscribe contact to Automizy only when payment is received.', 'automizy-gravity-forms' ),
			)
		);
	}

	/**
	 * Custom format the phone type field values before they are returned by $this->get_field_value().
	 *
	 * @param array          $entry The Entry currently being processed.
	 * @param string         $field_id The ID of the Field currently being processed.
	 * @param GF_Field_Phone $field The Field currently being processed.
	 *
	 * @return string
	 */
	public function get_phone_field_value( $entry, $field_id, $field ) {

		// Get the field value from the Entry Object.
		$field_value = rgar( $entry, $field_id );

		// If there is a value and the field phoneFormat setting is set to standard reformat the value.
		if ( ! empty( $field_value ) && $field->phoneFormat == 'standard' && preg_match( '/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/', $field_value, $matches ) ) {
			$field_value = sprintf( '%s-%s-%s', $matches[1], $matches[2], $matches[3] );
		}

		return $field_value;
	}

	/**
	 * Configure the settings page settings
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
	  return array(
		  array(
			  'title'     => esc_html__( 'Automizy Account Information', 'automizy-gravity-forms' ),
			  'description' => sprintf(
										  '<p>%s</p>',
				  sprintf(
											  esc_html__( 'Automizy is Email Marketing Software
											  that is designed to increase your open rates. If you don\'t have an Automizy account, you can %1$ssign up for one here.%2$s', 'automizy-gravity-forms' ),
											  '<a href="http://automizy.com" target="_blank">',
											  '</a>'
										  )
									  ),
			  'fields'    => array(
				  array(
					  'name'              => 'apiKey',
					  'label'             => esc_html__( 'API Key', 'automizy-gravity-forms' ),
					  'type'              => 'text',
					  'class'             => 'medium',
					  'feedback_callback' => array( $this, 'initialize_api' ),
					  'description'         => sprintf(
						  wp_kses(
													  __( 'You can find your API key <a href="%s" target="_blank">here</a>.', 'automizy-gravity-forms' ),
													  array(
														  'a' => array(
															  'href' => array(),
															  'target' => array(),
														  ),
													  )
												  ),
												  esc_url( 'https://app.automizy.com/api-token' )
											  ),
				  ),
			  ),
		  ),
	  );
	}

	/**
	 * Configures the settings which should be rendered on the feed edit page in the Form Settings > Simple Feed Add-On area.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		$settings_fields =  array(
			array(
				'title'  => esc_html__( 'Automizy Feed Settings', 'automizy-gravity-forms' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Feed name', 'automizy-gravity-forms' ),
						'type'    => 'text',
						'name'    => 'feedName',
						'tooltip' => esc_html__( 'Give the feed a name', 'automizy-gravity-forms' ),
						'class'   => 'small',
					),
					array(
						'label'   => esc_html__( 'Feed Description', 'automizy-gravity-forms' ),
						'type'    => 'text',
						'name'    => 'feedDescription',
						'tooltip' => esc_html__( 'Enter a description for this feed', 'automizy-gravity-forms' ),
						'class'   => 'small',
					),
					array(
						'name'       => 'contactList',
						'label'      => esc_html__( 'Contact List', 'automizy-gravity-forms' ),
						'type'       => 'select',
						'required'   => true,
						'choices'    => $this->get_lists(),
						'no_choices' =>
						esc_html__( 'No lists found', 'automizy-gravity-forms' ),
						'tooltip'    => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Contact List', 'automizy-gravity-forms' ),
							esc_html__( 'Select your list you want to add your contacts to', 'automizy-gravity-forms' )
						),
					),
					array(
						'name'       => 'contactTag',
						'label'      => esc_html__( 'Tag', 'automizy-gravity-forms' ),
						'type'       => 'select',
						'required'   => false,
						'choices'    => $this->get_tags(),
						'no_choices' =>
						esc_html__( 'No tags found', 'automizy-gravity-forms' ),
						'tooltip'    => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Tags', 'automizy-gravity-forms' ),
							esc_html__( 'Tag this contact', 'automizy-gravity-forms' )
						),
					),
					array(
						'name'      => 'mappedFields',
						'label'     => esc_html__( 'Map Fields', 'automizy-gravity-forms' ),
						'type'      => 'field_map',
						'field_map' => $this->get_custom_fields_as_field_map(),
						'tooltip'   => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Map Fields', 'automizy-gravity-forms' ),
							esc_html__( 'Map your Automizy fields to the appropriate Gravity Form fields by selecting the appropriate form field from the list.', 'automizy-gravity-forms' )
						),
					),
					array(
						'name'           => 'condition',
						'label'          => esc_html__( 'Condition', 'automizy-gravity-forms' ),
						'type'           => 'feed_condition',
						'checkbox_label' => esc_html__( 'Enable Condition', 'automizy-gravity-forms' ),
						'instructions'   => esc_html__( 'Process this simple feed if', 'automizy-gravity-forms' ),
					),
				),
			),
		);
		$settings_fields = apply_filters('gf_automizy_feed_settings_fields', $settings_fields);
		return $settings_fields;
	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * Display the name, contact list and the feed description
	 *
	 * TODO: Should we put the tag in here as well?
	 *
	 * @return array
	 */
	public function feed_list_columns() {
	   $feed_list_columns =  array(
		   'feedName'        => esc_html__( 'Name', 'automizy-gravity-forms' ),
		   'contactList'     => esc_html__( 'Contact List', 'automizy-gravity-forms' ),
		   'feedDescription' => esc_html__( 'Feed Description', 'automizy-gravity-forms' ),
	   );
	   return apply_filters('gf_automizy_feed_list_columns', $feed_list_columns);
	}

	/**
	 * Format the value to be displayed in the contactList column.
	 *
	 * @param array $feed The feed being included in the feed list.
	 *
	 * @return string
	 */
	public function get_column_value_contactList( $feed ) {
		if ( ! $this->initialize_api() ) {
			return;
		}
		$contact_list = rgars( $feed, 'meta/contactList' );
		return $this->automizy->get_list_name( $contact_list );
	}


	/**
	 *
	 * Set the api key and make sure we can connect to the API
	 *
	 * @return boolean
	 */
	public function initialize_api() {
	  if ( false == $this->automizy ) {
			$this->automizy = new Automizy_API( rgar( $this->get_plugin_settings(), 'apiKey' ) );
		}
		return $this->automizy->check_api();
	}

	/**
	 * Prevent feeds being listed or created if an api key isn't valid.
	 *
	 * @return bool
	 */
	public function can_create_feed() {
		 return $this->initialize_api();
	}

	/**
	 *
	 * Get the contact lists from the API
	 *
	 * @return array
	 */
	public function get_lists() {
	   if ( ! $this->initialize_api() ) {
			return;
		}

		$choices = array(
			array(
				'label' => esc_html__( 'Select a list', 'automizy-gravity-forms' ),
				'value' => '',
			),
		);

		$lists = $this->automizy->get_lists();
		if ( false == $lists ) {
			return;
		}

		foreach ( $lists as $list ) {
			$choices[] = array(
				'label' => esc_html( $list['name'] ),
				'value' => esc_html( $list['id'] ),
			);
		}
		return $choices;
	}

	/**
	 *
	 * Get the tags from the API
	 *
	 * @return array
	 */
	public function get_tags() {
		if ( ! $this->initialize_api() ) {
			return;
		}
		$choices = array(
			array(
				'label' => esc_html__( 'Select a tag', 'automizy-gravity-forms' ),
				'value' => '',
			),
		);

		$tags = $this->automizy->get_tags();
		if ( false == $tags ) {
			return;
		}

		foreach ( $tags as $tag ) {
			$choices[] = array(
				'label' => esc_html( $tag['name'] ),
				'value' => esc_html( $tag['name'] ),
			);
		}
		return $choices;
	}



	/**
	 * Process the feed e.g. subscribe the user to a list.
	 *
	 * @param array $feed The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 *
	 * @return bool|void
	 */
	public function process_feed( $feed, $entry, $form ) {

		// Bail if we can't get an api connection

		if ( ! $this->initialize_api() ) {
			$this->add_feed_error( esc_html__( 'Couldn\'t add to list because we couldn\'t fire up the API', 'automizy-gravity-forms' ), $feed, $entry, $form );
			return;
		}

		// Get the plugin settings.
		$settings = $this->get_plugin_settings();

		// Access a specific setting e.g. an api key
		$key = rgar( $settings, 'apiKey' );

		// Retrieve the name => value pairs for all fields mapped in the 'mappedFields' field map.
		$field_map = $this->get_field_map_fields( $feed, 'mappedFields' );

		// Loop through the fields from the field map setting building an array of values to be passed to the third-party service.
		$merge_vars = array();
		foreach ( $field_map as $name => $field_id ) {
			if ( $name == 'email' ) {
				$email = $this->get_field_value( $form, $entry, $field_id );
				continue;
			}
			// Get the field value for the specified field id
			$merge_vars[ $name ] = $this->get_field_value( $form, $entry, $field_id );
		}

		$list = $feed['meta']['contactList'];
		$tag  = $feed['meta']['contactTag'];

		$this->automizy->add_contact( $list, $email, $merge_vars, $tag );
	}
}
