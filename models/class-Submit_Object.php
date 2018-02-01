<?php
class Zenodo_Submit_Object {


	// private $zenodo_contract;
	public function __construct( $args ) {
		$this->related_identifiers = array();
		$this->zenodo_contract     = $this->deliver_contract();
		if ( ! is_array( $args ) ) {
			pf_log();
			return new WP_Error(
				'bad_object_call',
				__( 'Submit_Object was badly called. It requires, but did not receive, an array. ' )
			);
			/**        } elseif (empty($args['item_url'])) {
					return new WP_Error(
						'no_item_url',
						__('The array to form Submit_Object did not have a URL.')
					);*/
		} else {
			$this->set_up_object( $args );
		}
		// $this->depreciated = array();
		// $this->interface = $interface;
	}

	private function set( $key, $value ) {
		switch ( $key ) {
			case 'ID':
				$this->ID = $value;
				break;
			case 'guid':
				$this->related_identifiers[] = array(
					'relation'   => 'isAlternativeIdentifier',
					'identifier' => pressforward( 'controller.metas' )->get_post_pf_meta( $this->ID, 'item_link' ),
				);
				break;
			case 'post_title':
				$this->title = $value;
				break;
			default:
				$this->$key = $value;
				break;
		}
	}

	private function set_up_object( $basic_data ) {
		// var_dump($basic_data); die();
		$defaults = array(
			'ID'          => 0,
			'communities' => array(),
				// 'title' => '',
				// 'authors' => array(),
				// '2015-05-05'
				// 'publication_date' => '',
				// 'related' => array()
		);
		$args = wp_parse_args( $basic_data, $defaults );
		// var_dump($args);
		// Enforce business, sanitization rules.
		foreach ( $args as $key => $property ) {
			if ( ! empty( $property ) ) {
				$this->set( $key, $property );
				continue;
			} elseif ( empty( $property ) ) {
				continue;
			} else {
				continue;
			}
		}

		$this->fill_object();
	}

	private function fill_object() {
		// pressforward('controller.metas')->get_post_pf_meta($id, $field, $single = true, $obj = false)
		// var_dump('fill object');
		// var_dump($this->zenodo_contract);
		pf_log( $this->ID );
		foreach ( $this->zenodo_contract as $contract_prop => $contract_rule ) {
			if ( property_exists( $this, $contract_prop ) && ! empty( $this->$contract_prop ) ) {
				$this->$contract_prop = wp_to_zenodo()->enforce( $contract_prop, $this->$contract_prop );
			} else {
				$this->$contract_prop = wp_to_zenodo()->fill( $this->ID, $contract_prop );
			}
		}
		$this->related_identifiers[] = array(
			'relation'   => 'isIdenticalTo',
			'identifier' => get_permalink( $this->ID ),
		);

		foreach ( $this as $property => $value ) {
			if ( ! array_key_exists( $property, $this->deliver_contract() ) ) {
				unset( $this->$property );
			}
		}
	}

	public function validate() {
		foreach ( $this->deliver_contract() as $key => $value ) {
			if ( empty( $this->$key ) ) {
				unset( $this->$key );
			}
		}
	}

	private function deliver_contract() {
		return array(
			'upload_type'         => '',
			'publication_type'    => '',
			'publication_date'    => date( 'c' ),
			'title'               => '',
			'creators'            => array( new stdClass() ),
			'description'         => '',
			'access_right'        => 'open',
			'license'             => 'cc-zero',
			'keywords'            => array( '' ),
			'related_identifiers' => array( new stdClass() ),
			'references'          => array( '' ),
			'communities'         => array( new stdClass() ),
			'journal_title'       => '',
			'doi'                 => '',
			// 'journal_volume' => '',
			// 'journal_issue' => ''
		);
	}
}
