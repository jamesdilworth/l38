/**
 * ClassyadsView - Various tools for showing ClassyAds to end users.
 * @type {{init}}
 */

var ClassyadsView = (function($) {
    "use strict";

    var ajaxurl = '/wp-admin/admin-ajax.php';

    /**
     * Used to store the query state of the ClassyAds Listing Data
     * @type {{action: string, orderby: string, order: string}}
     */
    var listing_data = {
        'action'    : 'refresh_classy_list',
        'orderby'   : 'date',
        'order'     : 'ASC'
    };

    /**
     * Main filter query to update the Classy Ad Listings.
     *
     * This function is handled the filter name and new value after a filter has changed on the ClassyAds Listing Widget
     * It'll then make an AJAX request to the server to get the updated list of items. If it is adding more, it'll add them to
     * the bottom of the list.
     *
     * @param filter
     * @param val
     * @returns {boolean}
     */
    var updateResults = function(filter, val) {

        console.log('Running UpdateResults in classyads-view.js');

        if(!(listing_data.temp_primary)) { // It hasn't been set by filter... so we'll need to set it ourself.
            $('.secondary-cats>.popular-category').each(function () {
                var the_id = this.id;
                listing_data.temp_primary = the_id.split('adcat-')[1];
            });
        }

        switch(filter) {
            case 'primary':
                // Show secondary cats in LH Nav.
                $('.secondary-cats').show(); // just for the first time
                $('.secondary-cats input').removeAttr('disabled'); // Checkboxes are disabled for non-users by default in WP.
                $('.secondary-cats .children').hide();
                $('.secondary-cats input').prop('checked', false);
                $('#adcat-' + val).find('.children').show();
                listing_data.adcat = [val];
                listing_data.temp_primary = false; // flag that the adcat is top-level.
                break;
            case 'more-ads':
                $('.more-ads').remove();
                listing_data.paged = val;
                break;
            case 'min_length':
                listing_data.min_length = val;
                break;
            case 'max_length':
                listing_data.max_length = val;
                break;
            case 'search':
                listing_data.search = val;
                break;
            case 'tax_input[adcat][]':
                listing_data.adcat = [];
                $('.secondary-cats input:checked').each(function() {
                    listing_data.adcat.push($(this).val());
                });
                if(listing_data.adcat.length === 0) {
                    // no secondary cats detected, set the adcat back to our primary
                    listing_data.adcat = listing_data.temp_primary;
                }
                break;
            default:
                return false;
        }

        // Show spinner... this is a bit crap, but it works.
        if(filter !== 'more-ads') {
            // Replace and reset
            listing_data.paged = 1;
            $('#classyad_listing').html('<div class="spinner"><img src="/wp-admin/images/wpspin_light-2x.gif."></div>');
        } else {
            $('#classyad_listing').append('<div class="spinner"><img src="/wp-admin/images/wpspin_light-2x.gif."></div>');
        }

        $.ajax({
            url: ajaxurl,
            type: 'GET',
            timeout: 5000,
            dataType: 'html',
            data: listing_data,
            error: function(xml) {
                console.log('Error');
            },
            success: function(response) {
                // Remove the button.
                if(response !== "" && filter !== 'more-ads') {
                    $('#classyad_listing').html(response);
                } else if(response !== "") {
                    $('#classyad_listing .spinner').remove();
                    $('#classyad_listing').append(response);
                } else {
                    console.log('Uh-oh, empty response');
                }

                $('.more-ads').on('click', function(evt) {
                    evt.preventDefault();
                    updateResults('more-ads', $(this).data('paged'));
                });
            }
        });

    };

    /**
     * Initialize the Event Handlers on Items on Various Public Facing Classifieds Pages
     */
    var setupEventHandlers = function() {
        // Setup the handlers on Primary filters.
        $('.primary-filters input').on('change', function(evt) {
            updateResults('primary', $(this).val());
        });


        $('.secondary-filters input').on('change', function(evt) {
            evt.stopImmediatePropagation(); // prevents a double firing of the event... not sure why that is happening.
            updateResults($(this).attr('name'), $(this).val());
            setTimeout(function(){
                // Do Nothing?... what was I thinking here?
            }, 1000);
        });

        $('.more-ads').on('click', function(evt) {
            evt.preventDefault();
            updateResults('more-ads', $(this).data('paged'));
        });
    };

    return {
        init: function() {
            setupEventHandlers();

        }
    };
})(jQuery);

jQuery(document).ready(function($) {
    ClassyadsView.init();
});
