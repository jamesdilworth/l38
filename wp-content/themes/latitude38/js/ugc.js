// Scripts for User Generated Content Actions
var Ugc = (function($) {
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

    var setupHandlers = function() {

        // Handle the uploading of a new image.
        $("#main-photo-form input[name='main_photo_input']").on('change', updateMainImage);

    };

    return {
        init: function() {
            setupHandlers();
        }
    };
})(jQuery);

jQuery(document).ready(function($) {
    Ugc.init();
});
