<?php
/**
 * The option center of the plugin.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\Admin;

use RankMath_Woocommerce\CMB2;
use RankMath_Woocommerce\Helper;
use RankMath_Woocommerce\Runner;
use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Option_Center class.
 */
class Option_Center implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		if ( ! Helper::is_addon_active( '404-monitor' ) ) {
			$this->action( 'init', 'register_general_settings', 125 );
		}

		// Check for fields and act accordingly.
		$this->action( 'cmb2_save_options-page_fields_rank-math-options-general_options', 'check_updated_fields', 25, 2 );
	}

	/**
	 * General Settings.
	 */
	public function register_general_settings() {
		/**
		 * Allow developers to add new section into general setting option panel.
		 *
		 * @param array $tabs
		 */
		$tabs = apply_filters( 'rank_math/settings/general', [] );
		new Options([
			'key'        => 'rank-math-options-general',
			'title'      => esc_html__( 'Rank Math', 'seo-for-woocommerce' ),
			'menu_title' => esc_html__( 'General Settings', 'seo-for-woocommerce' ),
			'folder'     => 'general',
			'tabs'       => $tabs,
		]);
	}

	/**
	 * Check if certain fields got updated.
	 *
	 * @param int   $object_id The ID of the current object.
	 * @param array $updated   Array of field ids that were updated.
	 *                         Will only include field ids that had values change.
	 */
	public function check_updated_fields( $object_id, $updated ) {

		/**
		 * Filter: Allow developers to add option fields which will flush the rewrite rules when updated.
		 *
		 * @param array $flush_fields Array of field IDs for which we need to flush.
		 */
		$flush_fields = $this->do_filter(
			'flush_fields',
			[]
		);

		foreach ( $flush_fields as $field_id ) {
			if ( in_array( $field_id, $updated, true ) ) {
				Helper::schedule_flush_rewrite();
				break;
			}
		}
	}
}
