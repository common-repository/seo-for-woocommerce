<?php
/**
 * The metabox functionality of the plugin.
 *
 * @since      1.0.0
 * @package    RankMath_Woocommerce
 * @subpackage RankMath_Woocommerce\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\Admin;

use CMB2_hookup;
use RankMath_Woocommerce\CMB2;
use RankMath_Woocommerce\Runner;
use RankMath_Woocommerce\Traits\Hooker;
use RankMath_Woocommerce\Helper;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Metabox class.
 */
class Metabox implements Runner {

	use Hooker;

	/**
	 * Metabox id.
	 *
	 * @var string
	 */
	private $metabox_id = 'rank_math_metabox';

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_enqueue_scripts', 'enqueue' );
		$this->action( 'cmb2_admin_init', 'add_main_metabox', 30 );
		$this->action( 'cmb2_' . CMB2::current_object_type() . '_process_fields_' . $this->metabox_id, 'save_meta' );
	}

	/**
	 * Enqueue Styles and Scripts required for metabox.
	 */
	public function enqueue() {
		// Early bail out if is not the valid screen or if it's WPBakery's Frontend editor.
		$screen = get_current_screen();
		if ( ! Admin_Helper::is_post_edit() || 'product' !== $screen->id ) {
			return;
		}

		// Styles.
		CMB2_hookup::enqueue_cmb_css();
		wp_enqueue_style( 'rank-math-metabox', rank_math_woocommerce()->plugin_url() . '/assets/admin/css/metabox.css', [ 'rank-math-common', 'rank-math-cmb2' ], rank_math_woocommerce()->version );

		$js = rank_math_woocommerce()->plugin_url() . 'assets/admin/js/';

		wp_enqueue_script( 'rank-math-post-metabox', $js . 'post-metabox.js', [ 'rank-math-common' ], rank_math_woocommerce()->version, true );

	}

	/**
	 * Add main metabox.
	 */
	public function add_main_metabox() {

		$cmb = new_cmb2_box([
			'id'               => $this->metabox_id,
			'title'            => esc_html__( 'Rank Math SEO', 'schema-markup' ),
			'object_types'     => [ 'product' ],
			'new_term_section' => false,
			'new_user_section' => 'add-existing-user',
			'context'          => 'normal',
			'priority'         => $this->get_priority(),
			'cmb_styles'       => false,
			'classes'          => 'rank-math-metabox-wrap',
		]);

		$tabs = $this->get_tabs();
		$cmb->add_field([
			'id'   => 'setting-panel-container-' . $this->metabox_id,
			'type' => 'meta_tab_container_open',
			'tabs' => $tabs,
		]);

		foreach ( $tabs as $id => $tab ) {

			$cmb->add_field( [
				'id'   => 'setting-panel-' . $id,
				'type' => 'tab_open',
			] );

			include_once $tab['file'];

			/**
			 * Add setting into specific tab of main metabox.
			 *
			 * The dynamic part of the hook name. $id, is the tab id.
			 *
			 * @param CMB2 $cmb CMB2 object.
			 */
			$this->do_action( 'metabox/settings/' . $id, $cmb );

			$cmb->add_field([
				'id'   => 'setting-panel-' . $id . '-close',
				'type' => 'tab_close',
			]);
		}

		$cmb->add_field([
			'id'   => 'setting-panel-container-close-' . $this->metabox_id,
			'type' => 'tab_container_close',
		]);

		CMB2::pre_init( $cmb );
	}

	/**
	 * Save post meta handler.
	 *
	 * @param  CMB2 $cmb CMB2 metabox object.
	 */
	public function save_meta( $cmb ) {
		/**
		 * Hook into save handler for main metabox.
		 *
		 * @param CMB2 $cmb CMB2 object.
		 */
		$this->do_action( 'metabox/process_fields', $cmb );
	}

	/**
	 * Get metabox priority
	 *
	 * @return string
	 */
	private function get_priority() {
		$post_type = Param::get(
			'post_type',
			get_post_type( Param::get( 'post', 0, FILTER_VALIDATE_INT ) )
		);
		$priority = 'product' === $post_type ? 'default' : 'high';

		return $this->do_filter( 'metabox/priority', $priority );
	}

	/**
	 * Adds custom category description editor.
	 *
	 * @return {void}
	 */
	private function description_field_editor() {
		$taxonomy        = filter_input( INPUT_GET, 'taxonomy', FILTER_DEFAULT, [ 'options' => [ 'default' => '' ] ] );
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( empty( $taxonomy_object ) || empty( $taxonomy_object->public ) ) {
			return;
		}

		if ( ! Helper::get_settings( 'titles.tax_' . $taxonomy . '_add_meta_box' ) ) {
			return;
		}
	}

	/**
	 * Is user metabox enabled.
	 *
	 * @return bool
	 */
	private function is_user_metabox() {
		return ( false === Helper::get_settings( 'titles.disable_author_archives' ) && Helper::get_settings( 'titles.author_add_meta_box' ) );
	}

	/**
	 * Get tabs.
	 *
	 * @return array
	 */
	private function get_tabs() {

		/**
		 * Allow developers to add new tabs into main metabox.
		 *
		 * @param array $tabs Array of tabs.
		 */
		return $this->do_filter( 'metabox/tabs', [] );
	}
}
