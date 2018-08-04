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
    }

    var extraAnalytics = function() {

        var documents = /\.(pdf|doc*|xls*|ppt*)$/i;
        var baseHref = '';
        if ( $('base').attr('href') !== undefined ) {
            baseHref = $('base').attr('href');
        }

        $('a').each( function() {
            // Track External Clicks
            var href = $( this ).attr('href');
            if ( href && ( href.match(/^https?\:/i) ) && ( ! href.match( document.domain ) ) ) {
                $( this ).on( 'click', function() {
                    // If an event binding is already set, exit.
                    if ( $( this ).attr('onclick') && $( this ).attr('onclick').indexOf('ga(') ) {
                        return false;
                    }

                    var extLink = href.replace( /^https?\:\/\//i, '' );
                    var title = $( this ).attr('title');
                    title = title ? title : extLink;

                    // Check to see if there are any overrides.
                    var category = $( this ).attr('data-gaaction') ? $( this ).attr('data-gaaction') : 'External';
                    var label = $( this ).attr('data-galabel') ? $( this ).attr('data-galabel') : extLink;

                    ga('send','event', category, title, label );
                    if ( $( this ).attr('target') !== undefined && $( this ).attr('target').toLowerCase() != '_blank' ) {
                        setTimeout( function() { location.href = href; }, 200 );
                        return false;
                    }
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
                    var category = $( this ).attr('data-gaaction') ? $( this ).attr('data-gaaction') : 'Content';
                    var label = $( this ).attr('data-galabel') ? $( this ).attr('data-galabel') : filePath;

                    ga('send','event', category, title, label );
                    if ( $( this ).attr('target') !== undefined && $( this ).attr('target').toLowerCase() != '_blank' ) {
                        setTimeout( function() { location.href = baseHref + href; }, 200 );
                        return false;
                    }
                });
            }
        });
   }

    var queryParams = function(name) {
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( window.location.href );
        if( results == null )
            return "";
        else
            return results[1];
    }

    var bindEventHandlers = function() {
        
    }

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
            // extraAnalytics();
            this.miniTabs();
            initializeMfp();
        },

        miniTabs: function() {

            /* ================================================================================================
             *
             * MINI TABS - A REPEATABLE TAB SYSTEM FOR USE WITHIN THE FLOW OF THE PAGE.
             *
             * BUILDS ON :
             * <ul class="tabs" data-target="name_of_container">...
             *
             * CONTAINER PANELS SHOULD BE THE CHILD DIV's. WE KEEP IT SIMPLE.
             *
             */


            function miniTabs( $tab, $firstload ) {
                // First check to see if the tab system is in place!
                if ( $('ul.tabs').length === 0 ) {
                    return false;
                }

                var target_container_id = $('ul.tabs').attr('data-target');
                console.log(target_container_id);

                if ( window.location.hash && $firstload ) {
                    $(window).load( function() {
                        $(this).scrollTop(0);
                    });
                    $tab = $('ul.tabs a[href="' + window.location.hash + '"]').parent();
                }

                // $tab expects a jquery object in the sectionnav
                if ( $tab === void(0) || $tab.length < 1 ) {
                    // On page load, there's no object, so choose the first tab.
                    $tab = $('ul.tabs li:first-child');
                }

                var $activeTab = $tab.closest('ul').find('li.active'),
                    contentLocation = $tab.children('a').attr("href");

                // Strip off the current url that IE adds
                contentLocation = contentLocation.replace(/^.+#/, '#');

                //Make Tab Active
                $activeTab.removeClass('active');
                $tab.addClass('active');

                //Show Tab Content
                $('#' + target_container_id + '>div').hide();
                $( contentLocation ).show();

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

