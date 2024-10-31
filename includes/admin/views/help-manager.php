<?php
/**
 * Help page template.
 *
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Admin
 */

use RankMath_Woocommerce\Helper;

$tabs = apply_filters( 'rank_math/help/tabs', [] );

?>
<div class="wrap rank-math-wrap limit-wrap">

	<span class="wp-header-end"></span>

	<h1 class="page-title"><?php esc_html_e( 'Help &amp; Support', 'seo-for-woocommerce' ); ?></h1>
	<br>

	<div id="rank-math-help-wrapper" class="rank-math-tabs">

		<?php if ( 1 < count( $tabs ) ) { ?>
			<div class="rank-math-tabs-navigation wp-clearfix">
				<?php foreach ( $tabs as $id => $tab ) : ?>
				<a href="#help-panel-<?php echo $id; ?>"><?php echo $tab['title']; ?></a>
				<?php endforeach; ?>
			</div>
		<?php } ?>
		<div class="rank-math-tabs-content">
			<?php foreach ( $tabs as $id => $tab ) : ?>
			<div id="help-panel-<?php echo $id; ?>" class="rank-math-tab">
				<?php include $tab['view']; ?>
			</div>
			<?php endforeach; ?>
		</div>

	</div>

</div>
