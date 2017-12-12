<?php

defined('ABSPATH') or die('Computer says no!');

class DBQuery_Report_Table_Form extends DBQuery_Report_Table {
   
    private static function dbquery_get_form_ids_query()
	{
		global $wpdb;
        $table_name = $wpdb->prefix . 'dbquery_report_table';
        $ids_array = $wpdb->get_results("SELECT id FROM $table_name", ARRAY_A);
        return $ids_array;
    }

    protected function list_report_tables()
    {
        if ( is_admin() ) {
            ob_start();
            $html = '<table class="table-responsive dbquery-report-table">';
            $html .= '<thead><tr><td>Name</td><td>Shortcode</td><td></td></tr></thead><tbody>';
            $table_id_array = array();
            $lastValue = 0;
            if($form_ids = $this->dbquery_get_form_ids_query()){
                foreach ($form_ids as $key => $value) {
                    $divClass = ($value['id'] % 2 === 0) ? 'evens' : 'odds';
                    $html .= '<tr class="' . $divClass . '">';
                    $html .= '<td>DBQuery Report Table ' . $value['id'] . '</td><td class="shortcode">[WPDBQRT id="' . htmlspecialchars($value['id'], ENT_QUOTES) . '"]</td><td><a href="' . esc_url( admin_url("admin.php?page=dbquery-report-table-form&id=" . $value['id']) ) . '">Edit</a></td>';
                    $html .= '</tr>';
                    $lastValue = $value['id'];
                    $table_id_array[] = $value['id'];
                }
            }
            $html .= "</tbody></table>";
            $html .= $this->create_button( $lastValue );
            ob_end_flush();
            return $html;
        }
    }

    protected function report_table_form()
    {
        if ( is_admin() ) {
            ob_start();
            $html = "";
            if( isset($_REQUEST['id']) && !empty($_REQUEST['id']) ){
                $id = intval($_REQUEST['id']);
                $html .= $this->form_output( $id );
            } else {
                $lastValue = 0;
                if($form_ids = $this->dbquery_get_form_ids_query()){
                    foreach ($form_ids as $key => $value) {
                        $lastValue = $value['id'];
                    }
                }
                $html .= $this->form_output( $lastValue );
            }
            ob_end_flush();
            return $html;
        }
    }

    protected function create_button($id = null){
        $html = '<div id="WPDBQRT_admin_create_table" class="WPDBQRT-controls create-table">
        <form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
        <input type="hidden" name="dbquery_report_table_id" value="' . ($id + 1). '" />
        <input type="hidden" name="action" value="dbquery_report_new_form_query" />
        <input type="hidden" name="new_dbquery_report_table" value="' . ($id + 1). '" />
        ' . wp_nonce_field( 'dbquery_report_new_form_query', 'dbquery_report_new_form_query_nonce' ) . '
        <input type="submit" value="Create New Table" />
        </form>
        </div>';
        return $html;
    }

    protected function delete_button($id = null){
        $html = '<div id="WPDBQRT_admin_delete_table" class="WPDBQRT-controls delete-table"><form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
        <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '" />
        <input type="hidden" name="action" value="dbquery_report_delete_form_query" />
        <input type="hidden" name="delete_dbquery_report_table" value="' . htmlspecialchars($id, ENT_QUOTES). '" />
        ' . wp_nonce_field( 'dbquery_report_delete_form_query', 'dbquery_report_delete_form_query_nonce' ) . '
        <input type="submit" onclick="return confirmDelete(' . htmlspecialchars($id, ENT_QUOTES) . ')" value="Delete" />
        </form></div>';
        return $html;
    }
    
    protected function form_output($id = null)
    {
        return '
        <div class="report-table-form" id="WPDBQRT_admin_form_' . htmlspecialchars($id, ENT_QUOTES) . '">
        <h3 class="report-table-header">DBQuery Report Table  ' . htmlspecialchars($id, ENT_QUOTES) . " " . $this->delete_button($id) . '</h3>
        <h4>Remember to make a backup of your database before proceeding and you must only use SELECT statements in your query or you might break your database</h4>
        <label>Enter query</label>
        <br>
        <form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
            <textarea cols="150" rows="4" name="dbquery_report_table_query">' . htmlspecialchars($this->get_dbquery_report_table_query($id), ENT_QUOTES) . '</textarea>
            <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '"/>
            <input type="hidden" name="action" value="dbquery_report_dbquery_update_query" />
            <br>
            ' . wp_nonce_field( 'dbquery_report_dbquery_update_query', 'dbquery_report_dbquery_update_query_nonce' ) . '
            <input type="submit" name="dbquery_report_dbquery_update_query_submit" value="Update" />
        </form>
        <br>
        ' . $this->dbquery_report_table_validate_error( htmlspecialchars($this->get_dbquery_report_table_query($id, ENT_QUOTES) ) ) . '
        <br>
        <label>Enter HTML table head to be displayed before results of query - Only enter valid HTML table head elements</label>
        <br>
        &lt;thead&gt;
        <form method="POST" action="' . esc_url( admin_url( 'admin-post.php' )) . '">
            <textarea cols="150" rows="8" name="dbquery_report_table_head_content">' .  htmlspecialchars($this->get_dbquery_report_table_head_content($id), ENT_QUOTES) . '</textarea>
            <br>
            <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '" />
            <input type="hidden" name="action" value="report_table_head_update_query" />
            &lt;/thead&gt; 
            <br>
            ' . wp_nonce_field() . '
            <input type="submit" name="report_table_head_update_query_submit" value="Update" />
        </form>
        </div>';
    }
}
