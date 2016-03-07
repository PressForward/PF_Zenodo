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
		$this->fire_hooks();
	}

	public function fire_hooks(){
		add_action( 'transition_pf_post_meta', array($this, 'process_zenodo_meta') );
		add_action( 'establish_pf_metas', array($this, 'establish_zenodo_meta') );
		add_action( 'nominate_this_sidebar_top', array($this, 'zenodo_required_meta_fields') );

		add_filter( 'pf_author_nominate_this_prompt', function(){ return 'Enter author names, semicolon seperated, in the following format: [last-name], [first-name]; ...'; } );
	}

	public function process_zenodo_meta( $old_id, $new_id ){
		$this->zenodo_meta_maker($new_id);
	}

	public function establish_zenodo_meta( $id, $args ){
		$this->zenodo_meta_maker($id);
	}

	public function zenodo_meta_maker( $id ){

	}

	public function zenodo_required_meta_fields(){
		?>
			<div id="zenododiv" class="postbox">
				<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle' ); ?>"><br /></div>
				<h3 class="hndle"><?php _e('Bibliographic Metadata') ?></h3>
				<div class="inside">

				</div>

			</div>
		<?php
	}


}

function pf_modifications() {
	return PFModifications::init();
}

// Start me up!
pf_modifications();