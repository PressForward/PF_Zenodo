<?php
class Zenodo_Submit_Object{

    function __construct( $args ){
		$this->related_identifiers = array();
        $this->zenodo_contract = $this->deliver_contract();
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
				$this->ID = $value;
				break;
			case 'item_url':
				$this->related_identifiers[] = array(
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
            elseif ( empty($property) ){
                continue;
            } else {
                continue;
            }
        }

        $this->fill_object();

    }

    private function fill_object(){
        //pressforward()->metas->get_post_pf_meta($id, $field, $single = true, $obj = false)
        foreach ($this->zenodo_contract as $contract_prop=>$contract_rule){
            if ( property_exists($this, $contract_prop) && !empty($this->$contract_prop) ){
                $this->$contract_prop = wp_to_zenodo()->enforce($contract_prop, $this->$contract_prop);
            } else {
                $this->contract_prop = wp_to_zenodo()->fill($this->ID, $contract_prop);
            }

        }
    }

    public function deliver_contract(){
        return array(
            'upload_type' => '',
            'publication_type' => '',
            'publication_date'  => date('c'),
            'title' => '',
            'creators'  => array( new stdClass() ),
            'description' => '',
            'access_right' => 'open',
            'license'   =>  'cc-zero',
            'keywords'  =>  array(''),
            'related_identifiers' => array( new stdClass() ),
            'references' => array(''),
            'communities' => array( new stdClass() ),
            'journal_title' => '',
            'journal_volume' => '',
            'journal_issue' => ''

        );
    }

}
