<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
<h2><?php _e('Send SMS Message', 'ezsmsn')?></h2>
<form method="post" action="">
<?php wp_nonce_field( 'ezsmsn-send-sms', '_ezsmsn_nonce' ); ?>

<table class="form-table">
<tr valign="top">
    <th scope="row">
        <?php _e('Your SMS Message:', 'ezsmsn') ?>
        <div id="ezsmsn_counter">
            <?php _e('160 Remaining Characters', 'ezsmsn')?>
        </div>
    </th>
    <td>
        <textarea id="ezsmsn_message" name="ezsmsn_message"></textarea>
        <div id="ezsmsn_message_template">
            <p id="ezsmsn_blog_length"><span>{blog_name}</span>&nbsp;(<span class="value"><?php echo esc_html($length_blog_name)?></span>) - <?php _e('The blog name', 'ezsmsn') ?></p>
            <p id="ezsmsn_blog_url_length"><span>{blog_url}</span>&nbsp;(<span class="value"><?php echo esc_html($length_blog_url)?></span>) - <?php _e('The homepage of your blog', 'ezsmsn') ?></p>
            <div class="ezsmsn_note">
                <?php _e('<strong>Send a text message to all of your subscribers.</strong><br />
				<a href="http://eztexting.com">Ez Texting</a> does not allow illegal, obscene or sexually oriented messages.
                Learn more <a href="http://www.eztexting.com/about/">about Ez Texting</a> including <a href="http://www.eztexting.com/group-sms-pricing.html">pricing</a> and <a href="http://www.eztexting.com/tou.html">terms of use</a>. <a href="http://www.eztexting.com/signup.php">Create a free trial account</a> to get started.
                <br/>Message can be up to a max of 160 characters.
                <br/>We do not recommend using non-standard characters such as but not limited to ~ or { or }.', 'ezsmsn')?>
            </div>
        </div>
    </td>
</tr>
</table>
<input type="hidden" name="action" value="send-sms" />
<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Send Message', 'ezsmsn') ?>" />
</p>
</form>
</div>