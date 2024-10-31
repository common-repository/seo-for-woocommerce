<?php
/**
 * The Import Export Class
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\Admin;

use RankMath_Woocommerce\Runner;
use RankMath_Woocommerce\Traits\Hooker;
use RankMath_Woocommerce\Helper;
use MyThemeShop\Admin\Page;
use MyThemeShop\Helpers\WordPress;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Import_Export class.
 */
class Import_Export implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'register_page', 1 );
	}

	/**
	 * Register admin pages for plugin.
	 */
	public function register_page() {
		$uri = rank_math_woocommerce()->plugin_url() . 'assets/admin/';
		new Page( 'rank-math-import-export', esc_html__( 'Import &amp; Export', 'seo-for-woocommerce' ), [
			'position' => 99,
			'parent'   => 'rank-math-woocommerce',
			'render'   => Admin_Helper::get_view( 'import-export/main' ),
			'onsave'   => [ $this, 'handler' ],
			'classes'  => [ 'rank-math-page' ],
			'assets'   => [
				'styles'  => [
					'cmb2-styles'      => '',
					'rank-math-common' => '',
					'rank-math-cmb2'   => '',
				],
				'scripts' => [ 'rank-math-import-export' => $uri . 'js/import-export.js' ],
			],
		]);

		Helper::add_json( 'importConfirm', esc_html__( 'Are you sure you want to import settings into Rank Math? Don\'t worry, your current configuration will be saved as a backup.', 'seo-for-woocommerce' ) );
	}

	/**
	 * Handle import or export.
	 */
	public function handler() {

		$object_id = Param::post( 'object_id' );

		if ( 'export-plz' === $object_id ) {
			$this->export();
		}

		if ( isset( $_FILES['import-me'] ) && 'import-plz' === $object_id && check_admin_referer( 'rank-math-woocommerce-import-settings' ) ) {
			$this->import();
		}
	}

	/**
	 * Handle export.
	 */
	private function export() {
		$panels   = Param::post( 'panels', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data     = $this->get_export_data( $panels );
		$filename = 'seo-for-woocommerce-settings-' . date( 'Y-m-d-H-i-s' ) . '.txt';

		header( 'Content-Type: application/txt' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo wp_json_encode( $data );
		exit;
	}

	/**
	 * Handle import.
	 */
	private function import() {

		// Handle file upload.
		$file = $this->has_valid_file();
		if ( false === $file ) {
			return false;
		}

		// Parse Options.
		$wp_filesystem = WordPress::get_filesystem();
		$settings      = $wp_filesystem->get_contents( $file['file'] );
		$settings      = json_decode( $settings, true );

		\unlink( $file['file'] );

		if ( $this->do_import_data( $settings ) ) {
			Helper::add_notification( esc_html__( 'Settings successfully imported.', 'seo-for-woocommerce' ), 'success' );
			return;
		}

		Helper::add_notification( esc_html__( 'No settings found to be imported.', 'seo-for-woocommerce' ), [ 'type' => 'info' ] );
	}

	/**jn56
	 * Import has valid file.
	 *
	 * @return mixed
	 */
	private function has_valid_file() {
		$file = wp_handle_upload( $_FILES['import-me'] );
		if ( is_wp_error( $file ) ) {
			Helper::add_notification( esc_html__( 'Settings could not be imported:', 'seo-for-woocommerce' ) . ' ' . $file->get_error_message(), [ 'type' => 'error' ] );
			return false;
		}

		if ( isset( $file['error'] ) ) {
			Helper::add_notification( esc_html__( 'Settings could not be imported:', 'seo-for-woocommerce' ) . ' ' . $file['error'], [ 'type' => 'error' ] );
			return false;
		}

		if ( ! isset( $file['file'] ) ) {
			Helper::add_notification( esc_html__( 'Settings could not be imported: Upload failed.', 'seo-for-woocommerce' ), [ 'type' => 'error' ] );
			return false;
		}

		return $file;
	}

	/**
	 * Does import data.
	 *
	 * @param  array $data           Import data.
	 * @param  bool  $suppress_hooks Suppress hooks or not.
	 * @return bool
	 */
	private function do_import_data( array $data, $suppress_hooks = false ) {
		$down = false;
		$hash = [
			'general' => 'rank-math-options-general',
		];

		$this->run_import_hooks( 'pre_import', $data, $suppress_hooks );

		foreach ( $hash as $key => $option_key ) {
			if ( isset( $data[ $key ] ) && ! empty( $data[ $key ] ) ) {
				$down = true;
				update_option( $option_key, $data[ $key ] );
			}
		}

		$this->run_import_hooks( 'after_import', $data, $suppress_hooks );

		return $down;
	}

	/**
	 * Run import hooks
	 *
	 * @param string $hook     Hook to fire.
	 * @param array  $data     Import data.
	 * @param bool   $suppress Suppress hooks or not.
	 */
	private function run_import_hooks( $hook, $data, $suppress ) {
		if ( ! $suppress ) {
			/**
			 * Fires while importing settings.
			 *
			 * @since 1.0.0
			 *
			 * @param array $data Import data.
			 */
			$this->do_action( 'importers/settings/' . $hook, $data );
		}
	}

	/**
	 * Gets export data.
	 *
	 * @param array $panels Which panels do you want to export. It will export all panels if this param is empty.
	 * @return array
	 */
	private function get_export_data( array $panels = [] ) {
		if ( ! $panels ) {
			$panels = [ 'general' ];
		}

		$settings = rank_math_woocommerce()->settings->all_raw();

		foreach ( $panels as $panel ) {
			if ( isset( $settings[ $panel ] ) ) {
				$data[ $panel ] = $settings[ $panel ];
			}
		}

		return $data;
	}
}
