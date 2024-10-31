<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce\Admin;

use RankMath_Woocommerce\Runner;
use RankMath_Woocommerce\Helper;
use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @codeCoverageIgnore
 */
class Admin implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'flush', 999 );
		$this->action( 'wp_dashboard_setup', 'add_dashboard_widgets' );
		$this->action( 'admin_footer', 'rank_math_modal' );
	}

	/**
	 * Flush the rewrite rules once if the rank_math_flush_rewrite option is set.
	 */
	public function flush() {
		if ( get_option( 'rank_math_flush_rewrite' ) ) {
			flush_rewrite_rules();
			delete_option( 'rank_math_flush_rewrite' );
		}
	}

	/**
	 * Register dashboard widget.
	 */
	public function add_dashboard_widgets() {
		wp_add_dashboard_widget( 'rank_math_dashboard_widget', esc_html__( 'Rank Math', 'seo-for-woocommerce' ), [ $this, 'render_dashboard_widget' ] );
	}

	/**
	 * Render dashboard widget.
	 */
	public function render_dashboard_widget() {
		?>
		<div id="published-posts" class="activity-block">
			<?php $this->do_action( 'dashboard/widget' ); ?>
		</div>
		<?php
	}

	/**
	 * Display dashabord tabs.
	 */
	public function display_dashboard_nav() {
		?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $this->get_nav_links() as $id => $link ) :
				if ( isset( $link['cap'] ) && ! current_user_can( $link['cap'] ) ) {
					continue;
				}
				?>
			<a class="nav-tab<?php echo Param::get( 'view', 'modules' ) === $id ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( Helper::get_admin_url( $link['url'], $link['args'] ) ); ?>" title="<?php echo $link['title']; ?>"><?php echo $link['title']; ?></a>
			<?php endforeach; ?>
		</h2>
		<?php
	}

	/**
	 * Get dashbaord navigation links
	 *
	 * @return array
	 */
	private function get_nav_links() {
		$links = [
			'modules'       => [
				'url'   => '',
				'args'  => 'view=modules',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Modules', 'seo-for-woocommerce' ),
			],
			'help'          => [
				'url'   => 'help',
				'args'  => '',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Help', 'seo-for-woocommerce' ),
			],
			'import-export' => [
				'url'   => 'import-export',
				'args'  => '',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Import &amp; Export', 'seo-for-woocommerce' ),
			],
		];

		if ( Helper::is_plugin_active_for_network() ) {
			unset( $links['help'] );
		}

		return $links;
	}

	/**
	 * Activate Rank Math Modal.
	 */
	public function rank_math_modal() {
		$screen = get_current_screen();

		// Early Bail!
		if ( ! in_array( $screen->id, [ 'toplevel_page_rank-math-woocommerce', 'rank-math_page_rank-math-seo-for-woocommerce', 'rank-math_page_rank-math-options-general' ] ) ) {
			return;
		}

		if ( file_exists( WP_PLUGIN_DIR . '/seo-by-rank-math' ) ) {
			$text         = __( 'Activate Now', 'seo-for-woocommerce' );
			$path         = 'seo-by-rank-math/rank-math.php';
			$link         = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $path ), 'activate-plugin_' . $path );
			$button_class = 'activate-now';
		} else {
			$text         = __( 'Install for Free', 'seo-for-woocommerce' );
			$link         = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=seo-by-rank-math' ), 'install-plugin_seo-by-rank-math' );
			$button_class = 'install-now';
		}

		// Scripts.
		rank_math_woocommerce()->admin_assets->enqueue_style( 'plugin-modal' );
		rank_math_woocommerce()->admin_assets->enqueue_script( 'plugin-modal' );

		?>
		<div class="rank-math-feedback-modal rank-math-ui try-rankmath-panel" id="rank-math-feedback-form">
			<div class="rank-math-feedback-content">

				<div class="plugin-card plugin-card-seo-by-rank-math">
					<span class="button-close dashicons dashicons-no-alt alignright"></span>
					<div class="plugin-card-top">
						<div class="name column-name">
							<h3>
								<a href="https://rankmath.com/wordpress/plugin/seo-suite/" target="_blank">
								<?php esc_html_e( 'WordPress SEO Plugin – Rank Math', 'seo-for-woocommerce' ); ?>
								<img src="<?php echo esc_url( rank_math_woocommerce()->plugin_url() . 'assets/admin/img/icon.svg' ); ?>" class="plugin-icon" alt="<?php esc_html_e( 'Rank Math SEO', 'seo-for-woocommerce' ); ?>">
								</a>
								<span class="vers column-rating">
									<a href="https://wordpress.org/support/plugin/seo-by-rank-math/reviews/" target="_blank">
										<div class="star-rating">
											<div class="star star-full" aria-hidden="true"></div>
											<div class="star star-full" aria-hidden="true"></div>
											<div class="star star-full" aria-hidden="true"></div>
											<div class="star star-full" aria-hidden="true"></div>
											<div class="star star-full" aria-hidden="true"></div>
										</div>
										<span class="num-ratings" aria-hidden="true">(463)</span>
									</a>
								</span>
							</h3>
						</div>

						<div class="desc column-description">
							<p><?php esc_html_e( 'Rank Math is a revolutionary SEO plugin that combines the features of many SEO tools in a single package & helps you multiply your traffic.', 'seo-for-woocommerce' ); ?></p>
						</div>
					</div>

					<div class="plugin-card-bottom">
						<div class="column-compatibility">
							<span class="compatibility-compatible"><strong><?php esc_html_e( 'Compatible', 'seo-for-woocommerce' ); ?></strong> <?php esc_html_e( 'with your version of WordPress', 'seo-for-woocommerce' ); ?></span>
						</div>
						<a href="<?php echo $link; ?>" class="button button-primary install-button <?php echo $button_class; ?>" data-slug="seo-by-rank-math" data-name="Rank Math"><?php echo $text; ?></a>
					</div>
				</div>

			</div>

		</div>
		<?php
	}
}
