<?php
/**
 * The WooCommerce Module
 *
 * @since      1.0
 * @package    RankMath_Woocommerce
 * @subpackage RankMath_Woocommerce\WooCommerce
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\WooCommerce;

use RankMath_Woocommerce\Helper;
use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce class.
 */
class WooCommerce {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			new Admin;
		}

		// Permalink Manager.
		if ( ! is_admin() ) {
			if (
				Helper::get_settings( 'general.wc_remove_product_base' ) ||
				Helper::get_settings( 'general.wc_remove_category_base' ) ||
				Helper::get_settings( 'general.wc_remove_category_parent_slugs' )
			) {
				$this->action( 'request', 'request' );
			}

			if ( Helper::get_settings( 'general.wc_remove_generator' ) ) {
				remove_action( 'get_the_generator_html', 'wc_generator_tag', 10 );
				remove_action( 'get_the_generator_xhtml', 'wc_generator_tag', 10 );
			}
		}

		if ( Helper::get_settings( 'general.wc_remove_product_base' ) ) {
			$this->filter( 'post_type_link', 'product_post_type_link', 1, 2 );
		}
		if ( Helper::get_settings( 'general.wc_remove_category_base' ) || Helper::get_settings( 'general.wc_remove_category_parent_slugs' ) ) {
			$this->filter( 'term_link', 'product_term_link', 1, 3 );
		}

		$this->filter( 'rank_math/active_modules', 'set_module_active' );

		if ( ! Helper::is_addon_active( 'schema-markup' ) ) {
			new \RankMath_Woocommerce\RichSnippet\RichSnippet;
		}
	}

	/**
	 * Replace request if product found.
	 *
	 * @param  array $request Current request.
	 * @return array
	 */
	public function request( $request ) {
		global $wp, $wpdb;
		$url = $wp->request;

		if ( ! empty( $url ) ) {
			$replace = [];
			$url     = explode( '/', $url );
			$slug    = array_pop( $url );

			if ( 'feed' === $slug ) {
				$replace['feed'] = $slug;
				$slug            = array_pop( $url );
			}

			if ( 'amp' === $slug ) {
				$replace['amp'] = $slug;
				$slug           = array_pop( $url );
			}

			if ( 0 === strpos( $slug, 'comment-page-' ) ) {
				$replace['cpage'] = substr( $slug, strlen( 'comment-page-' ) );
				$slug             = array_pop( $url );
			}

			$query = "SELECT COUNT(ID) as count_id FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s";
			$num   = intval( $wpdb->get_var( $wpdb->prepare( $query, [ $slug, 'product' ] ) ) ); // phpcs:ignore
			if ( $num > 0 ) {
				$replace['page']      = '';
				$replace['name']      = $slug;
				$replace['product']   = $slug;
				$replace['post_type'] = 'product';

				return $replace;
			}
		}

		return $request;
	}

	/**
	 * Replace product permalink according to settings.
	 *
	 * @param  string  $permalink The existing permalink URL.
	 * @param  WP_Post $post WP_Post object.
	 * @return string
	 */
	public function product_post_type_link( $permalink, $post ) {
		if ( 'product' !== $post->post_type ) {
			return $permalink;
		}

		if ( ! get_option( 'permalink_structure' ) ) {
			return $permalink;
		}

		$permalink_structure = wc_get_permalink_structure();
		$product_base        = $permalink_structure['product_rewrite_slug'];
		$product_base        = explode( '/', ltrim( $product_base, '/' ) );

		$link = $permalink;
		foreach ( $product_base as $remove ) {
			if ( '%product_cat%' === $remove ) {
				continue;
			}
			$link = preg_replace( "#{$remove}/#i", '', $link, 1 );
		}

		return $link;
	}

	/**
	 * Replace category permalink according to settings.
	 *
	 * @param  string $link     Term link URL.
	 * @param  object $term     Term object.
	 * @param  string $taxonomy Taxonomy slug.
	 * @return string
	 */
	public function product_term_link( $link, $term, $taxonomy ) {
		if ( 'product_cat' !== $taxonomy ) {
			return $link;
		}

		if ( ! get_option( 'permalink_structure' ) ) {
			return $link;
		}

		$permalink_structure  = wc_get_permalink_structure();
		$category_base        = trailingslashit( $permalink_structure['category_rewrite_slug'] );
		$remove_category_base = Helper::get_settings( 'general.wc_remove_category_base' );
		$remove_parent_slugs  = Helper::get_settings( 'general.wc_remove_category_parent_slugs' );
		$is_language_switcher = ( class_exists( 'Sitepress' ) && strpos( $original_link, 'lang=' ) );

		if ( $remove_category_base ) {
			$link          = str_replace( $category_base, '', $link );
			$category_base = '';
		}

		if ( $remove_parent_slugs && ! $is_language_switcher ) {
			$link = home_url( trailingslashit( $category_base . $term->slug ) );
		}

		return $link;
	}

	/**
	 * Add woocommerce to the list of active modules.
	 *
	 * @param  array $modules List of active modules.
	 * @return array $modules List of active modules.
	 */
	public function set_module_active( $modules ) {
		array_push( $modules, 'woocommerce' );

		return $modules;
	}

	/**
	 * Returns the product object when the current page is the product page.
	 *
	 * @return null|WC_Product
	 */
	protected function get_product() {
		$product_id = Param::get( 'post', get_queried_object_id(), FILTER_VALIDATE_INT );
		if ( ! $product_id && ( ! is_singular( 'product' ) || ! function_exists( 'wc_get_product' ) ) ) {
			return null;
		}
		return wc_get_product( $product_id );
	}

	/**
	 * Retrieves the product brand.
	 *
	 * @return string
	 */
	public function get_product_var_brand() {
		$product = $this->get_product();
		if ( ! is_object( $product ) ) {
			return '';
		}

		$brands = $this->get_brands( $product->get_id() );
		if ( ! empty( $brands ) ) {
			return $brands[0]->name;
		}

		return '';
	}

	/**
	 * Returns the array of brand taxonomy.
	 *
	 * @param  int $product_id The id to get the product brands for.
	 * @return bool|array
	 */
	protected function get_brands( $product_id ) {
		$taxonomy = Helper::get_settings( 'general.product_brand' );
		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$brands = wp_get_post_terms( $product_id, $taxonomy );
		return empty( $brands ) || is_wp_error( $brands ) ? false : $brands;
	}
}
