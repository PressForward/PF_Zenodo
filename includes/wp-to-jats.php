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
		$this->template_dir = PF_A_ROOT."/templates/";
	}

	private function cats_to_cat_names($categories){
		if (empty($categories)){
			return array();
		}
		$cat_names = array();
		foreach ($categories as $category){
			$cat_names[] = $category->name;
		}
		return $cat_names;
	}

	private function creators_to_authors($creators){
		if (empty( $creators )){
			return array();
		}
		$authors = array();
		foreach ( $creators as $creator ){
			$name = $creator['name'];
			$name_pieces = explode(',', $name);
			$authors[$name] = array(
				'lname'	=> $name[0],
				'fname'	=> $name[1],
			);
		}
		return $authors;
	}

	/**
	 * Method for building date objects that can then be turned into whatever you please.
	 *
	 * This is nice, because you have a netral date object that can be output into any
	 * timezone or format you'd like.
	 * @param  [type] $raw_date_string A raw date string. Whatever the format, make sure it
	 *                                 matches the value passed to $format.
	 * @param  string $format          The date format.
	 * @param  string $zone            A PHP DateTimeZone-compatable timezone string.
	 * @return object                  A date time object.
	 */
	public function date_maker( $raw_date_string, $format = 'wordpress', $zone = 'UTC' ){
		if ( 'wordpress' == $format ){
			$format = 'Y-m-d H:i:s';
		}
		if ( 'now' == $raw_date_string ){
			$format = 'U';
			$raw_date_string = date('U');
		}
		$date_obj = DateTime::createFromFormat( $format, $raw_date_string, new DateTimeZone($zone) );
		return $date_obj;
		/**
		 * How to use this properly!
		 *
		 * Get the date object in whatever format you like!
		 * $date_obj->format('Y-m-d H:i:s');
		 *
		 * Set the date object's time zone like below and get the right time with the
		 * format function!
		 * Here's a list of timezones - http://php.net/manual/en/timezones.america.php
		 * $date_obj->setTimezone(new DateTimeZone('America/New_York'));
		 *
		 */
	}

	public function assemble_jats( $post_object ){
		$post_array = get_post($post_object, ARRAY_A);
		$zenodo_object = new Zenodo_Submit_Object($post_array);
		$categories = get_the_category($post_object->ID);
		if (empty($zenodo_object->description)){
			$excerpt = $post_array['post_excerpt'];
		} else {
			$excerpt = $zenodo_object->description;
		}
		$date_obj = $this->date_maker($post_array['post_date_gmt']);
		$jats_values = array(
			'DOI'	=>	$zenodo_object->doi,
			'categories'	=> $this->cats_to_cat_names($categories),
			'item_title'	=>	$zenodo_object->title,
			'authors'	=>	$this->creators_to_authors($zenodo_object->authors),
			'excerpt'	=> $excerpt,
			'the_content'	=> $post_array['post_content'],
			'day' => $date_obj->format('d'),
			'month' => $date_obj->format('m'),
			'year' => $date_obj->format('Y'),

		);

		return $jats_values;
	}

	public function get_the_jats( $post_object, $override_vars = array(), $use_mu = false ){
		if ( is_array( $post_object ) ){
			$post_vars = $post_object;
		} else {
			$post_vars = $this->assemble_jats( $post_object );
		}
		/**
		 * Define the array of defaults
		 */
		$defaults = array(
			'DOI' => '',
			'categories' => array(),
			'item_title' => "",
			'authors' => array(),
			'pub_type' => 'epub',
			'day' => '',
			'month' => '',
			'year' => '',
			'excerpt' => '',
			'the_content' => ''
		);

		// Clean out empties and false.
		//$override_vars = array_filter($override_vars);
		// Overrides gooo
		//$vars = array_merge($post_vars, $override_vars);
		/**
		 * Parse final set of vars an array and merge it with $defaults
		 */
		$vars = wp_parse_args( $post_vars, $defaults );
		//var_dump($this->get_view( 'JATS', $vars, $use_mu ));
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
			  //var_dump('Noooo ' .  $view_file); die();
			  return '';
		  }
		 // var_dump($view_file);
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
