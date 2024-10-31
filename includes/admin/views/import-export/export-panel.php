<?php
/**
 * Export panel template.
 *
 * @package    RankMath
 * @subpackage RankMath_Woocommerce\Admin
 */

?>
<form class="rank-math-export-form cmb2-form" action="" method="post">

	<h3><?php esc_html_e( 'Export Settings', 'seo-for-woocommerce' ); ?></h3>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="status"><?php esc_html_e( 'Panels', 'seo-for-woocommerce' ); ?></label></th>
				<td>
					<ul class="cmb2-checkbox-list no-select-all cmb2-list">
						<li><input type="checkbox" class="cmb2-option" name="panels[]" id="status1" value="general" checked="checked"> <label for="status1"><?php esc_html_e( 'General Settings', 'seo-for-woocommerce' ); ?></label></li>
					</ul>
					<p class="description"><?php esc_html_e( 'Choose the panels to export.', 'seo-for-woocommerce' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

	<footer>
		<input type="hidden" name="object_id" value="export-plz">
		<button type="submit" class="button button-primary button-xlarge"><?php esc_html_e( 'Export', 'seo-for-woocommerce' ); ?></button>
	</footer>

</form>
