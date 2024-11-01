<?php

// session_start();
class DBargain {
	/*
		function __construct() {
			return $this;
		}
	*/

	public static function activate() {
		flush_rewrite_rules();

		global $pt_db_version, $wpdb;
		$pt_db_version = '1.0';

		$dbargain_table_name = $wpdb->prefix . 'dbargain_reports';
		$session_table_name = $wpdb->prefix . 'dbargain_session';
		$response_table_name = $wpdb->prefix . 'dbargain_responses';
		$colours_table_name = $wpdb->prefix . 'dbargain_style';

		$charset_collate = $wpdb->get_charset_collate();

		$sql_dbargain = "CREATE TABLE IF NOT EXISTS $dbargain_table_name (
                                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                                session_id varchar(128) NULL,
                                user_id mediumint(9) NULL,
                                product_id mediumint(9) NOT NULL,
                                quantity mediumint(9) NOT NULL,
                                order_price float(24) NULL,
                                date_created datetime NOT NULL,
                                PRIMARY KEY (ID)
                            ) $charset_collate;";

		$sql_session = "CREATE TABLE IF NOT EXISTS $session_table_name (
                                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                                session_id VARCHAR(128) NOT NULL,
                                product_id mediumint(9) NOT NULL,
                                offer float(24) NULL,
                                message text NULL,
                                status tinyint(4) NOT NULL,
                                date_created datetime default now(),
                                PRIMARY KEY (ID)
                            ) $charset_collate;";

		$sql_responses = "CREATE TABLE IF NOT EXISTS $response_table_name (
                                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                                percentage_difference mediumint(4) NULL,
                                `condition` VARCHAR(128) NULL,
                                message text NOT NULL,
                                PRIMARY KEY (ID)
                            ) $charset_collate;";

		$sql_colours = "CREATE TABLE IF NOT EXISTS $colours_table_name ( 
                                ID mediumint(9) NOT NULL AUTO_INCREMENT, 
                                object varchar(128) NULL, 
                                colour varchar(128) NULL, 
                                font varchar(128) NULL, 
                                PRIMARY KEY (ID) 
                            ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		dbDelta($sql_dbargain);
		dbDelta($sql_session);
		dbDelta($sql_responses);
		dbDelta($sql_colours);

		$wpdb->query("INSERT INTO {$wpdb->prefix}dbargain_style (`object`, `colour`, `font`) VALUES ('backgroundcolor', '#fff' ,''),('textcolor', '#000' ,''),('buttoncolor', '#293239' ,''),('headings', '','Sans Serif'),('text', '','Sans Serif'),('button', '','Sans Serif'),('label', '','Sans Serif');");
		$wpdb->query("delete from {$wpdb->prefix}dbargain_responses");
		$wpdb->query("insert  into {$wpdb->prefix}dbargain_responses(`ID`,`percentage_difference`,`condition`,`message`) values (1,10,'less','Your offer is very good but you are just slightly under our accepted limit. Please try again'),(2,20,'less','You are getting closer but still you need to give a better offer'),(3,30,'less','We don\'t wish to be rude but this product is worth a bit more than what you are offering'),(4,40,'less','The offer doesn\'t match with the worth of product. Please improve'),(5,50,'less','You need to give a better offer than this'),(6,60,'less','Please make a serious offer considering the worth of the product.'),(7,NULL,'more','Thankyou for your enthusiasm but the offer you are making, is above the product price itself. You can buy the product on original price'),(8,NULL,'success','Success!! Thankyou for the great offer. You can buy the product on your offered price'),(9,NULL,'warning','You have exhausted all your attempts. Considering your interest and effort, we are going to grant you one last chance. Please give us your best offer this time.'),(10,NULL,'welcome','Welcome, Thankyou for taking interest in this product. Please give us your best offer.'),(11,NULL,'failure','We are sorry but you have exhausted all your chances. Considering your interest and effort, we can let you buy this product on a discounted price still. Our final price for you is: ');");

		$current_user = wp_get_current_user();
		$current_user_email = $current_user->user_email;
		$current_user_name = $current_user->display_name;
		$domain = home_url('/');

		$xml = http_build_query(
			array(
				'user_name' => $current_user_name,
				'domain' => $domain,
				'email' => $current_user_email,
				'platform' => 'wordpress',
			)
		);

		$add = 'api/auth/register';

		// $url = 'https://api.d-bargain.link/'.$add;

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$resultt = curl_exec($ch);

		curl_close($ch);
		$responset = json_decode($resultt, true);


		
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$dbargain_session_id = '';

		if (get_option('dbargain_session_id')) {
			$dbargain_session_id = get_option('dbargain_session_id');
		} else {
			for ($i = 0; $i < 20; $i++) {
				$dbargain_session_id .= $characters[rand(0, strlen($characters) - 1)];
			}
			add_option('dbargain_session_id', $dbargain_session_id);
		}

		$xml1 = http_build_query(
			array(
				'domain' => $domain,
				'email' => $current_user_email,
				'session_id' => $dbargain_session_id,
				'name' => $current_user_name
			)
		);

		$add1 = 'api/register';

		// $url1 = 'https://wp.d-bargain.link/' . $add1;

		$ch1 = curl_init($url1);

		curl_setopt($ch1, CURLOPT_POST, 1);
		curl_setopt($ch1, CURLOPT_POSTFIELDS, $xml1);
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch1);

		curl_close($ch1);

		$response = json_decode($result, true);

		add_option('pt_db_version', $pt_db_version);
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public static function uninstall() {
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dbargain_reports");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dbargain_session");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dbargain_responses");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dbargain_style");

		delete_option('pt_db_version');
	}

	public static function register() {
		//Actions

		/* Hook into the 'init' action so that the function
		 * Containing our post type registration is not
		 * unnecessarily executed.
		 */

		// Add an AJAX endpoint to fetch the HTML content

		add_action('admin_menu', [self::class, 'add_admin_pages']);
		// Register javascript
		add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_js']);
		add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend_js']);
		add_action('wp_ajax_nopriv_fetch_details', [self::class, 'fetch_details']);
		add_action('wp_ajax_fetch_details', [self::class, 'fetch_details']);
		// The code for displaying WooCommerce Product Custom Fields
		add_action('woocommerce_product_options_general_product_data', [self::class, 'dbargain_fields']);
		// Following code Saves  WooCommerce Product Custom Fields
		add_action('woocommerce_process_product_meta', [self::class, 'dbargain_fields_save']);
		//Code for adding DBargain button on frontend
		add_action('woocommerce_after_single_product', [self::class, 'render_dbargain_window']);
		//Code for handling chat
		add_action('wp_ajax_make_offer', [self::class, 'make_offer']);
		add_action('wp_ajax_nopriv_make_offer', [self::class, 'make_offer']);
		//Code for overriding cart prices
		add_action('woocommerce_before_calculate_totals', [self::class, 'update_cart_prices'], 10, 1);
		add_action('woocommerce_thankyou', [self::class, 'custom_process_order'], 10, 1);
		//Code to unset session data after checkout
		add_action('woocommerce_thankyou', [self::class, 'unset_session'], 10, 1);
		// Hook into the WooCommerce order completed action
		add_action('woocommerce_order_status_completed', 'insert_sold_quantity');
		add_action('wp_ajax_purchase', [self::class, 'purchase']);
	}

	public static function custom_process_order($order_id) {

		$url_2 = 'https://api.d-bargain.link/api/products/get-store';
		
		$domain = home_url('/');

		$data2 = http_build_query(
			array(
				'domain' => $domain
			)
		);

		$ch2 = curl_init($url_2);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $data2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); 

		$response2 = curl_exec($ch2);
        curl_close($ch2);

        $customerData = json_decode($response2, true);
		$store_id = $customerData['store'];


		$order = wc_get_order($order_id); 
		$order_items = $order->get_items();

		foreach ( $order_items as $item ){
			$product_id = $item->get_product_id();
			$product_name = $item->get_name();
			$product = $item->get_product();
			$product_price = $product->get_regular_price(); 
		
			$add = 'api/products/create-product';

			$url = 'https://api.d-bargain.link/' . $add;

			$data = http_build_query(
					array(
						'store_id' => $store_id,
						'product_id' => $product_id,
						'product_price' => $product_price,
						'product_name' => $product_name
					)
				);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			curl_close($ch);
			$response = json_decode($result, true);



			$url_1 = "https://api.d-bargain.link/api/products/get-productid";

			$data_1 = http_build_query(
				array(
					'product_id' => $product_id
				)
			);

			$ch_1 = curl_init($url_1);
			curl_setopt($ch_1, CURLOPT_POST, 1);
			curl_setopt($ch_1, CURLOPT_POSTFIELDS, $data_1);
			curl_setopt($ch_1, CURLOPT_RETURNTRANSFER, true);

			$result_1 = curl_exec($ch_1);
			$response_1 = json_decode($result_1, true);

			$productID = $response_1['store'];



			$url_3 = "https://api.d-bargain.link/api/order/store";
			$final_price = $_SESSION['dbargain'][$product_id]['dbargain_price'];
			$session_id = $_SESSION['dbargain'][$product_id]['session_id'];

			global $wpdb;
			$offer_data = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}dbargain_session WHERE session_id = %s AND product_id = %s",
					$session_id,
					$product_id
				),
				ARRAY_A
			);

			$data_3 = http_build_query([
					'order_data' => [
							'store_id' => $store_id,
							'session_id' => $session_id,
							'order_id' => $order_id, 
							'product_id' => $productID, 
							'final_price' => $final_price, 
							'status' => 1,
							'session_data' => $offer_data
						]
				]);

			$ch_3 = curl_init($url_3);
			curl_setopt($ch_3, CURLOPT_POST, 1);
			curl_setopt($ch_3, CURLOPT_POSTFIELDS, $data_3);
			curl_setopt($ch_3, CURLOPT_HTTPHEADER,
						array(
							'Authorization: Bearer ' . $_SESSION['token'],
							'Content-Type: application/x-www-form-urlencoded',
						)
					);

			$result_3 = curl_exec($ch_3);
			$response_3 = json_decode($result_3, true);
		}
	}



	public static function unset_session( $order_id ) {
		if (isset($_SESSION['dbargain'])) {
			unset( $_SESSION['dbargain'] );
			session_regenerate_id();
		}
		if (isset($_SESSION['offerdata'])) {
			unset( $_SESSION['offerdata'] );
		}
	}

	public static function update_cart_prices( $cart ) {
		// This is necessary for WC 3.0+
		if ( is_admin() && !defined('DOING_AJAX') ) { 
			return;
		}

		// Avoiding hook repetition (when using price calculations for example | optional)
		if ( did_action('woocommerce_before_calculate_totals') >= 2 ) {
			return;
		}

		// Loop through cart items
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$id = $cart_item['data']->get_id();

			if ( isset( $_SESSION['dbargain'][$id]['dbargain_price'] ) && !empty( $_SESSION['dbargain'][$id]['dbargain_price'] ) ) {
				$cart_item['data']->set_price($_SESSION['dbargain'][$id]['dbargain_price']);
			}
		}
	}

	public static function update_price_mini_cart( $price_html, $cart_item, $cart_item_key ) {
		$id = $cart_item['data']->get_id();

		if (isset($_SESSION['dbargain'][$id]['dbargain_price']) && !empty($_SESSION['dbargain'][$id]['dbargain_price'])) {
			$price = ['price' => $_SESSION['dbargain'][$id]['dbargain_price']];
			if (WC()->cart->display_prices_including_tax()) {
				$product_price = wc_get_price_including_tax($cart_item['data'], $price);
			} else {
				$product_price = wc_get_price_excluding_tax($cart_item['data'], $price);
			}
			return wc_price($product_price);
		}

		return $price_html;
	}

	//start session
	public static function start_session() {
		session_start();
		if (!isset($_SESSION['dbargain'])) {
			$_SESSION['dbargain'] = [];
		}
		if (!isset($_SESSION['offerdata'])) {
			$_SESSION['offerdata'] = [];
		}
		if (!isset($_SESSION['token'])) {
			$_SESSION['token'] = [];
		}
	}

	//Ajax function to get offer details  
	

	public static function fetch_details() {
		if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'dbargain-settings')) {// nosemgrep: scanner.php.wp.security.csrf.nonce-flawed-logic
			if (isset($_POST['id'])) {
				$product = wc_get_product( sanitize_text_field( $_POST['id'] ) );
				$pstid = sanitize_text_field($_POST['id']);
			}
		} else {
			return 'something went wrong';
		}
		
		global $wpdb;
		$data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT user_id
				FROM {$wpdb->prefix}dbargain_reports
				WHERE product_id = %d",
				$pstid
			),
			ARRAY_A
		);
		// $data = $wpdb->get_results("select distinct user_id from {$wpdb->prefix}dbargain_reports where product_id = '" . $pstid . "'", 'ARRAY_A');

		$image = wp_get_attachment_image_src(get_post_thumbnail_id($pstid), 'product');

		$html = "
                    <table class='wp-list-table widefat fixed table-view-list offers' border='0' width='100%'>
                        <tr>
                            <td width='10%'>
                                <img src='" . $image[0] . "' width='100px'>
                            </td>
                            <td width='11%'>
                                <p>#" . $pstid . '</p>
                                <p><h2>' . $product->get_title() . '</h2></p>
                                <p>' . $product->get_short_description() . '</p>
                                <p>' . wc_price($product->get_regular_price()) . "</p>
                            </td>
                            <td width='78%'>
                                <table class='wp-list-table widefat fixed table-view-list offers' border='0' width='100%'>
                                    <tr>
                                        <td style='background-color:#efefef'><b>Customer Name</b></td>
                                        <td style='background-color:#efefef'><b>Bargain Price</b></td>
                                        <td style='background-color:#efefef'><b>Past Orders</b></td>
                                        <td style='background-color:#efefef'><b>Email</b></td>
                                        <!--<td style='background-color:#efefef'><b>Phone</b></td>-->
                                        <td style='background-color:#efefef'><b>Status</b></td>
                                        <td style='background-color:#efefef'><b>Chat History</b></td>
                                    </tr>";
		foreach ($data as $d) {
			$user = get_user_by('id', $d['user_id']);
			$d_user_id = sanitize_text_field($d['user_id']);
			$session = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT session_id, order_price FROM {$wpdb->prefix}dbargain_reports WHERE user_id = %d AND product_id = %d ORDER BY order_price DESC LIMIT 0, 1",
					$d_user_id,
					$pstid
				),
				ARRAY_A
			);
			// $session = $wpdb->get_row("select session_id, order_price from {$wpdb->prefix}dbargain_reports where user_id = {$d_user_id} and product_id = {$_POST["id"]} order by order_price DESC limit 0,1", ARRAY_A);
			$html .= '
                                    <tr>
                                        <td>' . ( '0' == $d['user_id'] || null == $d['user_id'] ? 'Guest' : $user->display_name ) . '</td>
                                        <td>' . ( $session && $session['order_price'] > 1 ? $session['order_price'] : 'N/A' ) . '</td>
                                        <td>' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->prefix}dbargain_reports WHERE user_id = %s AND product_id = %s", $d_user_id, $pstid ) ) . '</td>
                                        <td>' . ( '0' == $d['user_id'] || null == $d['user_id'] ? '-' : $user->user_email ) . '</td>
                                        <!--<td>' . get_user_meta($d['user_id'], 'user_phone', true) . '</td>-->
                                        <td>' . ( ( isset($session['order_price']) && !empty($session['order_price']) ) ? 'Success' : 'Failed' ) . "</td>
                                        <td><a href='?page=chat&sid=" . $session['session_id'] . '&pid=' . $pstid . "'>Show Chat</a></td>
                                    </tr>";

		} //echo $session;

		$html .= "
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='3' style='text-align:right'><a href='javascript:;' onclick='hide_detail(" . $pstid . ")' class='hide_detail' rel='" . $pstid . "'>Hide Details</a></td>
                        </tr>
                    </table>
                    ";
		wp_send_json_success(['id' => $pstid, 'data' => $html]);
	}

	/**
	 * Function that will add javascript file for Color Piker.
	 */
	public static function enqueue_admin_js() {
		// Css rules for Color Picker
		wp_enqueue_style('wp-color-picker');
		// Make sure to add the wp-color-picker dependecy to js file
		wp_enqueue_script('dbargain_admin_js', plugins_url('assets/javascript.js', __FILE__), array('jquery', 'wp-color-picker'), '1.0', true);

		wp_enqueue_style('jquery-ui');
		wp_enqueue_script('jquery-ui-datepicker');
	}

	/**
	 * Function that will add javascript file for DBargain popup.
	 */
	public static function enqueue_frontend_js() {
		wp_enqueue_script('dbargain_frontend_js', plugins_url('assets/javascript_frontend.js', __FILE__), array('jquery'), '2.0', true);
	}

	//Function to create custom fields on product edit page
	public static function dbargain_fields() {
		echo '<div class="product_custom_field">
		<style>
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
		</style>';
		// DBargain Price Threshold Field
		woocommerce_wp_text_input(
			array(
				'id' => '_dbargain_price_threshold',
				'placeholder' => 'Bargain Price Threshold',
				'label' => __('Bargain Price Threshold<span class="info-icon" title="Enter the maximum discount percentage that will be allowed for bargaining on an individual product.">?</span>', 'woocommerce'),
				'desc_tip' => 'true'
			)
		);

		// DBargain Date Start Field
		woocommerce_wp_text_input(
			array(
				'id' => '_dbargain_date_start',
				'placeholder' => 'Bargain Start Date',
				'class' => 'datepicker',
				'label' => __('Bargain Start Date<span class="info-icon" title="Enter the date when bargaining will start for an individual product.">?</span>', 'woocommerce'),
				'desc_tip' => 'true'
			)
		);

		// DBargain Date End Field
		woocommerce_wp_text_input(
			array(
				'id' => '_dbargain_date_end',
				'placeholder' => 'Bargain End Date',
				'class' => 'datepicker',
				'label' => __('Bargain End Date<span class="info-icon" title="Enter the date when bargaining will end for an individual product.">?</span>', 'woocommerce'),
				'desc_tip' => 'true'
			)
		);
		echo '</div>';
	}

	//Function to save custom fields from product edit page
	public static function dbargain_fields_save() {
		global $post_id;
		if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'dbargain-settings')) {// nosemgrep: scanner.php.wp.security.csrf.nonce-flawed-logic
			// DBargain Price Threshold Field
			if (isset($_POST['_dbargain_price_threshold'])) {
				$price_threshold = sanitize_text_field( $_POST['_dbargain_price_threshold'] );
				if (!empty($price_threshold)) {
					update_post_meta($post_id, '_dbargain_price_threshold', esc_attr($price_threshold));
				}
			}
			
			// DBargain Price Threshold Field
			if ( isset( $_POST['_dbargain_date_start'] ) ) {
				$start_date = sanitize_text_field( $_POST['_dbargain_date_start'] );
				if ( !empty($start_date) ) {
					update_post_meta($post_id, '_dbargain_date_start', esc_attr($start_date));
				}
			}
			
			// DBargain Price Threshold Field
			if ( isset( $_POST['_dbargain_date_end'] ) ) {
				$end_date = sanitize_text_field( $_POST['_dbargain_date_end'] );
				if (!empty($end_date)) {
					update_post_meta($post_id, '_dbargain_date_end', esc_attr($end_date));
				}
			}
		} else {
			return response()->json(['error' => 'something went wrong']);
		}
	}

	//Function to create admin menu
	public static function add_admin_pages() {
		add_menu_page('D-Bargain', 'DBargain', 'manage_options', 'dbargain', [self::class, 'all_offers'], 'dashicons-admin-settings', 100);
		add_submenu_page('dbargain', 'All Offers', 'All Offers', 'manage_options', 'dbargain', [self::class, 'all_offers']);
		add_submenu_page('dbargain', 'Settings', 'Settings', 'manage_options', 'dbargain_settings', [self::class, 'settings']);
		// add_submenu_page('dbargain', 'Reports', 'Reports', 'manage_options', 'reports', [self::class, 'reports']);
		add_submenu_page('', 'Session Chat History', 'Session Chat History', 'manage_options', 'chat', [self::class, 'chat']);
		add_submenu_page('dbargain', 'User Guide', 'User Guide', 'manage_options', 'user_guide', [self::class, 'user_guide']);
	}

	//Function to render all offers admin menu page template
	public static function all_offers() {
		$current_user = wp_get_current_user();
		$current_user_email = $current_user->user_email;
		$domain = home_url('/');
		$xml1 = http_build_query([
			'domain' => $domain,
			'email' => $current_user_email,
			'session_id' => get_option('dbargain_session_id')
		]);

		$add1 = 'api/check-expiry';

		$url1 = 'https://wp.d-bargain.link/' . $add1;

		$ch1 = curl_init($url1);

		curl_setopt($ch1, CURLOPT_POST, 1);
		curl_setopt($ch1, CURLOPT_POSTFIELDS, $xml1);
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch1);

		curl_close($ch1);

		$response = json_decode($result, true);

		if (200 == $response['status']) {
			if ('Trial Period' == $response['message']) {
				$_SESSION['dbargain-status'] = $response['message'];
				$_SESSION['dbargain-days-left'] = $response['days'];
			} else {
				unset($_SESSION['dbargain-status']);
				unset($_SESSION['dbargain-days-left']);
			}
			require_once DBARGAIN_PLUGIN_PATH . 'templates/all-offers.php';
		} else {
			require_once DBARGAIN_PLUGIN_PATH . 'templates/expire.php';
		}
	}

	//Function to render settings admin menu page template
	public static function settings() {
		$current_user = wp_get_current_user();
		$current_user_email = $current_user->user_email;
		$domain = home_url('/');
		$xml1 = http_build_query(
			array(
				'domain' => $domain,
				'email' => $current_user_email,
				'session_id' => get_option('dbargain_session_id')
			)
		);

		$add1 = 'api/check-expiry';
		$url1 = 'https://wp.d-bargain.link/' . $add1;
		$ch1 = curl_init($url1);

		curl_setopt($ch1, CURLOPT_POST, 1);
		curl_setopt($ch1, CURLOPT_POSTFIELDS, $xml1);
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch1);

		curl_close($ch1);

		$response = json_decode($result, true);
		// print_r($response);
		// die;
		if (200 == $response['status']) {
			if ('Trial Period' == $response['message']) {
				$_SESSION['dbargain-status'] = $response['message'];
				$_SESSION['dbargain-days-left'] = $response['days'];
			} else {
				unset($_SESSION['dbargain-status']);
				unset($_SESSION['dbargain-days-left']);
			}
			require_once DBARGAIN_PLUGIN_PATH . 'templates/settings.php';
		} else {
			require_once DBARGAIN_PLUGIN_PATH . 'templates/expire.php';
		}
	}

	//Function to render reports admin menu page template
	public static function reports() {
		require_once DBARGAIN_PLUGIN_PATH . 'templates/reports.php';
	}

	public static function user_guide() {
		require_once DBARGAIN_PLUGIN_PATH . 'templates/userguide.php';
	}

	//Function to render chat history admin menu page template
	public static function chat() {
		require_once DBARGAIN_PLUGIN_PATH . 'templates/chat.php';
	}

	

	//Function to render DBargain Window
	public static function render_dbargain_window() {
		global $wpdb, $product;
		$id = $product->get_id();

		$session_upper_limit = get_option('dbargain_session_upper_limit');
		$session_lower_limit = get_option('dbargain_session_lower_limit');
		$btn_color = get_option('dbargain_btn_color');
		$button_font = get_option('dbargain_button_font');
		$window_layout = get_option('dbargain_window_layout');
		$display_criteria = get_option('dbargain_display_criteria');
		$window_delay = get_option('dbargain_window_delay');
		$window_chat_delay = get_option('dbargain_window_chat_delay');
		$global_threshold = get_option('dbargain_threshold');
		$global_start = get_option('dbargain_start_date');
		$global_end = get_option('dbargain_end_date');
		$agent_name = get_option('dbargain_agent_name', 'Jone D');

		$threshold = get_post_meta($id, '_dbargain_price_threshold', true);
		$start_date = get_post_meta($id, '_dbargain_date_start', true);
		$end_date = get_post_meta($id, '_dbargain_date_end', true);

		if ('' == $session_upper_limit) {
			$session_upper_limit = '5';
		}
		if ('' == $session_lower_limit) {
			$session_lower_limit = '3';
		}

		if (!isset($_SESSION['dbargain'][$id]) || !is_array($_SESSION['dbargain'][$id])) {
			$_SESSION['dbargain'][$id] = ['attempts' => rand($session_lower_limit, $session_upper_limit), 'session_id' => session_id()];

			$message = $wpdb->get_var("select message from {$wpdb->prefix}dbargain_responses where `condition` = 'welcome' order by rand() limit 1");

			if ( empty( $message ) ) {
				$message = 'Welcome, Thankyou for taking interest in this product. Please give us your best offer.';
			}

			$wpdb->insert($wpdb->prefix . 'dbargain_reports', ['session_id' => $_SESSION['dbargain'][$id]['session_id'], 'product_id' => $id, 'user_id' => get_current_user_id(), 'date_created' => 'now()']);
			$wpdb->insert($wpdb->prefix . 'dbargain_session', ['session_id' => $_SESSION['dbargain'][$id]['session_id'], 'product_id' => $id, 'message' => $message, 'status' => '0', 'date_created' => gmdate('Y-m-d H:i:s')]);
		}
		$data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
				FROM {$wpdb->prefix}dbargain_session
				WHERE session_id = %s AND product_id = %d",
				$_SESSION['dbargain'][$id]['session_id'],
				$id
			),
			ARRAY_A
		);
		// $data = $wpdb->get_results("select * from {$wpdb->prefix}dbargain_session where session_id = '" . $_SESSION['dbargain'][$id]['session_id'] . "' and product_id = {$id}", ARRAY_A);


		if ('on' === isset($_SERVER['HTTPS']) && sanitize_text_field( $_SERVER['HTTPS'] ) ) {
			$urlact = 'https://';
		} else {
			$urlact = 'http://';
		}

		if (isset($_SERVER['HTTP_HOST'])) {
			// Append the host (domain name, ip) to the URL
			$urlact = esc_url_raw($_SERVER['HTTP_HOST']);
		}

		if (isset($_SERVER['REQUEST_URI'])) {
			// Append the requested resource location to the URL
			$urlact = esc_url_raw($_SERVER['REQUEST_URI']);
		}

		$html = '<form class="cart" id="cust-act" action="' . $urlact . '" method="post" enctype="multipart/form-data">
            <style>
                    /* The Modal (background) */
                    .modal {
                    display: none; /* Hidden by default */
                    position: fixed; /* Stay in place */
                    z-index: 1; /* Sit on top */
                    left: 0;
                    top: 0;
                    width: 100%; /* Full width */
                    height: 100%; /* Full height */
                    /* overflow: auto; Enable scroll if needed */
                    background-color: rgb(0,0,0); /* Fallback color */
                    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
                    }

                    /* Modal Content/Box */
                    .modal-content {
                    background-color: #fcfcfc;
                    margin: 10% auto; /* 15% from the top and centered */
                    border: 1px solid #888;
                    width: 50%; /* Could be more or less, depending on screen size */
                    }

                    /* The Close Button */
                    .close {
                    color: #aaa;
                    float: right;
                    font-size: 28px;
                    font-weight: bold;
                    }

                    .close:hover,
                    .close:focus {
                    color: black;
                    text-decoration: none;
                    cursor: pointer;
                    }
                    @charset "utf-8";
                    /* CSS Document */

                    #chat_window{
                        height: 300px;
                        background-color: #fcfcfc;
                        color: #85be54;
                        padding: 2%;
                        margin: 2%;
                        border: #85be54 solid;
                    }
                    #chat_window img{
                        width: 100px;
                        height: 100px;
                        margin: 0 auto;
                        margin-top: 23px;
                        border: #85be54 solid;
                        padding: 9px;
                        border-radius: 60px;
                    }
                    #chat_window h1{
                        font-size: 40px;
                        text-align: center;
                        font-weight: bold;
                    }
                    #chat_window p{
                        font-size: 20px;
                        text-align: center;
                        font-weight: bold;
                        color: #9c9c9c;
                    }

                    
                    
                
                    @media only screen and (max-width: 600px) {
                        .modal-content {
                            width: 100%;
                        }
                    }
                    /* ---------- GENERAL ---------- */

                    body {
                        background: #e9e9e9;
                        color: #9a9a9a;
                        font: 100%/1.5em "Droid Sans", sans-serif;
                        margin: 0;
                    }

                    a { text-decoration: none; }

                    fieldset {
                        border: 0;
                        margin: 0;
                        padding: 0;
                    }

                    h4, h5 {
                        line-height: 1.5em;
                        margin: 0;
                    }

                    hr {
                        background: #e9e9e9;
                        border: 0;
                        -moz-box-sizing: content-box;
                        box-sizing: content-box;
                        height: 1px;
                        margin: 0;
                        min-height: 1px;
                    }

                    img {
                        border: 0;
                        display: block;
                        height: auto;
                        max-width: 100%;
                    }

                    input {
                        border: 0;
                        color: inherit;
                        font-family: inherit;
                        font-size: 100%;
                        line-height: normal;
                        margin: 0;
                    }

                    p { margin: 0; }

                    .clearfix { *zoom: 1; } /* For IE 6/7 */
                    .clearfix:before, .clearfix:after {
                        content: "";
                        display: table;
                    }
                    .clearfix:after { clear: both; }

                    /* ---------- LIVE-CHAT ---------- */

                    #db_live-chat {
                        bottom: 0;
                        font-size: 12px;
                        right: 24px;
                        position: fixed;
                        width: 300px;
                        z-index: 999999 !important;
                    }
                

                    #db_live-chat .header {
                        background: #293239;
                        border-radius: 5px 5px 0 0;
                        color: #fff !important;
                        cursor: pointer;
                        padding: 16px 24px;
                    }

                    #db_live-chat h4:before {
                        background: #1a8a34;
                        border-radius: 50%;
                        content: "";
                        display: inline-block;
                        height: 8px;
                        margin: 0 8px 0 0;
                        width: 8px;
                    }
                    #db_add_to_cart, #make_offer {
                        font-size:14px !important;
                        border-radius: 0px !important;
                    }

                    #db_live-chat h4 {
                        font-size: 12px;
                        color:#fff !important;
                        padding: 0px;
                    }

                    #db_live-chat h5 {
                        font-size: 10px;
                    }

                    #db_live-chat form, #db_live-chat .form {
                        padding: 24px;
                    }

                    #db_live-chat input[type="text"],#db_live-chat input[type="number"] {
                        border: 1px solid #ccc;
                        border-radius: 3px;
                        padding: 8px;
                        outline: none;
                        width: 130px;
                    }

                    .chat-message-counter {
                        background: #e62727;
                        border: 1px solid #fff;
                        border-radius: 50%;
                        display: none;
                        font-size: 12px;
                        font-weight: bold;
                        height: 28px;
                        left: 0;
                        line-height: 28px;
                        margin: -15px 0 0 -15px;
                        position: absolute;
                        text-align: center;
                        top: 0;
                        width: 28px;
                    }

                    .chat-close {
                        background: #1b2126;
                        border-radius: 50%;
                        color: #fff;
                        display: block;
                        float: right;
                        font-size: 10px;
                        height: 16px;
                        line-height: 16px;
                        margin: 2px 0 0 0;
                        text-align: center;
                        width: 16px;
                    }

                    .chat {
                        background: #fff;
                    }

                    .chat-history {
                        height: 252px;
                        padding: 8px 24px;
                        overflow-y: scroll;
                    }

                    .chat-message {
                        margin: 16px 0;
                    }

                    .chat-message img {
                        border-radius: 50%;
                        float: left;
                    }

                    .chat-message-content {
                        margin-left: 56px;
                    }

                    .chat-time {
                        float: right;
                        font-size: 10px;
                    }

                    .chat-feedback {
                        font-style: italic;	
                        margin: 0 0 0 80px;
                    }
                    </style>
                    ';

		global $wpdb;

		$bcclr = $wpdb->get_var("SELECT colour from {$wpdb->prefix}dbargain_style WHERE object = 'backgroundcolor';");
		$txtclr = $wpdb->get_var("SELECT colour from {$wpdb->prefix}dbargain_style WHERE object = 'textcolor';");
		$btnclr = $wpdb->get_var("SELECT colour from {$wpdb->prefix}dbargain_style WHERE object = 'buttoncolor';");
		$txtftn = $wpdb->get_var("SELECT font from {$wpdb->prefix}dbargain_style WHERE object = 'text';");
		$btnftn = $wpdb->get_var("SELECT font from {$wpdb->prefix}dbargain_style WHERE object = 'button';");

		$html .= ' <!-- Trigger/Open The Modal -->
                        <!-- <button type="button" class="single_add_to_cart_button button alt" id="btn" style="display:none;' . ( $btn_color ? 'background-color:' . $btn_color . ';' : '' ) . ( $button_font ? 'font-family:' . $button_font . ';' : '' ) . '">Open DBargain Window</button> -->
                        <!-- The Modal -->
                        <input type="hidden" id="time_limit" name="time_limit" value="' . ( ( !empty($window_delay) && in_array('delay', $display_criteria) ) ? $window_delay : '0' ) . '">
                        <input type="hidden" id="time_chat_limit" name="time_chat_limit" value="' . ( ( !empty($window_chat_delay) && in_array('delay', $display_criteria) ) ? $window_chat_delay : '0' ) . '">
                        <input type="hidden" id="exit" name="exit" value="' . ( ( !empty( $display_criteria ) && in_array( 'exit', $display_criteria ) ) ? 'exit' : '' ) . '">
                        <input type="hidden" id="window_layout" name="window_layout" value="' . ( !empty($window_layout) ? $window_layout : 'popup' ) . '">
                        <input type="hidden" name="ajax_url" id="ajax_url" value="' . admin_url('admin-ajax.php') . '">
                        <input type="hidden" name="session_id" id="session_id" value="' . $_SESSION['dbargain'][$id]['session_id'] . '">
                        <input type="hidden" name="product_id" id="product_id" value="' . $id . '">

                        
                        <div id="db_live-chat" style="display:none; z-index:9999;">
            
                            <div class="clearfix header">
                                
                                <a href="javascript:;" class="chat-close">-</a>

                                <h4>' . $agent_name . '</h4>

                              

                            </div>

                            <div class="chat" id="chat-box" style="background:' . $bcclr . ' !important; color:' . $txtclr . ' !important; font-family:' . $txtftn . ' !important;">
                                
                                <div class="chat-history" id="db_chat-history">

                                ';

		foreach ($data as $msg) {
			if (!empty($msg['message'])) {

				$html .= self::chat_message_response($agent_name, $msg['message'], $msg['offer']);

			} else {

				$html .= self::chat_message_response($agent_name, wc_price($msg['offer']), $msg['offer']);
			}
		}


		$html .= '    
                                </div> <!-- end chat-history -->


                                <div class="form"> <!-- form -->

                                    <fieldset>
                                ';
		if (!isset($_SESSION['dbargain'][$id]['dbargain_price']) || empty($_SESSION['dbargain'][$id]['dbargain_price'])) {
			$html .= '                                      
                                
                                                    <input type="number" name="offer" id="offer" value="" placeholder="Make your best offer" Offer>
                                                    <button type="button" class="buttr alt" id="make_offer" style="background-color:' . $btnclr . ' !important; font-family:' . $btnftn . ' !important;position: absolute;padding: 6px !important;color: #ffffff;">Submit Offer</button>
                                                    <button type="submit" name="add-to-cart" value="' . $id . '" class="single_add_to_cart_button buttr alt" id="db_add_to_cart" style="display:none;background-color:' . $btnclr . ' !important; font-family:' . $btnftn . ' !important;position: absolute;padding: 6px !important;color: #ffffff;margin: 0;">Add to Cart</button>
                                            ';
		} else {
			$html .= '     
                                                    <input type="number" name="offer" id="offer" value="" placeholder="Make your best offer" style="display:none;float: left; width: 60%; margin-right: 4px">
                                                    <button type="button" class="buttr alt" id="make_offer" style="background-color:' . $btnclr . ' !important; font-family:' . $btnftn . ' !important;position: absolute;padding: 6px !important;color: #ffffff;">Submit Offer</button>
                                                    <button type="submit" name="add-to-cart" value="' . $id . '" class="single_add_to_cart_button buttr alt" id="db_add_to_cart" style="display:none;background-color:' . $btnclr . ' !important; font-family:' . $btnftn . ' !important;position: absolute;padding: 6px !important;color: #ffffff;margin: 0;">Add to Cart</button>
                                            ';
		}

		$html .= ' </fieldset>
                                </div> <!-- form -->
                            </div> <!-- end chat -->

                        </div> <!-- end db_live-chat -->

                        <div id="myModal" class="modal" style="z-index:9999;">

                        <!-- Modal content -->
                        <div class="modal-content">
                            
                            <div id="chat_window" >
                                <img src="' . plugin_dir_url(__FILE__) . 'assets/cart_img.png" />
                                <h1> Hey Wait!</h1>
                                <p>You can negotiate the price, our representative will join you soon!</p>
                                ';


		$html .= '      </div>
                        </div>
                        </div>  
                        </form>';

		//show modal only on product detail page
		if($global_start) {
			$DBdateG = strtotime($global_start);
			$global_start = date("Y-m-d", $DBdateG);
		}

		if ($global_end) {
			$dateTime = DateTime::createFromFormat('l, j F, Y', $global_end);
			$global_end = $dateTime->format("Y-m-d");
		}

		if($start_date) {
			$DBdateS = strtotime($start_date);
			$start_date = date("Y-m-d", $DBdateS);
		}

		if($end_date) {
			$DBdateE = Date::createFromFormat($end_date);
			$end_date = $DBdateE->format("Y-m-d");
		}

		$tim = time();
		$systime = date("Y-m-d", $tim);
		
		if ( is_product() && ( ( !empty($global_threshold) && $global_threshold > 0 && !empty($global_start) && $global_start <= $systime && !empty($global_end) && $global_end >= $systime ) || ( !empty($threshold) && $threshold > 0 && !empty($start_date) && $start_date <= $systime && !empty($end_date) && $end_date >= $systime ) ) ) {
			echo esc_html_e( _e( $html ) );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped   // nosemgrep: audit.php.wp.security.xss.unescaped-stored-option
		}
	}

	//Ajax chat handler
	public static function make_offer() {

		$offerdata = array();
		$domain = home_url('/');
		$data = http_build_query(array('domain' => $domain));
		$add = 'api/auth/login';
		$url = 'https://api.d-bargain.link/' . $add;
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		$result = json_decode($result, true);
		$token = $result['data']['access_token'];
		$_SESSION['token'] = $token;

		curl_close($ch);

		if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'dbargain-settings')) {// nosemgrep: scanner.php.wp.security.csrf.nonce-flawed-logic
			if ( isset( $_POST['product_id'] ) ) {
				$product_id = sanitize_text_field( $_POST['product_id'] );
			}
			if ( isset( $_POST['offer'] ) ) {
				$post_offer = sanitize_text_field( $_POST['offer'] );
			}
		} else {
			return 'something went wrong';
		}

		try {
			// run your code here
			global $wpdb;
			$text_font = get_option('dbargain_text_font');
			$html = '';
			$agent_name = get_option('dbargain_agent_name', 'Jone D');

			if (!isset($_SESSION['dbargain'][$product_id]['dbargain_price']) || empty($_SESSION['dbargain'][$product_id]['dbargain_price'])) {
				$wpdb->insert($wpdb->prefix . 'dbargain_session', ['session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'product_id' => $product_id, 'offer' => $post_offer, 'status' => '0']);
			} else {
				$data = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT *
						FROM {$wpdb->prefix}dbargain_session
						WHERE session_id = %s AND product_id = %d",
						$_SESSION['dbargain'][$product_id]['session_id'],
						$product_id
					),
					ARRAY_A
				);
				// $data = $wpdb->get_results("select * from {$wpdb->prefix}dbargain_session where session_id = '" . $_SESSION['dbargain'][$product_id]['session_id'] . "' and product_id = {$product_id}", ARRAY_A);
				foreach ($data as $msg) {
					if (!empty($msg['message'])) {
						$html .= self::chat_message_response($agent_name, $msg['message'], $msg['offer']);
					} else {
						$html .= self::chat_message_response($agent_name, $msg['offer'], $msg['offer']);
					}
				}
				wp_send_json(['data' => $html, 'button_status' => 'true', 'chat_status' => 'false']);
				die;
			}



			$message = '';

			$product = wc_get_product($product_id);
			$price = $product->get_price();
			$discount = get_post_meta($product_id, '_dbargain_price_threshold', true);

			$threshold = '';
			if (empty($discount)) {
				$global_threshold = get_option('dbargain_threshold');
				$threshold = $price - ( (int) ( $price * $global_threshold / 100 ) );
			} else {
				$threshold = $price - ( (int) ( $price * $discount / 100 ) );
			}



			//Check if user reached limit.
			// $count = $wpdb->get_var("select count(*) from {$wpdb->prefix}dbargain_session where session_id = '" . $_SESSION['dbargain'][$product_id]['session_id'] . "' and product_id = '" . $product_id . "' and offer > 0");
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}dbargain_session WHERE session_id = %s AND product_id = %d AND offer > 0",
					$_SESSION['dbargain'][$product_id]['session_id'],
					$product_id
				)
			);			

			if ($post_offer >= $threshold && $post_offer <= $price) {

				$message = $wpdb->get_var("select message from {$wpdb->prefix}dbargain_responses where `condition` = 'success' order by rand() limit 1");

				if ( empty( $message ) ) {
					$message = 'Success!! Thankyou for the great offer. You can buy the product on your offered price';
				}

				$button_status = 'true';
				$chat_status = 'false';

				$_SESSION['dbargain'][$product_id]['dbargain_price'] = $post_offer;

				array_push($_SESSION['offerdata'], ['ID' => $product_id, 'product_id' => $product->id, 'session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'offer' => $post_offer, 'message' => $message, 'status' => 1, 'date_created' => gmdate('Y-m-d H:i:s')]);
				$data = http_build_query(array('session_data' => $_SESSION['offerdata'], 'product_data' => ['id' => $product_id, 'product_sku' => '', 'variations' => '', 'price' => $price, 'name' => $product->name]));
				$wpdb->insert($wpdb->prefix . 'dbargain_reports', ['session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'product_id' => $product_id, 'user_id' => get_current_user_id(), 'order_price' => $_SESSION['dbargain'][$product_id]['dbargain_price'], 'date_created' => gmdate('Y-m-d H:i:s')]);



			} else if ($count == $_SESSION['dbargain'][$product_id]['attempts']) {
				$message = $wpdb->get_var("select message from {$wpdb->prefix}dbargain_responses where `condition` = 'failure' order by rand() limit 1");

				if ( empty( $message ) ) { 
					$message = 'We are sorry but you have exhausted all your chances. Considering your interest and effort, we can let you buy this product on a discounted price still. Our final price for you is: ';
				}

				// $final_price = (int)($price+$threshold)/2;
				$final_price = $threshold;
				$message .= wc_price($final_price);

				$button_status = 'true';
				$chat_status = 'false';

				$_SESSION['dbargain'][$product_id]['dbargain_price'] = $final_price;

				array_push($_SESSION['offerdata'], ['ID' => $product_id, 'product_id' => $product->id, 'session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'offer' => $post_offer, 'message' => $message, 'status' => 0, 'date_created' => gmdate('Y-m-d H:i:s')]);
			} else if ( $post_offer > $price ) {
				$message = $wpdb->get_var("select message from {$wpdb->prefix}dbargain_responses where `condition` = 'more' order by rand() limit 1");

				if ( empty( $message ) ) {
					$message = 'Thankyou for your enthusiasm but the offer you are making, is above the product price itself. You can buy the product on original price';
				}

				$button_status = 'false';
				$chat_status = 'true';
				array_push($_SESSION['offerdata'], ['ID' => $product_id, 'product_id' => $product->id, 'session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'offer' => $post_offer, 'message' => $message, 'status' => 0, 'date_created' => gmdate('Y-m-d H:i:s')]);

			} else {
				if ( is_array($threshold) ) {
					$percentage_difference = (int) ( ( ( $threshold[0] - $post_offer ) / $threshold[0] ) * 100 );
				} else {
					$percentage_difference = (int) ( ( ( $threshold - $post_offer ) / $threshold ) * 100 );
				}


				//Get all messages in range lesser than calculated $percentage_difference
				$messages = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT percentage_difference, message
						FROM {$wpdb->prefix}dbargain_responses
						WHERE `condition` = 'less' AND percentage_difference <= %f
						ORDER BY percentage_difference DESC",
						$percentage_difference
					),
					ARRAY_A
				);

				//We need to pick all messages of closest value
				if ( isset( $messages[0] ) ) {
					$required_value = $messages[0]['percentage_difference']; //echo $required_value;
					$msgs = [];
					foreach ($messages as $m) {
						//Gather all messages from same value in case if there are multiple messages for same value.
						if ( $required_value == $m['percentage_difference'] ) {
							$msgs[] = $m['message'];
						}
					}

					//Pick a random message from same range.
					$message = $msgs[array_rand($msgs, 1)];
				}

				if ( empty( $message ) ) {
					$message = 'Please make a better offer ';
				}
				// $message ="threshold ".$percentage_difference;

				$button_status = 'false';
				$chat_status = 'true';
				array_push($_SESSION['offerdata'], ['ID' => $product_id, 'product_id' => $product->id, 'session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'offer' => $post_offer, 'message' => $message, 'status' => 0, 'date_created' => gmdate('Y-m-d H:i:s')]);

			}


			$wpdb->insert($wpdb->prefix . 'dbargain_session', ['session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'product_id' => $product_id, 'message' => $message, 'status' => '0', 'date_created' => gmdate('Y-m-d H:i:s')]);

			if ($count == $_SESSION['dbargain'][$product_id]['attempts'] - 1) {
				$message = $wpdb->get_var("select message from {$wpdb->prefix}dbargain_responses where `condition` = 'warning' order by rand() limit 1");

				if ( empty( $message ) ) {
					$message = 'You have exhausted all your attempts. Considering your interest and effort, we are going to grant you one last chance. Please give us your best offer this time.';
				}

				array_push($_SESSION['offerdata'], ['ID' => $product_id, 'product_id' => $product->id, 'session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'offer' => $post_offer, 'message' => $message, 'status' => 0, 'date_created' => gmdate('Y-m-d H:i:s')]);

				$wpdb->insert($wpdb->prefix . 'dbargain_session', ['session_id' => $_SESSION['dbargain'][$product_id]['session_id'], 'product_id' => $product_id, 'message' => $message, 'status' => '0', 'date_created' => gmdate('Y-m-d H:i:s')]);
			}

			$data = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT *
					FROM {$wpdb->prefix}dbargain_session
					WHERE session_id = %s AND product_id = %d",
					$_SESSION['dbargain'][$product_id]['session_id'],
					$product_id
				),
				ARRAY_A
			);

			foreach ($data as $msg) {
				if (!empty($msg['message'])) {
					$html .= self::chat_message_response($agent_name, $msg['message'], $msg['offer']);
				} else {
					$html .= self::chat_message_response($agent_name, $msg['offer'], $msg['offer']);
				}
			}

			wp_send_json(['data' => $html, 'button_status' => $button_status, 'chat_status' => $chat_status, 'token' => $_SESSION['token']]);
		} catch (exception $e) {
			$button_status = 'false';
			$chat_status = 'true';
			$html .= self::chat_message_response($agent_name, 'Error from Server', null);

			wp_send_json(['data' => '', 'button_status' => $button_status, 'chat_status' => $chat_status]);
		}
	}

	public static function chat_message_response( $agent_name, $msg, $offer ) {
		$res = '<div class="chat-message clearfix">';
		if ( null == $offer ) {
			$res .= '<img src="http://gravatar.com/avatar/2c0ad52fc5943b78d6abe069cc08f320?s=32" alt="" width="32" height="32">';
		}
		$res .= '
                    <div class="chat-message-content clearfix">
                    ';
		if ( null == $offer ) {

			$res .= '<h5>' . $agent_name . '</h5>';
		}
		if ( null == $offer ) {
			$res .= '<p>' . $msg . '</p>';
		} else {
			$res .= '<p style="text-align: right;">' . $msg . '</p>';
		}
		$res .= '
                    </div> <!-- end chat-message-content -->
            
                </div> <!-- end chat-message -->
            
                <hr>';

		return $res;

	}
}

//Message formate
DBargain::register();
