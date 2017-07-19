<?php
/**
 * @package    Ez Texting SMS Notification plugin
 * @author     Viktor <viktor@eztexting.com>
 * @copyright  2012 Ez Texting https://www.eztexting.com
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    1.4.1
 * @since      1.0
 */

/**
 * Utility: provides the URL to something in this plugin dir.
 *
 * @param string $path The path within this plugin dir
 * @return string The absolute URL
 **/
function ezsmsn_url( $path ) {
	$folder = rtrim( basename( dirname( __FILE__ ) ), '/' );
	$dir = trailingslashit( WP_PLUGIN_DIR ) . $folder;
	$url = plugins_url( $folder );
	return $url . $path;
}

/**
 * generate paginator html controll
 *
 * @param integer $current_page
 * @param integer $page_size
 * @param integer $total_items
 * @param integer $total_page
 * @return html of paginator
 */
function ezsmsn_build_pager_controll($current_page, $page_size = 10, $total_items, $total_page)
{
	$base_link = ezsmsn_current_page_url();

	$pager_content  = __('Subscribers', 'ezsmsn') . '&nbsp;:&nbsp;';
	$pager_content .= ( ( ($current_page) * $page_size ) - $page_size ) + 1 . ' - '. ( ( $current_page ) * $page_size );
	$pager_content .= '&nbsp;' . __( 'of', 'ezsmsn' ) . '&nbsp;' . $total_items;

	$base_link = remove_query_arg( array('p', 'pageSize'), $base_link);

	// build first link
	if( $current_page == 1 )
		$first_link = '<span class="ezsmsn-first disabled">' . __('First', 'ezsmsn') . '</span>';
	else
		$first_link = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( $base_link ),
			'ezsmsn-first',
			__( 'First', 'ezsmsn' )
		);

	$pager_content .= '&nbsp;|&nbsp;' . $first_link;

	//build previous link
	if( $current_page == 1 )
		$previous_link = '<span class="ezsmsn-previous disabled">' . __('Previous', 'ezsmsn') . '</span>';
	else
		$previous_link = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( add_query_arg('p', ( $current_page - 1 ), $base_link) ),
			'ezsmsn-previous',
			__('Previous', 'ezsmsn')
		);

	$pager_content .= '&nbsp;|&nbsp;' . $previous_link;

	//build next link
	if( $current_page == $total_page )
		$next_page = '<span class="ezsmsn-next disabled">' . __( 'Next', 'ezsmsn' ) . '</span>';
	else
		$next_page = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( add_query_arg( 'p', ($current_page + 1), $base_link ) ),
			'ezsmsn-next',
			__( 'Next', 'ezsmsn' )
		);

	$pager_content .= '&nbsp;|&nbsp;' . $next_page;

	//build last link
	if( $current_page == $total_page)
		$last_link = '<span class="ezsmsn-last disabled">' . __( 'Last', 'ezsmsn' ) . '</span>';
	else
		$last_link = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( add_query_arg('p', $total_page, $base_link) ),
			'ezsmsn-last',
			__( 'Last', 'ezsmsn' )
		);

	$pager_content .= '&nbsp;|&nbsp;' . $last_link;

	$pager_container = "<div class='ezsmsn-pager'>$pager_content</div>";
	return $pager_container;
}
/**
 * get current page url
 *
 * @return string url
 */
function ezsmsn_current_page_url()
{
	return ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * The database character collate.
 *
 * @return string The database character collate.
 **/
function ezsmsn_db_charset_collate() {
	global $wpdb;
	$charset_collate = '';
	if ( ! empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty($wpdb->collate) )
		$charset_collate .= " COLLATE $wpdb->collate";
	return $charset_collate;
}