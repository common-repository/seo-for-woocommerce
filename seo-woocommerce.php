<?php // @codingStandardsIgnoreLine
/**
 * SEO for WooCommerce by Rank Math.
 *
 * @package      RankMath_Woocommerce\RankMath_Woocommerce
 * @copyright    Copyright (C) 2019, Rank Math - support@rankmath.com
 * @link         https://rankmath.com
 * @since        1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       SEO for WooCommerce
 * Version:           1.0.0
 * Plugin URI:        https://s.rankmath.com/seo-for-woocommerce
 * Description:       A simple and powerful SEO plugin for WooCommerce, add automatic Schema markup(aka Rich Snippets) to product and archive pages.
 * Author:            Rank Math
 * Author URI:        https://s.rankmath.com/home
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       seo-for-woocommerce
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

/**
 * RankMath_Woocommerce class.
 *
 * @class The class that holds the entire plugin.
 */
final class RankMath_Woocommerce {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Rank Math database version.
	 *
	 * @var string
	 */
	public $db_version = '1';

	/**
	 * Minimum version of WordPress required to run the plugin
	 *
	 * @var string
	 */
	private $wordpress_version = '4.2';

	/**
	 * Minimum version of PHP required to run the plugin
	 *
	 * @var string
	 */
	private $php_version = '5.6';

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = [];

	/**
	 * Hold messages
	 *
	 * @var bool
	 */
	private $messages = [];

	/**
	 * The single instance of the class
	 *
	 * @var RankMath_Woocommerce
	 */
	protected static $instance = null;

	/**
	 * Magic isset to bypass referencing plugin
	 *
	 * @param  string $prop Property to check.
	 * @return bool
	 */
	public function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
	}

	/**
	 * Magic get method
	 *
	 * @param  string $prop Property to get.
	 * @return mixed Property value or NULL if it does not exists
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}

		return $this->{$prop};
	}

	/**
	 * Magic set method
	 *
	 * @param mixed $prop  Property to set.
	 * @param mixed $value Value to set.
	 */
	public function __set( $prop, $value ) {
		if ( property_exists( $this, $prop ) ) {
			$this->$prop = $value;
			return;
		}

		$this->container[ $prop ] = $value;
	}

	/**
	 * Magic call method.
	 *
	 * @param  string $name      Method to call.
	 * @param  array  $arguments Arguments to pass when calling.
	 * @return mixed Return value of the callback.
	 */
	public function __call( $name, $arguments ) {
		$hash = [
			'plugin_dir'   => RANK_MATH_WOOCOMMERCE_PATH,
			'plugin_url'   => RANK_MATH_WOOCOMMERCE_URL,
			'includes_dir' => RANK_MATH_WOOCOMMERCE_PATH . 'includes/',
			'admin_dir'    => RANK_MATH_WOOCOMMERCE_PATH . 'includes/admin/',
		];

		if ( isset( $hash[ $name ] ) ) {
			return $hash[ $name ];
		}

		return call_user_func_array( $name, $arguments );
	}

	/**
	 * Main RankMath_Woocommerce instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @see rank_math_woocommerce()
	 * @return RankMath_Woocommerce
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof RankMath_Woocommerce ) ) {
			self::$instance = new RankMath_Woocommerce;
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Instantiate the plugin
	 */
	private function setup() {
		// Define constants.
		$this->define_constants();

		if ( ! $this->is_requirements_meet() ) {
			return;
		}

		if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			return;
		}

		// Include required files.
		include dirname( __FILE__ ) . '/vendor/autoload.php';

		// instantiate classes.
		$this->instantiate();

		// Initialize the action hooks.
		$this->init_actions();

		// Loaded action.
		do_action( 'rank_math_woocommerce/loaded' );
	}

	/**
	 * Check that the WordPress and PHP setup meets the plugin requirements
	 *
	 * @return bool
	 */
	private function is_requirements_meet() {

		// Check if WordPress version is enough to run this plugin.
		if ( version_compare( get_bloginfo( 'version' ), $this->wordpress_version, '<' ) ) {
			/* translators: WordPress Version */
			$this->messages[] = sprintf( esc_html__( 'Rank Math requires WordPress version %s or above. Please update WordPress to run this plugin.', 'seo-for-woocommerce' ), $this->wordpress_version );
		}

		// Check if PHP version is enough to run this plugin.
		if ( version_compare( phpversion(), $this->php_version, '<' ) ) {
			/* translators: PHP Version */
			$this->messages[] = sprintf( esc_html__( 'Rank Math requires PHP version %s or above. Please update PHP to run this plugin.', 'seo-for-woocommerce' ), $this->php_version );
		}

		// Check if Rank Math Plugin is active.
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->messages[] = esc_html__( 'Please activate Woocommerce Plugin to use SEO for Woocommerce by Rank Math.', 'seo-for-woocommerce' );
		}

		if ( empty( $this->messages ) ) {
			return true;
		}

		// Auto-deactivate plugin.
		add_action( 'admin_init', [ $this, 'auto_deactivate' ] );
		add_action( 'admin_notices', [ $this, 'activation_error' ] );

		return false;
	}

	/**
	 * Auto-deactivate plugin if requirement not meet and display a notice
	 */
	public function auto_deactivate() {
		deactivate_plugins( plugin_basename( RANK_MATH_WOOCOMMERCE_FILE ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Plugin activation notice
	 */
	public function activation_error() {
		?>
		<div class="notice notice-error">
			<p>
				<?php echo join( '<br>', $this->messages ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Define the plugin constants
	 */
	private function define_constants() {
		define( 'RANK_MATH_WOOCOMMERCE_VERSION', $this->version );
		define( 'RANK_MATH_WOOCOMMERCE_FILE', __FILE__ );
		define( 'RANK_MATH_WOOCOMMERCE_PATH', dirname( RANK_MATH_WOOCOMMERCE_FILE ) . '/' );
		define( 'RANK_MATH_WOOCOMMERCE_URL', plugins_url( '', RANK_MATH_WOOCOMMERCE_FILE ) . '/' );
	}

	/**
	 * Instantiate classes
	 */
	private function instantiate() {
		new \RankMath_Woocommerce\Installer;

		// Setting Manager.
		$this->container['settings'] = new \RankMath_Woocommerce\Settings;

		// JSON Manager.
		$this->container['json'] = new \MyThemeShop\Json_Manager;

		// Notification Manager.
		$this->container['notification'] = new \MyThemeShop\Notification_Center( 'rank_math_woocommerce_notifications' );

		$this->container['manager'] = new \RankMath_Woocommerce\Module_Manager;
	}

	/**
	 * Initialize WordPress action hooks
	 */
	private function init_actions() {
		add_action( 'init', [ $this, 'localization_setup' ] );

		// Add plugin action links.
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( RANK_MATH_WOOCOMMERCE_FILE ), [ $this, 'plugin_action_links' ] );

		if ( is_admin() ) {
			add_action( 'plugins_loaded', [ $this, 'init_admin' ], 14 );
		}

		add_action( 'plugins_loaded', [ $this, 'init_modules' ], 15 );
	}

	/**
	 * Initialize module.
	 */
	public function init_modules() {
		new \RankMath_Woocommerce\WooCommerce\WooCommerce;
	}

	/**
	 * Initialize the admin.
	 */
	public function init_admin() {
		new \RankMath_Woocommerce\Admin\Engine;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param  mixed $links Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {

		$plugin_links = [
			'<a href="' . RankMath_Woocommerce\Helper::get_admin_url( 'options-general' ) . '">' . esc_html__( 'Settings', 'seo-for-woocommerce' ) . '</a>',
		];

		return array_merge( $links, $plugin_links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param  mixed $links Plugin Row Meta.
	 * @param  mixed $file  Plugin Base file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {

		if ( plugin_basename( RANK_MATH_WOOCOMMERCE_FILE ) !== $file ) {
			return $links;
		}

		$more = [
			'<a href="' . RankMath_Woocommerce\Helper::get_admin_url( 'help' ) . '">' . esc_html__( 'Getting Started', 'seo-for-woocommerce' ) . '</a>',
			'<a href="https://s.rankmath.com/documentation">' . esc_html__( 'Documentation', 'seo-for-woocommerce' ) . '</a>',
		];

		return array_merge( $links, $more );
	}

	/**
	 * Initialize plugin for localization.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *     - WP_LANG_DIR/rank-math/rank-math-LOCALE.mo
	 *     - WP_LANG_DIR/plugins/rank-math-LOCALE.mo
	 */
	public function localization_setup() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'seo-for-woocommerce' );

		unload_textdomain( 'seo-for-woocommerce' );
		load_textdomain( 'seo-for-woocommerce', WP_LANG_DIR . '/seo-for-woocommerce/seo-for-woocommerce-' . $locale . '.mo' );
		load_plugin_textdomain( 'seo-for-woocommerce', false, rank_math_woocommerce()->plugin_dir() . '/languages/' );

		$this->container['json']->add( 'version', $this->version, 'rankMath' );
		$this->container['json']->add( 'ajaxurl', admin_url( 'admin-ajax.php' ), 'rankMath' );
		$this->container['json']->add( 'security', wp_create_nonce( 'rank-math-woocommerce-ajax-nonce' ), 'rankMath' );
	}
}

/**
 * Main instance of RankMath_Woocommerce.
 *
 * Returns the main instance of RankMath_Woocommerce to prevent the need to use globals.
 *
 * @return RankMath_Woocommerce
 */
function rank_math_woocommerce() {
	return RankMath_Woocommerce::get();
}

// Kick it off.
rank_math_woocommerce();
