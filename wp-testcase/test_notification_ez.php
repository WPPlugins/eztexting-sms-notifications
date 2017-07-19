<?php
/**
 * @see http://codex.wordpress.org/Automated_Testing
 */
class EZNotificationPluginDataTest extends WPTestCase {
    function setUp() {
        parent::setUp();
        require_once WP_PLUGIN_DIR . '/ez-texting-sms-notifications/ez-texting-sms-notifications.php';
    }

    function tearDown() {
        parent::tearDown();
    }

    public function test_get_plugin_data() {

        $data = get_plugin_data( WP_PLUGIN_DIR . '/ez-texting-sms-notifications/ez-texting-sms-notifications.php' );
        
        $default_headers = array(
                'Name' => 'Ez Texting: Sms notifications',
                'Title' => '<a href="http://www.eztexting.com/" title="Visit plugin homepage">Ez Texting: Sms notifications</a>',
                'PluginURI' => 'http://www.eztexting.com/',
                'Version' => '1.0',
                'TextDomain' => '',
                'DomainPath' => ''
        );

        $this->assertTrue( is_array($data) );

        foreach($default_headers as $name => $value) {
            $this->assertTrue(isset($data[$name]));
            $this->assertEquals($value, $data[$name]);
        }
    }

    public function test_check_constant() {
        $this->assertEquals(EZSMSNOTIFY_VERSION, '1.0');
    }

    public function test_check_includes() {
        $this->assertTrue(class_exists('EZSMSN'));
        $this->assertTrue(class_exists('EZSMSN_Widget'));
        $this->assertTrue(class_exists('EZSMSN_Widget_Subscribe'));

        $this->assertTrue(class_exists('EZSMSN_Message'));
        $this->assertTrue(class_exists('EZSMSN_Response'));
        $this->assertTrue(class_exists('EZSMSN_Sending'));
    }
}

class EZNotificationPluginCoreTest extends _WPEmptyBlog {

    function setUp() {
        define('WP_ADMIN', true);
        
        require_once WP_PLUGIN_DIR . '/ez-texting-sms-notifications/ez-texting-sms-notifications.php';
        parent::setUp();
        define( 'SCRIPT_DEBUG', 1);
        $this->assertTrue(class_exists('EZSMSN'));

    }

    function tearDown() {
        parent::tearDown();
        
        global $wpdb;
        $table = $wpdb->prefix . 'ez_subscribers';
        $wpdb->query( "DROP TABLE $table" );
    }

    public function test_plugin_setup() {
        $core = new EZSMSN();
        $refl = new ReflectionObject($core);
        $properties = array(
            'name'   => 'ezsmsn',
            'folder' => 'ez-texting-sms-notifications',
            'type'   => 'plugin',
            'dir'    => WP_PLUGIN_DIR . '/ez-texting-sms-notifications',
            'url'    => plugins_url('ez-texting-sms-notifications')

        );

        foreach($properties as $property => $value) {
             $property = $refl->getProperty( $property );
             $property->setAccessible(true);
             $this->assertEquals($property->getValue($core), $value);
        }
    }
    
    public function test_action_init() {
        global $wp_filter;
        $wp_filter = array();

        $core = new EZSMSN();

        $this->assertNotNull($wp_filter['transition_post_status']);
        $this->assertNotNull($wp_filter['init']);
    }

    public function test_admin_action() {
        global $wp_filter;
        $wp_filter = array();

        $administrator_id = $this->_make_user( 'administrator' );
		wp_set_current_user( $administrator_id );
        
        $core = new EZSMSN();

        $this->assertNotNull($wp_filter['admin_init']);

        $this->assertNotNull($wp_filter['wp_ajax_ezsmsn_subscribe']);
        $this->assertNotNull($wp_filter['wp_ajax_nopriv_ezsmsn_subscribe']);

        $this->assertNotNull($wp_filter['transition_post_status']);
        $this->assertNotNull($wp_filter['init']);
    }
    
    public function test_do_action_admin_init() {
        
        global $wp_filter;
        $wp_filter = array();

        $core = new EZSMSN();

        do_action('admin_init');

        //check include js
        $this->assertTrue(isset($GLOBALS['wp_scripts']->registered['ezsmsn-admin']));
        $this->assertEquals($GLOBALS['wp_scripts']->registered['ezsmsn-admin']->src, plugins_url('ez-texting-sms-notifications').'/js/admin.dev.js');

        //check include admin css
        $this->assertTrue(isset($GLOBALS['wp_styles']->registered['ezsmsn-admin']));
        $this->assertEquals($GLOBALS['wp_styles']->registered['ezsmsn-admin']->src, plugins_url('ez-texting-sms-notifications').'/css/admin.dev.css');

        $this->assertEquals(get_option('ezsmsn-subscribers-version'), 1);

        global $wpdb;
        $table = $wpdb->prefix . 'ez_subscribers';
        $query = "SELECT 1
                 FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_TYPE='BASE TABLE'
                 AND TABLE_NAME='$table'";

        $this->assertNotNull($wpdb->get_row($query));
    }

    public function test_do_action_admin_menu() {
        global $_parent_pages, $wp_filter;
        $_parent_pages = $wp_filter = array();

        $core = new EZSMSN();

        do_action('admin_menu');

        $expected = array(
            'ezsmsn_main'    => 'http://example.com/wp-admin/admin.php?page=ezsmsn_main',
            'ezsmsn_options' => 'http://example.com/wp-admin/admin.php?page=ezsmsn_options',
            'ezsmsn_sendsms' => 'http://example.com/wp-admin/admin.php?page=ezsmsn_sendsms'
        );

         foreach ($expected as $name => $value) {
            $this->assertEquals($value, menu_page_url($name, false));
         }
    }

    public function test_do_action_admin_menu_not_admin() {
        global $_parent_pages;
        $_parent_pages = array();

         $old_id = get_current_user_id();

         $contributor_id = $this->_make_user( 'contributor' );
         wp_set_current_user( $contributor_id );

         $core = new EZSMSN();
         do_action('admin_menu');

         $expected = array(
            'ezsmsn_options' => 'http://example.com/wp-admin/admin.php?page=ezsmsn_options',
            'ezsmsn_sendsms' => 'http://example.com/wp-admin/admin.php?page=ezsmsn_sendsms'
         );

         foreach ($expected as $name => $value) {
            $this->assertEmpty(menu_page_url($name, false));
         }

         wp_set_current_user($old_id);
    }

}

class EZNotificationPluginCoreUnitTest extends _WPEmptyBlog {

    function setUp() {
        require_once WP_PLUGIN_DIR . '/ez-texting-sms-notifications/ez-texting-sms-notifications.php';
        
        parent::setUp();

        global $wpdb;
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

    }

    function tearDown() {
        parent::tearDown();
        
        global $wpdb;
        $table = $wpdb->prefix . 'ez_subscribers';
        $wpdb->query( "DROP TABLE $table" );
    }

    public function test_manage_subscribers_should_render_subscribers_page() {

        $vars = array(
            'subscribers'  => array(),
            'current_page' => 1,
            'page_size'    => 20,
            'total_items'  => 0,
            'total_page'   => 0
        );

        $mock = $this->getMock('EZSMSN', array('render_admin'));
        $mock->expects($this->once())
                 ->method('render_admin')
                 ->with($this->equalTo('subscribers.php'), $vars);

        $mock->manage_subscribers();
        
        EZSMSN_Subscribers::save_number('8634285794');
        $phone_number = EZSMSN_Subscribers::fetch_row_by_phone_number('8634285794');

        $vars = array(
            'subscribers'  => array($phone_number),
            'current_page' => 1,
            'page_size'    => 20,
            'total_items'  => 1,
            'total_page'   => 1
        );
        
        ;
       
        $mock = $this->getMock('EZSMSN', array('render_admin'));
        $mock->expects($this->once())
                 ->method('render_admin')
                 ->with($this->equalTo('subscribers.php'), $vars);

        $mock->manage_subscribers();
        EZSMSN_Subscribers::delete($phone_number->ID);
    }


    public function test_send_sms_should_render_send_sms_page() {

        $vars = array(
            'length_blog_name' => strlen( get_bloginfo( 'name' ) ),
            'length_blog_url'  => strlen( home_url() ),
        );

        $mock = $this->getMock('EZSMSN', array('render_admin'));
        $mock->expects($this->once())
                 ->method('render_admin')
                 ->with($this->equalTo('send-sms.php'), $vars);

        $mock->send_sms();
    }
    
    public function test_manage_options_should_render_manage_options_page() {

        $options = get_option('ezsmsn');
        
        add_option('ezsmsn', array('ez_user'=>'krot', 'ez_password' => 'med'));
        $this->_make_user('author', 'long-auth-login-for-test-purpose');
        $this->_insert_quick_posts(1, 'post', array('post_title' => 'post title length'));
      
        $vars = array(
            'ez_user'         => 'krot',
            'ez_password'     => 'med',
            'ezsmsn_new_post' => false,
            'length_blog_url' => strlen( home_url() ),
            'length_blog_name' => strlen( get_bloginfo( 'name' ) ),
            'ezsmsn_new_post_message' => 'New {blog_name} post: {post_url}',
            'length_post_author'      => strlen('long-auth-login-for-test-purpose'),
            'length_post_title'       => strlen('post title length'),
            'length_post_url'         => strlen(get_permalink(current(get_posts())->ID))

        );

        $mock = $this->getMock('EZSMSN', array('render_admin'));
        $mock->expects($this->once())
                 ->method('render_admin')
                 ->with($this->equalTo('options.php'), $vars);

        $mock->manage_options();

        $this->_delete_all_posts();
        delete_option('ezsmsn');
    }

    public function test_unsubscriber_request_should_render_unsubscribe_method() {

        $_REQUEST['ezsmsn-unsubscribe'] = 1;
        $_POST['ezsmsn-phone-number']   = '8634285794';

        EZSMSN_Subscribers::save_number('8634285794');

        $class = new EZSMSN();

        $mock = $this->getMock('EZSMSN', array('maybe_unsubscribe_request'));
        $mock->expects($this->once())
                 ->method('maybe_unsubscribe_request');

        $mock->init();
    }

    public function test_load_settings() {
        $_POST['_ezsmsn_nonce']  = '_nonce';
        $_POST['action']         = 'save-settings';


        $mock = $this->getMock('EZSMSN', array('save_settings'));
        $mock->expects($this->once())
                 ->method('save_settings');

        
        $mock_functions = $this->getMock('WP_Functions', array('wp_redirect'));
        $mock_functions->expects($this->once())
                       ->method('wp_redirect');

        $mock::set_wp_functions($mock_functions);

        $mock->load_settings();

        $_POST['_ezsmsn_nonce']  = '_nonce';
        $_POST['action']         = 'delete-uninstall';


        $mock = $this->getMock('EZSMSN', array('confirm_delete_uninstall'));
        $mock->expects($this->once())
                 ->method('confirm_delete_uninstall');


        $mock_functions = $this->getMock('WP_Functions', array('wp_redirect'));
        $mock_functions->expects($this->once())
                       ->method('wp_redirect');

        $mock::set_wp_functions($mock_functions);

        $mock->load_settings();

        $_POST['_ezsmsn_nonce']  = '_nonce';
        $_POST['action']         = 'confirm-delete-uninstall';


        $mock = $this->getMock('EZSMSN', array('delete_uninstall'));
        $mock->expects($this->once())
                 ->method('delete_uninstall');


        $mock_functions = $this->getMock('WP_Functions', array('wp_redirect'));
        $mock_functions->expects($this->once())
                       ->method('wp_redirect');

        $mock::set_wp_functions($mock_functions);

        $mock->load_settings();
    }

    public function test_save_setting_wrong_user() {
        delete_option('ezsmsn');
        $_POST['_ezsmsn_nonce']  = '_nonce';
        $_POST['action']         = 'save-settings';

        $core = new EZSMSN();

        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));
        
       $mock_functions->expects($this->at(1))
                      ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $mock_request = $this->getMock('EZSMSN_Request', array('send'));

        $response = new stdClass();
        $response->Code = 403;

        $http_response = new EZSMSN_Response($response);

        $mock_request->expects($this->once())
                     ->method('send')
                     ->will($this->returnValue($http_response));

        
        EZSMSN_Sending::set_http_request($mock_request);


        $core->load_settings();


        $options = get_option('ezsmsn');
        $this->assertEquals('Your Ez Texting username and/or password are missing or incorrect.', $options['admin_errors']['0']);
    }

    public function test_save_setting_empty_message() {
        delete_option('ezsmsn');
        $_POST['_ezsmsn_nonce']   = '_nonce';
        $_POST['action']          = 'save-settings';
        $_POST['ezsmsn_new_post'] = true;

        $core = new EZSMSN();

        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));

        $mock_functions->expects($this->at(1))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $mock_request = $this->getMock('EZSMSN_Request', array('send'));

        $response = new stdClass();
        $response->Code = 302;

        $http_response = new EZSMSN_Response($response);

        $mock_request->expects($this->once())
                     ->method('send')
                     ->will($this->returnValue($http_response));


        EZSMSN_Sending::set_http_request($mock_request);


        $core->load_settings();


        $options = get_option('ezsmsn');
        $this->assertEquals("The Message field is required", $options['admin_errors']['0']);
    }

    public function test_save_setting_should_successfully_save() {

        delete_option('ezsmsn');

        $_POST = array(
            '_ezsmsn_nonce'   => '_nonce',
            'action'          => 'save-settings',
            'ezsmsn_new_post' => true,
            'ezsmsn_new_post_message' => 'message to send',
            'ez_user' => 'medved',
            'ez_password' => 'med'
        );

        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));

        $mock_functions->expects($this->at(1))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $mock_request = $this->getMock('EZSMSN_Request', array('send'));

        $response = new stdClass();
        $response->Code = 302;

        $http_response = new EZSMSN_Response($response);

        $mock_request->expects($this->once())
                     ->method('send')
                     ->will($this->returnValue($http_response));


        EZSMSN_Sending::set_http_request($mock_request);

        $core = new EZSMSN();
        $core->load_settings();

        $options = get_option('ezsmsn');

        $expected = array(
            'ez_user' => 'medved',
            'ez_password' => 'med',
            'ezsmsn_new_post' => true,
            'ezsmsn_new_post_message' => 'message to send',
            'admin_notices' => array('Settings saved.')
        );

        $this->assertEquals($options, $expected);
    }

    public function test_confirm_delete_uninstall_not_allow() {
        delete_option('ezsmsn');

        $_POST = array(
            '_ezsmsn_nonce'   => '_nonce',
            'action'          => 'delete-uninstall'
        );

        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'current_user_can', 'wp_die', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));

        $mock_functions->expects($this->at(1))
                       ->method('current_user_can')
                       ->with('activate_plugins')
                       ->will($this->returnValue(false));

        $mock_functions->expects($this->at(2))
                       ->method('wp_die')
                       ->with('Sorry, you are not allowed to deactivate plugins.');

        $mock_functions->expects($this->at(3))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);
        
        $core = new EZSMSN();
        $core->load_settings();
    }

    public function test_confirm_delete_uninstall_allow() {
        delete_option('ezsmsn');

        $_POST = array(
            '_ezsmsn_nonce'   => '_nonce',
            'action'          => 'delete-uninstall'
        );

        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'current_user_can', 'wp_die', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));

        $mock_functions->expects($this->at(1))
                       ->method('current_user_can')
                       ->with('activate_plugins')
                       ->will($this->returnValue(true));

        $mock_functions->expects($this->at(2))
                       ->method('wp_die')
                       ->with($this->stringContains('Please confirm that you wish to DELETE ALL OF YOUR SUBSCRIBERS and uninstall the Ez Texting SMS notifications plugin.'));

        $mock_functions->expects($this->at(3))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $core = new EZSMSN();
        $core->load_settings();
    }

    public function test_delete_uninstall_not_allow() {
        delete_option('ezsmsn');

        $_POST = array(
            '_ezsmsn_nonce' => '_nonce',
            'action'        => 'confirm-delete-uninstall'
        );

        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'current_user_can', 'wp_die', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));

        $mock_functions->expects($this->at(1))
                       ->method('current_user_can')
                       ->with('activate_plugins')
                       ->will($this->returnValue(false));

        $mock_functions->expects($this->at(2))
                       ->method('wp_die')
                       ->with('Sorry, you are not allowed to deactivate plugins.');

        $mock_functions->expects($this->at(3))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $core = new EZSMSN();
        $core->load_settings();
    }

    public function test_delete_uninstall_allow() {
        delete_option('ezsmsn');

        $_POST = array(
            '_ezsmsn_nonce'   => '_nonce',
            'action'          => 'confirm-delete-uninstall'
        );

        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'current_user_can', 'deactivate_plugins',  'wp_die', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));

        $mock_functions->expects($this->at(1))
                       ->method('current_user_can')
                       ->with('activate_plugins')
                       ->will($this->returnValue(true));

        $mock_functions->expects($this->at(2))
                       ->method('deactivate_plugins')
                       ->with($this->stringContains('/ez-texting-sms-notifications.php'));

        $mock_functions->expects($this->at(3))
                       ->method('wp_die')
                       ->with($this->stringContains('Sms notifications plugin has been deactivated'));

        $mock_functions->expects($this->at(4))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $core = new EZSMSN();
        $core->load_settings();
    }

    public function test_send_single_message_should_produce_error() {

        delete_option('ezsmsn');

        $_POST = array(
            '_ezsmsn_nonce' => '_nonce',
            'ezsmsn_message' => ''
        );


        $mock_request = $this->getMock('EZSMSN_Request', array('send'));

        $response = new stdClass();
        $response->Code = 200;
        
        $http_response = new EZSMSN_Response($response);

        $mock_request->expects($this->never())
                     ->method('send')
                     ->will($this->returnValue($http_response));


        EZSMSN_Sending::set_http_request($mock_request);


        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));


        $mock_functions->expects($this->at(1))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);
        
        $core = new EZSMSN();
        $core->load_send_message();

        $options = get_option('ezsmsn');
        $this->assertEquals("The Message field is required!", $options['admin_errors']['0']);
    }

    public function test_send_single_message_with_wrong_account_should_produce_error() {

        delete_option('ezsmsn');

        add_option('ezsmsn', array('ez_user' => 'test', 'ez_password' => 'test'));

        EZSMSN_Subscribers::save_number('8634285794');

        $_POST = array(
            '_ezsmsn_nonce' => '_nonce',
            'ezsmsn_message' => 'sssssss'
        );


        $mock_request = $this->getMock('EZSMSN_Request', array('send'));

        $response = new stdClass();
        $response->Code = 401;

        $http_response = new EZSMSN_Response($response);

        $mock_request->expects($this->once())
                     ->method('send')
                     ->will($this->returnValue($http_response));


        EZSMSN_Sending::set_http_request($mock_request);


        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));


        $mock_functions->expects($this->at(1))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $core = new EZSMSN();
        $core->load_send_message();

        $options = get_option('ezsmsn');
        
        $this->assertEquals('Your Ez Texting username and/or password are missing or incorrect. <a href="http://example.com/wp-admin/admin.php?page=ezsmsn_options">Please visit the settings page</a>', $options['admin_errors']['0']);
    }

    public function test_send_single_message_500_should_produce_error() {

        delete_option('ezsmsn');

        add_option('ezsmsn', array('ez_user' => 'test', 'ez_password' => 'test'));

        EZSMSN_Subscribers::save_number('8634285794');

        $_POST = array(
            '_ezsmsn_nonce' => '_nonce',
            'ezsmsn_message' => 'sssssss'
        );


        $mock_request = $this->getMock('EZSMSN_Request', array('send'));

        $response = new stdClass();
        $response->Code = 500;

        $http_response = new EZSMSN_Response($response);

        $mock_request->expects($this->once())
                     ->method('send')
                     ->will($this->returnValue($http_response));


        EZSMSN_Sending::set_http_request($mock_request);


        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));


        $mock_functions->expects($this->at(1))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $core = new EZSMSN();
        $core->load_send_message();

        $options = get_option('ezsmsn');

        $this->assertEquals('Something is not working properly.<a href="http://www.eztexting.com/ticketing-contact.php">Please contact Ez Texting support.</a>', $options['admin_errors']['0']);
    }

    public function test_send_successfully_message() {

        delete_option('ezsmsn');

        add_option('ezsmsn', array('ez_user' => 'test', 'ez_password' => 'test'));

        EZSMSN_Subscribers::save_number('8634285794');

        $_POST = array(
            '_ezsmsn_nonce' => '_nonce',
            'ezsmsn_message' => 'sssssss'
        );

        $mock_request = $this->getMock('EZSMSN_Request', array('send'));

        $response = new stdClass();
        $response->Code = 200;
        $response->Entry->RecipientsCount = 1;

        $http_response = new EZSMSN_Response($response);

        $mock_request->expects($this->once())
                     ->method('send')
                     ->will($this->returnValue($http_response));


        EZSMSN_Sending::set_http_request($mock_request);


        $mock_functions = $this->getMock('WP_Functions', array('check_admin_referer', 'wp_redirect'));
        $mock_functions->expects($this->at(0))
                       ->method('check_admin_referer')
                       ->will($this->returnValue(true));


        $mock_functions->expects($this->at(1))
                       ->method('wp_redirect');

        EZSMSN::set_wp_functions($mock_functions);

        $core = new EZSMSN();
        $core->load_send_message();

        $options = get_option('ezsmsn');
        
        $this->assertEquals('Your Message has been sent to 1 subscribers.', $options['admin_notices']['0']);
    }

}
?>
