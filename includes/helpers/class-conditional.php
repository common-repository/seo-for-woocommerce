<?php
/**
 * The Conditional helpers.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\Helpers;

use RankMath_Woocommerce\Helper;
use RankMath_Woocommerce\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Conditional class.
 */
trait Conditional {

	/**
	 * Check if whitelabel filter is active.
	 *
	 * @return boolean
	 */
	public static function is_whitelabel() {
		return apply_filters( 'rank_math/whitelabel', false );
	}

	/**
	 * Checks if the WP-REST-API is available.
	 *
	 * @param  string $minimum_version The minimum version the API should be.
	 * @return bool Returns true if the API is available.
	 */
	public static function is_api_available( $minimum_version = '2.0' ) {
		return ( defined( 'REST_API_VERSION' ) && version_compare( REST_API_VERSION, $minimum_version, '>=' ) );
	}

	/**
	 * Check if any of the Rank Math addon is active.
	 *
	 * @param  string $addon Addon name.
	 *
	 * @return bool Returns true if the plugin is active.
	 */
	public static function is_addon_active( $addon = '' ) {
		$addons = [
			'404-monitor'   => defined( 'RANK_MATH_MONITOR_FILE' ),
			'redirections'  => defined( 'RANK_MATH_REDIRECTIONS_FILE' ),
			'schema-markup' => defined( 'RANKMATH_SCHEMA_FILE' ),
		];

		return ! empty( $addons[ $addon ] );
	}

	/**
	 * Function to get the list of active modules.
	 *
	 * @return array Active modules.
	 */
	public static function get_active_modules() {
		return apply_filters( 'rank_math/active_modules', [] );
	}
}
