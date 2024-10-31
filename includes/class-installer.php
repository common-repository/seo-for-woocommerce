<?php
/**
 * Plugin Activation and De-Activation
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce;

use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\WordPress;
use RankMath_Woocommerce\Role_Manager\Capability_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Installer class.
 */
class Installer {

	use Hooker;

	/**
	 * Binding all events
	 */
	public function __construct() {
		register_activation_hook( RANK_MATH_WOOCOMMERCE_FILE, [ $this, 'activation' ] );
		register_deactivation_hook( RANK_MATH_WOOCOMMERCE_FILE, [ $this, 'deactivation' ] );

		$this->action( 'wpmu_new_blog', 'activate_blog' );
		$this->action( 'activate_blog', 'activate_blog' );
		$this->filter( 'wpmu_drop_tables', 'on_delete_blog' );
	}

	/**
	 * Does something when activating Rank Math.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function activation( $network_wide = false ) {
		if ( ! is_multisite() || ! $network_wide ) {
			$this->activate();
			return;
		}

		$this->network_activate_deactivate( true );
	}

	/**
	 * Does something when deactivating Rank Math.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function deactivation( $network_wide = false ) {
		if ( ! is_multisite() || ! $network_wide ) {
			$this->deactivate();
			return;
		}

		$this->network_activate_deactivate( false );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param int $blog_id ID of the new blog.
	 */
	public function activate_blog( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		$this->activate();
		restore_current_blog();
	}

	/**
	 * Uninstall tables when MU blog is deleted.
	 *
	 * @param  array $tables List of tables that will be deleted by WP.
	 * @return array
	 */
	public function on_delete_blog( $tables ) {
		global $wpdb;

		$tables[] = $wpdb->prefix . 'rank_math_404_logs';

		return $tables;
	}

	/**
	 * Run network-wide (de-)activation of the plugin.
	 *
	 * @param bool $activate True for plugin activation, false for de-activation.
	 */
	private function network_activate_deactivate( $activate ) {
		global $wpdb;

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" );
		if ( empty( $blog_ids ) ) {
			return;
		}

		foreach ( $blog_ids as $blog_id ) {
			$func = true === $activate ? 'activate' : 'deactivate';

			switch_to_blog( $blog_id );
			$this->$func();
			restore_current_blog();
		}
	}

	/**
	 * Runs on activation of the plugin.
	 */
	private function activate() {
		$current_version    = get_option( 'rank_math_woocommerce_version', null );
		$current_db_version = get_option( 'rank_math_woocommerce_db_version', null );

		$this->create_options();

		if ( is_null( $current_version ) && is_null( $current_db_version ) ) {
			set_transient( '_rank_math_woocommerce_activation_redirect', 1, 30 );
		}

		// Update to latest version.
		update_option( 'rank_math_woocommerce_version', rank_math_woocommerce()->version );
		update_option( 'rank_math_woocommerce_db_version', rank_math_woocommerce()->db_version );

		// Save install date.
		if ( false == get_option( 'rank_math_woocommerce_install_date' ) ) {
			update_option( 'rank_math_woocommerce_install_date', current_time( 'timestamp' ) );
		}

		$this->clear_cache();
		$this->do_action( 'activate' );
	}

	/**
	 * Runs on deactivate of the plugin.
	 */
	private function deactivate() {
		$this->clear_cache();
		$this->do_action( 'deactivate' );
	}

	/**
	 * Create options.
	 */
	private function create_options() {
		add_option( 'rank-math-options-general', $this->do_filter( 'settings/defaults/general', [
			'wc_remove_product_base'          => 'off',
			'wc_remove_category_base'         => 'off',
			'wc_remove_category_parent_slugs' => 'off',
			'wc_remove_generator'             => 'on',
			'remove_shop_snippet_data'        => 'on',
		]));
	}

	/**
	 * Clears the WP or W3TC cache depending on which is used.
	 */
	private function clear_cache() {
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			w3tc_pgcache_flush();
		}
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}
	}
}
