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

		add_action( 'woocommerce_after_template_part', array($this, 'after_wc_template'), 10, 4 );
	}

	// add edit button
	public function after_wc_template($template_name, $template_path, $located, $args) {
		if ( $template_name === 'cart/cart-item-data.php' ) {
			?> 
			<div class="uvcw-edit">
				<i class="dashicons dashicons-edit"></i>
				<?php echo esc_html__('Edit', 'update-variation-cart-woocommerce'); ?>
			</div>
			<?php
		}
	}


	public function variation_data_html($item_data, $cart_item) {
		$product_id = $cart_item['product_id'];
		global $product, $post;
		$post = get_post($product_id);
		$product = wc_get_product($product_id);
		
		?> 
		<div class="uvcw-popup-source">
			<?php ob_start(); ?>
			<div class="uvcw-product-container product">
				<?php 
				$images = array(get_post_thumbnail_id( $product_id ));
				$gallery_images = $product->get_gallery_image_ids();
				if ( !is_array($gallery_images) ) {
					$gallery_images = array();
				}

				$images = array_unique(array_merge($images, $gallery_images));

				
				if ( count( $images ) ) {
					?> 
					<div class="uvcw-prod-images">
						<?php 
						foreach ($images as $attachment_id) {
							$url = wp_get_attachment_image_src( $attachment_id, 'full' );
							if ( is_array($url) ) {
								$url = $url[0];
							}
							?> 
							<a target="_blank" href="<?php echo esc_url( $url ); ?>">
								<img src="<?php echo esc_url($url); ?>" alt="">
							</a>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
				
				<div class="uvcw-prod-details">
					<h1 class="uvcw-prod-title">
						<a target="_blank" href="<?php echo esc_url( get_the_permalink( $product_id ) ); ?>">
							<?php echo esc_html($product->get_name()); ?>
						</a>
					</h1>
					<?php wc_get_template( 'single-product/price.php' ); ?>
					<?php do_action( 'woocommerce_' . $product->get_type() . '_add_to_cart' ); ?>
				</div>
			</div>
			<?php echo esc_html(ob_get_clean()); ?>
		</div>
		<?php
		return $item_data;
	}

	/**
	 * Enqueue public assets.
	 *
	 * @return void
	 */
	public function public_scripts() {
		if ( function_exists('is_cart') && is_cart() ) {
			wp_enqueue_script( 'wc-add-to-cart' );
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'sweetalert2', UVCW_ASSETS_URL . 'js/sweetalert2.min.js', array('jquery'), UVCW_ASSETS_VERSION, true );
			wp_enqueue_script( 'update-variation-cart-woocommerce-public', UVCW_ASSETS_URL . 'dist/js/public.min.js', array( 'jquery' ), UVCW_ASSETS_VERSION, true );
			wp_enqueue_style( 'update-variation-cart-woocommerce-public', UVCW_ASSETS_URL . 'dist/css/public.min.css', array(), UVCW_ASSETS_VERSION );
		}
	}

}