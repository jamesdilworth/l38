<?php

class magazine_contents_widget extends WP_Widget {
    function __construct() {
        // process
        $widget_ops = array(
            'classname' => 'single-issue',
            'description' => 'Adds the magazine contents & cover to the page',
        );
        parent::__construct('magazine_contents_widget','L38 : Magazine Contents', $widget_ops);
    }

    public function form($instance) {
        // widget form in dashboard
        $defaults = array(
            'title' => 'Magazine Contents',
            'issue ' => '0',
            'options' => array('cover','contents')
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = $instance['title'];
        // $issue = $instance['issue'];
        $options = $instance['options'];

        $args = array(
            'post_type'   => 'magazine',
            'orderby'     => 'date'
        );
        ?>
        <p>Title: <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        <?php
            /*
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'issue' ) ); ?>"><?php _e( 'Issue:' ); ?></label>
                <?php wp_dropdown_posts($args); ?>
            </p>
            */
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'options' )); ?>"><?php _e( 'Options:' ); ?></label>
            <ul class="nodots">
                <?php
                $valid_options = array('cover', 'contents', 'features');
                $fid = $this->get_field_id('options[]') ;
                $fn = $this->get_field_name('options[]') ;
                foreach($valid_options as $opt) {
                    $checked = in_array($opt, $options) ? 'checked' : '' ;
                    echo "<li><input type='checkbox' id='$fid' name='$fn' value='$opt' $checked>$opt</li>";
                }
                ?>
            </ul>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        // save widget options
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        // $instance['issue'] = $new_instance['issue'];
        $instance['options'] = $new_instance['options'];
        return $instance;
    }

    public function widget($args, $instance) {
        // display
        global $wp_query, $post;
        extract($args);

        $title = (empty($instance['title'])) ? "" : apply_filters('widget_title', $instance['title']);
        $type = (empty($instance['issue'])) ? "" : $instance['issue'];
        $options = (empty($instance['options'])) ? array('cover','contents') : $instance['options'];

        $pre_header = "";
        $header_classes = implode('-', $options);

        if($post->post_type != "magazine") {
            // It's not a magazine page.... so find the most recent.
            $args = array(
                'post_type' => 'magazine',
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'orderby' => "date",
                'order' => 'DESC'
            );
            $magazine = new WP_Query($args);
            $pre_header = "Latest Issue: ";
        } else {
            // Yes, this is a single magazine. Reform the query, based on the page ID.
            $args = array(
                'post_type' => 'magazine',
                'page_id' => $post->ID,
            );
            $magazine = new WP_Query($args);
        }

        echo $before_widget;
        $output = "";

        if ( $magazine->have_posts() ) {
            echo "<div class='type-$header_classes'>";
            while ($magazine->have_posts()) {
                $magazine->the_post();

                $core_url = get_field('magazine_url');
                $features = array();

                if($pre_header != "") echo '<div class="section-heading"><span class="title" style="width:250px;">'. $pre_header . get_the_title() . '</span></div>';

                if(in_array('cover', $options)) {
                    $inside_link = get_pdf_link($core_url,1);
                    if(is_single() || is_page('magazine'))
                        $link = $inside_link;
                    else
                        $link = 'href="' . get_the_permalink() . '"';

                    echo '<div class="cover"><a ' . $link . '>';

                    ?>
                    <div id="fpc_effect-back">
                        <div id="fpc_box">
                            <div id="fpc_content">
                                <?php the_post_thumbnail( 'medium', array( 'itemprop' => 'image',) ); ?>
                            </div>
                            <div id="fpc_corner-box">
                                <a id="fpc_page-tip" <?=$inside_link ?> >
                                    <div id="fpc_corner-contents"><div id="fpc_corner-button"><strong>Read Inside&nbsp;&raquo;</strong></div></div></a>
                            </div>
                        </div>
                    </div>

                    <?php
                    echo '</a>';

                    echo '<div class="download-links">';
                    $pdf = get_field('upload_pdf');
                    if($pdf) echo '<a class="wide btn" href="' . get_field('upload_pdf') . '" target="_blank"><i class="fa fa-download"></i> &nbsp;Download Magazine (PDF)</a>';
                    echo '<a href="/distribution/">Find a Magazine Distributor!</a>';
                    echo '    </div>';
                    echo '</div>';

                }

                // Build Features Content
                if( have_rows('features') ):
                    // loop through the rows of data
                    while ( have_rows('features') ) : the_row();
                        // vars
                        $feature_image = get_sub_field('feature_image');
                        $feature_name = get_sub_field('feature_title');
                        $feature_desc = get_sub_field('feature_description');
                        $page = get_sub_field('page');

                        $feature_output = '<div class="section feature">';
                        $feature_output .= '<a ' . get_pdf_link($core_url, $page) .'><h3 class="title"><span class="page">'. $page . '</span>' . $feature_name . '</h3></a>';
                        $feature_output .= '<div class="feature-image" style="background-image:url(' . $feature_image['url'] . ')"></div>';
                        $feature_output .= '<div class="desc">' . $feature_desc . '</div>';
                        $feature_output .= '</div>';

                        $feature_only_output = '<div class="section feature">';
                        $feature_only_output .= '   <a ' . get_pdf_link($core_url, $page) .'>';
                        $feature_only_output .= '   <div class="feature-image" style="background-image:url(' . $feature_image['url'] . ')">';
                        $feature_only_output .= '        <h3 class="title">' . $feature_name . '</h3></a>';
                        $feature_only_output .= '        <span class="page">'. $page . '</span>';
                        $feature_only_output .= '        <div class="desc">' . $feature_desc . '</div>';
                        $feature_only_output .= '    </div>';
                        $feature_only_output .= '</div>';

                        $features[] = $feature_output;
                        $features_only[] = $feature_only_output;
                    endwhile;
                else :
                    // no rows found
                endif;

                if(in_array('features', $options)) :
                    echo "<div class='magazine-features'>";
                    foreach($features_only as $feature) {
                        echo $feature;
                    }
                    echo "</div>";
                endif;

                if(in_array('contents',$options)) :

                    echo "<div class='magazine-contents'>";
                    if( have_rows('section') ):
                        // vars
                        // loop through the rows of data
                        while ( have_rows('section') ) : the_row();
                            // vars
                            $section_image = get_sub_field('section_image');
                            $section_name = get_sub_field('section_name');
                            $caption = "";
                            $page = get_sub_field('page');
                            $also = get_sub_field('also');
                            $parts = get_sub_field('parts');

                            $parts_output = "";

                            if($parts) {
                                foreach($parts as $part) {
                                    if(!$part['is_section_image']) {
                                        $parts_output .= '<a ' . get_pdf_link($core_url, $part['page']) .'><li><span class="page">' . $part['page'] . '</span>' . $part['part_title'] . '</li></a>';
                                    } else {
                                        $parts_output .= '<a' . get_pdf_link($core_url, $part['page']) .'><li class="is-section-image"><span class="page">' . $part['page'] . '</span>' . $part['part_title'] . '</li></a>';
                                        $caption = '<div class="subtitle">' . $part['part_title'] . '</div>';
                                        $caption .= '<div class="subtitle-page">' . $part['page'] . '</div>';
                                    }
                                }
                            }

                            ?>
                            <div class='section'>
                                <a <?php echo get_pdf_link($core_url, $page) ?> ><h3 class="title"><span class="page"><?=$page; ?></span><?=$section_name; ?> </h3></a>
                                <?php if(!empty($section_image)) : ?>
                                    <div class="section-image" style="background-image:url(<?=$section_image['url']; ?>);"><?=$caption ?></div>
                                <?php endif; ?>
                                <?php if(!empty($parts_output)) : ?>
                                    <ul class="parts">
                                        <?=$parts_output; ?>
                                    </ul>
                                <?php endif; ?>
                                <?php // if(!empty($also)) echo '<div class="also">' . $also . '</div>'; ?>
                            </div><!-- // End Section... only if there was an image -->

                            <?php

                            // Place features after Sightings
                            if($section_name == 'Sightings' ) {
                                foreach($features as $feature) {
                                    echo $feature;
                                }
                            }

                        endwhile;
                    else :
                        echo "<p>Sadly, contents are not yet available for this issue.</p>";
                        if (current_user_can('edit_posts')) echo '<div class="edit_link"><a href="' . get_edit_post_link() . '">Edit Magazine Contents</a></div>';
                    endif;
                    echo '</div>'; // End Contents
                endif;
                echo '</div>'; // End Header Classes
            }
        } else {
            $output = "No Magazine Found?";
        }
        echo $output;
        wp_reset_postdata();

        echo $after_widget;
    }
}
