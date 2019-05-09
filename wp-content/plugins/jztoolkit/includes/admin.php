<?php

/* Removes the silly WP logo from the Admin Menu. */
function JZ_remove_items( $wp_admin_bar ) {
    $wp_admin_bar->remove_node( 'wp-logo' );
    $wp_admin_bar->remove_node( 'customize' );
}

// Remove 'Administrator' from the list of roles if the current user is not an admin
function JZ_editable_roles( $roles ){
    if( isset( $roles['administrator'] ) && !current_user_can('administrator') ){
        unset( $roles['administrator']);
    }
    return $roles;
}

// If someone is trying to edit or delete and admin and that user isn't an admin, don't allow it
function JZ_map_meta_cap( $caps, $cap, $user_id, $args ){

    switch( $cap ){
        case 'edit_user':
        case 'remove_user':
        case 'promote_user':
            if( isset($args[0]) && $args[0] == $user_id )
                break;
            elseif( !isset($args[0]) )
                $caps[] = 'do_not_allow';
            $other = new WP_User( absint($args[0]) );
            if( $other->has_cap( 'administrator' ) ){
                if(!current_user_can('administrator')){
                    $caps[] = 'do_not_allow';
                }
            }
            break;
        case 'delete_user':
        case 'delete_users':
            if( !isset($args[0]) )
                break;
            $other = new WP_User( absint($args[0]) );
            if( $other->has_cap( 'administrator' ) ){
                if(!current_user_can('administrator')){
                    $caps[] = 'do_not_allow';
                }
            }
            break;
        default:
            break;
    }
    return $caps;
}

// Hook into the 'wp_dashboard_setup' action to register our function
function JZ_customize_editor_admin() {
    if(!current_user_can('activate_plugins')) {
        // Not an admin.
        // remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        remove_meta_box( 'simple_history_dashboard_widget', 'dashboard', 'side' );
    }
}

// Disable 'checked_ontop' for categories... I don't know why WP would even think feature was a good idea! - J
function JZ_wpcats_no_top_float( $args, $post_id ) {
    if ( isset( $args['taxonomy'] ) ) {
        $args['checked_ontop'] = false;
    }
    return $args;
}


// Preprocess WYSIWYG text pasted in from Word, etc.... it filters out everything but what's in the whitelist.
function JZ_configure_tinymce($in) {
    $in['paste_preprocess'] = "function(plugin, args) {
    // Strip all HTML tags except those we have whitelisted
    var whitelist = 'p,h1,h2,h3,h4,h5,h6,ul,li,ol,table,tr,td,th,tbody,thead,img,a,br';
    var stripped = jQuery('<div>' + args.content + '</div>');
    var els = stripped.find('*').not(whitelist);
    for (var i = els.length - 1; i >= 0; i--) {
      var e = els[i];
      jQuery(e).replaceWith(e.innerHTML);
    }
    // Strip all class and id attributes
    stripped.find('*').removeAttr('id').removeAttr('class');
    // Return the clean HTML
    args.content = stripped.html();
  }";
    return $in;
}

