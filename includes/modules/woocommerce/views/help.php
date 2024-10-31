<?php
/**
 * WooCommerce general settings.
 *
 * @package    RankMath_Woocommerce
 * @subpackage RankMath_Woocommerce\WooCommerce
 */

use RankMath_Woocommerce\Helper;
?>

<h3><?php esc_html_e( 'WooCommerce', 'seo-for-woocommerce' ); ?></h3>

<p><?php esc_html_e( 'SEO is the backbone of any website and it couldn\'t be more true for a WooCommerce store.', 'seo-for-woocommerce' ); ?></p>

<p><?php esc_html_e( 'When you sell something online, you want people to buy it. And, SEO is the best way to do so in the long run.', 'seo-for-woocommerce' ); ?></p>

<p><?php esc_html_e( 'With the Rank Math SEO plugin, you can easily optimize your WooCommerce store in general and product pages in particular.', 'seo-for-woocommerce' ); ?></p>

<p><img src="<?php echo esc_url( rank_math_woocommerce()->plugin_url() . 'assets/admin/img/help/product-archive-settings.jpg' ); ?>" alt="make categories noindex" /></p>

<p><strong><?php esc_html_e( 'Optimizing Your Product Pages', 'seo-for-woocommerce' ); ?></strong></p>

<p>
	<?php
	printf(
		/* translators: link to local seo settings */
		__( 'You can customize and automate the SEO Title/Description generation easily as well. Just head over to <a href="%1$s">WordPress Dashboard > Rank Math > Titles & Meta > Products</a>', 'seo-for-woocommerce' ),
		Helper::get_admin_url( 'options-titles#setting-panel-post-type-product' )
	);
	?>
</p>

<p><img src="<?php echo esc_url( rank_math_woocommerce()->plugin_url() . 'assets/admin/img/help/individual-product-settings.jpg' ); ?>" alt="product seo title" /></p>

<p><?php esc_html_e( 'You can also add rich snippets to your product pages easily with Rank Math, apart from doing the regular SEO like you would do on posts.', 'seo-for-woocommerce' ); ?></p>

<p>
	<?php
	printf(
		/* translators: link to local seo settings */
		__( 'Do that from the product pages themeselve. Go to <a href="%1$s">WordPress Dashboard > Products > Add New</a>', 'seo-for-woocommerce' ),
		admin_url( 'post-new.php?post_type=product' )
	);
	?>
</p>

<p><?php esc_html_e( 'And, choose the product schema from the Rich Snippets tab.', 'seo-for-woocommerce' ); ?></p>

<p><img src="<?php echo esc_url( rank_math_woocommerce()->plugin_url() . 'assets/admin/img/help/product-rich-snippets.jpg' ); ?>" alt="product rich snippets" /></p>

<p><strong><?php esc_html_e( 'Optimizing Your Product URLs', 'seo-for-woocommerce' ); ?></strong></p>

<p><?php esc_html_e( 'Rank Math offers you to remove category base from your product archive URLs so the URLs are cleaner, more SEO friendly and easier to remember.', 'seo-for-woocommerce' ); ?></p>

<p>
	<?php
	printf(
		/* translators: link to local seo settings */
		__( 'To access those options, head over to <a href="%1$s">WordPress Dashboard > Rank Math > General Settings > WooCommerce</a>.', 'seo-for-woocommerce' ),
		Helper::get_admin_url( 'options-general#setting-panel-woocommerce' )
	);
	?>
</p>

<p><img src="<?php echo esc_url( rank_math_woocommerce()->plugin_url() . 'assets/admin/img/help/woocommerce-url-settings.jpg' ); ?>" alt="product category base" /></p>
