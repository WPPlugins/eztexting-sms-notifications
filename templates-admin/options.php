<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
<h2><?php _e('Ez Texting SMS Notification Configuration', 'ezsmsn') ?></h2>

<form method="post" action="">
<?php wp_nonce_field( 'ezsmsn-save-settings', '_ezsmsn_nonce' ); ?>

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Ez Texting Username:', 'ezsmsn') ?></th>
<td><input type="text" name="ez_user" value="<?php echo  esc_attr( $ez_user ) ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Ez Texting Password:', 'ezsmsn') ?></th>
<td><input type="password" name="ez_password" value="<?php echo  esc_attr( $ez_password ) ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Notify SMS subscribers:', 'ezsmsn') ?></th>
<td>
    <fieldset>
        <legend class="screen-reader-text"><span><?php _e( 'Notify SMS subscribers', 'ezsmsn' ); ?></span></legend>
        <label for="ezsmsn_new_post">
            <input name="ezsmsn_new_post" type="checkbox" id="ezsmsn_new_post" value="1" <?php echo ( $ezsmsn_new_post ) ? 'checked' : ''?>/>
            <?php _e( 'When a new post is published', 'ezsmsn' ); ?></label><br>
    </fieldset>
</td>
</tr>
<tr valign="top" id="ezsmsn_new_post_message_row">
<th scope="row">
    <?php _e('New Post Message:', 'ezsmsn') ?>
    <div id="ezsmsn_counter">
        <?php _e('160 Remaining Characters', 'ezsmsn')?>
    </div>
</th>
<td>
    <textarea id="ezsmsn_new_post_message" name="ezsmsn_new_post_message"><?php echo esc_textarea($ezsmsn_new_post_message)?></textarea>
    <div id="ezsmsn_message_template">
        <p id="ezsmsn_blog_length"><span>{blog_name}</span>&nbsp;(<span class="value"><?php echo esc_html($length_blog_name)?></span>) - <?php _e('The blog name', 'ezsmsn') ?></p>
        <p id="ezsmsn_blog_url_length"><span>{blog_url}</span>&nbsp;(<span class="value"><?php echo esc_html($length_blog_url)?></span>) - <?php _e('The homepage of your blog', 'ezsmsn') ?></p>
        <p id="ezsmsn_post_author"><span>{post_author}</span>&nbsp;(<span class="value"><?php echo esc_html($length_post_author)?></span>) - <?php _e('The display name the author of the post', 'ezsmsn') ?></p>
        <p id="ezsmsn_post_title"><span>{post_title}</span>&nbsp;(<span class="value"><?php echo esc_html($length_post_title)?></span>) - <?php _e('The title of the post', 'ezsmsn') ?></p>
        <p id="ezsmsn_post_url"><span>{post_url}</span>&nbsp;(<span class="value"><?php echo esc_html($length_post_url)?></span>) - <?php _e('The web address of the post', 'ezsmsn') ?></p>
        <div class="ezsmsn_note">
            <?php _e('<a href="http://eztexting.com">Ez Texting</a> does not allow illegal, obscene or sexually oriented messages.
                Learn more <a href="http://www.eztexting.com/about/">about Ez Texting</a> including <a href="http://www.eztexting.com/group-sms-pricing.html">pricing</a> and <a href="http://www.eztexting.com/tou.html">terms of use</a>. <a href="http://www.eztexting.com/signup.php">Create a free trial account</a> to get started.
  
            <br/>Message can be up to a max of 160 characters.
           <br/>We do not recommend using non-standard characters such as but not limited to ~ or { or }.', 'ezsmsn')?>
        </div>
    </div>
</td>
</tr>
</table>

<input type="hidden" name="action" value="save-settings" />
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'ezsmsn') ?>" />
</p>
</form>
<form action="" method="post">
    <?php wp_nonce_field( 'ezsmsn-delete-uninstall', '_ezsmsn_nonce' ); ?>
    <input type="hidden" name="action" value="delete-uninstall" />
    <input type="submit" name="delete" value="<?php echo esc_attr( __( 'Delete and Uninstall', 'ezsmsn' ) ); ?>" class="button-uninstall"/> <?php _e( 'This will permanently delete the plugin data, including all of your subscribers, then deactivate this plugin.', 'ezsmsn' ); ?>
</form>
</div>