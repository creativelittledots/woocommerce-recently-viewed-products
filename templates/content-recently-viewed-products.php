<?php

/**
 * Recently viewed products template.
 *
 * @version 	1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $woocommerce_recently_viewed_products;

?>

<?php if( have_posts() ) : ?>

	<?php do_action( 'woocommerce_before_recently_viewed_products_loop' ); ?>
	
	<h2 class="title"><?php echo apply_filters('woocommerce_before_recently_viewed_products_title', __('Recently viewed by you', 'woocommerce-recently-viewed-products')); ?></h2>
	
	<?php woocommerce_product_loop_start(); ?>
	
	<?php while( have_posts() ) : the_post(); ?>
	
		<?php wc_get_template( 'content-recently-viewed-product.php', array(), $woocommerce_recently_viewed_products->plugin_path() . '/templates/' ); ?>
	
	<?php endwhile; ?>
	
	<?php woocommerce_product_loop_end(); ?>

	<?php do_action( 'woocommerce_after_recently_viewed_products_loop' ); ?>

<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) && apply_filters('woocommerce_after_recently_viewed_products_display_no_products_found', true) ) : ?>

	<?php wc_get_template( 'loop/no-products-found.php' ); ?>

<?php endif; wp_reset_query(); ?>