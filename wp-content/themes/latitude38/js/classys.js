// Test stuff here.
var Classys = (function($) {
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
        console.log('fired updateResults() with ' + filter + ' of ' + val);

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
                if(!(data.temp_primary)) { // We're adding a secondary adcat for the first time.
                    data.temp_primary = data.adcat; // hold our primary adcat
                    data.adcat = [val];
                } else { // We're working with adcat in secondary mode....
                    data.adcat = [];
                    $('.secondary-cats input:checked').each(function() {
                        data.adcat.push($(this).val());
                    });
                    if(data.adcat.length == 0) {
                        // no secondary cats detected, set the adcat back to our primary
                        data.adcat = data.temp_primary;
                    }
                }
                break;
            default:
                return false;
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

    function updateImageDisplay() {
        // Working through a tutorial that doesn't use jQuery...cool!
        // https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/file

        var form = document.getElementById('main-photo-form');
        var preview = document.querySelector('.main-photo-preview');
        var input = document.querySelector('#main_photo_input');

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
                    formData.append('action', 'update_classy_mainphoto');
                    formData.append('main_photo', curFiles[0], curFiles[0].name);
                    formData.append('_wpnonce', form.elements['_wpnonce'].value);
                    formData.append('_wp_http_referer', form.elements['_wp_http_referer'].value);
                    formData.append('post_id', form.elements['post_id'].value);


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
        // Setup the handlers on Primary filters.
        $('.primary-filters input').on('change', function(evt) {
            updateResults('primary', $(this).val());
        });

        $('.secondary-filters input').on('change', function(evt) {
            updateResults($(this).attr('name'), $(this).val());
            setTimeout(function(){ /* Do Nothing */ }, 1000);
        });

        $('.switch_public_edit_mode').click(function(evt) {
            evt.preventDefault();
            $('.main-content').toggle();
            $('.update-classy-ad').toggle();
        });

        // Handle the uploading of a new image.
        $('#main_photo_input').on('change', updateImageDisplay);

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
