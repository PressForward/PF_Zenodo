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

	}

	public function setup($env = 'stage'){
		if ( 'stage' == $env){
			$this->base_zenodo_url = 'https://sandbox.zenodo.org/api/';
			$this->api_key = STAGE_ZENODO_KEY;
		}
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

	public function inital_submit(){
		$url = $this->assemble_url('deposit');
		$args = array(
			'metadata' => array(
				'title'				=> 	$title,
				'upload_type'		=>	'publication',
				# @TODO Turn this to 'blog' when ready
				'publication_type'	=>	'other',
				//Note: Can take basic HTML
				'description'		=>	$description,
				// An array of objects to become
				// {"name": "Doe, John", "affiliation": "Zenodo"}
				'creators'			=>	$authors,
				'publication_date'	=>	$publication_date,
				'access_right'		=>	'open',
				'prereserve_doi'	=>	true,
				//'related_identifiers' later for citations.

			)
		);
		$post_result = $this->post($url, $args);

	}

	public function post(){
		$http_interface = ZS_JSON_Workers::init();
		$post = $http_interface->post();
	}

	public function process_error_code($code){

	}

}
