<?php
/**
 * The Rich Snippet Module
 *
 * @since      1.0.0
 * @package    RankMath_Woocommerce
 * @subpackage RankMath_Woocommerce\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\RichSnippet;

use RankMath_Woocommerce\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * RichSnippet class.
 */
class RichSnippet {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			new Admin;
		}
		$this->action( 'wp', 'integrations' );
	}

	/**
	 * Initialize integrations.
	 */
	public function integrations() {
		$type = get_query_var( 'sitemap' );
		if ( ! empty( $type ) ) {
			return;
		}

		new JsonLD;
	}
}
