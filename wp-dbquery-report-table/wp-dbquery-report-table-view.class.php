<?php

defined('ABSPATH') or die('Computer says no!');

class DBQueryReportTableView {

    /**
     * Initializes WordPress hooks
     */
    public function __construct()
    {
        // Add Menu to Settings 
        add_action('admin_menu', array($this, 'dbquery_report_table_menu'));
        // add admin menu scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts' ));
        // add admin notices
        add_action('admin_notices', array($this, 'dbquery_report_table_success_notice'));
        add_action('admin_notices', array($this, 'dbquery_report_table_error_notice'));
    }

    // Menu Setup 
    public function dbquery_report_table_menu()
    {
        $admin_main_menu = add_menu_page('DBQuery Report Table', 'DBQuery Report Table', 'manage_options', 'dbquery-report-table', array($this, 'dbquery_report_table_list'));
        $admin_submenu = add_submenu_page('', 'DBQuery Report Table Form', 'Report Table Form', 'manage_options', 'dbquery-report-table-form', array($this, 'dbquery_report_table_form'));

        // add admin menu styles
        add_action('admin_print_styles-' . $admin_main_menu, array($this, 'enqueue_admin_styles' ));
        add_action('admin_print_styles-' . $admin_submenu, array($this, 'enqueue_admin_styles' ));
    }

    // Enqueue Admin scripts and styles
    public function enqueue_admin_scripts()
    {
        wp_enqueue_script('dbquery-report-table-js', plugins_url('js/dbquery-report-table.js', __FILE__));
    }

    public function enqueue_admin_styles()
    {
        wp_register_style('dbquery-report-table-styles', plugins_url('css/dbquery-report-table.css', __FILE__));
        wp_enqueue_style('dbquery-report-table-styles');
    }

    // Setup admin success notice
    public static function dbquery_report_table_success_notice() 
    {
        $message = get_transient(  get_current_user_id() . '_wp_query_report_table_success_notice' );

        if ( $message ) {
            delete_transient(  get_current_user_id() . '_wp_query_report_table_success_notice' );

            printf( '<div class="%1$s"><p>%2$s</p></div>',
                'notice notice-success is-dismissible wpdbqrt_admin_notice',
                $message
            );
        }
    }

    // Setup admin error notice
    public static function dbquery_report_table_error_notice() 
    {
        $message = get_transient(  get_current_user_id() . '_wp_query_report_table_error_notice' );

        if ( $message ) {
            delete_transient(  get_current_user_id() . '_wp_query_report_table_error_notice' );

            printf( '<div class="%1$s"><p>%2$s</p></div>',
                'notice notice-error is-dismissible wpdbqrt_admin_notice',
                $message
            );
        }
    }

    /* dbquery Report Table Settings Admin Page */
    public static function dbquery_report_table_list()
    {
        if (!current_user_can('administrator')) {
            wp_die('You shall not pass!');
        }
        echo '<div id="WPDBQRT-admin">';
        echo "<h1>DBQuery Report Table</h1>";
        echo self::list_report_tables();
        echo "</div>";
    }

    public static function dbquery_report_table_form()
    {
        if (!current_user_can('administrator')) {
            wp_die('You shall not pass!');
        }
        echo '<div id="WPDBQRT-form">';
        echo '<span class="form-header"><div class="back"><a href="' . esc_url(admin_url("admin.php?page=dbquery-report-table")) . '"><</a></div><h2>Edit DBQuery Report Table Form</h2></span>';
        echo self::report_table_form();
        echo "</div>";
    }

    public static function list_report_tables()
    {
        if ( is_admin() ) {
            ob_start();
            $html = '<table class="table-responsive dbquery-report-table">';
            $html .= '<thead><tr><td>Name</td><td>Shortcode</td><td></td></tr></thead><tbody>';
            $tableIDArray = array();
            $lastValue = 0;
            if($form_ids = DBQueryReportTableModel::dbquery_get_form_ids_query()){
                foreach ($form_ids as $key => $value) {
                    $divClass = ($value['id'] % 2 === 0) ? 'evens' : 'odds';
                    $html .= '<tr class="' . $divClass . '">';
                    $html .= '<td>DBQuery Report Table ' . $value['id'] . '</td><td class="shortcode">[WPDBQRT id="' . htmlspecialchars($value['id'], ENT_QUOTES) . '"]</td><td><a href="' . esc_url( admin_url("admin.php?page=dbquery-report-table-form&id=" . $value['id']) ) . '">Edit</a></td>';
                    $html .= '</tr>';
                    $lastValue = $value['id'];
                    $tableIDArray[] = $value['id'];
                }
            }
            $html .= "</tbody></table>";
            $html .= self::create_button( $lastValue );
            ob_end_flush();
            return $html;
        }
    }

    protected static function report_table_form()
    {
        if ( is_admin() ) {
            ob_start();
            $html = "";
            if( isset($_REQUEST['id']) && !empty($_REQUEST['id']) ){
                $id = intval($_REQUEST['id']);
                $html .= self::form_output( $id );
            } else {
                $lastValue = 0;
                if($form_ids = DBQueryReportTableModel::dbquery_get_form_ids_query()){
                    foreach ($form_ids as $key => $value) {
                        $lastValue = $value['id'];
                    }
                }
                $html .= self::form_output( $lastValue );
            }
            ob_end_flush();
            return $html;
        }
    }

    protected static function create_button($id = null){
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

    protected static function delete_button($id = null){
        $html = '<div id="WPDBQRT_admin_delete_table" class="WPDBQRT-controls delete-table"><form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
        <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '" />
        <input type="hidden" name="action" value="dbquery_report_delete_form_query" />
        <input type="hidden" name="delete_dbquery_report_table" value="' . htmlspecialchars($id, ENT_QUOTES). '" />
        ' . wp_nonce_field( 'dbquery_report_delete_form_query', 'dbquery_report_delete_form_query_nonce' ) . '
        <input type="submit" onclick="return confirmDelete(' . htmlspecialchars($id, ENT_QUOTES) . ')" value="Delete" />
        </form></div>';
        return $html;
    }
    
    protected static function form_output($id = null)
    {
        return '
        <div class="report-table-form" id="WPDBQRT_admin_form_' . htmlspecialchars($id, ENT_QUOTES) . '">
        <h3 class="report-table-header">DBQuery Report Table  ' . htmlspecialchars($id, ENT_QUOTES) . " " . self::delete_button($id) . '</h3>
        <h4>Remember to make a backup of your database before proceeding and you must only use SELECT statements in your query or you might break your database</h4>
        <label>Enter query</label>
        <br>
        <form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
            <textarea cols="150" rows="4" name="dbquery_report_table_query">' . htmlspecialchars(DBQueryReportTableModel::get_dbquery_report_table_query($id), ENT_QUOTES) . '</textarea>
            <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '"/>
            <input type="hidden" name="action" value="dbquery_report_dbquery_update_query" />
            <br>
            ' . wp_nonce_field( 'dbquery_report_dbquery_update_query', 'dbquery_report_dbquery_update_query_nonce' ) . '
            <input class="update-btn" type="submit" name="dbquery_report_dbquery_update_query_submit" value="Update" />
        </form>
        <br>
        ' . DBQueryReportTable::dbquery_report_table_validate_error( htmlspecialchars(DBQueryReportTableModel::get_dbquery_report_table_query($id, ENT_QUOTES) ) ) . '
        <br>
        <label>Enter HTML table head to be displayed before results of query - Only enter valid HTML table head elements</label>
        <br>
        &lt;thead&gt;
        <form method="POST" action="' . esc_url( admin_url( 'admin-post.php' )) . '">
            <textarea cols="150" rows="8" name="dbquery_report_table_head_content">' .  htmlspecialchars(DBQueryReportTableModel::get_dbquery_report_table_head_content($id), ENT_QUOTES) . '</textarea>
            <br>
            <input type="hidden" name="dbquery_report_table_id" value="' . htmlspecialchars($id, ENT_QUOTES) . '" />
            <input type="hidden" name="action" value="report_table_head_update_query" />
            &lt;/thead&gt; 
            <br>
            ' . wp_nonce_field() . '
            <input class="update-btn" type="submit" name="report_table_head_update_query_submit" value="Update" />
        </form>
        </div>';
    }
}