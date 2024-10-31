<?php
/**
 * The Module Manager
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Woocommerce;

use RankMath_Woocommerce\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Module_Manager class.
 */
class Module_Manager {

	use Hooker;

	/**
	 * Hold modules.
	 *
	 * @var array
	 */
	public $modules = [];

	/**
	 * Hold module object.
	 *
	 * @var array
	 */
	private $controls = [];

	/**
	 * Hold active module ids.
	 *
	 * @var array
	 */
	private $active = [];

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		$this->action( 'plugins_loaded', 'setup_modules' );
	}

	/**
	 * Include default modules support.
	 */
	public function setup_modules() {
		/**
		 * Filters the array of modules available to be activated.
		 *
		 * @param array $modules Array of available modules.
		 */
		$modules = $this->do_filter( 'modules', [
			'woocommerce'     => [
				'id'            => 'woocommerce',
				'title'         => esc_html__( 'WooCommerce', 'seo-for-woocommerce' ),
				'desc'          => esc_html__( 'WooCommerce module to use Rank Math to optimize WooCommerce Product Pages.', 'seo-for-woocommerce' ),
				'icon'          => 'dashicons-cart',
				'settings_link' => Helper::get_admin_url( 'options-general' ),
			],
			'404-monitor' => [
				'id'    => '404-monitor',
				'title' => esc_html__( '404 Monitor', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'Records the URLs on which visitors & search engines run into 404 Errors. You can also turn on Redirections to redirect the error causing URLs to other URLs.', 'seo-for-woocommerce' ),
				'icon'  => 'dashicons-dismiss',
			],

			'redirections'    => [
				'id'            => 'redirections',
				'title'         => esc_html__( 'Redirections', 'seo-for-woocommerce' ),
				'desc'          => esc_html__( 'Redirect non-existent content easily with 301 and 302 status code. This can help reduce errors and improve your site ranking.', 'seo-for-woocommerce' ),
				'icon'          => 'dashicons-randomize',
				'settings_link' => Helper::is_addon_active( 'redirections' ) ? Helper::get_admin_url( 'options-general' ) . '#setting-panel-redirections' : '',
			],

			'rich-snippet'    => [
				'id'            => 'rich-snippet',
				'title'         => esc_html__( 'Rich Snippets', 'seo-for-woocommerce' ),
				'desc'          => esc_html__( 'Enable support for the Rich Snippets, which adds metadata to your website, resulting in rich search results and more traffic.', 'seo-for-woocommerce' ),
				'icon'          => 'dashicons-awards',
				'settings_link' => Helper::is_addon_active( 'schema-markup' ) ? Helper::get_admin_url( 'options-titles' ) . '#setting-panel-post-type-post' : '',
			],

			'local-seo'       => [
				'id'    => 'local-seo',
				'title' => esc_html__( 'Local SEO & Google Knowledge Graph', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'Dominate the search results for local audience by optimizing your website and posts using this Rank Math module.', 'seo-for-woocommerce' ),
				'icon'  => 'dashicons-location-alt',
			],

			'role-manager'    => [
				'id'    => 'role-manager',
				'title' => esc_html__( 'Role Manager', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'The Role Manager allows you to use internal WordPress\' roles to control which of your site admins can change Rank Math\'s settings', 'seo-for-woocommerce' ),
				'icon'  => 'dashicons-admin-users',
			],

			'search-console'  => [
				'id'    => 'search-console',
				'title' => esc_html__( 'Search Console', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'Connect Rank Math with Google Search Console to see the most important information from Google directly in your WordPress dashboard.', 'seo-for-woocommerce' ),
				'icon'  => 'dashicons-search',
			],

			'seo-analysis'    => [
				'id'    => 'seo-analysis',
				'title' => esc_html__( 'SEO Analysis', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'Let Rank Math analyze your website and your website\'s content using 70+ different tests to provide tailor-made SEO Analysis to you.', 'seo-for-woocommerce' ),
				'icon'  => 'dashicons-chart-bar',
			],

			'sitemap'         => [
				'id'    => 'sitemap',
				'title' => esc_html__( 'Sitemap', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'Enable Rank Math\'s sitemap feature, which helps search engines index your website\'s content effectively.', 'seo-for-woocommerce' ),
				'icon'  => 'dashicons-networking',
			],

			'amp'             => [
				'id'    => 'amp',
				'title' => esc_html__( 'AMP', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'Install AMP plugin from WordPress.org to make Rank Math work with Accelerated Mobile Pages. It is required because AMP are different than WordPress pages and our plugin doesn\'t work with them out-of-the-box.', 'seo-for-woocommerce' ),
				'icon'  => 'dashicons-smartphone',
			],

			'link-counter'    => [
				'id'    => 'link-counter',
				'title' => esc_html__( 'Link Counter', 'seo-for-woocommerce' ),
				'desc'  => esc_html__( 'Counts the total number of internal, external links, to and from links inside your posts.', 'seo-for-woocommerce' ),
				'icon'  => 'dashicons-admin-links',
			],
		] );

		foreach ( $modules as $module ) {
			$this->add_module( $module );
		}
	}

	/**
	 * Display module form to enable/disable them.
	 *
	 * @codeCoverageIgnore
	 */
	public function display_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo esc_html__( 'You cant access this page.', 'seo-for-woocommerce' );
			return;
		}
		?>
		<div class="rank-math-ui module-listing">

			<div class="two-col">
			<?php
			foreach ( $this->modules as $module_id => $module ) :

				$is_active      = false;
				$label_class    = 'rank-math-tooltip';
				$active_modules = Helper::get_active_modules();
				if ( in_array( $module_id, $active_modules ) ) {
					$is_active   = true;
					$label_class = '';
				}
				?>
				<div class="col">
					<div class="rank-math-box <?php echo $is_active ? 'active' : ''; ?>">

						<span class="dashicons <?php echo isset( $module['icon'] ) ? $module['icon'] : 'dashicons-category'; ?>"></span>

						<header>
							<h3><?php echo $module['title']; ?></h3>
							<p><em><?php echo $module['desc']; ?></em></p>
							<?php if ( $is_active && isset( $module['settings_link'] ) ) { ?>
								<a class="module-settings" href="<?php echo esc_url( $module['settings_link'] ); ?>"><?php esc_html_e( 'Settings', 'seo-for-woocommerce' ); ?></a>
							<?php } ?>
						</header>
						<div class="status wp-clearfix">
							<span class="rank-math-switch">
								<input type="checkbox" class="rank-math-modules" id="module-<?php echo $module_id; ?>" name="modules[]" value="<?php echo $module_id; ?>"<?php checked( $is_active ); ?> disabled="disabled">
								<label for="module-<?php echo $module_id; ?>" class="<?php echo $label_class; ?>"><?php esc_html_e( 'Toggle', 'seo-for-woocommerce' ); ?></label>
							</span>
							<label>
								<?php esc_html_e( 'Status:', 'seo-for-woocommerce' ); ?>
								<?php if ( $is_active ) { ?>
									<span class="module-status active-text"><?php echo esc_html__( 'Active', 'seo-for-woocommerce' ); ?></span>
								<?php } else { ?>
									<span class="module-status inactive-text"><?php echo esc_html__( 'Inactive', 'seo-for-woocommerce' ); ?> </span>
								<?php } ?>
							</label>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			</div>

		</div>
		<?php
	}

	/**
	 * Add module.
	 *
	 * @param array $args Module configuration.
	 */
	public function add_module( $args = [] ) {
		$this->modules[ $args['id'] ] = $args;
	}
}
