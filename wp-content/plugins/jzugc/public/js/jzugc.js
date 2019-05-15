// Scripts for User Generated Content Actions
var Jzugc = (function($) {
    "use strict";

    var ajaxurl = '/wp-admin/admin-ajax.php';

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

    /**
     * Improve the functionality and appearance of the standard <input type="file"> element.
     *
     * Attaches to file elements with the class 'jz-image'
	 * Fired when a user chooses an image in the filesystem.
     * Works alongside CSS that hides the filebox.
     *
     */
    function setupBetterImageHandling() {
        var $inputs = $('.jzugc_image');

        $inputs.each(function () {
            var name = $(this).attr('name') ? $(this).attr('name') : $(this).attr('id');
            $(this).before('<div class="_jzugc_image_preview" id="_jzugc_preview_' + name + '"></div>');
            $(this).parent().addClass('_jzugc_image_container');
        });

        $inputs.on('change', preProcessImage);

    }

    function updateMainImage() {
        // Working through a tutorial that doesn't use jQuery...:)
        // https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/file

        var form = document.getElementById('main-photo-form');
        var preview = document.querySelector('.main-photo-preview');
        var input = document.querySelector('#main_photo_input');

        //
        while(preview.firstChild) {
            preview.removeChild(preview.firstChild);
        }

        var curFiles = input.files;  // Grab the FileList object that contains the information on all the selected files

        if(curFiles.length === 0) {
            var para = document.createElement('p');
            para.textContent = 'No files currently selected for upload';
            preview.appendChild(para);
        } else {
            var valid_items = false;
            var list = document.createElement('ol');
            list.classList.add("new-images");
            preview.appendChild(list);

            var submit = document.createElement('a');
            submit.classList.add("submit-new-image", "btn");
            submit.textContent = 'Save';

            for(var i = 0; i < curFiles.length; i++) {
                var listItem = document.createElement('li');
                var para = document.createElement('p');

                // Use the custom validFileType() function to check whether the file is of the correct type .
                if(validFileType(curFiles[i])) {
                    para.textContent = 'File name ' + curFiles[i].name + ', file size ' + returnFileSize(curFiles[i].size) + '.';
                    var image = document.createElement('img');
                    image.src = window.URL.createObjectURL(curFiles[i]);

                    listItem.appendChild(image);
                    // listItem.appendChild(para);
                    valid_items = true;
                } else {
                    para.textContent = 'File name ' + curFiles[i].name + ': Not a valid file type. Update your selection.';
                    listItem.appendChild(para);
                }
                list.appendChild(listItem);
            }

            if(valid_items) {
                preview.appendChild(submit);
                submit.addEventListener('click', function() {
                    // Created with the help of this tut....  https://blog.teamtreehouse.com/uploading-files-ajax

                    var uploadButton = this;
                    this.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

                    var formData = new FormData();
                    formData.append('action', form.elements['ajax_action'].value);
                    formData.append('main_photo', curFiles[0], curFiles[0].name);
                    formData.append('_wpnonce', form.elements['_mainphoto_nonce'].value);
                    formData.append('_wp_http_referer', form.elements['_wp_http_referer'].value);

                    // If post... get the post id.
                    if(form.elements['post_id']) {
                        formData.append('post_id', form.elements['post_id'].value);
                    }

                    // This will handle multiple... but for now, we just want one.
                    /*
                    for (var i = 0; i < curFiles.length; i++) {
                        var file = curFiles[i];

                        // Check the file type.... again?
                        if (!file.type.match('image.*')) {
                            continue;
                        }

                        // Add the file to the request.
                        formData.append('photos[]', file, file.name);
                    }
                    */

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxurl, true);
                    // Set up a handler for when the request finishes.
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            // Success! - File(s) uploaded.
                            if(xhr.response === 'Success!') {
                                uploadButton.innerHTML = 'Done <i class="fa fa-check"></i>';
                                if(image) {
                                    $('.mag-img').css('background-image', 'url(' + image.src + ')'); // JQUERY!
                                }
                            } else {
                                uploadButton.innerHTML = 'Uh-oh! Something went wrong';
                            }
                        } else {
                            alert('Ooops. An error occurred!');
                        }
                    };
                    xhr.send(formData);
                });
            }
        }
    }

    /**
	 * After a file has been selected, do stuff to it and prepare it for uploading.
	 * Do stuff based on parameters within the image, do things like crop and what not.
     */
    function preProcessImage(evt) {

        var input = this;
        var name = input.name ? input.name : input.id;
        var preview = document.getElementById('_jzugc_preview_' + name);
        var async_option = $(this).data('async');

    	// Validate that it's not more than a certain size.
		// Load the preview box with a copy of the image.
        // Constrain proportions?
        // Crop and other stuff.

        while(preview.firstChild) {
            preview.removeChild(preview.firstChild);
        }

        var curFiles = input.files;  // Grab the FileList object that contains the information on all the selected files

        if(curFiles.length === 0) {
            var para = document.createElement('p');
            para.textContent = 'No files currently selected for upload';
            preview.appendChild(para);
        } else {
            var valid_items = false;
            var list = document.createElement('ol');
            list.classList.add("new-images");
            preview.appendChild(list);

            for (var i = 0; i < curFiles.length; i++) {
                var listItem = document.createElement('li');
                var para = document.createElement('p');

                // Use the custom validFileType() function to check whether the file is of the correct type .
                if (validFileType(curFiles[i])) {
                    para.textContent = 'File name ' + curFiles[i].name + ', file size ' + returnFileSize(curFiles[i].size) + '.';
                    var image = document.createElement('img');
                    image.src = window.URL.createObjectURL(curFiles[i]);

                    listItem.appendChild(image);
                    // listItem.appendChild(para);
                    valid_items = true;
                } else {
                    para.textContent = 'File name ' + curFiles[i].name + ': Not a valid file type. Update your selection.';
                    listItem.appendChild(para);
                }
                list.appendChild(listItem);
            }
        }

        /**
         *   If it's set up to do an async submit, we'll upload the image to a ajax handler, that'll
         *   send back a temp url indicator of the file, and store that in a seperate hidden field. I dunno!
         *   This is why I should just use a library!
         */
        if(async_option === 'auto') {
            // Automatically upload item to the server
        } else if ((async_option === 'manual')) {
            // Add an 'Upload' button to allow user to upload item to the server.
        }
	}

    /**
	 * Asynchronously submit an image to admin-ajax.
	 *
	 * @Return: a file reference, or an error if failure
     */
	function asyncSubmitImage() {

		// We'll need to create a form object
		// Add an action parameter
		// Get a nonce?

		// Submit the form
		// Return a fileID or URL?... should this be as part of a seperate input element?
	}

	function ajaxLogMeIn(evt) {
		evt.preventDefault();
		$('form#login p.status').show().text("Sending user info, please wait...");
		$.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                'action': 'ajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
                'email': $('form#login #login-email').val(),
                'password': $('form#login #login-password').val(),
                'security': $('form#login #login-security').val()
            },
            success: function (data) {
                $('form#login p.status').text(data.message);
                if (data.loggedin === true) {
                    document.location.reload(true);
                }
            }
        });
	}

    var setupHandlers = function() {

        $('.login-register-link').magnificPopup({
            items: {
                type: 'inline',
                src: "#login-register",
                modal:true
            }
        });

        $('.sign-up').magnificPopup({
            items: {
                type: 'inline',
                src: "#sign-up",
                modal:true
            }
        });

        // Perform AJAX login on form submit
        $('form#login').on('submit', ajaxLogMeIn);
        $("#main-photo-form input[name='main_photo_input']").on('change', updateMainImage);

    };

    return {
        init: function() {
            setupHandlers();
            setupBetterImageHandling();
        }
    };
})(jQuery);

jQuery(document).ready(function($) {
    Jzugc.init();
});
