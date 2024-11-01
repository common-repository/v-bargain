<?php
error_reporting(E_ERROR | E_PARSE);

session_start();
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
// require the All Offers class which extends WP_LIST_TABLE class to render WP default list view
require_once WP_PLUGIN_DIR . '/v-bargain/include/class-alloffers.php';

$all_offers = new All_Offers();
?>

<head>
	<style>
		.notice {
			display: none;
		}
		
		section.rio {
			background: #ffffffa3;
			display: grid;
			width: 98.5%;
			height: -webkit-fill-available;
			place-items: center;
			position: absolute;
			border-radius: 9px;
		}

		.paytm-loader {
			color: #002e6e;
			width: 3px;
			aspect-ratio: 1;
			border-radius: 50%;
			box-shadow: 19px 0 0 7px, 38px 0 0 3px, 57px 0 0 0;
			transform: translateX(-38px);
			animation: loader 0.5s infinite alternate linear;
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

		@keyframes loader {
			50% {
				box-shadow: 19px 0 0 3px, 38px 0 0 7px, 57px 0 0 3px;
			}

			100% {
				box-shadow: 19px 0 0 0, 38px 0 0 3px, 57px 0 0 7px;
			}
		}
	</style>
	<script>
		jQuery(document).ready(function () {
			jQuery(".rio").hide();

			jQuery(".show_detail").click(function () {
				jQuery(".rio").show();
				setTimeout(function () {
					jQuery(".rio").hide();
				}, 1500);
			});
		});
	</script>
</head>
<div class="wrap">
	<?php if ($_SESSION['dbargain-status']) { ?>
		<div style="float: right;text-align:center"><span class="badge badge-success">
				<?php echo esc_html( $_SESSION['dbargain-status'] ); ?>
			</span>
			<p style="margin-top:5px;text-align: center;">
				<?php echo esc_html( $_SESSION['dbargain-days-left'] ); ?>
				<?php echo 1 == $_SESSION['dbargain-days-left'] ? 'day' : 'days'; ?> left
			</p>
			<button class="button-primary" onclick="handlePurchase()" id="purchase">Purchase Plan</button>
		</div>
	<?php } ?>
	<h1 class="wp-heading-inline">All Offers</h1>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-1">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<section class="rio">
						<div class="paytm-loader"></div>
					</section>
					<form method="post">
						<?php
						$all_offers->prepare_items();
						$all_offers->display();
						?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>
<script>
	function handlePurchase() {
		window.location = 'https://wp.d-bargain.link/subscribe/<?php echo esc_html( get_option('dbargain_session_id') ); ?>';
	}
</script>
