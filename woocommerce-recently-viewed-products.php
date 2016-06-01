<?php
/*
* Plugin Name: WooCommerce Recently Viewed Products
* Description: Adds a section in your shop that displays a visitors recently viewed products.
* Version: 1.0.0
* Author: Creative Little Dots
* Author URI: http://creativelittledots.co.uk
*
* Text Domain: woocommerce-recently-viewed-products
* Domain Path: /languages/
*
* Requires at least: 3.8
* Tested up to: 4.1.1
*
* Copyright: © 2009-2015 Creative Little Dots
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! function_exists('is_woocommerce_active') ) {
	return;
}

// Check if WooCommerce is active
if ( ! is_woocommerce_active() ) {
	
	return;
	
}


class WC_Recently_Viewed {
	
	public $version 	= '1.0.0';
	
	public function __construct() {
		
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		
		// Add Recently Viewed Products to Shop Page
		add_action( 'woocommerce_after_main_content', array($this, 'woocommerce_recently_viewed_products') );
		
		// Tracking Product View
		add_action( 'wp', array($this, 'woocommerce_recently_viewed_products_track_view') );
		
		// Before Recently View Loop
		add_action('woocommerce_before_recently_viewed_products_loop', 'woocommerce_result_count', 20);
		add_action('woocommerce_before_recently_viewed_products_loop', 'woocommerce_catalog_ordering', 30);
		
		// After Recently Viewed Loop
		add_action('woocommerce_after_recently_viewed_products_loop', 'woocommerce_pagination');
		
	}
	
	public function init() {
		load_plugin_textdomain( 'woocommerce-recently-viewed', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	public function activate() {
		
		global $wpdb;

		$version = get_option( 'woocommerce_recently_viewed_products_version', false );
		
		if ( $version == false ) {
			
			add_option( 'woocommerce_recently_viewed_products_version', $this->version );

			// Update from previous versions

			// delete old option
			delete_option( 'woocommerce_composite_products_extension_active' );
				
		} elseif ( version_compare( $version, $this->version, '<' ) ) {

			update_option( 'woocommerce_recently_viewed_products_version', $this->version );
		}

	}
	
	/**
	 * Deactivate extension.
	 * @return void
	 */
	public function deactivate() {

		delete_option( 'woocommerce_recently_viewed_products_version' );
		
	}
	
	public function plugin_url() {
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}

	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function plugins_loaded() {
		
		global $woocomerce;	
		
	}
	
	public function woocommerce_recently_viewed_products() {
		
		global $woocommerce_recently_viewed_products, $current_user;
		
		$recently_viewed_products = array(0);
		
		if(is_user_logged_in()) {
			
			if($current_user->woocommerce_recently_viewed_products && is_array($current_user->woocommerce_recently_viewed_products)) {
				
				$recently_viewed_products = $current_user->woocommerce_recently_viewed_products;
				
			}
			
			elseif($recently_viewed_products_by_ip_address = $this->woocommerce_recently_viewed_products_get_by_ip_address($_SERVER['REMOTE_ADDR'])) {
				
				$recently_viewed_products = $recently_viewed_products_by_ip_address;
				
			}
			
		}
		
		elseif($recently_viewed_products_by_ip_address = $this->woocommerce_recently_viewed_products_get_by_ip_address($_SERVER['REMOTE_ADDR'])) {
			
			$recently_viewed_products = $recently_viewed_products_by_ip_address;
			
		}
		
		query_posts(apply_filters('woocommerce_recently_viewed_products_args', array(
		    'post_type' => 'product',
		    'post__in'	=> $recently_viewed_products,
		    'orderby' => 'post__in',
		)));
		
		$args = array();
	
		wc_get_template('content-recently-viewed-products.php', $args, false, $woocommerce_recently_viewed_products->plugin_path() . '/templates/');
		
	}
	
	public function woocommerce_recently_viewed_products_get_by_ip_address($ip_address) {
		
		return get_transient( 'woocommerce_recently_viewed_products_' . base64_encode($ip_address) );
		
	}
	
	public function woocommerce_recently_viewed_products_track_view() {
		
		if(is_singular('product')) {
			
			global $post;
			
			$recently_viewed_products = array();
			
			$product = new WC_Product($post->ID);
			
			if($product->is_visible()) {
				
				if(is_user_logged_in()) {
					
					global $current_user;
					
					$recently_viewed_products = $current_user->woocommerce_recently_viewed_products;
					
				}
				
				else {
					
					$recently_viewed_products = $this->woocommerce_recently_viewed_products_get_by_ip_address($_SERVER['REMOTE_ADDR']);
					
				}
				
				$recently_viewed_products = is_array($recently_viewed_products) && $recently_viewed_products ? $recently_viewed_products : array();
				
				array_unshift($recently_viewed_products, $product->id);
				
				$recently_viewed_products = array_unique($recently_viewed_products);
				
				if(is_user_logged_in()) {
					
					update_user_meta($current_user->ID, 'woocommerce_recently_viewed_products', $recently_viewed_products);
					
				}
				
				else {
					
					set_transient( 'woocommerce_recently_viewed_products_' . base64_encode($_SERVER['REMOTE_ADDR']), $recently_viewed_products, 12 * HOUR_IN_SECONDS );
					
				}
				
			}
			
		}
		
	}
	
}

$GLOBALS[ 'woocommerce_recently_viewed_products' ] = new WC_Recently_Viewed();