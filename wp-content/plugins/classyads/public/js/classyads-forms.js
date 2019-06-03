/**
 * ClassyadsForms - Tools for creating, updating and deleting Classyads info.
 * @type {{init}}
 */

var ClassyadsForms = (function($) {
    "use strict";

    var ajaxurl = '/wp-admin/admin-ajax.php';
    var plans = localized.plans; // Populate plans with localize script?
    var validator;
    var form_sections = [];
    var $form;
    var $cloned_form;
    var steps = 'disabled';

    var handleAjaxErrors = function() {
        // Universal function to highlight the fields that contain errors.
    };

    /**
     * When the user selects a plan, this will configure the form.
     */
    var changePlan = function(evt) {
        evt.preventDefault();

        var plan_type = $(this).data('plan'); // Link that kicks off this function defines the plan.
        var plan_options = plans[plan_type]; // This should now hold an array of all the plan options :)

        if(steps === 'active') {
            console.log('destroying steps');
            // When the steps container is reinitiated after mfp-close, it's all fucked up. Not quite sure why,
            // but seems like it's best just to destroy the steps, and allow user to use it as a single page
            // form in the meantime. After destroy, steps never seems to work correctly again. :(
            // what's more, destroying the steps strips the classnames off the sections, so this seems broken too. :(
            $form.steps("destroy");
            steps = 'destroyed';
            $("form#create_classyad").remove();
            $('#create_classyad_container .wrapper').append($cloned_form);
        } else {
            $form = $("form#create_classyad");
            // cloned_form = $form.clone();
        }

        // Set the value of our form field.... v. important!
        $form.find('[name=ad_subscription_level]').val(plan_type);

        // Highlight the chosen plan
        // $('.plan_options .plan').removeClass('active').addClass('unselected').find('.btn.choose_plan').text('Choose');
        // $(this).parents('.plan').removeClass('unselected').addClass('active');
        // $(this).text('Selected');

        // Initialize the payment fields first.... if they don't have a cim, you can show the cc fields.
        changeCreatePayment();

        // Hide CC stuff if the plan is free.
        if(plan_options.amount === 0) {
            form_sections.payment = $('section.payment_info').detach(); // TODO!!! - Remove from the form, so that validation doesn't fire.
        } else {
            $('section.contact_info').after(form_sections.payment);
        }

        // Hide/show magazine text options if available.
        if(plan_options.in_print) {
            if(typeof form_sections.admag !== 'undefined') {
                $('section.online_listing').after(form_sections.admag);
            }
        } else {
            form_sections.admag = $('section.magazine_listing').detach();
        }

        // Hide/show image upload if available.
        if(plan_options.multiple_photos) {
            if(typeof form_sections.mulitple_photos !== 'undefined') {
                $('section.upload_images').append(form_sections.mulitple_photos);
            }
        } else {
            form_sections.mulitple_photos = $('.section_upload_mulitple').detach();
        }

        if(plan_options.multiple_photos) {
            if(typeof form_sections.mulitple_photos !== 'undefined') {
                $('section.upload_images').append(form_sections.mulitple_photos);
            }
        } else {
            form_sections.mulitple_photos = $('.section_upload_multiple').detach();
        }

        // Move the h3's outside the sections so that steps can work with them.

        if(typeof steps === 'undefined') {
            $('#create_classyad section h3').each(function() {
                var parentelem = $(this).parents('section');
                $(this).insertBefore(parentelem);
            });

            $('.submit_container').hide();

            console.log('launching steps');
            $form.steps({
                headerTag: "h3",
                bodyTag: "section",
                transitionEffect: "slideLeft",
                autoFocus: true,
                onStepChanging: function (event, currentIndex, newIndex)
                {
                    // Always allow previous action even if the current form is not valid!
                    if (currentIndex > newIndex)
                    {
                        return true;
                    }

                    $form.validate().settings.ignore = ":disabled,:hidden";
                    return $form.valid();
                },
                onFinishing: function (event, currentIndex)
                {
                    // $form.validate().settings.ignore = ":disabled";
                    return $form.valid();
                },
                onFinished: function (event, currentIndex)
                {
                    $form.submit();
                }
            });
            steps = 'active';

        } else {
            // This only necessary when we're not using steps.
            validator = $form.validate({
                submitHandler: function(form, evt) {
                    createClassyAd(form, evt);
                }
            });
        }

        // rebind the image handlers
        $form.find('.jzugc_image').on('change', Jzugc.preProcessImage);

        // Set the number of characters on the ad_mag_text.
        // This needs to be after the steps declaration, so that it works.
        $form.find('[name=ad_mag_text]').simplyCountable({
            maxCount    : plan_options.print_chars,
            strictMax   : true,
            counter     : '.magazine_listing .counter'
        });

        // Open form in a mfp window
        $.magnificPopup.open({
            items: {
                src: $('#create_classyad_container'),
                type: 'inline',
                closeOnContentClick: false,
                closeOnBgClick: false,
                showCloseBtn: true
            }
        });
    };

    // Initialize and handle layout for changes with the payment method... this fires when the radio button changes
    var changeCreatePayment = function() {
        // This is the code that handles changing radio buttons for payment method.
        if($('input[name=cim_payment_profile_id]').length > 0) {
            var val = $('input[name=cim_payment_profile_id]:checked').val();
            if(val === 'new_payment_method') {
                $('#create_add_payment_fields').show();
            } else {
                $('#create_add_payment_fields').hide();
            }
        }
    };

    // Handle payment method...  this fires when the 'add new payment link is clicked.' - Connected to the above.
    var addCreditCardFields = function(evt) {
        // Show the cc fields and select the right radio button.
        evt.preventDefault();
        $('#create_add_payment_fields').show();
        $("input:radio[value=new_payment_method]").attr('checked', true);
    };

    /**
     * Fired on Place Classy Ad Form Submission. This is placed from the validator script in changePlan()
     *
     * @param evt
     * @param form - should refer to the form.
     */
    var createClassyAd = function(form, evt) {
        var formData = new FormData(form);

        evt.preventDefault(); // Prevent Default Submission

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
                var pause = setTimeout(function() {
                    document.location.href = response.data.url + '?created=new';
                }, 2000);

            } else {
                // Failed with a reason.
                $.Toast.hideToast();
                $(form).find('[name=post_id]').val(response.data.post_id);
                $.Toast.showToast({'title': response.data.msg, 'icon':'error', 'duration':4000});
                if(response.data.errors) {
                    validator.showErrors(response.data.errors);
                }
            }
            // TODO!!! - Hide the form... show some text that does the confirmation.
        });


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

    /**
     * Handles form submission from a user wishing to upgrade the plan of the account they have.
     * @param evt
     */
    var upgradePlan = function(evt) {
        evt.preventDefault(); // Prevent Default Submission
        alert('Upgrade Ad - Functionality Coming Soon.... ');
    };

    // Used to set the correct fields in the form before opening the mfp window to allow a customer to renew.
    var openRenewModal = function(evt) {
        evt.preventDefault();
        // Do stuff.
    };

    var removeClassyAd = function(evt) {
        evt.preventDefault();

        var formData = new FormData(this);

        var remove_classyad_call = $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 5000,
            contentType: false, // Stop jQuery from reinterpreting the contentType.
            processData: false, // Stop jQuery from re-processing the formData
            dataType: 'json',
            data: formData
        });

        remove_classyad_call.fail(function(jqXHR, textStatus, errorThrown) {
            $.Toast.showToast({'title': errorThrown, 'icon':'error', 'duration':5000});
        });

        remove_classyad_call.done(function(response) {
            if(response.success) {
                $.Toast.hideToast();
                $.magnificPopup.close();
                // Replace form with confirmation, and a link to the new page.
                $.Toast.showToast({'title': 'Your ad has been removed. It will no longer appear in search results','icon':'success', 'duration':3000});
                window.location.href = "/my-account/#my-classies";
            } else {
                // Failed with a reason.
                $.Toast.hideToast();
                $.Toast.showToast({'title': response.data.msg, 'icon':'error', 'duration':3000});
            }
        });

    };

    var renewClassyAd = function(evt) {
        evt.preventDefault();
        var formData = new FormData(this);

        var renew_classyad_call = $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 5000,
            contentType: false, // Stop jQuery from reinterpreting the contentType.
            processData: false, // Stop jQuery from re-processing the formData
            dataType: 'json',
            data: formData
        });

        renew_classyad_call.fail(function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus + ': ' + errorThrown);
            $.Toast.showToast({'title': errorThrown, 'icon':'error', 'duration':5000});
        });

        renew_classyad_call.done(function(response) {
            if(response.success) {
                $.Toast.hideToast();
                $.magnificPopup.close();
                // Replace form with confirmation, and a link to the new page.
                $.Toast.showToast({'title': 'Sweet. Your ad has been renewed','icon':'success', 'duration':3000});
                document.reload();
            } else {
                // Failed with a reason.
                $.Toast.hideToast();
                $.Toast.showToast({'title': response.data.msg, 'icon':'error', 'duration':3000});
            }
        });

        // On Submit...
        // - AJAX update would update the plan details, along with a confirmation
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

        // These buttons submit associated forms.
        $('.ok-renew.btn').click(function(evt) { evt.preventDefault(); $('form#renew_classyad').submit(); });
        $('.ok-remove.btn').click(function(evt) { evt.preventDefault(); $('form#remove_classyad').submit(); });

        // Form Submissions
        // $('form#create_classyad').submit(createClassyAd) ... now handled with initializePlaceadForm();
        $('form#update_magad').submit(updateClassyAd);
        $('form#update_classy_public').submit(updateClassyAd);
        $('form#renew_classyad').submit(renewClassyAd);
        $('form#remove_classyad').submit(removeClassyAd);

        // Add New Payment Method Interaction
        $('input[name=cim_payment_profile_id]').change(changeCreatePayment);
        $('.add-payment-link').click(addCreditCardFields);

        // Create Form Interractions
        $('#card_admin_override').change(function() {
            if(this.checked) {
                $('#create_add_payment_fields').toggle('fast', 'linear');
            }
        });
        $('#owner_admin_override').change(function() {
            if(this.checked) {
                $('input[name=override_name]').val(' ').attr('placeholder', '');
                $('input[name=override_email]').val(' ').attr('placeholder', '');
                $('input[name=override_phone]').val(' ').attr('placeholder', '');
                $('input[name=override_other]').val(' ').attr('placeholder', '');
            }
        });

        // Other Dynamic Events that'll require a popup probably.
        $('.btn.choose_plan').click(changePlan);
        $('.upgrade-plan.btn').click(upgradePlan);

        // Buttons from my-account view.
        $('a.renew-this-ad-modal').click(openRenewModal);

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
            // initializePlaceadForm();
            // initializeFilepond();
        }
    };
})(jQuery);

jQuery(document).ready(function($) {
    ClassyadsForms.init();

    // This only handles the popup on the single-classy template.
    $('.renew-modal').magnificPopup({
        type: 'inline',
        closeOnContentClick: false,
        closeOnBgClick: false,
        showCloseBtn: true
    });

    $('.remove-modal').magnificPopup({
        type: 'inline',
        modal:true
    });

});
