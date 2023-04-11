<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 */
class Uvcw_Plugin {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'UVCW_VERSION' ) ) {
			$this->version = UVCW_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'update-variation-cart-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_hook_or_initialize();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

	}
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'update-variation-cart-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

	/**
	 * Include files.
	 *
	 * @return void
	 */
	private function load_dependencies() {

	}

	/**
	 * Defines hook or initializes any class.
	 *
	 * @return void
	 */
	public function define_hook_or_initialize() {

		//Admin enqueue script
		add_action( 'wp_enqueue_scripts', array( $this, 'public_scripts' ) );

		add_filter( 'woocommerce_get_item_data', array($this, 'variation_data_html'), 10, 2 );
	}


	public function variation_data_html($item_data, $cart_item) {
		// echo json_encode($item_data);
		return $item_data;
	}

	/**
	 * Enqueue public assets.
	 *
	 * @return void
	 */
	public function public_scripts() {
		if ( function_exists('is_cart') && is_cart() ) {
			wp_enqueue_script( 'sweetalert2', UVCW_ASSETS_URL . 'js/sweetalert2.min.js', array('jquery'), UVCW_ASSETS_VERSION, true );
			wp_enqueue_script( 'update-variation-cart-woocommerce-public', UVCW_ASSETS_URL . 'dist/js/public.min.js', array( 'jquery' ), UVCW_ASSETS_VERSION, true );
			wp_localize_script( 'update-variation-cart-woocommerce-public', 'uvcw', array(
				'edit' => esc_html__('Edit', 'update-variation-cart-woocommerce')
			) );
			wp_enqueue_style( 'update-variation-cart-woocommerce-public', UVCW_ASSETS_URL . 'dist/css/public.min.css', array(), UVCW_ASSETS_VERSION );
		}
	}

}
