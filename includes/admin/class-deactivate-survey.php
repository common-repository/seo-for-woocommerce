<?php
/**
 * Handle the plugin deactivation feedback
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\Admin;

use RankMath_Woocommerce\Runner;
use RankMath_Woocommerce\Traits\Ajax;
use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Deactivate_Survey class.
 *
 * @codeCoverageIgnore
 */
class Deactivate_Survey implements Runner {

	use Hooker, Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_footer', 'deactivate_scripts' );
		$this->ajax( 'deactivate_feedback', 'deactivate_feedback' );
	}

	/**
	 * Send deactivated feedback to api.
	 */
	public function deactivate_feedback() {

		check_ajax_referer( 'rank_math_woocommerce_deactivate_feedback_nonce', 'security' );

		$reason_key  = Param::post( 'reason_key', '' );
		$reason_text = Param::post(
			"reason_{$reason_key}",
			$this->get_uninstall_reasons()[ $reason_key ]['title']
		);

		wp_safe_remote_post( 'https://rankmath.com/mtsapi/v1/deactivate_feedback', [
			'timeout'   => 30,
			'blocking'  => false,
			'sslverify' => false,
			'cookies'   => [],
			'headers'   => [ 'user-agent' => 'RankMath/' . md5( esc_url( home_url( '/' ) ) ) . ';' ],
			'body'      => [
				'product_name'    => 'seo-for-woocommerce',
				'product_version' => rank_math_woocommerce()->version,
				'site_url'        => esc_url( site_url() ),
				'site_lang'       => get_bloginfo( 'language' ),
				'feedback_key'    => $reason_key,
				'feedback'        => $reason_text,
			],
		]);

		wp_send_json_success();
	}

	/**
	 * Print deactivate feedback dialog.
	 */
	public function deactivate_scripts() {
		$screen = get_current_screen();

		// Early Bail!
		if ( ! in_array( $screen->id, [ 'plugins', 'plugins-network' ], true ) ) {
			return;
		}

		// Scripts.
		rank_math_woocommerce()->admin_assets->enqueue_style( 'plugin-modal' );
		rank_math_woocommerce()->admin_assets->enqueue_script( 'plugin-modal' );

		// Form.
		?>
		<div class="rank-math-feedback-modal rank-math-ui" id="rank-math-feedback-form">
			<div class="rank-math-feedback-content">

				<header>

					<h2>
						<?php echo __( 'Quick Feedback', 'seo-for-woocommerce' ); ?>
						<span class="button-close dashicons dashicons-no-alt alignright"></span>
					</h2>

					<p><?php echo __( 'If you have a moment, please share why you are deactivating Rank Math:', 'seo-for-woocommerce' ); ?></p>

				</header>

				<form method="post">

					<input type="hidden" name="action" value="rank_math_woocommerce_deactivate_feedback" />
					<?php wp_nonce_field( 'rank_math_woocommerce_deactivate_feedback_nonce', 'security' ); ?>

					<?php foreach ( $this->get_uninstall_reasons() as $key => $reason ) : ?>
					<div class="rank-math-feedback-input-wrapper">

						<input id="deactivate-feedback-<?php echo esc_attr( $key ); ?>" type="radio" name="reason_key" value="<?php echo esc_attr( $key ); ?>" />

						<label for="deactivate-feedback-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $reason['title'] ); ?></label>

						<?php if ( ! empty( $reason['placeholder'] ) ) : ?>
							<input class="regular-text" type="text" name="reason_<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $reason['placeholder'] ); ?>" />
						<?php endif; ?>

					</div>
					<?php endforeach; ?>

					<footer>

						<button type="submit" class="button button-primary button-large button-submit"><?php esc_html_e( 'Submit & Deactivate', 'seo-for-woocommerce' ); ?></button>

						<button type="button" class="button button-link alignright button-skip"><?php esc_html_e( 'Skip & Deactivate', 'seo-for-woocommerce' ); ?></button>

					</footer>

				</form>

			</div>

		</div>
		<?php
	}

	/**
	 * Get uninstall reasons.
	 *
	 * @return array
	 */
	private function get_uninstall_reasons() {
		return [
			'no_longer_needed'           => [
				'title'       => esc_html__( 'I no longer need the plugin', 'seo-for-woocommerce' ),
				'placeholder' => '',
			],
			'found_a_better_plugin'      => [
				'title'       => esc_html__( 'I found a better plugin', 'seo-for-woocommerce' ),
				'placeholder' => esc_html__( 'Please share which plugin', 'seo-for-woocommerce' ),
			],
			'couldnt_get_plugin_to_work' => [
				'title'       => esc_html__( 'I couldn\'t get the plugin to work', 'seo-for-woocommerce' ),
				'placeholder' => '',
			],
			'temporary_deactivation'     => [
				'title'       => esc_html__( 'It\'s a temporary deactivation', 'seo-for-woocommerce' ),
				'placeholder' => '',
			],
			'other'                      => [
				'title'       => esc_html__( 'Other', 'seo-for-woocommerce' ),
				'placeholder' => esc_html__( 'Please share the reason', 'seo-for-woocommerce' ),
			],
		];
	}
}
