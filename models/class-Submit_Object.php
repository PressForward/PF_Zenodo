<?php
class Zenodo_Submit_Object{

    function __construct( $args ){

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

        # Enforce business, sanitization rules.
        foreach ($args as $key => $property) {
            if ( false === $property ){
                $this->$key = $property;
                continue;
            }
            elseif ( empty($property) ){
                continue;
            }
            switch ($key) {
                case 'ID':
                    if ( empty($property) ){
						//$property = md5($this->item_url);
                    }
                    break;
                default:
                    # code...
                    break;
            }
            $this->$key = $property;
        }

    }

}
