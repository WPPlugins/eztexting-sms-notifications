<?php

/**
 * @package    Ez Texting SMS Notification plugin
 * @author     Viktor <viktor@eztexting.com>
 * @copyright  2011 Ez Texting https://www.eztexting.com
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    1.4.1
 * @since      1.0
 */

/*  Copyright 2011 EzTexting

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/


class EZSMSN_Subscribers {

	/**
	 * The current version of the class
	 */
	const VERSION = 1;

	/**
	 * Check for updating db structure
	 *
	 * @global $wpdb WordPress Database Object
	 * @return void
	 */
	public static function check_update() {
		global $wpdb;
		$version = get_option( 'ezsmsn-subscribers-version', 0 );
		$done_upgrade = false;

		if( self::VERSION > $version ) {
			error_log( "EZSMSN: Upgrading subscribers DB table" );
			$charset_collate = ezsmsn_db_charset_collate();
			$table = $wpdb->prefix . 'ez_subscribers';
			$wpdb->query( "DROP TABLE $table" );
			$sql  = " CREATE TABLE $table ( ";
			$sql .= "    `ID` int(10) unsigned NOT NULL auto_increment, ";
			$sql .= "    `phone_number` varchar(10) NOT NULL, ";
			$sql .= "    `opt_out` smallint(1) unsigned NOT NULL default '0', ";
			$sql .= "    `created` datetime NOT NULL, ";
			$sql .= "    PRIMARY KEY  (`ID`)";
			$sql .= ") $charset_collate ";
			$wpdb->query( $sql );
			$done_upgrade = true;
		}

		if ( $done_upgrade ) {
			error_log( "EZSMSN: Done upgrade" );
			update_option( 'ezsmsn-subscribers-version', self::VERSION );
		}
	}

	/**
	 * Save phone number to subscribers table.Returns false if errors, or the number of rows
	 * affected if successful.
	 *
	 * @global $wpdb WordPress Database Object
	 * @param string $phone_number
	 * @return count inserted row
	 */
	public static function save_number($phone_number) {
		global $wpdb;
		$table = $wpdb->prefix . 'ez_subscribers';
		$data = array(
			'phone_number' => $phone_number,
			'created'      => date('Y-m-d H:i:s')
		);
		return $wpdb->insert( $table, $data );
	}

	/**
	 * Get subscriber by phone number
	 *
	 * @global $wpdb WordPress Database Object
	 * @param string $phone_number
	 * @return subscriber object
	 */
	public static function fetch_row_by_phone_number( $phone_number ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ez_subscribers';
		return $wpdb->get_row( $wpdb->prepare( " SELECT * FROM $table WHERE phone_number = %s ", $phone_number ) );
	}

	/**
	 * Get all subscribers
	 *
	 * @global $wpdb WordPress Database Object
	 * @param int $page - current page
	 * @param int $pageSize - subscribers count to get
	 * @param boolean $opt_out - opt_out status
	 * @return subscribers objects
	 */
	public static function fetch_all($page = 1, $pageSize = 10, $opt_out = null) {
		global $wpdb;
		$start = ( $page - 1 ) * $pageSize;
		$table = $wpdb->prefix . 'ez_subscribers';
		$where = '';

		if( $opt_out !== null ) {
			$where = ' AND `opt_out` = '.(int)$opt_out.' ';
		}

		$query = $wpdb->prepare( " SELECT * FROM $table WHERE 1=1 $where ORDER BY `created` DESC LIMIT %d, %d ", $start, $pageSize );
		return $wpdb->get_results( $query );
	}

	/**
	 * Get tottal count site subscribers
	 *
	 * @global $wpdb WordPress Database Object
	 * @return int
	 */
	public static function get_count() {
		global $wpdb;
		$table = $wpdb->prefix . 'ez_subscribers';
		return $wpdb->get_var(" SELECT COUNT(*) as Count FROM $table");
	}

	/**
	 * Delete subscribers from subscribers table.Returns false if errors, or the number of rows
	 * affected if successful.
	 *
	 * @global $wpdb WordPress Database Object
	 * @param int|array $subscriber_ids
	 * @return int
	 */
	public static function delete($subscriber_ids) {
		global $wpdb;
		if ( ! is_array($subscriber_ids) )
			$subscriber_ids = (array) $subscriber_ids;

		$table          = $wpdb->prefix . 'ez_subscribers';
		$subscriber_ids = join( ', ', $subscriber_ids );
		$sql = "DELETE FROM $table WHERE `ID` IN ( $subscriber_ids ) ";
		return (int) $deleted = $wpdb->query( $sql );
	}

	/**
	 * Opt out subscribers. Returns false if errors, or the number of rows
	 * affected if successful.
	 *
	 * @global $wpdb WordPress Database Object
	 * @param int|array $subscriber_ids
	 * @return <type>
	 */
	public static function opt_outs($subscriber_ids) {
		global $wpdb;
		if ( ! is_array($subscriber_ids) )
			$subscriber_ids = (array) $subscriber_ids;

		$table = $wpdb->prefix . 'ez_subscribers';
		$subscriber_ids = join( ', ', $subscriber_ids );
		$sql = "UPDATE $table SET `opt_out` = 1 WHERE `ID` IN ( $subscriber_ids ) ";
		return (int) $deleted = $wpdb->query( $sql );
	}

	/**
	 * Delete subscribers data. Drop the $wpdb->prefix . 'ez_subscribers' table,
	 * clear delete ezsmsn-subscribers-version option
	 *
	 * @global $wpdb WordPress Database Object
	 * @return void;
	 */
	public static function uninstall() {
		global $wpdb;
		$table = $wpdb->prefix . 'ez_subscribers';
		$wpdb->query( "DROP TABLE $table" );
		delete_option('ezsmsn-subscribers-version');
	}
}
