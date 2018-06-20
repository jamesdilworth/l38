/* Scripts that we want to run in the head of the document.... for whatever reason.
 *  Note this is before a lot of other stuff, but jQuery is available!
 */

var preGPU = (function($) {
    return {
        getURLParameter: function (sParam) {
            var sPageURL = decodeURIComponent(window.location.search.substring(1)),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        }
    }
})(jQuery);



