<?php
/**
 * Metabox - Rich Snippet Tab
 *
 * @package    RankMath_Woocommerce
 * @subpackage RankMath_Woocommerce\RichSnippet
 */

use RankMath_Woocommerce\Helper;
use MyThemeShop\Helpers\WordPress;

$post_type = WordPress::get_post_type();

if ( ( class_exists( 'WooCommerce' ) && 'product' === $post_type ) || ( class_exists( 'Easy_Digital_Downloads' ) && 'download' === $post_type ) ) {

	$cmb->add_field([
		'id'      => 'rank_math_woocommerce_notice',
		'type'    => 'notice',
		'what'    => 'info',
		'content' => '<span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Rank Math automatically inserts additional Rich Snippet meta data for WooCommerce products. You can set the Rich Snippet Type to "None" to disable this feature and just use the default data added by WooCommerce.', 'rank-math' ),
	]);

	$cmb->add_field([
		'id'      => 'rank_math_rich_snippet',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Rich Snippet Type', 'rank-math' ),
		/* translators: link to title setting screen */
		'desc'    => wp_kses_post( __( 'Rich Snippets help you stand out in SERPs. <a href="https://rankmath.com/kb/rich-snippets/" target="_blank">Learn more</a>.', 'rank-math' ) ),
		'options' => [
			'off'     => esc_html__( 'None', 'rank-math' ),
			'product' => esc_html__( 'Product', 'rank-math' ),
		],
		'default' => 'product',
	]);

	return;
}
