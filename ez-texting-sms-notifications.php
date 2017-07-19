<?php
/*
Plugin Name: Ez Texting: Sms notifications
Plugin URI: http://www.eztexting.com/
Description: Sms notifications.
Version: 1.5.0
License: http://opensource.org/licenses/gpl-license.php GNU Public License
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
define( 'EZSMSNOTIFY_VERSION' , '1.0');

require_once( 'ezsmsn-functions.php' );
require_once( 'class-ezsmsn.php' );

require_once( 'api/class-ezsmsn-message.php' );
require_once( 'api/class-ezsmsn-response.php' );
require_once( 'api/class-ezsmsn-sending.php' );

require_once( 'class-wp-functions.php' );
require_once( 'class-ezsmsn-subscribers.php' );
require_once( 'class-ezsmsn-widget-subscribe.php' );
require_once( 'helper-api-messages.php' );
