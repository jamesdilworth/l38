<?php

include_once('widgets/class-lectronic-stories-widget.php'); // Lectronic Stories Widget
include_once('widgets/class-magazine-contents-widget.php'); // Single Issue Widget
include_once('widgets/class-magazine-archive-widget.php'); // Magazine Archive Widget

/*
foreach (glob("widgets/*.php") as $filename)  {
    error_log('loaded ' . $filename);
    include $filename;
}
*/

add_action('widgets_init', 's4o_register_widgets');
function s4o_register_widgets() {
    register_widget('lectronic_stories_widget');
    register_widget('magazine_contents_widget');
    register_widget('magazine_archive_widget');
}



