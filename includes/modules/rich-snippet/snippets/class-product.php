<?php
/**
 * The Product Class
 *
 * @since      1.0.0
 * @package    RankMath_Woocommerce
 * @subpackage RankMath_Woocommerce\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\RichSnippet;

use RankMath_Woocommerce\Helper;
use MyThemeShop\Helpers\Conditional;
use RankMath_Woocommerce\RichSnippet\Product_Edd;
use RankMath_Woocommerce\RichSnippet\Product_WooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Product class.
 */
class Product implements Snippet {

	/**
	 * Hold JsonLD Instance.
	 *
	 * @var JsonLD
	 */
	private $json = '';

	/**
	 * Product rich snippet.
	 *
	 * @param array  $data   Array of json-ld data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$this->json = $jsonld;
		$sku        = Helper::get_post_meta( 'snippet_product_sku' );
		$price      = Helper::get_post_meta( 'snippet_product_price' );
		$entity     = [
			'@context'    => 'https://schema.org/',
			'@type'       => 'Product',
			'sku'         => $sku ? $sku : '',
			'name'        => $jsonld->parts['title'],
			'description' => $jsonld->parts['desc'],
			'releaseDate' => $jsonld->parts['published'],
		];

		if ( Conditional::is_woocommerce_active() && is_product() ) {
			remove_action( 'wp_footer', [ WC()->structured_data, 'output_structured_data' ], 10 );
			remove_action( 'woocommerce_email_order_details', [ WC()->structured_data, 'output_email_structured_data' ], 30 );
			$product = new Product_WooCommerce;
			unset( $entity['offers'] );
			$product->set_product( $entity, $jsonld );
		}

		return $entity;
	}

	/**
	 * Get seller
	 *
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public static function get_seller( $jsonld ) {
		$site_url = site_url();
		$seller   = [
			'@type' => 'Organization',
			'@id'   => $site_url . '/',
			'name'  => get_bloginfo( 'name' ),
			'url'   => $site_url,
		];

		return $seller;
	}

	/**
	 * Set product categories.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $taxonomy   Taxonomy.
	 */
	public static function get_category( $product_id, $taxonomy ) {
		$categories = get_the_terms( $product_id, $taxonomy );
		if ( is_wp_error( $categories ) || empty( $categories ) ) {
			return;
		}

		if ( 0 === $categories[0]->parent ) {
			return $categories[0]->name;
		}

		$ancestors = get_ancestors( $categories[0]->term_id, $taxonomy );
		foreach ( $ancestors as $parent ) {
			$term       = get_term( $parent, $taxonomy );
			$category[] = $term->name;
		}
		$category[] = $categories[0]->name;

		return join( ' > ', $category );
	}
}
