<?php if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
exit();

delete_option( 'ezsmsn' );
delete_option( 'widget_ezsms-subscribe' );
delete_option( 'ezsmsn-subscribers-version' );

