(function($){
    $(function(){
        $('#winpress-clear-cache').on('click', function(e){
            e.preventDefault();
            $('#winpress-clear-result').text('Processing...');
            $.post(winpressAdmin.ajaxurl, { action: 'winpress_clear_cache', nonce: winpressAdmin.nonce }, function(resp){
                if (resp.success) {
                    $('#winpress-clear-result').text(resp.data);
                } else {
                    $('#winpress-clear-result').text('Error');
                }
            }).fail(function(){ $('#winpress-clear-result').text('Request failed'); });
        });
    });
})(jQuery);
