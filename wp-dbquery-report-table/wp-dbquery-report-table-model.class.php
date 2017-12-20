<?php

defined('ABSPATH') or die('Computer says no!');

class DBQueryReportTableModel
{
    public static function setup_database_tables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'dbquery_report_table';
        $result = $wpdb->get_results("SHOW TABLES LIKE '$table_name'", ARRAY_A);
        if (count($result) === 0) {
            $setupPluginQuery = "SELECT * FROM " . $wpdb->prefix . "comments";
            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $wpdb->prefix . 'dbquery_report_table';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
				id int NOT NULL AUTO_INCREMENT,
				report_table_query TEXT NULL,
				report_table_head_content TEXT NULL, 
				PRIMARY KEY  (id)
			) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            $wpdb->query("INSERT INTO $table_name (report_table_query) VALUES ('$setupPluginQuery')");
        }
    }

    public static function dbquery_report_table_remove()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'dbquery_report_table';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

       
    public static function dbquery_get_form_ids_query()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbquery_report_table';
        $ids_array = $wpdb->get_results("SELECT id FROM $table_name", ARRAY_A);
        return $ids_array;
    }

    public static function dbquery_report_new_form_query()
    {
        global $wpdb;
        if (! isset($_POST['dbquery_report_new_form_query_nonce']) || ! wp_verify_nonce($_POST['dbquery_report_new_form_query_nonce'], 'dbquery_report_new_form_query')) {
            print 'Sorry, your nonce did not verify.';
            die();
        } else {
            if (isset($_POST['dbquery_report_table_id']) && isset($_POST['new_dbquery_report_table'])) {
                $id = intval($_POST['dbquery_report_table_id']);
                if ($id === 0) {
                    set_transient(get_current_user_id() . '_wp_query_report_table_error_notice', __('Something went wrong, new DBQuery Report Table was not created!', 'textdomain'));
                    wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
                    die();
                }
                $table_name = $wpdb->prefix . 'dbquery_report_table';
                $wpdb->query("INSERT INTO $table_name (id) VALUES ('$id')");
                set_transient(get_current_user_id() . '_wp_query_report_table_success_notice', __('New DBQuery Report Table created!', 'textdomain'));
                wp_redirect(admin_url("admin.php?page=dbquery-report-table-form"));
                die();
            }
        }
    }

    public static function dbquery_report_delete_form_query()
    {
        global $wpdb;
        if (! isset($_POST['dbquery_report_delete_form_query_nonce']) || ! wp_verify_nonce($_POST['dbquery_report_delete_form_query_nonce'], 'dbquery_report_delete_form_query')) {
            print 'Sorry, your nonce did not verify.';
            die();
        } else {
            if (isset($_POST['dbquery_report_table_id']) && isset($_POST['delete_dbquery_report_table'])) {
                $id = intval($_POST['dbquery_report_table_id']);
                if ($id === 0) {
                    wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
                    die();
                }
                $table_name = $wpdb->prefix . 'dbquery_report_table';
                $wpdb->query("DELETE FROM $table_name WHERE id = '$id'");
                set_transient(get_current_user_id() . '_wp_query_report_table_error_notice', __('DBQuery Report Table ' . htmlspecialchars($id, ENT_QUOTES) . ' was deleted!', 'textdomain'));
                wp_redirect(admin_url("admin.php?page=dbquery-report-table"));
                die();
            }
        }
    }

    // MySQL update data queries
    // update table head HTML
    public static function dbquery_report_table_head_update_query()
    {
        if (! isset($_POST['dbquery_report_table_head_update_query_nonce']) || ! wp_verify_nonce($_POST['dbquery_report_table_head_update_query_nonce'], 'dbquery_report_table_head_update_query')) {
            die();
        } else {
            if (isset($_POST['dbquery_report_table_head_content']) && !empty($_POST['dbquery_report_table_head_content'])  && isset($_POST['dbquery_report_table_id'])) {
                global $wpdb;
                $id = intval($_POST['dbquery_report_table_id']);
                if ($id < 1) {
                    die();
                }
                $submittedElements = $_POST['dbquery_report_table_head_content'];
                $submittedElements = preg_replace("/(\<table\>)|(\<\/table\>)|(\<thead\>)|(\<\/thead\>)|(\<script\>)|(\<\/script\>)|(\<a\>)|(\<\/a\>)|(\<link\>)|(\<area\>)|(\<img\>)|(\<embed\>)|(\<\/embed\>)|(\<iframe\>)|(\<\/iframe\>)|(\<video\>)|(\<audio\>)|(\<html\>)|(\<\/html\>)|(\<canvas\>)|(\<track\>)|(\<form\>)|(\<\/form\>)|(\<formaction\>)|(\<base\>)|(\<command\>)|(\<source\>)|(\<object\>)/imxU", "", strtolower($submittedElements));
                $table_name = $wpdb->prefix . 'dbquery_report_table';
                $wpdb->query("UPDATE $table_name SET report_table_head_content = '" . $submittedElements . "' WHERE id = $id");
                die('1');
            }
        }
    }

    // update report mysql query
    public static function dbquery_report_dbquery_update_query()
    {
        if (! isset($_POST['dbquery_report_dbquery_update_query_nonce']) || ! wp_verify_nonce($_POST['dbquery_report_dbquery_update_query_nonce'], 'dbquery_report_dbquery_update_query')) {
            die();
        } else {
            if (!isset($_POST['dbquery_report_table_query']) && !isset($_POST['dbquery_report_table_id']) && intval($_POST['dbquery_report_table_id']) < 1) {
                die();
            }
            global $wpdb;
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
                    die('1');
                } else {
                    die();
                }
            } else {
                die();
            }
        }
    }

    // return table head HTML from DB
    public static function get_dbquery_report_table_head_content($id = null)
    {
        if ($id == intval($id) > 0) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'dbquery_report_table';
            $query = $wpdb->get_results("SELECT report_table_head_content FROM $table_name WHERE id = $id");
            foreach ($query as $key => $value) {
                if ($key == 'report_table_head_content') {
                    return $value->report_table_head_content;
                }
            }
        }
        return;
    }

    // return dbquery query from DB
    public static function get_dbquery_report_table_query($id = null)
    {
        if ($id == intval($id) > 0) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'dbquery_report_table';
            $query = $wpdb->get_results("SELECT report_table_query FROM $table_name WHERE id = '$id'");
            foreach ($query as $key => $value) {
                if ($key == 'report_table_query') {
                    return $value->report_table_query;
                }
            }
        }
        return;
    }
}
