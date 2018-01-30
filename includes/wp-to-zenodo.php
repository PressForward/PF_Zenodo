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
		//pf_log('Start up WP_to_Zenodo');
		if ( WP_DEBUG ){
			$this->setup('stage');
		} else {
			$this->setup('prod');
		}
		$this->includes();
		add_action('transition_post_status', array($this, 'to_zenodo_on_publish'), 10, 3);
		add_action( 'admin_init', array( $this, 'register_api_key') );
	}

	public function to_zenodo_on_publish( $new_status, $old_status, $post ){
		remove_action( 'transition_post_status', array($this, 'to_zenodo_on_publish') );
		$zenodo_data = get_post_meta($post->ID, 'pf_zenodo', true);
		if ( is_array($zenodo_data) && array_key_exists( 'setup', $zenodo_data ) && ($zenodo_data['setup']->id != false)){
			// Already on Zenodo
			pf_log('Zenodo Publish complete');
		} else {
			$check = pressforward('controller.metas')->get_post_pf_meta($post->ID, 'pf_zenodo_ready', true);
			if ( ( 'publish' == $new_status ) && $check){
				$post_object = get_post($post, ARRAY_A);
				$zenodo_object = new Zenodo_Submit_Object($post_object);
				$response = $this->inital_submit( $post_object['ID'], $zenodo_object );
				// pf_log('Zenodo Object');
				// pf_log($zenodo_object);
				//var_dump($response); die();
				if ( false !== $response ){
					pf_log('Response is ready');
					$jats_response = $this->xml_submit($response, $zenodo_object);
					$metadata_response = $this->data_submit($response, $zenodo_object);
					$publish_response = $this->publish($response);
				} else {
					pf_log('submit failed');
					pf_log($response);
				}
			} else {
				pf_log('Not ready or after ready for Zenodo Publishing.');
				pf_log($check);
			}
		}

		add_action('transition_post_status', array($this, 'to_zenodo_on_publish'), 10, 3);
	}

	public function enforce($type, $value){
		switch ($type) {
			case 'value':
				# code...
				break;

			default:
				# code...
				break;
		}
		//var_dump($type);
		//var_dump($value);
		return $value;
	}

	public function fill($id, $type){
		$filled = '';
		switch ($type) {
			case 'upload_type':
				$filled = 'publication';
				break;

			case 'publication_type':
				$filled = 'other';
				break;

			case 'publication_date':
				$filled = pressforward('controller.metas')->get_post_pf_meta($id, 'item_date', true);
				if (empty($filled)){
					$filled = date('c');
				}
				break;
			case 'title':
				$filled = get_the_title($id);
				break;
			case 'access_right':
				$filled = 'open';
				break;
			case 'license':
				$filled = 'cc-zero';
				break;
			case 'related_identifiers':
				// Always will be set, never will be filled.
				break;
			case 'references':
				$filler = $this->semicolon_split(pressforward('controller.metas')->get_post_pf_meta($id, 'pf_references', true));
				break;
			case 'communities':
				$filler = array(
					array('identifier' => 'arceli')
				);
				break;
			default:
				$filler = 'fill_'.$type;
				if (method_exists($this, $filler)){
					$filled = $this->$filler($id);
				} else {
					pf_log('No fill for '.$type);
					$filled = '';
				}
				break;
		}

		return $this->enforce($type, $filled);
	}

	public function semicolon_split($string){
		return explode(';', $string);
	}

	public function fill_creators($id){
		$filled = pressforward('controller.metas')->get_post_pf_meta($id, 'item_author', true);
		$affiliation = $this->semicolon_split(pressforward('controller.metas')->get_post_pf_meta($id, 'pf_affiliations'));
		$c = 0;
		$authors = $this->semicolon_split($filled);
		if (!empty($authors)){
			$creators = array();
			foreach ($authors as $author){
				if (!empty($affiliation) && !empty($affiliation[$c])){
					$creators[] = array('name' => $author, 'affiliation' => $affiliation[$c]);
				} else {
					$creators[] = array('name' => $author );
				}
				$c++;
			}
		} else {
			$creators = array( 'name' => $filled );
		}
		return $creators;

	}

	public function fill_keywords($id){
		$keywords = pressforward('controller.metas')->get_post_pf_meta($id, 'pf_keywords');
		return $this->semicolon_split($keywords);

	}

	public function fill_description($id){
		$filled = pressforward('controller.metas')->get_post_pf_meta($id, 'revertible_feed_text');
		if (empty($filled)){
			$post = get_post($id);
			$filled = wp_html_excerpt($post->post_content, 160);
		} else {
			$filled = wp_html_excerpt($filled, 160);
		}
		return $filled;
	}

	public function fill_journal_title($id){
		$filled = '';
		$filled = pressforward('controller.metas')->get_post_pf_meta($id, 'source_title');
		if (!empty($filled)){
			$filled .= '; ';
		}
		$filled .= 'Aggregated by '.get_bloginfo('name');
		return $filled;
	}

	private function includes(){
		require_once(dirname(__FILE__).'/PF-Modifications.php');
		// Start me up!
		add_action('pressforward_init', 'pf_modifications', 12, 0 );
	}

	public function set_api_key($env){
		$value = get_option('zenodo_api-key', "");
		if ( "" !== $value ) {
			$this->api_key = $value;
		} else {
			if ( defined('STAGE_ZENODO_KEY') &&  'stage' == $env ){
				$this->api_key = STAGE_ZENODO_KEY;
			} else if ( defined('PROD_ZENODO_KEY') &&  'prod' == $env ) {
				$this->api_key = PROD_ZENODO_KEY;
			} else {
				$this->api_key = '';
			}
		}
	}

	public function setup($env = 'stage'){
		if ( 'stage' == $env ){
			$this->base_zenodo_url = 'https://sandbox.zenodo.org/api/';
			//Define in your WP config
			# @TODO User setting.
		} elseif ( 'prod' == $env ){
			$this->base_zenodo_url = 'https://zenodo.org/api/';
			//Define in your WP config
			# @TODO User setting.
		}
		$this->set_api_key($env);

		$this->http_interface = ZS_JSON_Workers::init();
	}

	public function assemble_url($endpoint = 'deposit', $id_array = false){
		// Allow the Zenodo metadata object to override the URL when required.
		if (is_array($id_array) && array_key_exists('setup', $id_array) && is_object($id_array['setup']) && 'Zenodo_Metadata_Object' === get_class($id_array['setup'])){
			$links = $id_array['setup']->get('links');
			if ( isset($links->$endpoint) ){
				$url = trailingslashit($links->$endpoint).'?access_token='.$this->api_key;
			}
		}
		switch ($endpoint) {
			case 'deposit':
				$url = $this->base_zenodo_url.'deposit/depositions?access_token='.$this->api_key;
				break;
			case 'publish':
				$url = $this->base_zenodo_url.'deposit/depositions/'.$id_array['id'].'/actions/publish?access_token='.$this->api_key;
				break;
			case 'files':
				$url = $this->base_zenodo_url.'deposit/depositions/'.$id_array['id'].'/files?access_token='.$this->api_key;
				break;
			case 'data':
				$url = $this->base_zenodo_url.'deposit/depositions/'.$id_array['id'].'?access_token='.$this->api_key;
				break;
			default:
				# code...
				break;
		}
		pf_log($url);
		return $url;
	}

	public function first_zenodo_request(){
		$url = $this->assemble_url('deposit');
		pf_log('deposit to:' + $url);
		$empty_deposit = $this->post( $url, array() );
		pf_log($empty_deposit);
		return $empty_deposit;
	}

	public function inital_submit( $post_id, Zenodo_Submit_Object $submit_object ){
		$empty_deposit = $this->first_zenodo_request();
		pf_log($empty_deposit);
		//$post_result = $this->post($url, $args);
		if ( !empty($empty_deposit) ){
			$zenodo_meta = get_post_meta($post_id, 'pf_zenodo', true);
			if ( empty( $zenodo_meta ) ) {
				$zenodo_meta = array();
			}
			$zenodo_meta_setup = new Zenodo_Metadata_Object($post_id);
			pf_log($zenodo_meta_setup);
			$zenodo_meta_setup->build($empty_deposit);
			pf_log($zenodo_meta_setup);
			$zenodo_meta['setup'] = $zenodo_meta_setup;
			$zenodo_meta['submit'] = $submit_object;
			pf_log('Zenodo Meta');
			pf_log($zenodo_meta);
			update_post_meta($post_id, 'pf_zenodo', $zenodo_meta);
			add_post_meta($post_id, 'pf_zenodo_id', $zenodo_meta_setup->get('id'), true);
			$post = get_post($post_id);
			return array (
				'id'			=>	$zenodo_meta_setup->get('id'),
				'title' 		=>  $post->post_title,
				'fileslug'		=>	$post->post_name.'.jats',
				'file'			=>	trailingslashit(get_permalink($post_id)).'jats',
				'setup'			=>	$zenodo_meta_setup
				//'file'	=>	$post_result['doi'] // this doesn't happen until later, when we publish
			);
		} else {
			return false;
		}

	}

	public function xml_submit($id_array, $zenodo_object){
		$url = $this->assemble_url('files', $id_array);
		$metas = $id_array['setup'];
		$post_id = $metas->get('post_id');
		$filepath = wp_to_jats()->save_the_jats($post_id);
		//file upload
		$r = $this->post_with_file($url, $filepath, array( 'filename' => $id_array['fileslug'] ) );
		return $r;
		//$file_data = wp_to_jats()->get_the_jats($id_array['post_id']);
	}

	public function data_submit($id_array, $zenodo_object){
		$url = $this->assemble_url('data', $id_array);
		$data = new stdClass();
		$zenodo_object->validate();
		$data->metadata = $zenodo_object;
		return $this->request($url, $data, array( 'method' => 'PUT' ) );
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
		$url = $this->assemble_url('publish', $id_array);
		$response = $this->post($url, array());
		pf_log($response);
		//Should return 202 to say accepted.
		return $response;
	}

	public function post($url, $args){
		$post = $this->http_interface->post($url, $args);
		return $post;
	}

	public function request($url, $args, $method){
		$post = $this->http_interface->post($url, $args, $method);
		return $post;
	}

	public function post_with_file($url, $file, $data){
		$post = $this->http_interface->curl_upload($url, $file, $data);
		return $post;
	}

	public function process_error_code($code){

	}

	public function zenodo_api_key_storage(){
		if (array_key_exists('zenodo_api-key', $_POST ) && ('' != $_POST['zenodo_api-key']) ){
			//update_option('zenodo_api-key', $_POST['zenodo_api-key']);
		}
		$value = get_option('zenodo_api-key', "");
		?>
		<input name="zenodo_api-key" type="text" id="zenodo_api-key" value="<?php echo esc_attr($value); ?>" class="regular-text code">'
		<?php
		return '';
	}

	public function register_api_key(){
		register_setting( 'general', 'zenodo_api-key', array(
			'type'	=>	'string',
			'description'	=>	'API Key for Zenodo, from your Applications Settings',
			'show_in_rest'	=> false,
			'sanitize_callback'	=> function($value){ return sanitize_text_field($value); }
		) );
		add_settings_field(
            'zenodo_api-key',
            'API Key for Zenodo, from your Applications Settings',
            array( $this, 'zenodo_api_key_storage' ),
            'general',
            'default'
            //array( 'label_for' => 'zenodo_api-key' )
        ); 
	}
}

function wp_to_zenodo(){
	return WP_to_Zenodo::init();
}
add_action('pressforward_init', 'wp_to_zenodo', 11, 0);
