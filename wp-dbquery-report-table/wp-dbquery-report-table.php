<?php
/**
 * Plugin Name: DBQuery Report Table
 * Plugin URI: https://github.com/MatthewCharlton/wp-dbquery-report-table/
 * Description: This plugin allows you to query the DB and outputs the results in a table that you can show via a shortcode
 * Version: 1.4.1
 * Author: Matt Charlton
 * Author URI: http://mattcharlton.id.au
 * License: GPLv2 or later
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2017 Matthew Charlton
*/

defined('ABSPATH') or die('Computer says no!');

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'You shall not pass!';
	exit;
}

if(!defined('WPDBQRT__PLUGIN_DIR'))
{
	define( 'WPDBQRT__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WPDBQRT__PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	
	require_once( WPDBQRT__PLUGIN_DIR . 'wp-dbquery-report-table.class.php' );
	require_once( WPDBQRT__PLUGIN_DIR . 'wp-dbquery-report-table-view.class.php' );
	require_once( WPDBQRT__PLUGIN_DIR . 'wp-dbquery-report-table-model.class.php' );
	
	register_activation_hook( __FILE__, array( 'DBQueryReportTableModel', 'setup_database_tables' ) );
	register_uninstall_hook( __FILE__, array( 'DBQueryReportTableModel', 'dbquery_report_table_remove' ) );
	
	$WPDBQRT = new DBQueryReportTable();
}
