<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class All_Offers extends WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct([
			'singular' => __('Offer', 'sp'),
			//singular name of the listed records
			'plural' => __('Offers', 'sp'),
			//plural name of the listed records
			'ajax' => false //should this table support ajax?
		]);
	}

	/**
	 * Retrieve All Offers from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_offers( $per_page = 15, $page_number = 1 ) {
		global $wpdb;
		$sql = "SELECT o.product_id, count(order_id) as 'orders', wp.post_title as product_name, SUM(o.product_qty) as 'sold_quantity',COUNT(DISTINCT o.customer_id) as 'customers' FROM `wp_wc_order_product_lookup` o JOIN wp_posts AS wp ON o.product_id = wp.ID JOIN wp_wc_product_meta_lookup pm ON o.product_id = pm.product_id WHERE o.product_net_revenue < pm.max_price GROUP BY o.product_id";
		if ( isset( $_REQUEST['orderby'] ) ) {
			$order_by = sanitize_text_field($_REQUEST['orderby']);
		}

		if ( !empty( $order_by ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $order_by );
			$sql .= !empty( sanitize_text_field( $_REQUEST['order'] ) ) ? ' ' . esc_sql( sanitize_text_field( $_REQUEST['order'] ) ) : ' ASC';
		}

		$sql .= ' LIMIT %d';

		$sql .= ' OFFSET %d';

		$result = $wpdb->get_results( 
			$wpdb->prepare( // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- ignore formatting of class name
				"SELECT o.product_id, count(order_id) as 'orders', wp.post_title as product_name, SUM(o.product_qty) as 'sold_quantity',COUNT(DISTINCT o.customer_id) as 'customers' FROM `wp_wc_order_product_lookup` o JOIN wp_posts AS wp ON o.product_id = wp.ID JOIN wp_wc_product_meta_lookup pm ON o.product_id = pm.product_id WHERE o.product_net_revenue < pm.max_price GROUP BY o.product_id  LIMIT %d  OFFSET %d" // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- ignore formatting of class name
				, array($per_page,( $page_number - 1 ) * $per_page) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- ignore formatting of class name
			), 
			'ARRAY_A' 
		);

		// print_r(array($per_page,( $page_number - 1 ) * $per_page));
		$finalData = array();

		// print_r($finalData);
		return $result;
	}

	/**
	 * Returns the count of reports in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$sql = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT product_id) FROM {$wpdb->prefix}dbargain_reports"
			)
		);
		// $sql = "SELECT COUNT(DISTINCT product_id) FROM {$wpdb->prefix}dbargain_reports";
		return $sql;
	}

	/** Text displayed when no data is available */
	public function no_items() {
		esc_html_e('No data.', 'sp');
	}


	/**
	 * Method for view details
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_details( $item ) {
		$title =  '<a href="javascript:;" class="show_detail populate_detail" rel="' . $item['product_id'] . '">Show Details</a>';
		return $title;
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		return $item[$column_name];
	}



	/**
	 * Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			//    'cb' => '<input type="checkbox" />',
			'product_id' => __('Product ID', 'sp'),
			'product_name' => __('Product Name', 'sp'),
			'customers' => __('Total Buyers', 'sp'),
			'orders' => __('Total No. of Orders', 'sp'),
			// 'quantity' => __( 'Total Quantity', 'sp' ),
			'sold_quantity' => __('Total Quantity Sold', 'sp'),
			'details' => ''
		];
		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'customers' => array('customers', true),
			'orders' => array('orders', true),
			'quantity' => array('quantity', true)
		);
		return $sortable_columns;
	}



	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		// $sortable = $this->get_sortable_columns();

		$perPage = 15;
		$currentPage = $this->get_pagenum();
		$totalItems = self::record_count();

		$data = self::get_offers($perPage, $currentPage);

		$this->set_pagination_args(
			array(
				'total_items' => $totalItems,
				'per_page' => $perPage
			)
		);

		$this->_column_headers = array($columns, $hidden);
		$this->items = $data;
	}
}
