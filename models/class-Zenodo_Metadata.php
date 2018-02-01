<?php
class Zenodo_Metadata_Object {

	private $data = array();

	public function __construct( $id ) {
		$this->set( 'post_id', $id );
	}

	public function build( $args ) {
		if ( ! is_string( $args ) ) {
			return new WP_Error(
				'bad_object_build',
				__( 'Zenodo_Metadata_Object was badly called. It requires, but did not receive, a string. ' )
			);
		} else {
			$as_object = json_decode( $args );
			if ( ! is_object( $as_object ) ) {
				return new WP_Error(
					'bad_object_build',
					__( 'Zenodo_Metadata_Object was badly called. It requires, but did not receive, a JSON string with data. ' )
				);
			}
			foreach ( $as_object as $key => $value ) {
				$this->set( $key, $value );
			}
		}
	}

	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	public function get( $key ) {
		return $this->data[ $key ];
	}

	public function __set( $key, $value ) {
		return $this->set( $key, $value );
	}

	public function __get( $key ) {
		return $this->get( $key );
	}

	public function __unset( $key ) {
		$data = $this->data;
		unset( $data[ $key ] );
		$this->data = $data;
		return '';
	}

	public function __toString() {
		return json_encode( $this->data );
	}
}
