<?php
error_reporting(E_ERROR | E_PARSE);

session_start();
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$current_user_info = wp_get_current_user();
$current_user_name = $current_user_info->first_name;
$current_user_email = $current_user_info->user_email;
$site_domain = home_url('/');
?>

<head>
	<style>
		.badge-success {
			background-color: green;
			width: fit-content;
			height: auto;
			border-radius: 6px;
			padding: 5px;
			font-size: 12px;
			color: white;
			font-weight: 600;
		}
		
		.notice {
			display: none;
		}

		.wrap {
			background-color: white;
			border: 1px solid lightgray;
			border-radius: 5px;
			text-align: center;
			padding: 3em;
			max-width: 60%;
			margin: 9% auto !important;
		}
	</style>
</head>
<div class="wrap">
	<h1 class="wp-heading-inline">Trial Expired</h1>
	<br>
	<p>Your trial period has been expire please purchase plan to continue</p>
	<br>
	<button class="button-primary" onclick="handlePurchase()" id="purchase">Purchase Plan</button>
</div>
<script>
	function handlePurchase() {
		window.location = 'https://wp.d-bargain.link/subscribe/<?php echo esc_html( get_option('dbargain_session_id') ); ?>';
	}
</script>
