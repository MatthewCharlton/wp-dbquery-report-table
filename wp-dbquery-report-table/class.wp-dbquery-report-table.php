<?php

defined('ABSPATH') or die('Computer says no!');

class DBQuery_Report_Table {

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0' );
	}
	
	/**
	 * Initializes WordPress hooks
	 */
	public function __construct()
	{
        /* Add Menu to Settings */
		add_action('admin_menu', array($this, 'dbquery_report_table_menu'));
		/* Add settings link */
		add_filter( 'plugin_action_links_' . WPDBQRT__PLUGIN_BASENAME , array($this,  'plugin_add_settings_link' ));
		// add admin menu scripts
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts' ));
		// Add frontend scripts
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_front_end_scripts' ));
		// receive POST from form with table head HTML and put in DB 
		add_action('admin_post_report_table_head_update_query', array($this, 'dbquery_report_table_head_update_query'));
		// receive POST from form with dbquery query and put in DB 
		add_action('admin_post_dbquery_report_dbquery_update_query', array($this, 'dbquery_report_dbquery_update_query'));
		// receive POST from form with dbquery query and put in DB 
		add_action('admin_post_dbquery_report_new_form_query', array($this, 'dbquery_report_new_form_query'));
		// receive POST from form with dbquery query and put in DB 
		add_action('admin_post_dbquery_report_delete_form_query', array($this, 'dbquery_report_delete_form_query'));
		// create shortcode to output Report Table
		add_shortcode('WPDBQRT', array($this, 'dbquery_report_table_shortcode'));
		
	}

	// Enqueue Admin scripts and styles
	public function enqueue_admin_scripts() 
	{
		wp_enqueue_script( 'dbquery-report-table-js', plugins_url('js/dbquery-report-table.js', __FILE__) );
	}

	public function enqueue_admin_styles() 
	{
		wp_register_style( 'dbquery-report-table-styles', plugins_url('css/dbquery-report-table.css', __FILE__) );
		wp_enqueue_style('dbquery-report-table-styles');
	}

	// Enqueue frontend scripts
	public function enqueue_front_end_scripts()
	{
		wp_enqueue_script( 'list-js', plugins_url('js/list-js-v1.5.0.js', __FILE__), array('jquery'), null,  false );
	} 

	// Add settings link to plugin
	public function plugin_add_settings_link( $links )
	{
		$settings_link = '<a href="admin.php?page=dbquery-report-table">' . __( 'Settings', 'textdomain' ) . '</a>';
		array_unshift( $links, $settings_link );
		  return $links;
	}

	/* Menu Setup */
	public function dbquery_report_table_menu()
	{
		$admin_main_menu = add_menu_page('DBQuery Report Table', 'DBQuery Report Table', 'manage_options', 'dbquery-report-table', array($this, 'dbquery_report_table_list'));
		$admin_submenu = add_submenu_page('', 'DBQuery Report Table Form', 'Report Table Form', 'manage_options', 'dbquery-report-table-form', array($this, 'dbquery_report_table_form'));

		// add admin menu styles
		add_action( 'admin_print_styles-' . $admin_main_menu, array($this, 'enqueue_admin_styles' ));
		add_action( 'admin_print_styles-' . $admin_submenu, array($this, 'enqueue_admin_styles' ));
	}

	/* dbquery Report Table Settings Admin Page */
	public static function dbquery_report_table_list()
	{
		if ( !current_user_can('administrator') ) {
			wp_die('You shall not pass!');
		}
		echo '<div id="WPDBQRT-admin">';
		echo "<h1>DBQuery Report Table</h1>";
		$dbquery_report_list = new DBQuery_Report_Table_Form();
		echo $dbquery_report_list->list_report_tables();
		echo "</div>";
	}	

	public static function dbquery_report_table_form()
	{
		if ( !current_user_can('administrator') ) {
			wp_die('You shall not pass!');
		}
		echo '<div id="WPDBQRT-form">';
		echo "<h1>DBQuery Report Table Form</h1>";
		$dbquery_report_form = new DBQuery_Report_Table_Form();
		echo $dbquery_report_form->report_table_form();
		echo "</div>";
	}	

    public static function dbquery_report_table_remove()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'dbquery_report_table';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
	}
	
	public static function dbquery_report_table_deactivate()
	{
		// TODO Add if needed
	}

	public static function dbquery_report_table_activate()
	{
		global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'dbquery_report_table';
		$result = $wpdb->get_results("SHOW TABLES LIKE '$table_name'", ARRAY_A);
		if( count($result) === 0 ) {
			$setupPluginQuery = "SELECT * FROM " . $wpdb->prefix . "comments";
			$charset_collate = $wpdb->get_charset_collate();
			$table_name = $wpdb->prefix . 'dbquery_report_table';
			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				id int NOT NULL AUTO_INCREMENT,
				report_table_query TEXT NULL,
				report_table_head_content TEXT NULL, 
				PRIMARY KEY  (id)
			) $charset_collate;";
			require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			$wpdb->query("INSERT INTO $table_name (report_table_query) VALUES ('$setupPluginQuery')");
		} 
	}

	public static function dbquery_report_new_form_query()
	{
		global $wpdb;
		if ( ! isset( $_POST['dbquery_report_new_form_query_nonce'] ) || ! wp_verify_nonce( $_POST['dbquery_report_new_form_query_nonce'], 'dbquery_report_new_form_query' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		} else {
			if( isset($_POST['dbquery_report_table_id']) && isset($_POST['new_dbquery_report_table']) ){
				$id = intval($_POST['dbquery_report_table_id']);
				if ($id === 0){
					wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
					exit;
				}
				$table_name = $wpdb->prefix . 'dbquery_report_table';
				$wpdb->query("INSERT INTO $table_name (id) VALUES ('$id')");
				wp_redirect(admin_url("admin.php?page=dbquery-report-table-form"));
				exit;
			}
		}
	}

	public static function dbquery_report_delete_form_query()
	{
		global $wpdb;
		if ( ! isset( $_POST['dbquery_report_delete_form_query_nonce'] ) || ! wp_verify_nonce( $_POST['dbquery_report_delete_form_query_nonce'], 'dbquery_report_delete_form_query' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		} else {
			if( isset($_POST['dbquery_report_table_id']) && isset($_POST['delete_dbquery_report_table']) ){
				$id = intval($_POST['dbquery_report_table_id']);
				if ($id === 0){
					wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
					exit;
				}
				$table_name = $wpdb->prefix . 'dbquery_report_table';
				$wpdb->query("DELETE FROM $table_name WHERE id = '$id'");
				wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
				exit;
			}
		}
	}

	public static function dbquery_report_table_head_update_query()
	{
		global $wpdb;
		if( isset($_POST['dbquery_report_table_head_content']) && isset($_POST['dbquery_report_table_id']) ){
			$id = intval($_POST['dbquery_report_table_id']);
			if ($id === 0) {
				wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
				exit;
			}

			$submittedElements = $_POST['dbquery_report_table_head_content'];

			$submittedElements = preg_replace("/(\<table\>)|(\<\/table\>)|(\<thead\>)|(\<\/thead\>)|(\<script\>)|(\<\/script\>)|(\<a\>)|(\<\/a\>)|(\<link\>)|(\<\/link\>)/imxU", "", strtolower($submittedElements));	

			$table_name = $wpdb->prefix . 'dbquery_report_table';
			$wpdb->query("UPDATE $table_name SET report_table_head_content = '" . $submittedElements . "' WHERE id = $id");
			wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
			exit;
		}
	}

	public static function dbquery_report_dbquery_update_query()
	{
		global $wpdb;
		if ( ! isset( $_POST['dbquery_report_dbquery_update_query_nonce'] ) || ! wp_verify_nonce( $_POST['dbquery_report_dbquery_update_query_nonce'], 'dbquery_report_dbquery_update_query' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		} else {
			if( !isset($_POST['dbquery_report_table_query']) && !isset($_POST['dbquery_report_table_id']) && intval($_POST['dbquery_report_table_id']) === 0 ){
				wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
				exit;
			}
			$id = intval($_POST['dbquery_report_table_id']);
			$query = $_POST['dbquery_report_table_query'];
			// Sanitize query 
			// Check if SELECT is in the query
			if (preg_match('/SELECT/', strtoupper($query)) != 0) {
				// Array with forbidden query parts
				$disAllow = array(
					'INSERT',
					'UPDATE',
					'DELETE',
					'RENAME',
					'DROP',
					'CREATE',
					'TRUNCATE',
					'ALTER',
					'COMMIT',
					'ROLLBACK',
					'MERGE',
					'CALL',
					'EXPLAIN',
					'LOCK',
					'GRANT',
					'REVOKE',
					'SAVEPOINT',
					'TRANSACTION',
					'SET',
				);

				// Convert array to pipe-seperated string
				$disAllow = implode('|', $disAllow);
				// Check if no other harmfull statements exist
				if (preg_match('/(' . $disAllow . ')/', strtoupper($query)) == 0) {
					// Execute query
					$validationError = "";
					$table_name = $wpdb->prefix . 'dbquery_report_table';
					$result = $wpdb->query("UPDATE $table_name SET report_table_query = '$query' WHERE id = $id");
					wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
					exit;
				}
				else {
					wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
					exit;
				}
			}
			else {
				wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
				exit;
			}
		}
	}

	// return table head HTML from DB
	protected function get_dbquery_report_table_head_content($id = null)
	{
		if ($id == intval($id) > 0){
			global $wpdb;
			$table_name = $wpdb->prefix . 'dbquery_report_table';
			$query = $wpdb->get_results("SELECT report_table_head_content FROM $table_name WHERE id = $id");
			foreach ($query as $key => $value)
			{
				if ($key == 'report_table_head_content') {
					return $value->report_table_head_content;
				}
			}
		}
		return;
	}

	// return dbquery query from DB
	protected function get_dbquery_report_table_query($id = null)
	{
		if ($id == intval($id) > 0){
			global $wpdb;
			$table_name = $wpdb->prefix . 'dbquery_report_table';
			$query = $wpdb->get_results("SELECT report_table_query FROM $table_name WHERE id = '$id'");
			foreach ($query as $key => $value)
			{
				if ($key == 'report_table_query') {
					return $value->report_table_query;
				}
			}
		}
		return;
	}

	// if function passed to this returns empty then show error
	protected function dbquery_report_table_validate_error($returnedValue)
	{
		if (empty($returnedValue)) {
			return '<p style="color: #e11; font-size= 0.8em;">You must enter a value for table to work properly</p>';
		}
		else {
			return;
		}
	}

	/* dbquery Report Table */
	public function dbquery_report_table_shortcode($atts)
	{
		extract ( shortcode_atts( array(
			'id' => '',
		), $atts ) );

		$id = intval($id);

		global $wpdb;
		$report_table_query = $this->get_dbquery_report_table_query($id);
		if(!$report_table_results = $wpdb->get_results($report_table_query, ARRAY_A)){
			return '[WPDBQRT id="' . htmlspecialchars($id, ENT_QUOTES) . '"]';
		}
		$html = '<div id="wpdbqrt_table_wrapper_' . htmlspecialchars($id, ENT_QUOTES) . '" class="wpdbqrt-table-wrapper">';
		$html .= '<table id="wpdbqrt_table_' . htmlspecialchars($id, ENT_QUOTES) . '" class="wpdbqrt-table table-responsive">';
		$html .= '<input id="wpdbqrt_search_' . htmlspecialchars($id, ENT_QUOTES) . '" class="fuzzy-search searchbox" placeholder="Search" />';
		if (!empty($this->get_dbquery_report_table_head_content($id))) {
			$html .= "<thead>";
			$html .= $this->get_dbquery_report_table_head_content($id);
			$html .= "</thead>";
		}
		$totalCount = 1;
		$rowCellCount = 1;
		$rowCount = 1;
		$html .= '<tbody class="list">';
		foreach ($report_table_results as $data) {
			$html .= '<tr id="wpdbqrt_row_' . $rowCount . '">';
			$cellCount = 1;
			foreach ($data as $key => $value) {
				$html .= '<td class="wpdbqrt_data_' . $cellCount . '">' . htmlspecialchars($value, ENT_QUOTES) . "</td>";
				$cellCount++;
				$totalCount++;
				if($rowCount < 2) $rowCellCount += 1;
			}
			$html .= '</tr>';
			$rowCount++;
		}
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '</div>';
		$html .= '<script>';
		$html .= '
		var report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_tds = document.querySelectorAll("#report_table_' . htmlspecialchars($id, ENT_QUOTES) . ' thead td");
		var report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_ths = document.querySelectorAll("#report_table_' . htmlspecialchars($id, ENT_QUOTES) . ' thead th");
		if( report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_tds.length > 0 ){
			for (var i = 0; i < report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_tds.length; i++) {
				report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_tds[i].setAttribute("data-sort", "wpdbqrt_data_" + i);
				report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_tds[i].classList.add("sort");
			}
		} else if ( report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_ths.length > 0 ) {
			for (var i = 0; i < report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_ths.length; i++) {
				report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_ths[i].setAttribute("data-sort", "wpdbqrt_data_" + i);
				report_table_' . htmlspecialchars($id, ENT_QUOTES) . '_ths[i].classList.add("sort");
			}
		}';
		$html .= '</script>';
		$html .= '<script>';
		$html .= 'var options = { valueNames: ["';
		for( $cellNo = 1; $cellNo <= $rowCellCount; $cellNo++ ){
			$html .= 'wpdbqrt_data_' . $cellNo . '","';
		}	
		$html .= '"], indexAsync = true; };';
		$html .= ' var list_report_table_' . htmlspecialchars($id, ENT_QUOTES) . ' = new List("wpdbqrt-table-wrapper_' . htmlspecialchars($id, ENT_QUOTES) . '", options );';
		$html .= '</script>';
		return $html;
	}
}

return new dbquery_Report_Table();