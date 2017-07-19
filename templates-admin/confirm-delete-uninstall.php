<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<style type="text/css" media="screen">
	
	form input.button-uninstall {
	    text-decoration: none;
	    font-size: 12px !important;
	    line-height: 13px;
	    padding: 3px 8px;
	    cursor: pointer;
	    border-width: 1px;
	    border-style: solid;
	    -moz-border-radius: 11px;
	    -khtml-border-radius: 11px;
	    -webkit-border-radius: 11px;
	    border-radius: 11px;
	    -moz-box-sizing: content-box;
	    -webkit-box-sizing: content-box;
	    -khtml-box-sizing: content-box;
	    box-sizing: content-box;
		border-color: #900;
		font-weight: bold;
		color: #fff;
		background-color: #c00;
		text-shadow: rgba(0,0,0,0.3) 0 -1px 0;
	}

	form input.button-uninstall:active {
		background-color: #c00;
		color: #fee;
	}

	form input.button-uninstall:hover {
		border-color: #300;
		color: #fee;
	}

</style>

<p><?php _e( 'Please confirm that you wish to DELETE ALL OF YOUR SUBSCRIBERS and uninstall the Ez Texting SMS notifications plugin.', 'ezsmsn' ); ?></p>

<form action="" method="post">
	<?php wp_nonce_field( 'ezsmsn-confirm-delete-uninstall', '_ezsmsn_nonce' ); ?>
	<input type="hidden" name="action" value="confirm-delete-uninstall" />

	<p>
		<input type="submit" name="submit" value="<?php echo esc_attr( __( 'Confirm Delete and Uninstall', 'ezsmsn' ) ); ?>" class="button-uninstall" />
	</p>

</form>