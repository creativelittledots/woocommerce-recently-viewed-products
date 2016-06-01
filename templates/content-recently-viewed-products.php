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

	<?php
		/**
		 * woocommerce_before_shop_loop hook
		 *
		 * @hooked woocommerce_result_count - 20
		 * @hooked woocommerce_catalog_ordering - 30
		 */
		do_action( 'woocommerce_before_recently_viewed_products_loop' );
	?>
	
	<h2 class="<?php echo implode(' ', apply_filters('woocommerce_before_recently_viewed_products_title_classes', array('title'))); ?>"><?php echo apply_filters('woocommerce_before_recently_viewed_products_title', __('Recently viewed by you', 'woocommerce-recently-viewed-products')); ?></h2>
	
	<?php woocommerce_product_loop_start(); ?>
	
	<?php while( have_posts() ) : the_post(); ?>
	
		<?php wc_get_template(apply_filters('woocommerce_recently_viewed_product_template', 'content-product.php')); ?>
	
	<?php endwhile; ?>
	
	<?php woocommerce_product_loop_end(); ?>

	<?php
		/**
		 * woocommerce_after_shop_loop hook
		 *
		 * @hooked woocommerce_pagination - 10
		 */
		do_action( 'woocommerce_after_recently_viewed_products_loop' );
	?>

<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) && apply_filters('woocommerce_after_recently_viewed_products_display_no_products_found', true) ) : ?>

	<?php wc_get_template( 'loop/no-products-found.php' ); ?>

<?php endif; wp_reset_query(); ?>