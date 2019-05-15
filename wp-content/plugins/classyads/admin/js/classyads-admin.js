(function( $ ) {
	'use strict';

	var is_classy_expired = localized.is_classy_expired;
    var is_classy_removed = localized.is_classy_removed;
    var is_classy_post_page = localized.is_classy_post_page;

	// Add Post Status of Expired to the Drop Down
    if(is_classy_post_page) {
        if(is_classy_expired) {
            $('#post-status-display').html('Expired');
        }
        var expired_complete = is_classy_expired ? ' selected="selected' : "";

        if(is_classy_removed) {
            $('#post-status-display').html('Removed');
        }
        var removed_complete = is_classy_removed ? ' selected="selected' : "";

        // Append the items to the dropdown.
        $("select#post_status").append("<option value='expired" + expired_complete + "'>Expired</option>");
        $("select#post_status").append("<option value='removed" + removed_complete + "'>Remove</option>");
    }
})( jQuery );
