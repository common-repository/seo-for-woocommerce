<?php
/**
 * The WordPress helpers.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\Helpers;

use RankMath_Woocommerce\Post;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress class.
 */
trait WordPress {

	/**
	 * Get admin url.
	 *
	 * @param  string $page Page id.
	 * @param  array  $args Pass arguments to query string.
	 * @return string
	 */
	public static function get_admin_url( $page = '', $args = [] ) {
		$page = $page ? 'rank-math-' . $page : 'rank-math-woocommerce';
		$args = wp_parse_args( $args, [ 'page' => $page ] );

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Check if plugin is network active
	 *
	 * @codeCoverageIgnore
	 *
	 * @return boolean
	 */
	public static function is_plugin_active_for_network() {
		if ( ! is_multisite() ) {
			return false;
		}

		// Makes sure the plugin is defined before trying to use it.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( ! is_plugin_active_for_network( plugin_basename( RANK_MATH_WOOCOMMERCE_FILE ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get taxonomies attached to a post type.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string  $post_type Post type to get taxonomy data for.
	 * @param string  $output    (Optional) Output type can be `names`, `objects`, `choices`.
	 * @param boolean $filter    (Optional) Whether to filter taxonomies.
	 *
	 * @return boolean|array
	 */
	public static function get_object_taxonomies( $post_type, $output = 'choices', $filter = true ) {

		if ( 'names' === $output ) {
			return get_object_taxonomies( $post_type );
		}

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		$taxonomies = self::filter_exclude_taxonomies( $taxonomies, $filter );

		if ( 'objects' === $output ) {
			return $taxonomies;
		}

		return empty( $taxonomies ) ? false : [ 'off' => esc_html__( 'None', 'seo-for-woocommerce' ) ] + wp_list_pluck( $taxonomies, 'label', 'name' );
	}

	/**
	 * Filter taxonomies using
	 *        `is_taxonomy_viewable` function
	 *        'rank_math_excluded_taxonomies' filter
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array|object $taxonomies Collection of taxonomies to filter.
	 * @param boolean      $filter     (Optional) Whether to filter taxonomies.
	 *
	 * @return array|object
	 */
	public static function filter_exclude_taxonomies( $taxonomies, $filter = true ) {
		$taxonomies = $filter ? array_filter( $taxonomies, [ __CLASS__, 'is_taxonomy_viewable' ] ) : $taxonomies;

		/**
		 * Filter: 'rank_math_excluded_taxonomies' - Allow changing the accessible taxonomies.
		 *
		 * @api array $taxonomies The public taxonomies.
		 */
		$taxonomies = apply_filters( 'rank_math/excluded_taxonomies', $taxonomies );

		return $taxonomies;
	}

	/**
	 * Determine whether a taxonomy is considered "viewable".
	 *
	 * For built-in taxonomies such as categories and tags, the 'public' value will be evaluated.
	 * For all others, the 'publicly_queryable' value will be used.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string|WP_Taxonomy $taxonomy Taxonomy name or object.
	 *
	 * @return bool
	 */
	public static function is_taxonomy_viewable( $taxonomy ) {
		if ( is_scalar( $taxonomy ) ) {
			$taxonomy = get_taxonomy( $taxonomy );
			if ( ! $taxonomy ) {
				return false;
			}
		}

		return $taxonomy->publicly_queryable || ( $taxonomy->_builtin && $taxonomy->public );
	}

	/**
	 * Get post meta value.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string  $key     Internal key of the value to get (without prefix).
	 * @param  integer $post_id Post ID of the post to get the value for.
	 * @return mixed
	 */
	public static function get_post_meta( $key, $post_id = 0 ) {
		return Post::get_meta( $key, $post_id );
	}

	/**
	 * Schedules a rewrite flush to happen.
	 *
	 * @codeCoverageIgnore
	 */
	public static function schedule_flush_rewrite() {
		update_option( 'rank_math_flush_rewrite', 1 );
	}
}
