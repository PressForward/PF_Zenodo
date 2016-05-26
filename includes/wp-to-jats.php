<?php

class WP_to_JATS {
	public static $ver = '1.0.0';

	public static function init() {
				static $instance;

				if ( ! is_a( $instance, 'WP_to_JATS' ) ) {
					$instance = new self();
				}

				return $instance;
		}

	private function __construct() {
		#Stuff
		pf_log('Start up WP_to_JATS');
	}
}
