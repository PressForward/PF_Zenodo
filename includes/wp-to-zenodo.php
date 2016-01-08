<?php

class WP_to_Zenodo {

	public static $ver = '1.0.0';

	public static function init() {
				static $instance;

				if ( ! is_a( $instance, 'WP_to_Zenodo' ) ) {
					$instance = new self();
				}

				return $instance;
		}

	private function __construct() {
		#Stuff
		$this->setup();
	}

	public function setup($env = 'stage'){
		if ( 'stage' == $env){
			$this->base_zenodo_url = 'https://sandbox.zenodo.org/api/';
			$this->api_key = STAGE_ZENODO_KEY;
		}
		$this->http_interface = ZS_JSON_Workers::init();
	}

	public function assemble_url($endpoint = 'deposit'){
		switch ($endpoint) {
			case 'deposit':
				return $this->base_zenodo_url.'deposit/depositions/?access_token='.$this->api_key;
				break;

			default:
				# code...
				break;
		}
	}

	public function inital_submit(Submit_Object $submit_object){
		$core_data = array(
			'relation'		=>	'isAlternateIdentifier',
			'identifier'	=>	$submit_object->item_url
		);
		$related_ids = array_merge($submit_object->related, $core_data);
		$args = array(
			'metadata' => array(
				'title'				=> 	$submit_object->title,
				'upload_type'		=>	'publication',
				# @TODO Turn this to 'blog' when ready
				'publication_type'	=>	'other',
				//Note: Can take basic HTML
				'description'		=>	$submit_object->description,
				// An array of objects to become
				// {"name": "Doe, John", "affiliation": "Zenodo"}
				'creators'			=>	$submit_object->authors,
				'publication_date'	=>	$submit_object->publication_date,
				'access_right'		=>	'open',
				'prereserve_doi'	=>	true,
				'related_identifiers'	=>	$submit_object->related_ids

			)
		);
		$post_result = $this->post($args);

	}

	public function post($args){
		$url = $this->assemble_url('deposit');
		$post = $this->http_interface->post();
	}

	public function process_error_code($code){

	}

}
