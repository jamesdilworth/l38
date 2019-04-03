/**
 * ClassyadsForms - Tools for creating, updating and deleting Classyads info.
 * @type {{init}}
 */

var ClassyadsForms = (function($) {
    "use strict";

    var ajaxurl = '/wp-admin/admin-ajax.php';
    var classy_images = "test";

    /**
     * Fired on Place Classy Ad Form Submission.
     *
     * @param evt
     * @param this - should refer to the form.
     */
    var createClassyAd = function(evt) {
        evt.preventDefault(); // Prevent Auto Submission

        // var formData = $(this).serializeArray();
        var formData = new FormData(this);

        // Hide the Form, Show the Spinner

        var create_classyad_call = $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 5000,
            contentType: false, // Stop jQuery from reinterpreting the contentType.
            processData: false, // Stop jQuery from re-processing the formData
            dataType: 'json',
            data: formData,
            error: function(response) {
                console.log(response);
            },
            success: function(response) {
                // Write the return to the screen.
                console.log(response);
            },
            complete: function() {
                // When AJAX call is complete, will fire upon success or when error is thrown
                console.log('AJAX call completed');
            }
        });
    };


    /**
     * General Purpose Handler for updating Classy Ad Fields.
     * @param evt
     */
    var updateClassyAd = function(evt) {
        evt.preventDefault();
        var $button = $(this).find("input[type='submit']");
        var formData = new FormData(this);

        // Any additional client side validation.
        $.Toast.showToast({'title': 'Updating your Ad...','icon':'loading'});

        // Disable the Update Button
        $button.val('...').prop('disabled', true);

        // Overlay a spinner.
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 5000,
            contentType: false, // Stop jQuery from reinterpreting the contentType.
            processData: false, // Stop jQuery from re-processing the formData
            dataType: 'json',
            data: formData,
            error: function(response) {
                console.log(response);
                var message = "";
                if(response.data.isArray()) {
                    message = "We had problems updating some of the fields. Please check and try again.";
                } else {
                    // It is just the message.
                    message = response.data;
                }
                $.Toast.hideToast();
                $.Toast.showToast({'title': message,'icon':'error', 'duration' : 10000});
                // Overlay that errors occured... highlight them.
                $button.val('Try Again').prop('disabled', false);

            },
            success: function(response) {
                // Write the return to the screen.
                // TODO... no, we should let PHP do the formatting, and then return the html from that part of the template. This makes it easier when dealing with hidden fields etc. Just less to do on the front end.
                var data = response.data;
                $.Toast.hideToast();
                $.Toast.showToast({'title': 'Your ad has been updated','icon':'success'});

                // Now we need to update the plain text part with the success values?
                for(var field in data) {
                    if (data.hasOwnProperty(field)) {
                        // console.log(field + ' is ' + data[field]);
                        $('#_view_' + field).html(data[field]);
                    }
                }
                $('.main-content .switch_public_edit_mode').trigger('click'); // And hide the form.
                $button.val('Update').prop('disabled', false);
            }
        });

    }


    /**
     * Initialize the Event Handlers on Items on Various Public Facing Classifieds Pages
     */
    var setupEventHandlers = function() {

        // This toggles visibility of the core form field in the single template.
        $('.switch_public_edit_mode').click(function(evt) {
            evt.preventDefault();
            $('.main-content').toggle();
            $('.update-classy-ad').toggle();
        });

        $('form#update_classy_public').submit(updateClassyAd);

        $('form#create_classyad').submit(createClassyAd);
    };

    function initializeFilepond() {
        var elems = document.querySelector('.photo-upload input');
        FilePond.registerPlugin(
            FilePondPluginImagePreview,
            FilePondPluginFileEncode
        );

        // classy_images  = FilePond.create(elems);
    }

    return {
        init: function() {
            setupEventHandlers();
            // initializeFilepond();
        }
    };
})(jQuery);

jQuery(document).ready(function($) {
    ClassyadsForms.init();
});
