// Custom scripts go here.
var S4O = (function($) {

    var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

    var initializeMfp = function() {

        $issuu_orientation = w > h ? '62432356' : '62432579';

        $('.issuu').magnificPopup({
            type: 'iframe',
            iframe: {
                patterns: {
                    issuu: {
                        index: 'issuu.com/',
                        id: 'docs/',
                        src: '//issuu.com/latitude38/docs/%id%/' + $issuu_orientation // Single Page is 62432579, Double Page is 62432356
                    }
                },
                srcAction: 'iframe_src'
            },
            mainClass: 'mfp-issuu',
            enableEscapeKey: true,
            callbacks: {
                open: function() {
                    // Will fire when this exact popup is opened. 'this' refers to mfp object.
                    // Trap back button
                    var mfp_object = this;
                    window.history.pushState({page: 1}, "", "");
                    $(window).on('popstate', function(event) {
                        mfp_object.close();
                    });

                    // Dig out the issue and page number from the object and send to google.
                    for(var i=0; i < mfp_object.items.length; i++) {
                        if(typeof mfp_object.items[i].src !== 'undefined') {
                            var url = new URL(mfp_object.items[i].src);
                            var parts = url.pathname.split('/');
                            var page = parts[parts.length - 1];
                            var issue = parts[parts.length - 2];
                        }
                    }
                    ga('send', 'event', 'View Issuu', issue, page );
                },
                close: function() {
                    // Release back button when popup is closed
                    $(window).off('popstate');
                }

            }

        });

        $('.inline.popup').magnificPopup({
            type: 'inline',
            src: this.href
            // other options
        });
    };

    var autoPagePopup = function() {
        // If this is a magazine issue, and the issuu link is set
        var hash_link = window.location.hash.split('#')[1],
            issuu_link = $('#default-issuu-link').attr('href'),
            new_url;

        if (!isNaN(hash_link) && issuu_link !== "") {
            new_url = issuu_link + '/' + hash_link + '?e=1997181';
            $('#default-issuu-link').attr('href', new_url).click();
        }
    };

    var extraAnalytics = function() {

        var documents = /\.(pdf|doc*|xls*|ppt*)$/i;
        var baseHref = '';
        if ( $('base').attr('href') !== undefined ) {
            baseHref = $('base').attr('href');
        }


        $('a').each( function() {
            // Track External Clicks
            var href = $( this ).attr('href');
            var hostname = extractHostname(href);
            if ( href && ( href.match(/^https?\:/i) ) && ( ! hostname.match( document.domain ) ) ) {
                // While we're at it, open in a new window!
                if(!$(this).attr('target')) {
                    $(this).attr('target','_blank').attr('rel','noopener');
                }

                $( this ).on( 'click', function() {

                    // Don't bother binding handlers to issuu links as these are tracked in mfp
                    if ($(this).hasClass('issuu')) {
                        return false;
                    }

                    // If an event binding is already set, exit.
                    if ( $( this ).attr('onclick') && $( this ).attr('onclick').indexOf('ga(') ) {
                        return false;
                    }

                    var extLink = href.replace( /^https?\:\/\//i, '' );
                    var title = $(this).attr('data-gatitle') ? $(this).attr('data-gatitle') : $( this ).attr('title');
                    title = title ? title : extLink;

                    // Check to see if there are any overrides.
                    var category = $( this ).attr('data-gacategory') ? $( this ).attr('data-gacategory') : 'External';
                    var label = $( this ).attr('data-galabel') ? $( this ).attr('data-galabel') : extLink;

                    ga('send','event', category, title, label );
                    if ( $( this ).attr('target') !== undefined && $( this ).attr('target').toLowerCase() != '_blank' ) {
                        setTimeout( function() { location.href = href; }, 200 );
                        return false;
                    }
                });
            }

            // Track Emails
            else if ( href.match(/^mailto\:/i)) {
                $( this ).on( 'click', function() {
                    // Function needs to be built out.
                    var category = $( this ).attr('data-gacategory') ? $( this ).attr('data-gacategory') : 'Email';
                    var title = $(this).attr('data-gatitle') ? $(this).attr('data-gatitle') : $( this ).attr('href');
                    var label = $( this ).attr('data-galabel') ? $( this ).attr('data-galabel') : $(this).text();
                    // console.log('GA Event Sent: ' + category + ':' + title + ':' + label + ':')
                    ga('send','event', category, title, label );
                });
            }


            // Track Documents
            else if ( href && href.match(documents) ) {
                $( this ).on( 'click', function() {
                    var extension = ( /[.]/.exec(href) ) ? /[^.]+$/.exec(href) : undefined;
                    var filePath = href;
                    var shortPath = filePath.substr( filePath.lastIndexOf('/') );

                    var title = $( this ).attr('title');
                    title = title ? title : shortPath;
                    var category = $( this ).attr('data-gacategory') ? $( this ).attr('data-gacategory') : 'Content';
                    var label = $( this ).attr('data-galabel') ? $( this ).attr('data-galabel') : filePath;

                    ga('send','event', category, title, label );
                    if ( $( this ).attr('target') !== undefined && $( this ).attr('target').toLowerCase() != '_blank' ) {
                        setTimeout( function() { location.href = baseHref + href; }, 200 );
                        return false;
                    }
                });
            }
        });
   };

    var queryParams = function(name) {
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( window.location.href );
        if( results == null )
            return "";
        else
            return results[1];
    };

    var initDropDowns = function() {
        $('.dropdown-submenu > a').on("click", function(e){
            $('.dropdown-submenu ul').hide();
            $(this).next('ul').toggle();
            e.stopPropagation();
            e.preventDefault();
        });
    }

    var bindEventHandlers = function() {
        $('.cover').on('touchend',function(evt) {
            var $elem = $(this);
            if($elem.hasClass('active')) {
                return true;
            } else {
                $('.cover').removeClass('active');
                $elem.addClass('active');
                evt.preventDefault();
                return false;
            }
        });


    };



    return {
        preInit: function() {
            // Sometimes I stuff in CSS layout stuff in here... such as equalization
            if(w > 768) {
                $('.equalize').equalize('innerHeight');
                $('.four-story').equalize({children: '.normal.story'});
            }

        },

        init: function() {
            bindEventHandlers();
            extraAnalytics();
            this.miniTabs();
            initializeMfp();
            autoPagePopup();
            initDropDowns();
        },

        miniTabs: function() {

            /* ================================================================================================
             *
             * MINI TABS - A REPEATABLE TAB SYSTEM FOR USE WITHIN THE FLOW OF THE PAGE.
             *
             * BUILDS ON :
             * <ul class="tabs">
             *     <li><a href="#id of item" [OR] data-targets=".class_of_items to be shown"
             *
             * <div class='tabbed'>
              *     <div id="#id of item" [OR] class='<target_name>'>
             */


            function miniTabs( $tab, $firstload ) {
                // First check to see if the tab system is in place!
                if ( $('ul.tabs').length === 0 ) {
                    return false;
                }

                // If the tabs are too

                // Set $tab to be the <li> that matches window.hash
                if (window.location.hash && $firstload ) {
                    $(window).load( function() {
                        $(this).scrollTop(0);
                    });
                    $tab = $('ul.tabs a[href="' + window.location.hash + '"]').parent();
                }

                if ( $tab === void(0) || $tab.length < 1 ) {
                    // On page load, there's no object, so choose the first tab.
                    $tab = $('ul.tabs li:first-child');
                }

                var $oldActiveTab = $tab.closest('ul').find('li.active'),
                    contentLocation = $tab.children('a').attr("href");

                // Strip off the current url that IE adds
                contentLocation = contentLocation.replace(/^.+#/, '#');

                //Make Tab Active
                $oldActiveTab.removeClass('active');
                $tab.addClass('active');

                //Show Tab Content
                $('.tabbed > div').hide();
                console.log($tab.children('a').data('targets'));
                if($tab.children('a').data('targets')) {
                    $('.tabbed .' + $tab.children('a').data('targets')).show();
                } else if(contentLocation) {
                    $('#' + contentLocation).show();
                }
            }
            miniTabs( void(0), true );

            // Set up event Handlers for Tabs
            $( document ).on( 'click', 'ul.tabs li', function ( event ) {
                event.preventDefault();
                history.pushState( null, null, $('a', this).attr('href') );
                miniTabs( $( this ), false );
            });
        }
    };

})(jQuery);
S4O.preInit(); // Stuff to do ASAP before onLoad

jQuery(document).ready(function($) {
    S4O.init();

});

