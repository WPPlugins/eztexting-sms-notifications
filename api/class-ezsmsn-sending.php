<?php

/**
 * @package    Ez Texting SMS Notification plugin
 * @author     Viktor <viktor@eztexting.com>
 * @copyright  2011 Ez Texting https://www.eztexting.com
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    1.0
 * @since      1.0
 * @see        http://www.eztexting.com/developers/rest-api/sms/docs.html
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


require_once 'class-ezsmsn-request.php';

/**
 * Send SMS messages.
 *
 * @package EZ SMS Notification plugin
 * */
class EZSMSN_Sending {

    const API_CAN_LOGIN_URL = 'https://app.eztexting.com?format=json';
    const API_MESSAGE_URL   = 'https://app.eztexting.com/sending/messages?format=json';

    private $_user;
    private $_password;
    private $_last_response;

    protected static $_http_request = null;

    public function __construct($user, $password) {
        $this->_user = $user;
        $this->_password = $password;
    }

    /**
     * Send SMS via EZ Gateway
     *
     * @param EZSMSN_Message $message
     * @return EZSMSN_Message
     */
    public function send_sms(EZSMSN_Message $message) {
        $data = array(
            'User'          => $this->_user,
            'Password'      => $this->_password,
            'PhoneNumbers'  => $message->get_phone_numbers(),
            'Subject'       => $message->get_subject(),
            'Message'       => $message->get_message(),
            'MessageTypeID' => $message->get_type()
        );

        $stamp_to_send = $message->get_stamp_to_send();

        if( !empty($stamp_to_send) )
            $data['StampToSend'] = $stamp_to_send;

        $http_request = $this->get_http_request();
        $response     = $http_request->send(self::API_MESSAGE_URL, $data);
        
        $this->_last_response = $response;

        if ( $response->is_error() )
            return null;

        $response_body = $response->get_body();

        $phone_numbers = array();
        if ( !empty($response_body->PhoneNumbers) )
            $phone_numbers = $response_body->PhoneNumbers;

        $local_opt_outs = array();
        if ( !empty($response_body->LocalOptOuts) )
            $local_opt_outs = $response_body->LocalOptOuts;

        $global_opt_outs = array();
        if ( !empty($response_body->GlobalOptOuts) )
            $global_opt_outs = $response_body->GlobalOptOuts;

        $message->set_message_id($response_body->ID)
                ->set_recipients_count($response_body->RecipientsCount)
                ->set_credits($response_body->Credits)
                ->set_phone_numbers($phone_numbers)
                ->set_global_opt_outs($global_opt_outs)
                ->set_local_opt_outs($local_opt_outs);

        return $message;
    }

    /**
     * Check if user can login to ez texting
     *
     * @param string $user
     * @param string $password
     * @return boolean
     */
    public function check_can_login()
    {
       $data = array(
            'User'          => $this->_user,
            'Password'      => $this->_password
        );

        $http_request = $this->get_http_request();
        $response     = $http_request->send(self::API_CAN_LOGIN_URL, $data);

        if( $response->is_error() )
            return false;
        return true;
    }

    /**
     * get last response data
     *
     * @return EZSMSN_Response
     */
    public function get_last_response() {
        return $this->_last_response;
    }

    public static function set_http_request(EZSMSN_Request $request) {
        self::$_http_request = $request;
    }

    public static function get_http_request() {
        if( empty(self::$_http_request) )
            self::$_http_request = new EZSMSN_Request();

        return self::$_http_request;
    }

}