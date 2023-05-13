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

		add_action( 'wp_ajax_uvcw_update_cart', array($this, 'ajax_update_cart') );
		add_action( 'wp_ajax_nopriv_uvcw_update_cart', array($this, 'ajax_update_cart') );
	}

	public function ajax_update_cart() {
		$cart = WC()->cart;

		if ( isset( $_POST['data']['item_key'] ) ) {
			$item_key = sanitize_text_field( $_POST['data']['item_key'] );
			$cart_item = $cart->get_cart_item( $item_key );

			//get the current item position in cart
			$item_position_in_cart = 0;
			$counter = 0;
			foreach ($cart->get_cart_contents() as $key => $itm) {
				if ( $key == $item_key ) {
					$item_position_in_cart = $counter;
					break;
				}

				$counter++;
			}

			$cart->remove_cart_item($item_key);

			// add to cart as new product
			$variation_attributes = array();
			foreach ($_POST['data'] as $key => $value) {
				if ( strpos($key, 'attribute_') !== false ) {
					$variation_attributes[sanitize_text_field($key)] = sanitize_text_field($value);
				}
			}

			$prod_id = sanitize_text_field($_POST['data']['product_id']);
			$quantity = sanitize_text_field($cart_item['quantity']);
			$variation_id = sanitize_text_field( $_POST['data']['variation_id'] );

			$cart->add_to_cart( $prod_id, (int)$quantity, $variation_id, $variation_attributes );


			// re order the cart to match the previous order
			$contents = WC()->cart->get_cart_contents();

			// Get the key of the last item
			$last_key = end( array_keys( $contents ) );

			// Get the value of the last item
			$last_value = array_pop($contents);

			// remove the last item

			// insert at specific position
			$new_item = array( $last_key => $last_value );
			$final_contents = array_slice( $contents, 0, $item_position_in_cart, true ) + $new_item + array_slice( $contents, $item_position_in_cart, count( $contents ) - $item_position_in_cart, true );

			WC()->cart->set_cart_contents($final_contents);

			WC()->cart->maybe_set_cart_cookies();
            WC()->cart->calculate_totals();


			wp_send_json( array('success' => true, 'quantity' => $quantity, 'contents' => WC()->cart->get_cart_contents()) );
		}

		wp_die();
	}

	// add edit button
	public function after_wc_template($template_name, $template_path, $located, $args) {
		if ( $template_name === 'cart/cart-item-data.php' && is_cart() ) {
			?> 
			<div class="uvcw-edit">
				<i class="dashicons dashicons-edit"></i>
				<?php echo esc_html__('Edit', 'update-variation-cart-woocommerce'); ?>
			</div>
			<?php
		}
	}


	public function variation_data_html($item_data, $cart_item) {
		if ( !is_admin(  ) && is_cart() ) {

			$product_id = $cart_item['product_id'];
			global $product, $post;
			$backup_prod = $product;
			$backup_post = $post;
			$post = get_post($product_id);
			$product = wc_get_product($product_id);
			?> 
			<input type="hidden" name="" class="uvcw-item-key" value="<?php echo esc_attr($cart_item['key']); ?>">
			<div class="uvcw-popup-source">
				<?php ob_start(); ?>
				<div class="uvcw-product-container quickshop-product-wrap product">
					<?php 
					$images = array(get_post_thumbnail_id( $product_id ));
					$gallery_images = $product->get_gallery_image_ids();
					if ( !is_array($gallery_images) ) {
						$gallery_images = array();
					}

					$images = array_unique(array_merge($images, $gallery_images));

					
					if ( count( $images ) ) {
						?> 
						<div class="uvcw-prod-images qs-product-images">
							<div class="uvcw-prod-images-wrapper">
								<div class="uvcw-slider-wrapper">
									<?php 
									foreach ($images as $attachment_id) {
										$url = wp_get_attachment_image_src( $attachment_id, 'full' );
										if ( is_array($url) ) {
											$url = $url[0];
										}
										?> 
										<a class="uvcw-single-prod-image" target="_blank" href="<?php echo esc_url( $url ); ?>">
											<img src="<?php echo esc_url($url); ?>" alt="">
										</a>
										<?php
									}
									?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					
					<div class="uvcw-prod-details qs-product-details">
						<h1 class="uvcw-prod-title">
							<a target="_blank" href="<?php echo esc_url( get_the_permalink( $product_id ) ); ?>">
								<?php echo esc_html($product->get_name()); ?>
							</a>
						</h1>
						<div class="uvcw-view-details">
							<a href="<?php echo esc_url(get_the_permalink( $product_id )); ?>" target="_blank">
								<?php echo esc_html__('VIEW DETAILS', 'update-variation-cart-woocommerce'); ?>
							</a>
						</div>
						<?php 

						if ( isset( $cart_item['variation'] ) ) {
							foreach ($cart_item['variation'] as $key => $value) {
								$_REQUEST[$key] = $value;
							}
						}

						?> 
						<div class="">
							<?php do_action( 'woocommerce_single_product_summary' ); ?>
						</div>
					</div>
				</div>
				<?php echo htmlspecialchars(ob_get_clean(), ENT_NOQUOTES); ?>
			</div>
			<?php
			$product = $backup_prod;
			$post = $backup_post;
		}
		return $item_data;
	}

	/**
	 * Enqueue public assets.
	 *
	 * @return void
	 */
	public function public_scripts() {
		wp_enqueue_style( 'update-variation-cart-woocommerce-public', UVCW_ASSETS_URL . 'dist/css/public.min.css', array(), UVCW_ASSETS_VERSION );
		wp_enqueue_script( 'sweetalert2', UVCW_ASSETS_URL . 'js/sweetalert2.min.js', array('jquery'), UVCW_ASSETS_VERSION, true );
		if ( function_exists('is_cart') && is_cart() ) {
			wp_enqueue_script( 'wc-add-to-cart' );
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'update-variation-cart-woocommerce-public', UVCW_ASSETS_URL . 'dist/js/public.min.js', array( 'jquery' ), UVCW_ASSETS_VERSION, true );
			wp_localize_script( 'update-variation-cart-woocommerce-public', 'uvcw', array(
				'update' => esc_html__('Update', 'update-variation-cart-woocommerce'),
				'ajaxurl' => admin_url('admin-ajax.php')
			) );
		}
	}

}
