<?php
// INCLUDES
include_once('functions/bb-config.php'); // BB Press custom config
include_once('functions/helpers.php'); // Miscellaneous functions
include_once('functions/wp_dropdown_posts.php'); // Should get moved into a site-specific plugin.
include_once('functions/custom_post_types.php'); // Definitions for the custom post types
include_once('functions/filters.php'); // Broad range of filters.
include_once('functions/actions.php'); // Actions
include_once('functions/admin_tweaks.php'); // Stuff for the admin console.
include_once('functions/enqueue.php'); // Scripts & styles
include_once('functions/taxonomies.php'); // Taxonomy Changes
include_once('functions/shortcodes.php'); // Custom Shortcodes
include_once('functions/widgets.php'); // Custom Widgets
include_once('functions/users.php'); // Subscriber Mods for general login.

// include_once('functions/ajax.php'); // AJAX calls.
// include_once('functions/debug.php'); // When shit starts going wrong, uncomment this.

// Theme support configures how things like images are displayed in body copy.
add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );

// Used to set the standard monetary system for the classifieds.
setlocale(LC_MONETARY, 'en_US.UTF-8');


