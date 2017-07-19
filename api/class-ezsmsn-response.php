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

/**
 * Parse EZ API Response.
 *
 * @package EZ SMS Notification plugin
 * */
class EZSMSN_Response {

    /**
     * HTTP response code
     * @var int
     */
    private $_code;
    /**
     * Api response errors
     * @var array
     */
    private $_errors;
    /**
     * api response body
     * @var array
     */
    private $_body;

    /**
     * @param json $response
     * @see http://www.eztexting.com/developers/rest-api/sms/docs.html
     */
    public function __construct($response) {
        $this->_code = $response->Code;

        if (!empty($response->Errors))
            $this->_errors = $response->Errors;

        if (!empty($response->Entry))
            $this->_body = $response->Entry;
    }

    /**
     * Check whether the response is an error
     *
     * @return boolean
     */
    public function is_error() {
        $restype = floor($this->_code / 100);
        if ($restype == 4 || $restype == 5)
            return true;
        return false;
    }

    /**
     * Check whether the response in successful
     *
     * @return boolean
     */
    public function is_successful() {
        $restype = floor($this->_code / 100);
        if ($restype == 2 || $restype == 1)
            return true;
        return false;
    }

    /**
     * Get Response body
     *
     * @return array
     */
    public function get_body() {
        return $this->_body;
    }

    /**
     * Get the HTTP response status code
     *
     * @return int
     */
    public function get_status() {
        return $this->_code;
    }

    /**
     * Return Error Message
     * 
     * @return array
     */
    public function get_errors() {
        return $this->_errors;
    }
}