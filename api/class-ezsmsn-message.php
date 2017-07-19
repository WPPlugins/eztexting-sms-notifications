<?php
/**
 * @package    Ez_Texting_SMS_Notification_plugin
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

/**
 * Build Message to send.
 *
 * @package EZ SMS Notification plugin
 * */
class EZSMSN_Message {
    /**
     * Send via Express delivery method
     *
     */
    const EXPRESS = 1;

    /**
     * Send via Standard delivery method
     *
     */
    const STANDARD = 2;

    /**
     * EZ Message ID
     *
     * @var int
     */
    private $_id;
    /**
     * Subject of the message
     *
     * @var string
     */
    private $_subject;
    /**
     * The body of your message
     *
     * @var string
     */
    private $_message;
    /**
     * Message delivery method: set to 1 to send via Express delivery method;
     * set to 2 to send via Standard delivery method; if you leave this parameter out,
     * your message will be sent via Express delivery method.
     *
     * @var int
     */
    private $_type = 1;
    /**
     * Time to send a scheduled message (should be a Unix timestamp)
     *
     * @var timestamp
     */
    private $_stamp_to_send;
    /**
     * Array of phone numbers to receive the message
     *
     * @var array
     */
    private $_phone_numbers = array();
    /**
     * total recipients count
     *
     * @var int
     */
    private $_recipients_count = 0;
    /**
     * Number of credits charged for the message
     *
     * @var int
     */
    private $_credits = 0;
    /**
     * Array of locally opted-out phone numbers
     *
     * @var array
     */
    private $_local_opt_outs = array();
    /**
     * Array of globally opted-out phone numbers
     *
     * @var array
     */
    private $_global_opt_outs = array();

    /**
     * Message constructor
     *
     * @param array/string $phone_numbers
     * @param string $message
     * @param int $type
     */
    public function __construct($phone_numbers, $message, $type = self::EXPRESS) {

        if ( ! is_array($phone_numbers) )
            $phone_numbers = (array) $phone_numbers;


        $this->_phone_numbers = array_unique($phone_numbers);
        $this->_message = (string) $message;
        $this->_type = $type;
    }

    /**
     * Message ID
     *
     * @param int $id
     * @return EZSMSN_Message
     */
    public function set_message_id($id) {
        $this->_id = $id;
        return $this;
    }

    /**
     * Set subject of the Message
     *
     * @param sring $subject
     * @return EZSMSN_Message
     */
    public function set_subject($subject) {
        $this->_subject = (string) $subject;
        return $this;
    }

    /**
     * Set body of the message
     *
     * @param string $message
     * @return EZSMSN_Message
     */
    public function set_message($message) {
        $this->_message = (string) $message;
        return $this;
    }

    /**
     * Set delivery type
     *
     * @param int $type
     * @return EZSMSN_Message
     */
    public function set_type($type) {
        $this->_type = 1;
        return $this;
    }

    /**
     * Set time to send a scheduled message (should be a Unix timestamp)
     *
     * @param unix(timestamp) $time_stamp
     * @return EZSMSN_Message
     */
    public function set_stamp_to_send($time_stamp) {
        $this->_stamp_to_send = $time_stamp;
        return $this;
    }

    /**
     * Array of phone numbers or single phone number to receive the message
     *
     * @param array/string $phone_numbers
     * @return EZSMSN_Message
     */
    public function set_phone_numbers($phone_numbers) {
        if ( ! is_array($phone_numbers) ) {
            $phone_numbers = (array) $phone_numbers;
        }

        $this->_phone_numbers = array_unique($phone_numbers);
        return $this;
    }

    /**
     * Set number of credits charged for the message
     *
     * @param integer $credits
     * @return EZSMSN_Message
     */
    public function set_credits($credits) {
        $this->_credits = $credits;
        return $this;
    }

    /**
     * Number of intended recipients. Please note: This includes globally opted out numbers.
     *
     * @param integer $count
     * @return EZSMSN_Message
     */
    public function set_recipients_count($count) {
        $this->_recipients_count = $count;
        return $this;
    }

    /**
     * Set global optouts phone numbers
     *
     * @param array $phone_numbers
     * @return EZSMSN_Message
     */
    public function set_global_opt_outs(array $phone_numbers) {
        $this->_global_opt_outs = $phone_numbers;
        return $this;
    }

    /**
     * Set local opt outs phone numbers
     *
     * @param array $phone_numbers
     * @return EZSMSN_Message
     */
    public function set_local_opt_outs(array $phone_numbers) {
        $this->_local_opt_outs = $phone_numbers;
        return $this;
    }

    /**
     * Add phone number to receive the message
     *
     * @param string $phone_numbers
     * @return EZSMSN_Message
     */
    public function add_phone_number($phone_numbers) {
        if ( !in_array($phone_numbers, $this->_phone_numbers) )
            $this->_phone_numbers[] = $phone_numbers;

        return $this;
    }

    /**
     * Return delivered message id
     *
     * @return int
     */
    public function get_message_id() {
        return $this->_id;
    }

    /**
     * Get subject message
     * @return string
     */
    public function get_subject() {
        return $this->_subject;
    }

    /**
     * get body of the message
     *
     * @return string
     */
    public function get_message() {
        return $this->_message;
    }

    /**
     * get array of phone numbers to receive the message
     *
     * @return array
     */
    public function get_phone_numbers() {
        return $this->_phone_numbers;
    }

    /**
     * get time to send a scheduled message
     *
     * @return timestamp
     */
    public function get_stamp_to_send() {
        return $this->_stamp_to_send;
    }

    /**
     * get message delivery method
     *
     * @return int
     */
    public function get_type() {
        return $this->_type;
    }

    /**
     * get number of credits charged for the message
     *
     * @return integer
     */
    public function get_credits() {
        return $this->_credits;
    }

    /**
     * get number of intended recipients.
     *
     * @return int
     */
    public function get_recipients_count() {
        return $this->_recipients_count;
    }

    /**
     * get globally opted-out phone numbers
     *
     * @return array
     */
    public function get_global_opt_outs() {
        return $this->_global_opt_outs;
    }

    /**
     * get locally opted-out phone numbers
     *
     * @return array
     */
    public function get_local_opt_outs() {
        return $this->_local_opt_outs;
    }

}