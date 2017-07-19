<?php

/**
 * @package    Ez Texting SMS Notification plugin
 * @author     Viktor <viktor@eztexting.com>
 * @copyright  2012 Ez Texting https://www.eztexting.com
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    1.4.1
 * @since      1.0
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
*/

require_once( 'class-ezsmsn-plugin.php' );

/**
 * The main class of the plugin.
 */
class EZSMSN extends EZSMSN_Plugin {

	/**
	 * The version of this file, for the purposes of rewrite
	 * refreshes, JS/CSS cache bursting, etc.
	 *
	 * @var int
	 **/
	protected $version;

	/**
	 * for test purpose
	 *
	 * @var WP_Functions
	 */
	public static $_wp_functions = null;

	/**
	 * Start point.
	 * init base action
	 */
	public function  __construct() {
		$this->setup( 'ezsmsn' );
		if ( is_admin() ) {
			$this->add_action( 'admin_init' );
			$this->add_action( 'admin_menu' );
			$this->add_action( 'wp_ajax_ezsmsn_subscribe', 'ajax_subscribe' );
			$this->add_action( 'wp_ajax_nopriv_ezsmsn_subscribe', 'ajax_subscribe' );
		}

		$this->add_action( 'transition_post_status', null, null, 3 );
		$this->add_action( 'init' );
		$this->version = 1;
	}

	/**
	 * Hook admin init action
	 */
	public function admin_init() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';
		wp_enqueue_script( 'ezsmsn-admin', $this->url( "/js/admin{$suffix}.js" ), array( 'jquery' ), $this->version );
		$localized = array(
			'top_menu_label' => __( 'Ez Texting SMS', 'ezsmsn' ),
		);
		wp_localize_script( 'ezsmsn-admin', 'ezsmsn', $localized );
		wp_enqueue_style( 'ezsmsn-admin', $this->url( "/css/admin{$suffix}.css" ), array(), $this->version, 'all' );

		EZSMSN_Subscribers::check_update();
	}

	/**
	 * Hooks the init action to:
	 * * Process unsubscribe requests
	 *
	 * @return void
	 **/
	public function init()
	{
		$this->maybe_unsubscribe_request();
	}

	/**
	 * Hooks the WP admin_menu function to add in our top
	 * level menu and various sub-pages.
	 *
	 * @return void
	 **/
	public function admin_menu() {
		// Create a top level SMS menu item, and some sub-items
		$hook_name = add_menu_page( __( 'Ez Texting SMS', 'ezsmsn' ), __( 'Ez Texting SMS', 'ezsmsn' ), 'edit_users', 'ezsmsn_main', array( $this, 'manage_subscribers' ) );
        $this->add_action( "load-$hook_name", 'load_subscribers_action' );

		$hook_name = add_submenu_page( 'ezsmsn_main', __( 'Your Subscribers', 'ezsmsn' ), __( 'Your Subscribers', 'ezsmsn' ), 'edit_users', 'ezsmsn_main', array( $this, 'manage_subscribers' ) );
        $this->add_action( "load-$hook_name", 'load_settings' );

		$hook_name = add_submenu_page( 'ezsmsn_main', __( 'SMS Settings', 'ezsmsn' ), __( 'SMS Settings', 'ezsmsn' ), 'publish_posts', 'ezsmsn_options', array( $this, 'manage_options' ) );
		$this->add_action( "load-$hook_name", 'load_settings' );

		$hook_name = add_submenu_page( 'ezsmsn_main', __( 'Send SMS', 'ezsmsn' ), __( 'Send SMS', 'ezsmsn' ), 'publish_posts', 'ezsmsn_sendsms', array( $this, 'send_sms' ) );
		$this->add_action( "load-$hook_name", 'load_send_message' );
	}

	/**
	 * Callback function for rendering options page
	 *
	 * @global WordPress database object $wpdb
	 * @return void
	 */
	public function manage_options() {
		global $wpdb;

		$vars = array();

		$vars['ez_user']                 = $this->get_option( 'ez_user' );
		$vars['ez_password']             = $this->get_option( 'ez_password' );
		$vars['ezsmsn_new_post']         = (bool) $this->get_option( 'ezsmsn_new_post' );
		$vars['ezsmsn_new_post_message'] = $this->new_post_message();


		$vars['length_post_title']   = (int) ceil( $wpdb->get_var( " SELECT AVG( CHAR_LENGTH( post_title ) ) FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' LIMIT 500 " ) );
		$vars['length_post_author']  = (int) ceil( $wpdb->get_var( " SELECT AVG( CHAR_LENGTH( display_name ) ) FROM $wpdb->users LIMIT 500 " ) );

		$max_post_id = $wpdb->get_var( " SELECT MAX(ID) FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' " );

		$vars['length_post_url']  = strlen( $this->get_post_short_link( $max_post_id ) );
		$vars['length_blog_name'] = strlen( get_bloginfo( 'name' ) );
		$vars['length_blog_url']  = strlen( home_url() );

		$this->render_admin( 'options.php', $vars);
	}

	/**
	 * Hook the load Ez SMS setting page
	 *
	 * @return void
	 */
	public function load_settings() {
		if ( ! isset($_POST[ '_ezsmsn_nonce' ]) )
			return;

		if( isset($_POST['action']) ) {
			switch ( $_POST['action'] ) {
				case 'save-settings':
					$this->save_settings();
					break;
				case 'delete-uninstall':
					$this->confirm_delete_uninstall();
					break;
				case 'confirm-delete-uninstall':
					$this->delete_uninstall();
					break;
			}
		}

		$wp_function = self::get_wp_functions();
		$wp_function->wp_redirect(admin_url( '/admin.php?page=ezsmsn_options' ), true);
	}

	/**
	 * Callback function for rendering send sms page
	 *
	 * @return void
	 */
	public function send_sms() {
		$vars = array();
		$vars['length_blog_name'] = strlen( get_bloginfo( 'name' ) );
		$vars['length_blog_url']  = strlen( home_url() );

		$this->render_admin( 'send-sms.php', $vars);
	}

	/**
	 * Hook the load send message page
	 *
	 * @return void
	 */
	public function load_send_message() {

		if ( ! isset($_POST[ '_ezsmsn_nonce' ]) )
			return;

		$wp_function = self::get_wp_functions();
		$wp_function->check_admin_referer( 'ezsmsn-send-sms', '_ezsmsn_nonce' );

		$post = $_POST;
		$message = ( isset($_POST['ezsmsn_message']) ) ? trim($_POST['ezsmsn_message']) : '';

		if( empty($message) ) {
			$this->set_admin_error( __("The Message field is required!", 'ezsmsn') );
			$wp_function->wp_redirect(ezsmsn_current_page_url(), true );
		}

		$message = $this->get_original_message($message);

		$response = ezsmsn_send_sms_to_subscribers($message);

		if($response->failed) {
			if( $response->response->get_status() == 401 ) {
				$this->set_admin_error(
					__('Your Ez Texting username and/or password are missing or incorrect.', 'ezsmsn')
					.' <a href="'.get_admin_url().'admin.php?page=ezsmsn_options">'
					.__('Please visit the settings page', 'ezsmsn')
					.'</a>'
				);
			}

			if( $response->response->get_status() == 500 ) {
				$this->set_admin_error(
						__('Something is not working properly.', 'ezsmsn')
						.'<a href="http://www.eztexting.com/ticketing-contact.php">'
						.__('Please contact Ez Texting support.', 'ezsmsn')
						. '</a>'
				);

			}

			if( $response->response->get_status() == 403 ) {
				$this->set_admin_error(__(implode('<br>', $response->response->get_errors()), 'ezsmsn'));
			}
		} else {
			$this->set_admin_notice( sprintf( __('Your Message has been sent to %d subscribers.', 'ezsmsn'), $response->recipient_counts ) );
		}
		$wp_function->wp_redirect( ezsmsn_current_page_url(), true );
	}

	/**
	 * Callback function for rendering subscribers page
	 *
	 * @return void
	 */
	public function manage_subscribers() {
		$page = ( !isset($_GET['p']) || !is_numeric($_GET['p']) ) ? 1 : $_GET['p'];

		$total_items = EZSMSN_Subscribers::get_count();
		$page_size  = 20;
		$total_page = ceil( $total_items / $page_size );

		$vars = array(
			'subscribers'  => EZSMSN_Subscribers::fetch_all( $page, $page_size ),
			'current_page' => $page,
			'page_size'    => $page_size,
			'total_items'  => $total_items,
			'total_page'   => $total_page
		);

		$this->render_admin( 'subscribers.php', $vars );
	}

	/**
	 * Hook the load action for subscribe page
	 *
	 * @return void
	 */
	public function load_subscribers_action() {
		if ( ! isset($_POST[ '_ezsmsn_nonce' ]) )
			return;

		$wp_function = self::get_wp_functions();
		$wp_function->check_admin_referer( 'ezsmsn-subscribe-action', '_ezsmsn_nonce' );

		$subscribers_ids = isset ($_POST['subscriber_ids']) ? $_POST['subscriber_ids'] : array();

		$action  = ( isset($_POST['action']) && $_POST['action'] == 'delete' );
		$action2 = ( isset($_POST['action2']) && $_POST['action2'] == 'delete' );

		if( $action || $action2 ) {

			if( empty($subscribers_ids) ) {
				$this->set_admin_notice( __('Please select subscribers to delete.', 'ezsmsn') );
				$wp_function->wp_redirect( ezsmsn_current_page_url(), true );
			}

			$result = EZSMSN_Subscribers::delete( $subscribers_ids );

			$this->set_admin_notice( sprintf( __( 'Deleted %d subscribers.', 'ezsmsn' ), $result ) );
			$wp_function->wp_redirect( ezsmsn_current_page_url(), true );
		}
	}

	/**
	 * Hook for wp_ajax_ezsmsn_subscribe. prcess subscibe form
	 *
	 * @return void
	 */
	public function ajax_subscribe() {
		$response = array( 'success' => true );

		//filter phoen number
		$phone_number = trim( $_POST['phone_number'] );
		$phone_number = preg_replace('/[^\d]/', '', $phone_number);

		if ( strlen( $phone_number ) == 11 && substr( $phone_number, 0, 1 ) == '1' )
			$phone_number = substr( $phone_number, 1 );

		//validate phone number
		if( strlen( $phone_number ) != 10 )
			$response = array( 'success' => false, 'messages' => __( 'That is not a valid phone number', 'ezsmsn' ) );

		if( $response['success'] ) {
			$exist_phone_number = EZSMSN_Subscribers::fetch_row_by_phone_number($phone_number);

			if( ! empty($exist_phone_number) && $exist_phone_number->opt_out == 1)
				$response = array(
					'success' => false,
					'messages' => __('That phone number is opted out from Ez Texting services', 'ezsmsn')
				);
			elseif( !empty($exist_phone_number) )
				$response = array(
					'success' => false,
					'messages' => __('That phone number is already subscribed to this list', 'ezsmsn')
				);
			else
				EZSMSN_Subscribers::save_number($phone_number);

		}

		header( "Content-Type: application/json" );
		echo json_encode( $response );
		exit;
	}

	/**
	 * Hook the transition_post_status for sending notifications
	 * @param string $new_status Transition to this post status.
	 * @param string $old_status Previous post status.
	 * @param object $post Post data.
	 */
	public function transition_post_status($new_status, $old_status, $post) {
		if( $new_status == 'publish' && $old_status != 'publish' && (bool)$this->get_option('ezsmsn_new_post') ) {
			$message  = $this->get_original_message( $this->new_post_message(), $post );
			$response = ezsmsn_send_sms_to_subscribers( $message );

            if( $response->failed && $response->response->get_status() == 403 ) {
                $this->set_admin_error(__('EzTexting: ' . implode('<br>', $response->response->get_errors()), 'ezsmsn'));
            }
        }
	}

	/**
	 * setup the default notification message template if message not set
	 *
	 * @return string
	 */
	protected function new_post_message() {
		$default = sprintf( __( 'New %1$s post: %2$s', 'mbe2s' ), '{blog_name}', '{post_url}' );
		return stripslashes( $this->get_option( 'ezsmsn_new_post_message', $default ) );
	}

	/**
	 * prepare message to send via sms
	 *
	 * @param string $message
	 * @param object $post Post data.
	 * @return string original message to send
	 */
	protected function get_original_message($message, $post = null) {
		$search = array(
			'{blog_name}',
			'{blog_url}'
		);

		$replace = array(
			get_bloginfo('name'),
			home_url()
		);

		if( $post ) {
			$account = new WP_User( $post->post_author );

			$search[] = '{post_author}';
			$search[] = '{post_title}';
			$search[] = '{post_url}';

			$replace[] = $account->display_name;
			$replace[] = get_the_title( $post->ID );
			$replace[] = $this->get_post_short_link( $post->ID );
		}

		return str_replace( $search, $replace, $message );
	}

	/**
	 * Save settings from the Options page.
	 *
	 * @return void
	 **/
	protected function save_settings() {
		$wp_function = self::get_wp_functions();
		$wp_function->check_admin_referer( 'ezsmsn-save-settings', '_ezsmsn_nonce' );

		$api = new EZSMSN_Sending($_POST['ez_user'], $_POST['ez_password']);
		$can_login = $api->check_can_login();

		if( ! $can_login ) {
			$this->update_option( 'ezsmsn_new_post', false );
			$this->set_admin_error( __('Your Ez Texting username and/or password are missing or incorrect.', 'ezsmsn') );
			$wp_function->wp_redirect(ezsmsn_current_page_url());
			return;
		}

		if( (bool) @$_POST['ezsmsn_new_post'] ) {
			if( empty($_POST['ezsmsn_new_post_message']) ) {
				$this->update_option( 'ezsmsn_new_post', false );
				$this->update_option( 'ezsmsn_new_post_message', '' );
				$this->set_admin_error( __("The Message field is required", 'ezsmsn') );
				$wp_function->wp_redirect(ezsmsn_current_page_url());
				return;
			}
		}

		$this->update_option( 'ez_user', ( isset($_POST['ez_user']) ? $_POST['ez_user'] : '' ) );
		$this->update_option( 'ez_password', ( isset($_POST['ez_password']) ? $_POST['ez_password'] : '' ) );
		$this->update_option( 'ezsmsn_new_post', (bool) @$_POST['ezsmsn_new_post'] );
		$this->update_option( 'ezsmsn_new_post_message', ( isset( $_POST['ezsmsn_new_post_message'] ) ? $_POST['ezsmsn_new_post_message'] : '') );

		$this->set_admin_notice( __( 'Settings saved.', 'ezsmsn' ) );
	}

	/**
	 * Rendering confirmation page
	 */
	protected function confirm_delete_uninstall() {

		$wp_function = self::get_wp_functions();
		$wp_function->check_admin_referer( 'ezsmsn-delete-uninstall', '_ezsmsn_nonce' );

		if ( ! $wp_function->current_user_can( 'activate_plugins' ) )
			$wp_function->wp_die( __( 'Sorry, you are not allowed to deactivate plugins.', 'ezsmsn' ) );

		$html = $this->capture_admin( 'confirm-delete-uninstall.php', array() );
		$wp_function->wp_die( $html, __( 'Confirm uninstall and delete!', 'ezsmsn' ), array( 'response' => 200 ) );
	}

	/**
	 * Rendering delete plugin page
	 */
	protected function delete_uninstall() {

		$wp_function = self::get_wp_functions();
		$wp_function->check_admin_referer( 'ezsmsn-confirm-delete-uninstall', '_ezsmsn_nonce' );

		if ( ! $wp_function->current_user_can( 'activate_plugins' ) )
			$wp_function->wp_die( __( 'Sorry, you are not allowed to deactivate plugins.', 'ezsmsn' ) );

		EZSMSN_Subscribers::uninstall();

		delete_option( $this->name );

		$wp_function->deactivate_plugins( $this->folder . '/ez-texting-sms-notifications.php' );
		$wp_function->wp_die( sprintf( __( "Ez Texting: The SMS notifications plugin has been deactivated and all its data has been deleted; return to the <a href='%s'>Dashboard</a>.", 'ezsmsn' ), admin_url() ), __( 'Confirm uninstall and delete!', 'ezsmsn' ), array( 'response' => 200 ) );
	}

	/**
	 *  Callback function for unsubscribe request
	 *
	 * @return void
	 */
	protected function maybe_unsubscribe_request()
	{
		if ( ! isset ($_REQUEST['ezsmsn-unsubscribe']) )
			return;

		$vars = array();
		$removed = false;

		if( isset($_POST['ezsmsn-phone-number']) ) {

			$vars['ezsmsn_phone_number'] = $_POST['ezsmsn-phone-number'];

			$phone_number = trim( $_POST['ezsmsn-phone-number'] );
			$phone_number = preg_replace( '/[^\d]/', '', $phone_number );

			if ( strlen( $phone_number ) == 11 && substr( $phone_number, 0, 1 ) == '1' )
				$phone_number = substr( $phone_number, 1 );

			if( strlen($phone_number) != 10 ) {
				$vars['error'] = __('Is not a valid phone number', 'ezsmsn');
			} else {
				$subscriber = EZSMSN_Subscribers::fetch_row_by_phone_number($phone_number);
				if( $subscriber )
				{
					$removed = EZSMSN_Subscribers::delete( $subscriber->ID );
				}
				else
				{
					$vars['error'] = sprintf( __( "We could not find the phone number %s to unsubscribe.", 'ezsmsn' ), $vars['ezsmsn_phone_number'] );
				}
			}
		}


		if ( $removed )
			$html = $this->capture( 'unsubscribed.php', $vars );
		else
			$html = $this->capture( 'unsubscribe.php', $vars );

		$wp_function = self::get_wp_functions();
		$wp_function->wp_die( $html, __( 'Unsubscribe from SMS Notifications', 'ezsmsn' ), array( 'response' => 200 ) );
	}

	protected function get_post_short_link($id) {
		return ( ( '' != get_option('permalink_structure') ) ?  wp_get_shortlink($id) : get_permalink($id) );
	}

	public static function set_wp_functions(WP_Functions $class) {
		self::$_wp_functions = $class;
	}

	public static function get_wp_functions() {

		if( empty(self::$_wp_functions) )
			self::$_wp_functions = new WP_Functions();

		return self::$_wp_functions;
	}

}

$ezsms = new EZSMSN();