<?php

defined('ABSPATH') or die('Computer says no!');

class DBQueryReportTable
{
    public $DBQRTV;

    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), '1.0');
    }
    
    /**
     * Initializes WordPress hooks
     */
    public function __construct()
    {
        /* Add settings link */
        add_filter('plugin_action_links_' . WPDBQRT__PLUGIN_BASENAME, array($this,  'plugin_add_settings_link' ));
        // Add frontend scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_front_end_scripts' ));
        // receive POST from form with table head HTML and put in DB
        add_action('admin_post_report_table_head_update_query', array('DBQueryReportTableModel', 'dbquery_report_table_head_update_query'));
        // receive POST from form with dbquery query and put in DB
        add_action('admin_post_dbquery_report_dbquery_update_query', array('DBQueryReportTableModel', 'dbquery_report_dbquery_update_query'));
        // receive POST from form with dbquery query and put in DB
        add_action('admin_post_dbquery_report_new_form_query', array('DBQueryReportTableModel', 'dbquery_report_new_form_query'));
        // receive POST from form with dbquery query and put in DB
        add_action('admin_post_dbquery_report_delete_form_query', array('DBQueryReportTableModel', 'dbquery_report_delete_form_query'));
        // create shortcode to output Report Table
        add_shortcode('WPDBQRT', array($this, 'dbquery_report_table_shortcode'));
        // create new view
        $this->DBQRTV = new DBQueryReportTableView();
    }

    // Enqueue frontend scripts
    public function enqueue_front_end_scripts()
    {
        wp_enqueue_script('list-js', plugins_url('js/list-js-v1.5.0.js', __FILE__), array('jquery'), null, false);
    }

    // Add settings link to plugin
    public function plugin_add_settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=dbquery-report-table">' . __('Settings', 'textdomain') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    // if function passed to this returns empty then show error
    public static function dbquery_report_table_validate_error($returnedValue)
    {
        if (empty($returnedValue)) {
            return '<p style="color: #e11; font-size= 0.8em;">You must enter a value for table to work properly</p>';
        } else {
            return;
        }
    }

    // DBQuery Report Table Shortcode
    public function dbquery_report_table_shortcode($atts)
    {
        extract(shortcode_atts(array(
            'id' => '',
        ), $atts));

        $id = intval($id);

        global $wpdb;
        $report_table_query = DBQueryReportTableModel::get_dbquery_report_table_query($id);
        if (!$report_table_results = $wpdb->get_results($report_table_query, ARRAY_A)) {
            return '<p>Your MySQL query did not return any results</p>';
        }
        $html = '<div id="wpdbqrt_table_wrapper_' . htmlspecialchars($id, ENT_QUOTES) . '" class="wpdbqrt-table-wrapper">';
        $html .= '<table id="wpdbqrt_table_' . htmlspecialchars($id, ENT_QUOTES) . '" class="wpdbqrt-table table-responsive">';
        $html .= '<input id="wpdbqrt_search_' . htmlspecialchars($id, ENT_QUOTES) . '" class="fuzzy-search searchbox" placeholder="Search" />';
        if (!empty(DBQueryReportTableModel::get_dbquery_report_table_head_content($id))) {
            $html .= "<thead>";
            $html .= DBQueryReportTableModel::get_dbquery_report_table_head_content($id);
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
                if ($rowCount < 2) {
                    $rowCellCount += 1;
                }
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
        for ($cellNo = 1; $cellNo <= $rowCellCount; $cellNo++) {
            $html .= 'wpdbqrt_data_' . $cellNo . '","';
        }
        $html .= '"], indexAsync = true; };';
        $html .= ' var list_report_table_' . htmlspecialchars($id, ENT_QUOTES) . ' = new List("wpdbqrt-table-wrapper_' . htmlspecialchars($id, ENT_QUOTES) . '", options );';
        $html .= '</script>';
        return $html;
    }
}