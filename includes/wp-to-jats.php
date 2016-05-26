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
		$this->template_dir(PF_A_ROOT.'/templates');
	}

	public function assemble_jats( $post_object ){

	}

	public function get_the_jats( $post_object, $override_vars = array(), $use_mu = false ){
		$post_vars = $this->assemble_jats( $post_object );
		$vars = array_merge( $post_vars, $override_vars );
		return $this->get_view( 'JATS', $vars, $use_mu );
	}

	public function the_jats( $vars = array(), $use_mu = false ){
		echo $this->get_the_jats( $vars, $use_mu );
	}

	public function get_view_path( $view, $use_mu = false ){
	  if ( isset( $this->template_dir ) && !$use_mu ) {
		$template_dir = $this->template_dir;
	  } elseif ( !$use_mu && ( file_exists( dirname( __FILE__ ) . '/../template-parts/' ) ) ) {
		$template_dir = dirname( __FILE__ ) . '/../template-parts/';
	  } else {
		$template_dir = WPMU_PLUGIN_DIR . '/template-parts/';
	  }
	  $view_file = $template_dir . $view . '.tpl.php';
	  //var_dump($view_file); die();
	  if ( ! file_exists( $view_file ) ){
		return false;
	  }
	  return $view_file;
	}

	  /**
	   * Get a given view (if it exists)
	   *
	   * This function is how to activate the View part of your singleton
	   * plugin. But calling it with your template name and vars you pass
	   * the vars to what should be a mostly non-PHP template file that takes
	   * the variables and turns them into the view. The system
	   * will automatically append .tpl.php to your view string, so that
	   * should be the file naming pattern at the end of your template files
	   * no matter where they are.
	   *
	   * This plugin will attempt to find the template file using the following
	   * pattern:  1. It will look for a directory path set in the `setup_globals`
	   * method with the class-level property key 'template_dir'; 2. It will
	   * check for a folder local to the current directory (say a plugin or theme)
	   * above the directory of this file with a tirectory name of template-parts;
	   * 3. it will look at the common install-wide template folder located in the
	   * mu-plugins directory.
	   *
	   * Additionally, you can force any view to use a template at the mu-plugins
	   * level template folder by passing true as the third, final, paramater
	   * of this method.
	   *
	   * @param string   $view         The slug of the view
	   * @param array    $vars         An array of all variables to pass to the view.
	   * @param bool     $use_mu       Controls if you want to force the use of a
	   *                               system-wide mu-plugins-level template.
	   * @return string                The view.
	   */
	  public function get_view( $view, $vars = array(), $use_mu = false ) {
		  $view_file = $this->get_view_path($view, $use_mu);
		  if ( false === $view_file || ! file_exists( $view_file ) ){
			  #var_dump('Noooo ' .  $view_file); die();
			  return '';
		  }
		  extract( $vars, EXTR_SKIP );
		  ob_start();
		  include $view_file;
		  return ob_get_clean();
	  }
	  /**
	   * Echos a view.
	   *
	   * This function will echo the results of the `get_view` function.
	   *
	   * @see get_view
	   */
	  public function the_view( $view, $vars, $use_mu = false ){
		echo $this->get_view($view, $vars);
	  }
}

function wp_to_jats(){
	return WP_to_JATS::init();
}
