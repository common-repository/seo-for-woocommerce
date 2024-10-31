<?php
/**
 * WooCommerce general settings.
 *
 * @package    RankMath_Woocommerce
 * @subpackage RankMath_Woocommerce\WooCommerce
 */

use RankMath_Woocommerce\Helper;

$cmb->add_field( [
	'id'      => 'wc_remove_product_base',
	'type'    => 'switch',
	'name'    => esc_html__( 'Remove base', 'seo-for-woocommerce' ),
	'desc'    => esc_html__( 'Remove prefix from product URL.', 'seo-for-woocommerce' ) .
		'<br><code>' . esc_html__( 'default: /shop/accessories/action-figures/acme/ - changed: /accessories/action-figures/acme/', 'seo-for-woocommerce' ) . '</code>',
	'default' => 'off',
] );

$cmb->add_field( [
	'id'      => 'wc_remove_category_base',
	'type'    => 'switch',
	'name'    => esc_html__( 'Remove category base', 'seo-for-woocommerce' ),
	'desc'    => esc_html__( 'Remove prefix from category URL.', 'seo-for-woocommerce' ) .
		'<br><code>' . esc_html__( 'default: /product-category/accessories/action-figures/ - changed: /accessories/action-figures/', 'seo-for-woocommerce' ) . '</code>',
	'default' => 'off',
] );

$cmb->add_field( [
	'id'      => 'wc_remove_category_parent_slugs',
	'type'    => 'switch',
	'name'    => esc_html__( ' Remove parent slugs', 'seo-for-woocommerce' ),
	'desc'    => esc_html__( 'Remove parent slugs from category URL.', 'seo-for-woocommerce' ) .
		'<br><code>' . esc_html__( 'default: /product-category/accessories/action-figures/ - changed: /product-category/action-figures/', 'seo-for-woocommerce' ) . '</code>',
	'default' => 'off',
] );

$cmb->add_field( [
	'id'      => 'wc_remove_generator',
	'type'    => 'switch',
	'name'    => esc_html__( 'Remove Generator Tag', 'seo-for-woocommerce' ),
	'desc'    => esc_html__( 'Remove WooCommerce generator tag from the source code.', 'seo-for-woocommerce' ),
	'default' => 'on',
] );

$cmb->add_field( [
	'id'      => 'remove_shop_snippet_data',
	'type'    => 'switch',
	'name'    => esc_html__( 'Remove Snippet Data', 'seo-for-woocommerce' ),
	'desc'    => esc_html__( 'Remove Snippet Data from WooCommerce Archive page.', 'seo-for-woocommerce' ),
	'default' => 'on',
] );

$cmb->add_field( [
	'id'      => 'product_brand',
	'type'    => 'select',
	'name'    => esc_html__( 'Brand', 'seo-for-woocommerce' ),
	'desc'    => esc_html__( 'Select Product Brand Taxonomy to use in Schema.org & OpenGraph markup.', 'seo-for-woocommerce' ),
	'options' => Helper::get_object_taxonomies( 'product', 'choices', false ),
] );
