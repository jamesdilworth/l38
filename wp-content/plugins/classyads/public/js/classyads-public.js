var Classyads = (function($) {
    "use strict";

    var ajaxurl = '/wp-admin/admin-ajax.php';

    var data = {
        'action'    : 'update_classy_list',
        'orderby'   : 'date',
        'order'     : 'ASC'
    }; // This should keep track of the users filters?

    function validFileType(file) {
        // We should really grab this from the input field.
        var fileTypes = [
            'image/jpeg',
            'image/pjpeg',
            'image/png'
        ]

        for(var i = 0; i < fileTypes.length; i++) {
            if(file.type === fileTypes[i]) {
                return true;
            }
        }
        return false;
    }

    function returnFileSize(number) {
        if(number < 1024) {
            return number + 'bytes';
        } else if(number >= 1024 && number < 1048576) {
            return (number/1024).toFixed(1) + 'KB';
        } else if(number >= 1048576) {
            return (number/1048576).toFixed(1) + 'MB';
        }
    }

    var updateResults = function(filter, val) {
        // Main filter query to reset the results for what we're looking for.
        // console.log('fired updateResults() with ' + filter + ' of ' + val);

        if(!(data.temp_primary)) { // It hasn't been set by filter... so we'll need to set it ourself.
            $('.secondary-cats>.popular-category').each(function () {
                var the_id = this.id;
                data.temp_primary = the_id.split('adcat-')[1];
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
                data.adcat = [val];
                data.temp_primary = false; // flag that the adcat is top-level.
                break;
            case 'more-ads':
                $('.more-ads').remove();
                data.paged = val;
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
            case 'tax_input[adcat][]':
                data.adcat = [];
                $('.secondary-cats input:checked').each(function() {
                    data.adcat.push($(this).val());
                });
                if(data.adcat.length === 0) {
                    // no secondary cats detected, set the adcat back to our primary
                    data.adcat = data.temp_primary;
                }
                break;
            default:
                return false;
        }

        // Show spinner... this is a bit crap, but it works.
        if(filter !== 'more-ads') {
            // Replace and reset
            data.paged = 1;
            $('#classyad_listing').html('<div class="spinner"><img src="/wp-admin/images/wpspin_light-2x.gif."></div>');
        } else {
            $('#classyad_listing').append('<div class="spinner"><img src="/wp-admin/images/wpspin_light-2x.gif."></div>');
        }

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

    var setupHandlers = function() {
        // Setup the handlers on Primary filters.
        $('.primary-filters input').on('change', function(evt) {
            updateResults('primary', $(this).val());
        });

        $('.secondary-filters input').on('change', function(evt) {
            updateResults($(this).attr('name'), $(this).val());
            setTimeout(function(){ /* Do Nothing */ }, 1000);
        });

        $('.more-ads').on('click', function(evt) {
            evt.preventDefault();
            updateResults('more-ads', $(this).data('paged'));
        });

        $('.switch_public_edit_mode').click(function(evt) {
            evt.preventDefault();
            $('.main-content').toggle();
            $('.update-classy-ad').toggle();
        });

        // Handle the uploading of a new image.... This is done in UGC.
        // $('#main_photo_input').on('change', updateMainImage);

    };

    return {
        init: function() {
            setupHandlers();
        }
    };
})(jQuery);

jQuery(document).ready(function($) {
    Classyads.init();
});
