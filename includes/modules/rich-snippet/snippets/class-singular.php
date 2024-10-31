<?php
/**
 * The Singular Class
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\RichSnippet;

use RankMath_Woocommerce\Helper;
use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Singular class.
 */
class Singular implements Snippet {

	use Hooker;

	/**
	 * Generate rich snippet.
	 *
	 * @param array  $data   Array of json-ld data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$schema = $this->can_add_schema( $jsonld );
		if ( false === $schema ) {
			return $data;
		}

		$hook = 'snippet/rich_snippet_' . $schema;
		/**
		 * Short-circuit if 3rd party is interested generating his own data.
		 */
		$pre = $this->do_filter( $hook, false, $jsonld->parts, $data );
		if ( false !== $pre ) {
			$data['richSnippet'] = $this->do_filter( $hook . '_entity', $pre );
			return $data;
		}

		$object = $this->get_schema_class( $schema );
		if ( false === $object ) {
			return $data;
		}

		$entity = $object->process( $data, $jsonld );

		// Images.
		$jsonld->add_prop( 'thumbnail', $entity );
		$data['richSnippet'] = $this->do_filter( $hook . '_entity', $entity );

		return $data;
	}

	/**
	 * Can add schema.
	 *
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return boolean|string
	 */
	private function can_add_schema( $jsonld ) {
		return Helper::get_post_meta( 'rich_snippet' );
	}

	/**
	 * Get Schema Class.
	 *
	 * @param string $schema Schema type.
	 * @return bool|Class
	 */
	private function get_schema_class( $schema ) {
		$data = [
			'product' => '\\RankMath_Woocommerce\\RichSnippet\\Product',
		];

		if ( isset( $data[ $schema ] ) && class_exists( $data[ $schema ] ) ) {
			return new $data[ $schema ];
		}

		return false;
	}
}
