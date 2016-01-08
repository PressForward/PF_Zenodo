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

	private function includes(){

	}

	public function setup($env = 'stage'){
		if ( 'stage' == $env){
			$this->base_zenodo_url = 'https://sandbox.zenodo.org/api/';
			$this->api_key = STAGE_ZENODO_KEY;
		}
		$this->http_interface = ZS_JSON_Workers::init();
	}

	public function assemble_url($endpoint = 'deposit', $id_array = false){
		switch ($endpoint) {
			case 'deposit':
				return $this->base_zenodo_url.'deposit/depositions/?access_token='.$this->api_key;
				break;
			case 'publish':
				return $this->base_zenodo_url.'deposit/depositions/'.$id_array['id'].'/actions/publish?access_token='.$this->api_key;
				break;
			case 'files':
				return $this->base_zenodo_url.'deposit/depositions/'.$id_array['id'].'/files?access_token='.$this->api_key;
				break;
			default:
				# code...
				break;
		}
	}

	public function inital_submit( $submit_object){
		$core_data = array(array(
				'relation'		=>	'isAlternativeIdentifier',
				'identifier'	=>	$submit_object->item_url
			)
		);
		$related_ids = array_merge($submit_object->related, $core_data);
		//var_dump($related_ids); die();
		$related_objs = array();
		foreach ($related_ids as $array){
			$object = new stdClass();
			foreach ($array as $key => $value)
			{
			    $object->$key = $value;
			}
			$related_objs[] = $object;
		}
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
				'related_identifiers'	=>	$related_objs

			)
		);
		$url = $this->assemble_url('deposit');
		$post_result = $this->post($url, $args);
		if ( false !== $post_result ){
			return array (
				'id'	=>	$post_result['id'],
				'doi'	=>	$post_result['doi']
			);
		} else {
			return false;
		}

	}

	public function xml_submit($id_array, $post){
		$url = $this->assemble_url('files');

		$file_data = '';
	}

	public function image_submit($id_array, $image_atttachment_id){

		$src = wp_get_attachment_url( $image_atttachment_id );
		$file_data = wp_remote_get($src);
	}

	public function html_submit($id_array, $post){
		$url = $this->assemble_url('files');

		$file_data = '';
	}

	public function file_submit($args){
		$url = $this->assemble_url('files');
		//Content-Type: multipart/form-data

		return wp_remote_post($url, $args);
	}

	public function publish($id_array){
		$url = $this->assemble_url('publish');
		$response = $this->post($url, array());
		//Should return 202 to say accepted.
	}

	public function post($url, $args){
		//$url = 'http://requestb.in/1824n361';
		$post = $this->http_interface->post($url, $args);
	}

	public function process_error_code($code){

	}

}
function wp_to_zenodo(){
	return WP_to_Zenodo::init();
}
add_action('init', 'wp_to_zenodo');
