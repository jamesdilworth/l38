/**
 * ClassyadsForms - Tools for creating, updating and deleting Classyads info.
 * @type {{init}}
 */

var ClassyadsForms = (function($) {
    "use strict";

    var ajaxurl = '/wp-admin/admin-ajax.php';
    var plans = localized.plans; // Populate plans with localize script?
    var validator;
    var payment_form;

    var handleAjaxErrors = function() {
        // Universal function to highlight the fields that contain errors.
    };

    var changePlan = function() {

        /**
         *  TODO!!! Refer to plans as to whether the option:
         *  - Requires payment.
         *  - Allows mag text.
         *  - How much mag text
         *  - Multiple Images
         */

        var selected_plan = $(this).children("option:selected").val();
        var plan_options = plans[selected_plan]; // This should now hold an array of all the plan options :)

        if(plan_options.amount === 0) {
            payment_form = $('section.payment_info').detach(); // TODO!!! - Remove from the form, so that validation doesn't fire.
        } else {
            $('section.contact_info').after(payment_form);
        }
    };

    var initializePlaceadForm = function() {
        // Initialize wizard
        var $form = $("form#create_classyad");

        validator = $form.validate({
            submitHandler: function(form, evt) {
                createClassyAd(form, evt);
            }
        });

        $form.find('[name=ad_mag_text]').simplyCountable({
            maxCount    : 200,
            strictMax   : true,
            counter     : '.magazine_listing .counter'
        });

        $form.find('[name=ad_subscription_level]').change(changePlan).trigger('change');
        /*
         $form.steps({
             headerTag: "h3",
             bodyTag: "section",
             transitionEffect: "slideLeft",
             autoFocus: true,
             onStepChanging: function (event, currentIndex, newIndex)
             {
                 // Allways allow previous action even if the current form is not valid!
                 if (currentIndex > newIndex)
                 {
                     return true;
                 }

                 // $form.validate().settings.ignore = ":disabled,:hidden";
                 // return $form.valid();
                 return true;
             },
             onFinishing: function (event, currentIndex)
             {
                 // $form.validate().settings.ignore = ":disabled";
                 return $form.valid();
             },
             onFinished: function (event, currentIndex)
             {
                 $form.submit();
                 alert("Submitted!");
             }
         });
         */
     };


    /**
     * Fired on Place Classy Ad Form Submission.
     *
     * @param evt
     * @param form - should refer to the form.
     */
    var createClassyAd = function(form, evt) {
        var formData = new FormData(form);

        // Any additional client side validation.
        var waitingToast = $.Toast.showToast({'title': 'Creating your Ad...', 'icon':'loading', 'duration': 0 });

        var create_classyad_call = $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 5000,
            contentType: false, // Stop jQuery from reinterpreting the contentType.
            processData: false, // Stop jQuery from re-processing the formData
            dataType: 'json',
            data: formData
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus + ': ' + errorThrown);
            $.Toast.showToast({'title': errorThrown, 'icon':'error', 'duration':6000});
        }).done(function(response) {
            if(response.success) {
                $.Toast.hideToast();
                // Replace form with confirmation, and a link to the new page.
                $.Toast.showToast({'title': 'Sweet. Your ad has been created','icon':'success', 'duration':3000});
                document.location.href = response.data.url + '?created=new';
            } else {
                // Failed with a reason.
                $.Toast.hideToast();
                $(form).find('[name=post_id]').val(response.data.post_id);
                $.Toast.showToast({'title': response.data.msg, 'icon':'error', 'duration':0});
                if(response.data.errors) {
                    validator.showErrors(response.data.errors);
                }
            }
            // TODO!!! - Hide the form... show some text that does the confirmation.
        });

        evt.preventDefault(); // Prevent Default Submission
    };

    /**
     * General Purpose Handler for updating Classy Ad Fields.
     * @param evt
     */
    var updateClassyAd = function(evt) {
        evt.preventDefault();
        var $button = $(this).find("input[type='submit']");
        var $edit_toggle = $(this).find('.edit_link');
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
                var message = response.msg;
                $.Toast.showToast({'title': message,'icon':'error'});
                $button.val('Try Again').prop('disabled', false);

            },
            success: function(response) {
                var data = response.data;
                $.Toast.showToast({'title': 'Your ad has been updated','icon':'success'});

                for(var field in data.fields) {
                    if (data.fields.hasOwnProperty(field)) {
                        $('#_view_' + field).html(data.fields[field].replace(/\\/g, ""));
                    }
                }
                $edit_toggle.trigger('click'); // And hide the form.
                $button.val('Update').prop('disabled', false);
            },
            complete: function() {
                $.Toast.hideToast();
            }
        });
    };

    var markAsSold = function(evt) {
        evt.preventDefault();
        alert('Mark as Sold - functionality Coming Soon....');
    };

    var upgradeAd = function(evt) {
        evt.preventDefault();
        alert('Upgrade Ad - Functionality Coming Soon.... ');
    };

    var renewAd = function(evt) {
        evt.preventDefault();
        alert('Renew Ad - Functionality Coming Soon.... ');
    };

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

        // This toggles visibility of the magazine edit field in the single template.
        $('.switch_magad_edit_mode').click(function(evt) {
            evt.preventDefault();
            $('.mag-body').toggle();
            $('#update_magad').toggle();
        });

        // Form Submissions
        // $('form#create_classyad').submit(createClassyAd) ... now handled with initializePlaceadForm();
        $('form#update_magad').submit(updateClassyAd);
        $('form#update_classy_public').submit(updateClassyAd);

        // Other Dynamic Events that'll require a popup probably.
        $('.mark-as-sold.btn').click(markAsSold);
        $('.renew.btn').click(renewAd);
        $('.upgrade.btn').click(upgradeAd);
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
            initializePlaceadForm();
            // initializeFilepond();
        }
    };
})(jQuery);

jQuery(document).ready(function($) {
    ClassyadsForms.init();
});
