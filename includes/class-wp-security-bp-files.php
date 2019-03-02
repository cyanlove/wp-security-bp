<?php

/**
 * The file that defines the Files class
 *
 * This class handles all the files access to read and write.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    wp_security_bp
 * @subpackage wp_security_bp/includes
 */

/**
 * The Files class.
 *
 * This class handles all the files access to read and write.
 *
 * @since      1.0.0
 * @package    wp_security_bp
 * @subpackage wp_security_bp/includes
 * @author     Cyan Lovers <hello@cyanlove.com>
 */
class WP_Security_BP_Files {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * Short desc
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $request_uri    The URI where the request is made from
	 */
	protected $request_uri;

	/**
	 * Short desc
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $nonce_action_name    The action for the nonce creation
	 */
	protected $nonce_action_name;

	/**
	 * Short desc
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $wp_config    The name of the file wp-config.php
	 */
	protected $wp_config;

	/**
	 * Short desc
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $htaccess    The name of the file .htaccess
	 */
	protected $htaccess;

	/**
	 * Short desc
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      object    $wp_filesystem    The Object $wp_filesystem
	 */
	protected $wp_filesystem;

	/**
	 * Short desc
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $root    The uri of the installation root
	 */
	protected $root;

	/**
	 * Short desc
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $parent_root    The uri of the parent installation root
	 */
	protected $parent_root;

	/**
	 * Short desc
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $json    The array that will be passed as a JSON file to the admin
	 */
	protected $json;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $request_uri       The URI from the request is made.
	 */
	public function __construct( $plugin_name, $request_uri ) {

		$this->plugin_name = $plugin_name;
		$this->request_uri = $request_uri;
		$this->nonce_action_name = 'wp-security-bp-file-access';
		$this->wp_config = 'wp-config.php';
		$this->json = array(
			'status'   => 'fail',
			'message'  => '',
			'button'   => false,
			'uri'      => '',
		);

		$access_type = get_filesystem_method();
		if ( $access_type === 'direct' ) {

			// request credentials
			$creds = $this->request_credentials();

			// if user has no permission is asked for ftp credentials
			if ( false === $creds ) {
				return; // stop processing here
			}

			// if ftp credentials are not ok user is asked again for ftp credentials
			if ( false === WP_Filesystem( $creds ) ) {
				$this->request_credentials( true );
				return; // stop processing here
			}

			// call global $wp_filesystem variable
			global $wp_filesystem;
			$this->wp_filesystem = $wp_filesystem;

			// define root dir
			$this->root = $wp_filesystem->abspath();
			// define parent root dir
			$this->parent_root = trailingslashit( dirname( $this->root ) );

			// get the plugin directory path
			$plugin_path = trailingslashit( $this->wp_filesystem->wp_plugins_dir() . $this->plugin_name );

			// make a directory
			//$this->wp_filesystem->mkdir( $plugin_path . 'test-folder' );

			// make a file and write content
			/* $this->wp_filesystem->put_contents(
				$plugin_path . 'test-folder/test-file.txt',
				'Example contents of a file',
				FS_CHMOD_FILE // predefined mode settings for WP files
			); */
		
		}	
		else {
			/* don't have direct write access. Prompt user with our notice */
			echo "You don't have direct write access :(";
		}


	}

	/**
	 * Short desc
	 *
	 * Long desc
	 *
	 * @since    1.0.0
	 * @param    bool    $error       Optional. Defines if an error message should be displayed to the user or not.
	 * @access   private
	 */
	private function request_credentials( $error = false ) {

		$uri = wp_nonce_url( $this->request_uri, $this->nonce_action_name );
		return request_filesystem_credentials( $uri, '', $error, false, null );

	}

	/**
	 * Short desc
	 *
	 * Long desc
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function find_wp_config() {

		if ( $this->wp_filesystem->exists( $this->root . $this->wp_config ) ) {
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Short desc
	 *
	 * Long desc
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function check_wp_config() {

		$is_in_root = $this->find_wp_config();
		
		if ( $is_in_root ) {
			$this->json['message'] = __( 'Very bad guy', $this->plugin_name );
			$this->json['button'] = true;
			$this->json['uri'] = 'url-to-fix';
		}
		else {
			$this->json['status'] = 'passed';
			$this->json['message'] = __( 'Good job!!!', $this->plugin_name );
		}

		return json_encode($this->json);

	}

	/**
	 * Short desc
	 *
	 * Long desc
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function move_wp_config() {

		/*
		//$file = 'wp-config.php';
		$file = 'test-file.txt';

		$root = $wp_filesystem->abspath();
		$defaul_path = $wp_filesystem->wp_content_dir();
		if ( $wp_filesystem->exists( $root . $file ) ) {
			$parent_root = trailingslashit( dirname( $root ) );
			$wp_filesystem->move(
				$root . $file,
				$parent_root . $file,
				true // Overwrites if exists
			);
		}
		*/

	}

}
