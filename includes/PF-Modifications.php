<?php

class PFModifications {
	public static function init() {
		static $instance;

		if ( ! is_a( $instance, 'PFModifications' ) ) {
			$instance = new self();
		}

		return $instance;
	}


	/**
	 * Construct function.
	 *
	 * See http://php.net/manual/en/language.oop5.decon.php to get a better understanding of what's going on here.
	 *
	 * @since 1.7
	 *
	 */
	private function __construct() {
		$this->metas = $this->declare_valid_metas();
		pf_log('PFModifications go.');
		$this->fire_hooks();
	}

	public function fire_hooks(){
		add_action( 'transition_pf_post_meta', array($this, 'process_zenodo_meta'), 10, 2 );
		add_action( 'establish_pf_metas', array($this, 'establish_zenodo_meta'), 10, 2 );
		add_action( 'nominate_this_sidebar_top', array($this, 'zenodo_required_meta_fields') );

		add_filter( 'pf_author_nominate_this_prompt', function(){ return 'Enter author names, semicolon separated, in the following format: , ; ...'; } );
		add_filter( 'pf_meta_terms', array( $this, 'valid_meta_terms' ), 10, 1 );
	}

	public function declare_valid_metas(){
		return array(
				'pf_affiliations' => array(
					'name' => 'pf_affiliations',
					'definition' => __('Semicolon seperated list', 'pf'),
					'function'	=> __('Stores a count of the number of errors a feed has experianced', 'pf'),
					'type'	=> array('adm'),
					'use'	=> array(),
					'level'	=> array('post', 'nomination'),
					'serialize'	=> false
				),
				'pf_keywords' => array(
					'name' => 'pf_keywords',
					'definition' => __('Semicolon seperated list', 'pf'),
					'function'	=> __('Stores a count of the number of errors a feed has experianced', 'pf'),
					'type'	=> array('adm'),
					'use'	=> array(),
					'level'	=> array('post', 'nomination'),
					'serialize'	=> false
				),
				'pf_references' => array(
					'name' => 'pf_references',
					'definition' => __('Semicolon seperated list', 'pf'),
					'function'	=> __('Stores a count of the number of errors a feed has experianced', 'pf'),
					'type'	=> array('adm'),
					'use'	=> array(),
					'level'	=> array('post', 'nomination'),
					'serialize'	=> false
				),
				'pf_abstract' => array(
					'name' => 'pf_abstract',
					'definition' => __('Semicolon seperated list', 'pf'),
					'function'	=> __('Stores a count of the number of errors a feed has experianced', 'pf'),
					'type'	=> array('adm'),
					'use'	=> array(),
					'level'	=> array('post', 'nomination'),
					'serialize'	=> false
				),
				'pf_license' => array(
					'name' => 'pf_license',
					'definition' => __('Semicolon seperated list', 'pf'),
					'function'	=> __('Stores a count of the number of errors a feed has experianced', 'pf'),
					'type'	=> array('adm'),
					'use'	=> array(),
					'level'	=> array('post', 'nomination'),
					'serialize'	=> false
				),

			);
	}

	public function valid_meta_terms( $metas ){
		return array_merge($metas,
			$this->metas
		);
	}

	public function process_zenodo_meta( $old_id, $new_id = false ){
		//var_dump('<pre>');
		//var_dump($old_id.'+'.$new_id);
		if (!$new_id){
			$new_id = $old_id;
		}
		$this->zenodo_meta_maker($new_id);
	}

	public function establish_zenodo_meta( $id, $args ){
		//var_dump($id);
		$this->zenodo_meta_maker($id);
	}

	public function zenodo_meta_maker( $id ){
		//var_dump($_POST);
		//var_dump($_GET);
		//die();
		pf_log($id);
		pf_log($_POST);
		$valid_metas = array_merge(
			$this->metas,
			array(
				'zen_source_title' => array( 'name' => 'source_title' ),
				'zen_item_date'	=>	array( 'name' => 'item_date' )
			)
		);
		$check = pressforward('controller.metas')->update_pf_meta($id, 'pf_zenodo_ready', true);
		foreach ($valid_metas as $get_key => $descript ){
			pf_log($id.'-'.$get_key.'-'.$descript['name']);
			pf_log($_POST[$get_key]);
			$check = pressforward('controller.metas')->update_pf_meta($id, $descript['name'], $_POST[$get_key]);
			pf_log($check);
		}
		return '';
	}

	public function zenodo_required_meta_fields(){
		?>
			<div id="zenododiv" class="postbox">
				<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle' ); ?>"><br /></div>
				<h3 class="hndle"><?php _e('Bibliographic Metadata') ?></h3>
				<div class="inside">
					<label for="pf_affiliations"><input type="text" id="pf_affiliations" name="pf_affiliations" value="" placeholder="Sample University;" /><br />&nbsp;<?php _e('Enter Author Affiliations, semicolon seperated, in same order as authors.', 'pf'); ?></label><hr />
					<?php
						$title = isset( $_GET['t'] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET['t'] ) , ENT_QUOTES) ) ) : '';
						$tite = esc_html( $title );
					?>
					<label for="zen_source_title"><textarea type="text" id="zen_source_title" rows="5" cols="20" name="zen_source_title" placeholder="Name of Site"><?php echo $title; ?></textarea><br />&nbsp;<?php _e('Name of current site.', 'pf'); ?></label><hr />
					<label for="pf_keywords"><input type="text" id="pf_keywords" name="pf_keywords" value="" placeholder="Keyword1; Keyword2;" /><br />&nbsp;<?php _e('Enter Keywords, semicolon seperated.', 'pf'); ?></label><hr />

					<label for="pf_references"><textarea id="pf_references" name="pf_references" value="" rows="10" cols="20"></textarea><br />&nbsp;<?php _e('Enter links or DOIs for references listed in article, semicolons separated.', 'pf'); ?></label><hr />

					<label for="zen_item_date"><input type="text" id="zen_item_date" name="zen_item_date" value="" placeholder="2015-10-30" /><br />&nbsp;<?php _e('Enter published date in the format YYYY-MM-DD, if that date is not today.', 'pf'); ?></label><hr />

					<label for="pf_abstract"><textarea id="pf_abstract" name="pf_abstract" value="" rows="10" cols="20"></textarea><br />&nbsp;<?php _e('Enter an abstract or description.', 'pf'); ?></label><hr />
					<label for="pf_license">
						<select type="text" id="pf_license" name="pf_license">
							<option value="cc-zero">CC-0</option>
							<option value="cc-by">CC-BY</option>
						</select><br />
						CC0  :: “I don’t care about attribution”
						CC-By :: “I want to receive attribution”
					</label><hr />
				</div>

			</div>
		<?php
	}


}

function pf_modifications() {
	return PFModifications::init();
}
