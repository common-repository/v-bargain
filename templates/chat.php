<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
global $wpdb;
if ( isset( $_GET['sid'] ) ) {
	$sid = sanitize_text_field( $_GET['sid'] );
}
if ( isset($_GET['pid']) ) {
	$pid = sanitize_text_field( $_GET['pid'] );
}

$data = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT CASE WHEN (u.display_name IS NULL OR u.display_name = 0) THEN 'Guest' ELSE u.display_name END AS display_name, d.product_id
		FROM {$wpdb->prefix}dbargain_reports d
		LEFT JOIN {$wpdb->prefix}users u ON d.user_id = u.ID
		WHERE d.session_id = %s AND d.product_id = %d",
		$sid,
		$pid
	),
	ARRAY_A
);

$product = wc_get_product($data['product_id']);

$image = wp_get_attachment_image_src(get_post_thumbnail_id($data['product_id']), 'product');
?>
<div class="wrap">
	<h1 class="wp-heading-inline">Session Chat History of
		<?php echo esc_html( $data['display_name'] ); ?>
	</h1>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-1">
			<div id="post-body-content">
				<table class='wp-list-table widefat fixed striped table-view-list offers' border='0' width='100%'>
					<tr>
						<td colspan="3">
							<table class='wp-list-table widefat fixed table-view-list offers' border='0' width='100%'>
								<tr>
									<td width="10%"><img src="<?php echo esc_html( $image[0] ); ?>" style="max-width: 100px"
											class="img-responsive"></td>
									<td width="5%">#
										<?php echo esc_html( $data['product_id'] ); ?>
									</td>
									<td width="12%"><b>
											<?php echo esc_html( $product->get_title() ); ?>
										</b></td>
									<td width="70%">
										<?php echo esc_html( strip_tags( wc_price($product->get_regular_price() ) ) ); ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<?php
					$messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dbargain_session WHERE session_id = %s", $sid), ARRAY_A);// $messages = $wpdb->get_results("select * from {$wpdb->prefix}dbargain_session where session_id = '" . $sid . "'", ARRAY_A);
					foreach ($messages as $msg) {
						?>
						<tr>
							<td>
								<?php echo ( esc_html( isset( $msg['message'] ) ) && !empty($msg['message']) ) ? 'System: ' : esc_html( $data['display_name'] ); ?>
							</td>
							<td>
								<?php echo ( esc_html( !empty( $msg['offer'] ) ) && $msg['offer'] > 0 ) ? esc_html( strip_tags( wc_price($msg['offer']) ) ) : esc_html( $msg['message'] ); ?>
							</td>
							<td>
								<?php echo esc_html( $msg['date_created'] ); ?>
							</td>
						</tr>
						<?php
					} 
					?>
				</table>
			</div>
			<button type="button" class="button button-primary" onclick="window.location.href='?page=dbargain'">Back</button>
		</div>
	</div>
</div>
