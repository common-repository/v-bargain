<?php
error_reporting(E_ERROR | E_PARSE);

session_start();

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$messgae_res = null;
if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'dbargain-settings')) {
	if (isset($_POST['upper_limit'])) {
		update_option('dbargain_session_upper_limit', sanitize_text_field($_POST['upper_limit']), 'yes');
	}

	if (isset($_POST['delay'])) {
		update_option('dbargain_window_delay', sanitize_text_field($_POST['delay']), 'yes');
	}

	if (isset($_POST['threshold'])) {
		update_option('dbargain_threshold', sanitize_text_field($_POST['threshold']), 'yes');
	}

	if (isset($_POST['start_date'])) {
		update_option('dbargain_start_date', sanitize_text_field($_POST['start_date']), 'yes');
	}

	if (isset($_POST['end_date'])) {
		update_option('dbargain_end_date', sanitize_text_field($_POST['end_date']), 'yes');
	}

	if (isset($_POST['chat_delay'])) {
		update_option('dbargain_window_chat_delay', sanitize_text_field($_POST['chat_delay']), 'yes');
	}

	if (isset($_POST['agent_name'])) {
		update_option('dbargain_agent_name', sanitize_text_field($_POST['agent_name']), 'yes');
	}

	if (isset($_POST['criteria'])) {
		$sanitized_criteria = array_map( 'sanitize_text_field', $_POST['criteria'] );
		update_option( 'dbargain_display_criteria', $sanitized_criteria );
		// update_option('dbargain_display_criteria', $_POST['criteria'], 'yes');
	}

	$messgae_res = 'your setting has been updated';
}


$layout = get_option('dbargain_window_layout') ? get_option('dbargain_window_layout') : '';
$criteria = get_option('dbargain_display_criteria') ? get_option('dbargain_display_criteria') : [];
?>

<head>
	<style>
		.notice {
			display: none;
		}
		
		div#ui-datepicker-div {
			background-color: #fff;
			padding: 9px;
			border-radius: 9px;
			box-shadow: 0px 0px 11px 0px lightgrey;
			display: none;
		}

		.badge-success {
			background-color: green;
			width: fit-content;
			height: auto;
			border-radius: 5px;
			padding: 5px;
			font-size: 12px;
			color: white;
			font-weight: 600;
		}

		.info-icon {
			border: 2px solid orange;
			margin: 6px;
			border-radius: 25px;
			color: orange;
			font-size: 11px;
			font-weight: 700;
			padding: 0 4.5px;
			cursor: help;
		}
	</style>
	<!--Start of Tawk.to Script-->
	<script type="text/javascript">
		var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
		(function(){
		var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
		s1.async=true;
		s1.src='https://embed.tawk.to/64d49313cc26a871b02e64f1/1h7f5t1m7';
		s1.charset='UTF-8';
		s1.setAttribute('crossorigin','*');
		s0.parentNode.insertBefore(s1,s0);
		})();
	</script>
	<!--End of Tawk.to Script-->
</head>
<div class="wrap">
	<?php if ($_SESSION['dbargain-status']) { ?>
		<div style="float: right;text-align:center"><span class="badge badge-success">
				<?php echo esc_html( $_SESSION['dbargain-status'] ); ?>
			</span>
			<p style="margin-top:5px;text-align: center;">
				<?php echo esc_html( $_SESSION['dbargain-days-left'] ); ?>
				<?php echo esc_html( 1 == $_SESSION['dbargain-days-left'] ? 'day' : 'days' ); ?> left
			</p>
			<button class="button-primary" onclick="handlePurchase()" id="purchase">Purchase Plan</button>
		</div>
	<?php } ?>
	<h1 class="wp-heading-inline"> Settings</h1>
	<hr class="wp-header-end">
	<?php if (null != $messgae_res) { ?>
		<p style="
	color: green;
	font-weight: bold;
	padding: 10px;
	border: green solid;
"> 	<?php echo esc_html( $messgae_res ); ?> </p>
	<?php 
	}


	global $wpdb;
	if (isset($_POST['submit'])) {
		if (isset($_POST['bg_color'])) {
			$clrbak = sanitize_text_field($_POST['bg_color']);
		}
		if (isset($_POST['txt_color'])) {
			$clrtxt = sanitize_text_field($_POST['txt_color']);
		}
		if (isset($_POST['btn_color'])) {
			$clrbtn = sanitize_text_field($_POST['btn_color']);
		}
		if (isset($_POST['heading'])) {
			$fnthed = sanitize_text_field($_POST['heading']);
		}
		if (isset($_POST['text'])) {
			$fnttxt = sanitize_text_field($_POST['text']);
		}
		if (isset($_POST['button'])) {
			$fntbtn = sanitize_text_field($_POST['button']);
		}
		if (isset($_POST['label'])) {
			$fntlbl = sanitize_text_field($_POST['label']);
		}

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}dbargain_style SET colour = %s WHERE object = %s",
				$clrbak,
				'backgroundcolor'
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}dbargain_style SET colour = %s WHERE object = %s",
				$clrtxt,
				'textcolor'
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}dbargain_style SET colour = %s WHERE object = %s",
				$clrbtn,
				'buttoncolor'
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}dbargain_style SET font = %s WHERE object = %s",
				$fnthed,
				'headings'
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}dbargain_style SET font = %s WHERE object = %s",
				$fnttxt,
				'text'
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}dbargain_style SET font = %s WHERE object = %s",
				$fntbtn,
				'button'
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}dbargain_style SET font = %s WHERE object = %s",
				$fntlbl,
				'label'
			)
		);
	
	}
	?>
	<form name="edit_plaza" action="admin.php?page=dbargain_settings" method="post" id="post">

		<?php wp_nonce_field('dbargain-settings'); ?>
		<br><br>
		<h2>Number of bargaining attempts allowed per session</h2><span id="test"></span>

		<table class="form-table" role="presentation">
			<tbody>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Upper Limit<span class="info-icon" title="This is the maximum number of bargaining attempts; DBargain will allow buyer random number of bargaining attempts between the Upper Limit and Lower Limit. if the Upper Limit is 9 and Lower Limit is 3, then the buyer will be allowed a minimum of 3 and maximum 9 bargaining attempts.">?</span></th>
					<td>
						<label for="upper_limit"><input type="text" name="upper_limit" id="upper_limit"
								value="<?php echo esc_html( get_option('dbargain_session_upper_limit') ? get_option('dbargain_session_upper_limit') : '' ); ?>"
								class="regular-text">
						</label>
					</td>
				</tr>

			</tbody>
		</table>
		<br><br>
		<h2>Global Settings</h2>
		<table class="form-table" role="presentation">
			<tbody>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Bargaining Price Threshold Percentage <span class="info-icon" title="Enter the maximum discount percentage that will be allowed for bargaining on an individual product.">?</span></th>
					<td>
						<label for="threshold"><input type="number" name="threshold" id="threshold"
								value="<?php echo esc_html( get_option('dbargain_threshold') ? get_option('dbargain_threshold') : '' ); ?>"
								class="regular-text">
						</label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Start Date<span class="info-icon" title="Enter the date when bargaining will start for an individual product.">?</span></th>
					<?php echo esc_html( get_option('dbargain_start_date') ); ?>
					<td>
						<label for="start_date"><input type="text" name="start_date" id="start_date"
								value="<?php echo esc_html( get_option('dbargain_start_date') ? get_option('dbargain_start_date') : '' ); ?>"
								class="regular-text datepicker">
						</label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap">
					<th scope="row">End Date<span class="info-icon" title="Enter the date when bargaining will end for an individual product.">?</span></th>
					<td>
						<label for="end_date"><input type="text" name="end_date" id="end_date"
								value="<?php echo esc_html( get_option('dbargain_end_date') ? get_option('dbargain_end_date') : '' ); ?>"
								class="regular-text datepicker">
						</label>
					</td>
				</tr>
			</tbody>
		</table>
		<br><br>
		<h2>Frontend Window Settings</h2>

		<table class="form-table" role="presentation">
			<tbody>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Display Popup When</th>
					<td>
						<input type="checkbox" id="exit" name="criteria[]" value="exit" 
							<?php 
							if (in_array('exit', $criteria)) {
								echo 'checked="checked"';
							} 
							?>
						>
						<label for="exit">Display Popup When User Exit Page<span class="info-icon" title="If this option is checked it will be the first condition when the bargain Popup will be displayed">?</span></label><br>
						<input type="checkbox" id="delay" name="criteria[]" value="delay" onclick="jQuery('#time_delay').toggle();jQuery('#chat_time_delay').toggle();" 
							<?php 
							if (in_array('delay', $criteria)) { 
								echo 'checked="checked"'; 
							} 
							?>
							>
						<label for="delay">Display Popup When User has spent certain time on page<span class="info-icon" title="This field works in conjunction with Window Popup Delay field. If this option is checked it will be the second condition when the bargain Popup will be displayed after the number of seconds as specified in the Window Popup Delay field.">?</span></label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap" id="time_delay" 
					<?php 
					if ( !in_array( 'delay', $criteria ) ) {
						echo 'style="display: none"';
					} 
					?>
					>
					<th scope="row">Window Popup delay (seconds)<span class="info-icon" title="Enter the time the buyer spends on a product page in seconds before the bargain Popup will be displayed this field works in conjunction with the Display Popup Window option which must be Checked for the second condition for the bargain Popup to be displayed. ">?</span></th>
					<td>
						<label for="delay"><input type="number" name="delay" id="seconds"
								value="<?php echo esc_html( get_option('dbargain_window_delay') ? get_option('dbargain_window_delay') : '10' ); ?>"
								class="small-text">
						</label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap" id="chat_time_delay" 
					<?php 
					if (!in_array('delay', $criteria)) {
						echo 'style="display: none"';
					}
					?>
					>
					<th scope="row">Window Chat delay (seconds)<span class="info-icon" title="Enter the time the buyer spends on a product page in seconds before the Chat Box will be displayed this field works in conjunction with the Display Popup Window option which must be Checked for the second condition for the bargain Popup to be displayed. ">?</span></th>
					<td>
						<label for="chat_delay"><input type="number" name="chat_delay" id="chat_seconds"
								value="<?php echo esc_html( get_option('dbargain_window_chat_delay') ? get_option('dbargain_window_chat_delay') : '10' ); ?>"
								class="small-text">
						</label>
					</td>
				</tr>

				<tr class="user-rich-editing-wrap" id="chat_time_delay">
					<th scope="row">Chat Agent Name<span class="info-icon" title="Enter the name of your choice for the Chat agent; this name will appear on the top of the Bargain Popup Chat box.">?</span></th>
					<td>
						<label for="agent_name"><input type="text" name="agent_name" id="agent_name"
								value="<?php echo esc_html( get_option('dbargain_agent_name') ? get_option('dbargain_agent_name') : 'Jone D' ); ?>"
								class="regular-text">
						</label>
					</td>
				</tr>
			</tbody>
		</table>
		<br><br>
		<h2>Color Scheme</h2>

		<table class="form-table" role="presentation">
			<tbody>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Background Color<span class="info-icon" title="Enter Chat Background Color that will appear in the Chat box.">?</span></th>
					<td>
						<label for="bg_color"><input type="text" name="bg_color" id="bg_color"
								value="<?php echo esc_html( get_option('dbargain_bg_color') ? get_option('dbargain_bg_color') : '' ); ?>"
								class="regular-text cpa-color-picker">
						</label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Text Color<span class="info-icon" title="Enter Chat Font Color that will appear in the Chat box.">?</span></th>
					<td>
						<label for="txt_color"><input type="text" name="txt_color" id="txt_color"
								value="<?php echo esc_html( get_option('dbargain_txt_color') ? get_option('dbargain_txt_color') : '' ); ?>"
								class="regular-text cpa-color-picker">
						</label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Button Color<span class="info-icon" title="Enter Chat Button Color that will appear in the Chat box.">?</span></th>
					<td>
						<label for="btn_color"><input type="text" name="btn_color" id="btn_color"
								value="<?php echo esc_html( get_option('dbargain_btn_color') ? get_option('dbargain_btn_color') : '' ); ?>"
								data-default-color="<?php echo esc_html( get_option('dbargain_btn_color') ? get_option('dbargain_btn_color') : '' ); ?>"
								class="regular-text cpa-color-picker">
						</label>
					</td>
				</tr>
			</tbody>
		</table>
		<br><br>
		<h2>Typography</h2>

		<table class="form-table" role="presentation">
			<tbody>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Headings</th>
					<td>
						<label for="heading">
							<select name="heading" id="heading">
								<option value="Arial">Arial</option>
								<option value="Verdana">Verdana</option>
								<option value="Helvetica">Helvetica</option>
								<option value="sans-serif">Sans Serif</option>
							</select>
						</label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Text</th>
					<td>
						<label for="text">
							<select name="text" id="text">
								<option value="Arial">Arial</option>
								<option value="Verdana">Verdana</option>
								<option value="Helvetica">Helvetica</option>
								<option value="sans-serif">Sans Serif</option>
							</select>
						</label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Button</th>
					<td>
						<label for="button">
							<select name="button" id="button">
								<option value="Arial">Arial</option>
								<option value="Verdana">Verdana</option>
								<option value="Helvetica">Helvetica</option>
								<option value="sans-serif">Sans Serif</option>
							</select>
						</label>
					</td>
				</tr>
				<tr class="user-rich-editing-wrap">
					<th scope="row">Label</th>
					<td>
						<label for="label">
							<select name="label" id="label">
								<option value="Arial">Arial</option>
								<option value="Verdana">Verdana</option>
								<option value="Helvetica">Helvetica</option>
								<option value="sans-serif">Sans Serif</option>
							</select>
						</label>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
		</p>
	</form>
</div>
<script>
	function handlePurchase() {
		window.location = 'https://wp.d-bargain.link/subscribe/<?php echo esc_html( get_option('dbargain_session_id') ); ?>';
	}
</script>
