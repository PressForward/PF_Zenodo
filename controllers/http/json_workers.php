<?php
if (!class_exists('ZS_JSON_Workers')){
	class ZS_JSON_Workers {

		public static $ver = '1.0.3';

		public static function init() {
            		static $instance;

            		if ( ! is_a( $instance, 'ZS_JSON_Workers' ) ) {
                		$instance = new self();
            		}

            		return $instance;
        	}

		public function __construct() {
			#Stuff

		}

		public function head(){
			#header('Content-Type: application/json');
		}

		public function json_page($json){
			$this->head();
			echo $json;
		}

		private function handle($json_result, $error_reporting){
			if ($error_reporting && !$json_result){
				return json_last_error_msg();
			}
			return $json_result;
		}

		public function create($args, $error_reporting = false){
			$json = json_encode($args);
			return $this->handle($json, $error_reporting);
		}

		public function create_the($args, $error_reporting = false){
			echo $this->create($args, $error_reporting);
			return;
		}

		public function take($json, $is_array = false, $error_reporting = false){
			$php_ready = json_decode($json, $is_array);
			return $this->handle($php_ready, $error_reporting);
		}

		public function take_the($json, $is_array, $error_reporting){
			echo $this->take($json, $is_array, $error_reporting);
			return;
		}

		public function post($url, $args = array(), $settings = array(), $use_get = false, $error_reporting = false){
			if (!$use_get){
				if (!empty($settings['method']) && ('POST' != $settings['method'])){
					return $this->post_by_other($url, $args, $settings);
				}
				return $this->post_by_post($url, $args, $settings);
			} else {
				return $this->post_by_get($url, $args, $settings);
			}
		}

		private function post_returned($result, $error_reporting = false){
			if (is_wp_error($result)){
				wp_die($result->get_error_message());
			} elseif (WP_DEBUG){
				// @todo Build a logger function here.
				wp_die(print_r($result, true));
			}
			$r = $result['body'];
			return $r;
		}

		private function normalize_post_headers($args, $settings){
			$default_post_args = array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Content-Type'	=> 'application/json'
				),
				'body' => array(),
				'cookies' => array()
			);
			if (array_key_exists('headers', $settings)){
				$headers = array_merge($default_post_args['headers'], $settings['headers']);
			} else {
				$headers = array(
					'Content-Type'	=> 'application/json'
				);
			}
			$post_args = wp_parse_args($settings, $default_post_args);
			$post_args['headers'] = $headers;
			$post_args['body'] = $this->create($args);
			return $post_args;
		}

		private function post_by_post($url, $args, $settings = array()){
				$post_args = $this->normalize_post_headers($args, $settings);
				$result = wp_remote_post($url, $post_args);
				$posted = $this->post_returned($result, false);
				return $posted;
		}

		private function post_by_other($url, $args = array(), $settings = array()){
				$request_args = $this->normalize_post_headers($args, $settings);
				# wp_safe_remote_request here? https://core.trac.wordpress.org/browser/tags/3.9.2/src/wp-includes/http.php#L0
				$result = wp_remote_request($url, $request_args);
				$posted = $this->post_returned($result, false);
				return $posted;
		}

		private function post_by_get($url, $args, $settings = array()){
			$url_complete = add_query_arg($args, $url);
			$result = wp_remote_get($url_complete, $settings);
			$posted = $this->post_returned($result, false);
			return $posted;
		}

		public function get($url, $args, $settings = array()){
			return $this->post($url, $args, $settings, true);
		}
	}
}
