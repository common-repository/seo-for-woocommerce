<?php
/**
 * Outputs schema code specific for Google's JSON LD stuff
 *
 * @since      1.0.0
 * @package    RankMath_Woocommerce
 * @subpackage RankMath_Woocommerce\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\RichSnippet;

use RankMath_Woocommerce\Helper;
use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * JsonLD class.
 */
class JsonLD {

	use Hooker;

	/**
	 * Hold post object.
	 *
	 * @var WP_Post
	 */
	public $post = null;

	/**
	 * Hold post ID.
	 *
	 * @var ID
	 */
	public $post_id = 0;

	/**
	 * Hold post parts.
	 *
	 * @var array
	 */
	public $parts = [];

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'wp_head', 'json_ld' );
		$this->action( 'rank_math_woocommerce/json_ld', 'add_context_data' );
	}

	/**
	 * JSON LD output function that the functions for specific code can hook into.
	 */
	public function json_ld() {
		global $post;

		if ( is_singular() ) {
			$this->post    = $post;
			$this->post_id = $post->ID;
			$this->get_parts();
		}

		/**
		 * Collect data to output in JSON-LD.
		 *
		 * @param array  $unsigned An array of data to output in json-ld.
		 * @param JsonLD $unsigned JsonLD instance.
		 */
		$data = $this->do_filter( 'json_ld', [], $this );
		if ( is_array( $data ) && ! empty( $data ) ) {
			$this->credits();

			echo '<script type="application/ld+json">' . wp_json_encode( array_values( array_filter( $data ) ) ) . '</script>' . "\n";

			$this->credits( true );
		}
	}

	/**
	 * Get Default Schema Data.
	 *
	 * @param array $data Array of json-ld data.
	 *
	 * @return array
	 */
	public function add_context_data( $data ) {
		$is_product_page = $this->is_product_page();
		$snippets        = [
			'\\RankMath_Woocommerce\\RichSnippet\\Products_Page' => $is_product_page,
			'\\RankMath_Woocommerce\\RichSnippet\\Singular'      => is_singular(),
		];

		foreach ( $snippets as $class => $can_run ) {
			if ( $can_run ) {
				$class = new $class;
				$data  = $class->process( $data, $this );
			}
		}

		return $data;
	}

	/**
	 * Add property to entity.
	 *
	 * @param string $prop   Name of the property to add into entity.
	 * @param array  $entity Array of json-ld entity.
	 */
	public function add_prop( $prop, &$entity ) {
		if ( empty( $prop ) ) {
			return;
		}

		$perform = "add_prop_{$prop}";
		if ( method_exists( $this, $perform ) ) {
			$this->$perform( $entity );
		}
	}

	/**
	 * Get post thumbnail if any
	 *
	 * @param int $post_id  Post id to get featured image  for.
	 *
	 * @return array
	 */
	public function get_post_thumbnail( $post_id = 0 ) {
		if ( ! has_post_thumbnail( $post_id ) ) {
			return false;
		}

		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );

		return [
			'@type'  => 'ImageObject',
			'url'    => $image[0],
			'height' => $image[2],
			'width'  => $image[1],
		];
	}

	/**
	 * Get post title.
	 *
	 * @param  int $post_id Post ID to get title for.
	 * @return string
	 */
	public function get_post_title( $post_id = 0 ) {
		$title = Helper::get_post_meta( 'snippet_name', $post_id );
		return $title ? $title : get_the_title( $post_id );
	}

	/**
	 * Get product description.
	 *
	 * @param  int $post_id Post ID to get url for.
	 * @return string
	 */
	public function get_product_desc( $post_id = 0 ) {
		$product = wc_get_product( $post_id );
		if ( empty( $product ) ) {
			return;
		}

		$description = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
		return wp_strip_all_tags( do_shortcode( $description ), true );
	}

	/**
	 * Is product page.
	 *
	 * @return bool
	 */
	private function is_product_page() {
		return Conditional::is_woocommerce_active() && ( ( is_tax() && in_array( get_query_var( 'taxonomy' ), get_object_taxonomies( 'product' ), true ) ) || is_shop() );
	}

	/**
	 * Add property to entity.
	 *
	 * @param array $entity Array of json-ld entity.
	 */
	private function add_prop_thumbnail( &$entity ) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
		if ( ! empty( $image ) ) {
			$entity['image'] = [
				'@type'  => 'ImageObject',
				'url'    => $image[0],
				'width'  => $image[1],
				'height' => $image[2],
			];
		}
	}

	/**
	 * Get post parts.
	 */
	private function get_parts() {
		$parts = [
			'title'     => $this->get_post_title(),
			'published' => get_post_time( 'Y-m-d\TH:i:sP', true ),
			'desc'      => get_the_excerpt( $this->post ),
		];

		// Author.
		$parts['author'] = get_the_author_meta( 'display_name', $this->post->post_author );

		$this->parts = $parts;
	}

	/**
	 * Credits
	 *
	 * @param boolean $closing Is closing credits needed.
	 */
	private function credits( $closing = false ) {

		if ( $this->do_filter( 'frontend/remove_credit_notice', false ) ) {
			return;
		}

		if ( false === $closing ) {
			if ( ! Helper::is_whitelabel() ) {
				echo "\n<!-- " . esc_html__( 'SEO for Woocommerce by Rank Math - https://rankmath.com/', 'schema-markup' ) . " -->\n";
			}
			return;
		}

		if ( ! Helper::is_whitelabel() ) {
			echo '<!-- ' . esc_html__( 'SEO for Woocommerce plugin', 'schema-markup' ) . " -->\n\n";
		}
	}
}
