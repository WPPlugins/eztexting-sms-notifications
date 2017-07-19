<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<p><?php _e( 'Please enter your mobile number below and select "Unsubscribe" to unsubscribe from all SMS notifications from this site.', 'ezsmsn' ); ?></p>

<form action="" method="post">
	<input type="hidden" name="ezsmsn-unsubscribe" value="1" />
	<?php if ( $error ) : ?>
		<p style="color: #c00; background-color: #ffc; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px; padding: 5px"><?php echo esc_html( $error ); ?></p>
	<?php endif; ?>
	<p>
        <label for="ezsmsn-phone-number">
			<?php _e( 'Your phone number:', 'ezsmsn' ); ?><br />
			<input type="text" name="ezsmsn-phone-number" value="<?php echo esc_attr( $ezsmsn_phone_number ); ?>" id="ezsmsn-phone-number"  />
		</label>
	</p>
	<p><input type="submit" name="submit" value="<?php esc_attr_e( 'Unsubscribe', 'ezsmsn' ); ?>" /></p>

</form>