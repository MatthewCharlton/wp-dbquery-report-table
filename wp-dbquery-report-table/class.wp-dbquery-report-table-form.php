<?php

defined('ABSPATH') or die('Computer says no!');

class DBQuery_Report_Table_Form extends DBQuery_Report_Table {
   
    public function dbquery_get_form_ids_query()
	{
		global $wpdb;
        $table_name = $wpdb->prefix . 'dbquery_report_table';
        $ids_array = $wpdb->get_results("SELECT id FROM $table_name", ARRAY_A);
        return $ids_array;
    }

    public function show_report_tables()
    {
        ob_start();
        $html = '';
        $html .= '<div id="WPDBQRT-admin">';
        $html .= "<h2>DBQuery Report Table Settings</h2>";
        $lastValue = 0;
        if($form_ids = $this->dbquery_get_form_ids_query()){
            foreach ($form_ids as $key => $value) {
                $divClass = ($value['id'] % 2 === 0) ? 'evens' : 'odds';
                $html .= '<div class="' . $divClass . '">';
                $html .= $this->form_output( $value['id'] );
                $html .= '</div>';
                $lastValue = $value['id'];
            }
        }
        $html .= $this->create_button( $lastValue );
        $html .= '</div>';
        return $html;
        ob_end_flush();
    }

    public function create_button($id = null){
        $html = '<div id="create-table" class="wpmrt-controls"><form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
        <input type="hidden" name="dbquery_report_table_id" value="' . ($id + 1). '" />
        <input type="hidden" name="action" value="dbquery_report_new_form_query" />
        <input type="hidden" name="new_dbquery_report_table" value="' . ($id + 1). '" />
        ' . wp_nonce_field( 'dbquery_report_new_form_query', 'dbquery_report_new_form_query_nonce' ) . '
        <input type="submit" value="Create New Table" />
        </form></div>';
        return $html;
    }

    public function delete_button($id = null){
        $html = '<div id="delete-table" class="wpmrt-controls"><form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
        <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '" />
        <input type="hidden" name="action" value="dbquery_report_delete_form_query" />
        <input type="hidden" name="delete_dbquery_report_table" value="' . htmlspecialchars($id, ENT_QUOTES). '" />
        ' . wp_nonce_field( 'dbquery_report_delete_form_query', 'dbquery_report_delete_form_query_nonce' ) . '
        <input type="submit" onclick="return confirmDelete(' . htmlspecialchars($id, ENT_QUOTES) . ')" value="Delete" />
        </form></div>';
        return $html;
    }
    
    public function form_output($id)
    {
        return '
        <h3 class="report-table-header">DBQuery Report Table  ' . htmlspecialchars($id, ENT_QUOTES) . " " . $this->delete_button($id) . '</h3>
        <h4>Remember to make a backup of your database before proceeding and you must only use SELECT statements in your query or you might break your database</h4>
        <label>Enter query</label>
        <br>
        <form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
            <textarea cols="120" rows="4" name="dbquery_report_table_query">' . htmlspecialchars($this->get_dbquery_report_table_query($id), ENT_QUOTES) . '</textarea>
            <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '"/>
            <input type="hidden" name="action" value="dbquery_report_dbquery_update_query" />
            <br>
            ' . wp_nonce_field( 'dbquery_report_dbquery_update_query', 'dbquery_report_dbquery_update_query_nonce' ) . '
            <input type="submit" name="dbquery_report_dbquery_update_query_submit" value="Update" />
        </form>
        <br>
        ' . $this->dbquery_report_table_validate_error( $this->get_dbquery_report_table_query( htmlspecialchars($id, ENT_QUOTES) ) ) . '
        <br>
        <label>Enter HTML table head to be displayed before results of query - Only enter valid HTML table head elements</label>
        <br>
        &lt;thead&gt;
        <form method="POST" action="' . esc_url( admin_url( 'admin-post.php' )) . '">
            <textarea cols="120" rows="8" name="dbquery_report_table_head_content">' .  htmlspecialchars($this->get_dbquery_report_table_head_content($id), ENT_QUOTES) . '</textarea>
            <br>
            <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '" />
            <input type="hidden" name="action" value="report_table_head_update_query" />
            &lt;/thead&gt; 
            <br>
            ' . wp_nonce_field(  ) . '
            <input type="submit" name="report_table_head_update_query_submit" value="Update" />
        </form>
        <br>
        <div class="shortcode"><p>Use shortcode<br><pre>[WPDBQRT id="' . htmlspecialchars($id, ENT_QUOTES) . '"]</pre><br>on any page to display results table</p></div>';
    }
}
