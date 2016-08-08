<?php

/*
Plugin Name: Polylang Sync only some fields
Plugin URI: https://github.com/mestrona/polylang-acf-sync-some-fields
Description: For your custom fields, choose whether or not to "Sync Between Languages"
Author: Alexander Menk
Author URI: https://github.com/amenk
Version: 1.0.0
License: GPLv3
*/

if ( ! class_exists( 'PolylangSyncSomeFields' ) ) :
class PolylangSyncSomeFields {
	/**
	 *	Holding the singleton instance
	 */
	private static $_instance = null;

	/**
	 */
	public static function instance(){
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 *	Prevent from creating more instances
	 */
	private function __clone() { }

	/**
	 *	Prevent from creating more than one instance
	 */
	private function __construct() {
		add_action( 'plugins_loaded' , array( &$this , 'plugins_loaded') );
		// add_option( 'polylang_clone_attachments' , true );
        add_action('pre_get_posts', array(&$this, 'fetch_english_language'), 2);
    }

    /**
     * Always fetch english field groups. We consider them global.
     *
     * @param $query
     */
    public function fetch_english_language(WP_Query $query ) {
        if ($query->get('post_type') == 'acf') {
            $query->set('lang', 'en');
            $query->set('tax_query', false);
        }
    }

	/**
	 *	Setup plugin
	 *	
	 *	@action 'plugins_loaded'
	 */
	function plugins_loaded() {
		// load_plugin_textdomain( 'polylang-fix-relationships' , false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		if ( is_admin() && class_exists( 'Polylang' ) && function_exists( 'PLL' ) ) {
			PolylangSyncSomeFieldsWatch::instance();
		}
	}
}
endif;


/**
 * Autoload Classes
 *
 * @param string $classname
 */
function polylang_sync_some_fields_autoload( $classname ) {
	$class_path = dirname(__FILE__). sprintf('/include/class-%s.php' , $classname ) ; 
	if ( file_exists($class_path) )
		require_once $class_path;
}
spl_autoload_register( 'polylang_sync_some_fields_autoload' );

// init plugin
PolylangSyncSomeFields::instance();