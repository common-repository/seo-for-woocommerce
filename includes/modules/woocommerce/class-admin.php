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
use RankMath_Woocommerce\Module;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Str;
use RankMath_Woocommerce\OpenGraph_Image;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends Module {

	/**
	 * Hold product categories.
	 *
	 * @var array
	 */
	private $categories;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		$directory = dirname( __FILE__ );
		$this->config([
			'id'        => 'woocommerce',
			'directory' => $directory,
		]);
		parent::__construct();

		// Permalink Manager.
		$this->filter( 'rank_math/settings/general', 'add_general_settings' );
		$this->filter( 'rank_math_woocommerce/flush_fields', 'flush_fields' );
		$this->filter( 'rewrite_rules_array', 'add_rewrite_rules', 99 );
		$this->filter( 'rank_math/help/tabs', 'help_tabs' );
	}

	/**
	 * Add module settings into general optional panel.
	 *
	 * @param array $tabs Array of option panel tabs.
	 * @return array
	 */
	public function add_general_settings( $tabs ) {
		Arr::insert( $tabs, [
			'woocommerce' => [
				'icon'  => 'dashicons dashicons-cart',
				'title' => esc_html__( 'WooCommerce', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'Choose how you want Rank Math to handle your WooCommerce SEO. These options help you create cleaner, SEO friendly URLs, and optimize your WooCommerce product pages.', 'seo-for-woocommerce' ),
				'file'  => $this->directory . '/views/options-general.php',
			],
		], 7 );
		return $tabs;
	}

	/**
	 * Fields after updation of which we need to flush rewrite rules.
	 *
	 * @param  array $fields Fields to flush rewrite rules on.
	 * @return array
	 */
	public function flush_fields( $fields ) {
		$fields = [
			'wc_remove_product_base',
			'wc_remove_category_base',
			'wc_remove_category_parent_slugs',
		];
		return $fields;
	}

	/**
	 * Add rewrite rules for wp.
	 *
	 * @param array $rules The compiled array of rewrite rules.
	 * @return array
	 */
	public function add_rewrite_rules( $rules ) {
		global $wp_rewrite;

		wp_cache_flush();

		$permalink_structure  = wc_get_permalink_structure();
		$remove_product_base  = Helper::get_settings( 'general.wc_remove_product_base' );
		$remove_category_base = Helper::get_settings( 'general.wc_remove_category_base' );
		$remove_parent_slugs  = Helper::get_settings( 'general.wc_remove_category_parent_slugs' );

		$category_base   = $remove_category_base ? '' : $permalink_structure['category_rewrite_slug'];
		$use_parent_slug = Str::contains( '%product_cat%', $permalink_structure['product_rewrite_slug'] );

		$product_rules  = [];
		$category_rules = [];
		foreach ( $this->get_categories() as $category ) {
			$category_slug = $remove_parent_slugs ? $category['slug'] : $this->get_category_fullpath( $category );

			$category_rules[ $category_base . $category_slug . '/?$' ]                                    = 'index.php?product_cat=' . $category['slug'];
			$category_rules[ $category_base . $category_slug . '/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?product_cat=' . $category['slug'] . '&feed=$matches[1]';
			$category_rules[ $category_base . $category_slug . '/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$' ] = 'index.php?product_cat=' . $category['slug'] . '&paged=$matches[1]';

			if ( $remove_product_base && $use_parent_slug ) {
				$product_rules[ $category_slug . '/([^/]+)/?$' ] = 'index.php?product=$matches[1]';
				$product_rules[ $category_slug . '/([^/]+)/' . $wp_rewrite->comments_pagination_base . '-([0-9]{1,})/?$' ] = 'index.php?product=$matches[1]&cpage=$matches[2]';
			}
		}

		$rules = empty( $rules ) ? [] : $rules;
		return $category_rules + $product_rules + $rules;
	}

	/**
	 * Add help tab into help page.
	 *
	 * @param array $tabs Array of tabs.
	 * @return array
	 */
	public function help_tabs( $tabs ) {
		$tabs['woocommerce'] = [
			'title' => esc_html__( 'Woocommerce', 'seo-for-woocommerce' ),
			'view'  => dirname( __FILE__ ) . '/views/help.php',
		];

		return $tabs;
	}

	/**
	 * Returns categories array.
	 *
	 * ['category id' => ['slug' => 'category slug', 'parent' => 'parent category id']]
	 *
	 * @return array
	 */
	protected function get_categories() {
		if ( is_null( $this->categories ) ) {
			$categories = get_categories( [
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			]);

			$slugs = [];
			foreach ( $categories as $category ) {
				$slugs[ $category->term_id ] = [
					'parent' => $category->parent,
					'slug'   => $category->slug,
				];
			}

			$this->categories = $slugs;
		}

		return $this->categories;
	}

	/**
	 * Recursively builds category full path.
	 *
	 * @param object $category Term object.
	 * @return string
	 */
	protected function get_category_fullpath( $category ) {
		$categories = $this->get_categories();
		$parent     = $category['parent'];

		if ( $parent > 0 && array_key_exists( $parent, $categories ) ) {
			return $this->get_category_fullpath( $categories[ $parent ] ) . '/' . $category['slug'];
		}

		return $category['slug'];
	}
}
