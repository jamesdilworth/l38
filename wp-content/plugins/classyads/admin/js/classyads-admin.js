(function( $ ) {
	'use strict';

	var is_classy_expired = localized.is_classy_expired;
	var is_classy_post_page = localized.is_classy_post_page;

	// Add Post Status of Expired to the Drop Down
    if(is_classy_post_page) {
        if(is_classy_expired) {
            $('#post-status-display').html('Expired');
        }
        var complete = is_classy_expired ? ' selected="selected' : "";
        $("select#post_status").append("<option value='expired" + complete + "'>Expired</option>");
    }
})( jQuery );
