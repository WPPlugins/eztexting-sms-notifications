<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
    <h2><?php _e( 'SMS Subscribers', 'ezsmsn' )?></h2>
    <form id="ezsmsn_subcribe_list_form" method="post" action="">
    <?php wp_nonce_field( 'ezsmsn-subscribe-action', '_ezsmsn_nonce' ); ?>
    <div class="ezsmsn_subscribers_container">
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="action">
                    <option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'ezsmsn' ); ?></option>
                    <option value="delete"><?php _e( 'Delete', 'ezsmsn' ); ?></option>
                </select>
                <input type="submit" name="submit" class="button-secondary action" value="Apply">
            </div>
            <?php echo ezsmsn_build_pager_controll($current_page, $page_size, $total_items, $total_page);?>
        </div>
        <table class="wp-list-table widefat fixed" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column check-column colum-checker"><input type="checkbox"/></th>
                <th scope="col" id="phonenumber" class="manage-column column-phonenumber"><span><?php _e( 'Phone Number', 'ezsmsn' )?></span></th>
                <th scope="col" id="created" class="manage-column column-created"><span><?php _e( 'Subscribe Date', 'ezsmsn' )?></span></th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <th scope="col" class="manage-column check-column colum-checker"><input type="checkbox"/></th>
                <th scope="col" class="manage-column column-phonenumber"><span><?php _e( 'Phone Number', 'ezsmsn' )?></span></th>
                <th scope="col" class="manage-column column-created"><span><?php _e( 'Subscribe Date', 'ezsmsn' )?></span></th>
            </tr>
            </tfoot>
            <tbody id="the-list">
            <?php foreach($subscribers as $subscriber):?>
            <tr class="alternate <?php echo ( $subscriber->opt_out ) ? 'ezsmsn_opt_out' : ''?>">
                <th scope="row" class="check-column colum-checker"><input type="checkbox" name="subscriber_ids[]" value="<?php echo $subscriber->ID?>"/></th>
                <td><?php echo $subscriber->phone_number?></td>
                <td><?php echo date_i18n('F j, Y g:i a' ,strtotime($subscriber->created));?></td>
            </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="alignleft actions">
                <select name="action2">
                    <option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'ezsmsn' ); ?></option>
                    <option value="delete"><?php _e( 'Delete', 'ezsmsn' ); ?></option>
                </select>
                <input type="submit" name="submit" class="button-secondary action" value="Apply">
            </div>
            <?php echo ezsmsn_build_pager_controll($current_page, $page_size, $total_items, $total_page);?>
        </div>
    </div>
    </form>
</div>