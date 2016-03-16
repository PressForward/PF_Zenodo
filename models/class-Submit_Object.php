<?php
class Zenodo_Submit_Object{

    function __construct( $args ){
		$this->relation = array();
        if ( !is_array( $args ) ){
            return new WP_Error(
                'bad_object_call',
                __('Submit_Object was badly called. It requires, but did not receive, an array. ')
            );
        } elseif (empty($args['item_url'])) {
			return new WP_Error(
				'no_item_url',
				__('The array to form Submit_Object did not have a URL.')
			);
		} else {
            $this->set_up_object( $args );
        }
        #$this->depreciated = array();
        #$this->interface = $interface;
    }

	public function set($key, $value){
		switch ($key) {
			case 'ID':
				$this->post_id = $value;
				break;
			case 'item_url':
				$this->relation[] = array(
					'relation'		=>	'isAlternativeIdentifier',
					'identifier'	=>	$value
				);
				break;
			case 'title':
				$this->title = $value;
				break;
			default:
				$this->$key = $value;
				break;
		}
	}

    private function set_up_object( $basic_data ){
        //var_dump($args);
        $defaults = array(
                'ID'    => 0,
                'item_url' => '',
                'title' => '',
                'description' => '',
				'authors' => array(),
                // '2015-05-05'
				'publication_date' => '',
				'related'	=> array()
            );
        $args = wp_parse_args($basic_data, $defaults);
        //var_dump($args);
        # Enforce business, sanitization rules.
        foreach ($args as $key => $property) {
            if ( !empty( $property ) ){
                $this->set($key, $property);
                continue;
            }
            else ( empty($property) ){
                continue;
            }
        }

    }

}
