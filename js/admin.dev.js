jQuery( function( $ ) {

    var message, lengths;

    $(document).ready(function(){
        $('#ezsmsn_message_template span').click(function(){

            var textarea = document.getElementById('ezsmsn_new_post_message');

            if($(document).find('#ezsmsn_message').length > 0) {
                var textarea = document.getElementById('ezsmsn_message');
            }

            if(document.selection){
              textarea.focus();
              sel=document.selection.createRange();
              sel.text=$(this).html();
              return;
            }

            if(textarea.selectionStart||textarea.selectionStart=="0"){
                var t_start    = textarea.selectionStart;
                var t_end      = textarea.selectionEnd;
                var val_start  = textarea.value.substring(0,t_start);
                var val_end    = textarea.value.substring(t_end,textarea.value.length);
                textarea.value = val_start + ($(this).html()) + val_end;
            }else{
                textarea.value += $(this).html();
            }
            update_counter()
        })
        if($('#ezsmsn_new_post_message, #ezsmsn_message').length) {
            init_options();
        }

        if($('.column-cb input:checkbox').length) {
            selectSubscribers();
        }
    })

    function selectSubscribers()
    {
        $(".column-cb input:checkbox").click(function() {
            var checked_status = this.checked;
            $(".ezsmsn_subscribers_list input:checkbox").each(function(){
                this.checked = checked_status;
            })
        });
    }

    function update_counter() {
        var new_length, str;
        str = $( '#ezsmsn_new_post_message, #ezsmsn_message' ).val();

        str = str.replace(/{blog_name}/ig, lengths.blog_name)
                 .replace(/{blog_url}/ig, lengths.blog_url);

		if ( $( '#ezsmsn_new_post_message' ).length ) {
            str = str.replace(/{post_author}/ig, lengths.post_author)
                     .replace(/{post_title}/ig, lengths.post_title)
                     .replace(/{post_url}/ig, lengths.post_url);
		}

        new_length = str.length;
        $('#ezsmsn_counter').text( message.replace( '160', (160-new_length) ) );
    }

    function init_options() {
        lengths = new Object()
        lengths.blog_name = getOrigStrLen(parseInt($('#ezsmsn_blog_length .value').text()))
        lengths.blog_url  = getOrigStrLen(parseInt($('#ezsmsn_blog_url_length .value').text()))

		if ( $('#ezsmsn_new_post_message').length ) {
            lengths.post_author = getOrigStrLen(parseInt($('#ezsmsn_post_author .value').text()))
            lengths.post_title  = getOrigStrLen(parseInt($('#ezsmsn_post_title .value').text()))
            lengths.post_url    = getOrigStrLen(parseInt($('#ezsmsn_post_url .value').text()))
		}



        message = $('#ezsmsn_counter').text()
        $( '#ezsmsn_new_post_message, #ezsmsn_message' ).each( update_counter ).keyup( update_counter );

    }

    function getOrigStrLen(len) {
		var i, str = '';
		for ( i = 0; i < len; i++ )
        	str += 'x';
		return str;
    }

})