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

require_once( 'class-ezsmsn-widget.php' );

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class EZSMSN_Widget_Subscribe extends EZSMSN_Widget {

	function  __construct($id_base = false, $name = 'Ez Texting Widget', $widget_options = array(), $control_options = array()) {
		$widget_options = array(
			'description' => __( 'All your readers to subscribe to SMS updates when you add a post. Requires an Ez Texting account.', 'ezsmsn' )
		);
		parent::__construct('ezsms-subscribe', __( 'EZ Texting: SMS Updates', 'ezsmsn' ), $widget_options, $control_options);
		$this->load_widget_files();
	}


	function form($instance) {
		extract( $instance, EXTR_SKIP );

		if ( empty( $instance ) )
			$show_link = true;

		if ( empty( $title ) )
			$title = __( 'Subscribe To SMS Updates', 'ezsmsn' );
		if( empty( $info ) )
			$info = __( 'We will send you a text message when we post to the blog.', 'ezsmsn' );

		if ( empty( $success_info ) )
			$success_info = __( 'Thank you for joining our text messaging list.', 'ezsubscribe' );

		$this->input_text( __( 'Title', 'ezsmsn' ), 'title', $title );
		$this->textarea( __( 'Description', 'ezsmsn' ), 'info', $info, __( 'Add a sentence or two to explain to your customers or members what your text messaging list is all about.', 'ezsmsn' ));
		$this->textarea( __( 'Successful Signup Message', 'ezsmsn' ), 'success_info', $success_info,
			__( 'This message will appear after a customer or member has successfully been added to your text messaging list', 'ezsmsn' ));
	}

	function update($new_instance, $old_instance) {
		$updated_instance = $new_instance;
		return $updated_instance;
	}

	function widget($args, $instance) {
		// outputs the content of the widget
		extract( $args );
		extract( $instance, EXTR_SKIP );

		$title = apply_filters( 'widget_title', $title );
		$info  = apply_filters( 'widget_info', $info );
?>
	   <?php echo $before_widget; ?>
		<?php if ( $title ) : ?>
			<?php echo $before_title . $title . $after_title; ?>
		<?php endif; ?>

		<?php if ( $info ) : ?>
			<p><?php echo esc_html( $info ); ?></p>
		<?php endif; ?>
		<form action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="post" class="ezsubscribe_form">
			<input type="hidden" name="action" value="ezsmsn_subscribe" id="action">
			<input type="hidden" name="successInfo" value="<?php echo esc_attr( $success_info ); ?>"/>
			<p class="ezsubscribe-phoneNumber">
				<span><?php _e( 'Enter your mobile number:', 'ezsmsn' ); ?></span>
				<input type="text" name="phone_number" value="" id="<?php echo esc_attr( $widget_id ); ?>">
				<span class="phoneNumber-error"></span><br/>
				<span>(e.g. 2125550100)</span>
			</p>
			<p class="ezsmsn-subscribe">
				<input type="submit" name="subscribe_button" value="<?php echo esc_attr( __( 'Subscribe', 'ezsmsn' ) ); ?>" />
				|
				<a href="<?php echo esc_attr( add_query_arg( array( 'ezsmsn-unsubscribe' => 1 ), home_url() ) ); ?>"><?php _e( 'Unsubscribe', 'ezsmsn' ); ?></a>
			</p>
		</form>
		<p>Msg&amp;Data rates may apply. To opt out, reply <strong>STOP</strong> to any message</p>
		<p><a href="http://www.eztexting.com/"><?php _e( 'SMS Marketing', 'ezsmsn' ); ?></a> by Ez Texting</p>
		<?php echo $after_widget; ?>
<?php

	}

	public function load_widget_files()
	{
		if( is_admin() )
			return;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';
		wp_enqueue_script( 'ezsubscribe-admin', ezsmsn_url( "/js/widget{$suffix}.js" ), array( 'jquery' ));
		$localized = array(
		);
		wp_localize_script( 'ezsubscribe-admin', 'ezsubscribe_widget', $localized );
		wp_enqueue_style( 'ezsubscribe-admin', ezsmsn_url( "/css/widget{$suffix}.css" ), array());
	}

}

add_action( 'widgets_init', create_function( '', "register_widget('EZSMSN_Widget_Subscribe');" ) );