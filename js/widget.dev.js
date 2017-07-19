jQuery( function( $ ) {
    $(document).ready(function(){
        $( '.ezsubscribe_form' ).each( function() {
            var form        = $(this);
            var action      = form.children('input[name=action]').val();
            var successInfo = form.children('input[name=successInfo]').val();

            form.submit(function(e){
                var phone_number = form.children('.ezsubscribe-phoneNumber').children('input[name=phone_number]').val();

                $.ajax(
                {
                    type: "POST",
                    dataType: 'json',
                    url: form.attr('action'),
                    data:{
                        'action'      : action,
                        'phone_number' : phone_number
                    },
                    success: function(r)
                    {
                        if(r.success) {
                            form.html(successInfo)
                            return true;
                        }

                        if(!r.success) {
                            $('.phoneNumber-error').html(r.messages).css('color', 'red');
                        }
                    }
                })
                return false;
            })

        })
    })
})