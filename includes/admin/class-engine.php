<?php
/**
 * The admin engine of the plugin.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\Admin;

use RankMath_Woocommerce\Helper;
use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;
use RankMath_Woocommerce\Search_Console\Search_Console;

defined( 'ABSPATH' ) || exit;

/**
 * Engine class.
 *
 * @codeCoverageIgnore
 */
class Engine {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		rank_math_woocommerce()->admin        = new Admin;
		rank_math_woocommerce()->admin_assets = new Assets;

		$runners = [
			rank_math_woocommerce()->admin,
			rank_math_woocommerce()->admin_assets,
			new Admin_Menu,
			new Option_Center,
			new Metabox,
			new CMB2_Fields,
			new Deactivate_Survey,
		];

		if ( ! Helper::is_addon_active( 'redirections' ) && ! Helper::is_addon_active( 'schema-markup' ) ) {
			$runners[] = new Import_Export;
		}

		foreach ( $runners as $runner ) {
			$runner->hooks();
		}

		/**
		 * Fires when admin is loaded.
		 */
		$this->do_action( 'admin/loaded' );
	}
}
