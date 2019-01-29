// Test stuff here.
var Classys = (function($) {
    "use strict";

    var ajaxurl = '/wp-admin/admin-ajax.php';
    var data = {
        'action'    : 'update_classy_list',
        'orderby'   : 'date',
        'order'     : 'ASC'
    }; // This should keep track of the users filters?

    var updateResults = function(filter, val) {
        // Main filter query to reset the results for what we're looking for.
        console.log('fired updateResults()');

        switch(filter) {
            case 'primary':
                data.primary = val;
                break;
            case 'min_length':
                data.min_length = val;
                break;
            case 'max_length':
                data.max_length = val;
                break;
            case 'search':
                data.search = val;
                break;
            default:
                //
                break;
        }

        // Show spinner... this is a bit crap, but it works.
        $('#classyad_listing').html('<div class="spinner"><img src="/wp-admin/images/wpspin_light-2x.gif."></div>');

        $.ajax({
            url: ajaxurl,
            type: 'GET',
            timeout: 5000,
            dataType: 'html',
            data: data,
            error: function(xml) {
                console.log('Error');
            },
            success: function(response) {
                // Remove the button.
                if(response !== "") {
                    $('#classyad_listing').html(response);
                } else {
                    console.log('Uh-oh, empty response');
                }
            }
        });

    };

    var setupHandlers = function() {
        // Setup the handlers on Primary filters.
        $('.primary-filters input').on('change', function(evt) {
            updateResults('primary', $(this).val());
        });

        $('.secondary-filters input').on('change', function(evt) {
            updateResults($(this).attr('name'), $(this).val());
            setTimeout(function(){ /* Do Nothing */ }, 1000);
        });
    };

    return {
        init: function() {
            setupHandlers();
        }
    };
})(jQuery);

jQuery(document).ready(function($) {
    Classys.init();
});
