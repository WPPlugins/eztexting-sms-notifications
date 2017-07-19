<?php
/**
 * @package    Ez Texting SMS Notification plugin
 * @author     Viktor <viktor@eztexting.com>
 * @copyright  2011 Ez Texting https://www.eztexting.com
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    1.4.1
 * @since      1.0
 */

/*  Copyright 2012 EzTexting

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
 * Send SMS messages.
 *
 * @package EZ SMS Notification plugin
 **/

/**
 * send sms to all subscribers.
 * if success return stdOject with attribute:
 *  recipient_counts - Number of intended recipients. Please note: This includes globally opted out numbers.
 *  credits - Number of credits charged for the message.
 * if Failed return stdOject with attribute:
 *  failed - true
 *  response - object EZSMSN_Response
 *
 * @param string $message
 * @return stdClass
 */
function ezsmsn_send_sms_to_subscribers($message)
{
    $return_value = new stdClass();

    $patrial_count = 40;
    $page = 1;

    $ezsmsn_options = get_option( 'ezsmsn' );

    if( !isset($ezsmsn_options['ez_user']) || !isset($ezsmsn_options['ez_password']) )
        return;

    $ez_user     = $ezsmsn_options['ez_user'];
    $ez_password = $ezsmsn_options['ez_password'];

    $ez_sms_api = new EZSMSN_Sending( $ez_user, $ez_password );

    $recipient_counts   = 0;
    $credits            = 0;

    $responses = array();

    $subscribers = EZSMSN_Subscribers::fetch_all( $page, $patrial_count, false);
    while( $subscribers = EZSMSN_Subscribers::fetch_all( $page, $patrial_count, false) ) {
        ++$page;
        $phone_number_array = array();
        $subscribers_assoc  = array();
        foreach( $subscribers as $subscriber ) {
            $phone_number_array[] = $subscriber->phone_number;
            $subscribers_assoc[$subscriber->ID] = $subscriber->phone_number;
        }
        $ez_message       = new EZSMSN_Message( $phone_number_array, $message );
        $response_message = $ez_sms_api->send_sms( $ez_message );

        if( ! $response_message ) {
            $return_value->failed = true;
            $return_value->response = $ez_sms_api->get_last_response();
            break;
        }

        $recipient_counts += $response_message->get_recipients_count();
        $credits          += $response_message->get_credits();

        // unsubscribe block
        $local_opt_outs  = $response_message->get_local_opt_outs();
        $global_opt_outs = $response_message->get_global_opt_outs();

        $opt_outs = array_merge($local_opt_outs, $global_opt_outs);

        if( !empty($opt_outs) ) {
            $intersect = array_intersect($subscribers_assoc, $opt_outs);
            $subscriber_ids = array_keys($intersect);

            if( ! empty ($subscriber_ids) )
                EZSMSN_Subscribers::opt_outs($subscriber_ids);
        }
    }

    $return_value->recipient_counts = $recipient_counts;
    $return_value->credits          = $credits;

    return $return_value;
}



