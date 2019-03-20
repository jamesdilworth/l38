<?php
/* Functions, Filters and Actions related to Classified Ads */

// Pre-render the Gravity forms.
add_filter( 'gform_pre_render_2', 'populate_adcats' );
add_filter( 'gform_pre_validation_2', 'populate_adcats' );
add_filter( 'gform_pre_submission_filter_2', 'populate_adcats' );
// add_filter( 'gform_admin_pre_render_2', 'populate_adcats' );
function populate_adcats( $form ) {
    /* I've set it up so that field (10) is the primary filter that defines the main ad type, then
     * Field (22) does the sub cats, which we'll filter with jQuery
     */

    $adcats = get_terms(array(
        'taxonomy' => 'adcat',
        'hide_empty' => false,
    ));

    // Loop through fields in form and get those attached to
    foreach( $form['fields'] as &$field )  {
        $primary_field = 41;
        $secondary_field = 22;

        // If it's neither of the fields, move on.
        if ( $field->id == $primary_field ) {
            $adcats =  get_terms( array(
                'taxonomy' => 'adcat',
                'hide_empty' => false,
            ));

            $input_id = 1;
            $choices = array();
            $inputs = array();

            foreach( $adcats as $term ) {

                //skipping index that are multiples of 10 (multiples of 10 create problems as the input IDs)
                if ( $input_id % 10 == 0 ) {
                    $input_id++;
                }

                if ($term->parent == 0) {
                    $choices[] = array( 'text' => $term->name, 'value' => $term->slug );
                    $inputs[] = array( 'label' => $term->name, 'id' => "{$primary_field}.{$input_id}" );
                }

                $input_id++;
            }

            $field->choices = $choices;
            $field->inputs = $inputs;

        }

        else if ( $field->id == $secondary_field ) {

            $input_id = 1;
            $choices = array();
            $inputs = array();

            foreach ($adcats as $term) {

                //skipping index that are multiples of 10 (multiples of 10 create problems as the input IDs)
                if ($input_id % 10 == 0) {
                    $input_id++;
                }

                if ($term->parent == 376) {
                    // It's a boat!
                    $choices[] = array('text' => $term->name, 'value' => $term->slug);
                    $inputs[] = array('label' => $term->name, 'id' => "{$secondary_field}.{$input_id}");
                }

                $input_id++;
            }

            $field->choices = $choices;
            $field->inputs = $inputs;
        }

    }

    return $form;
}

add_action( 'gform_pre_submission_2', 'new_classy_pre_submission_handler' );
function new_classy_pre_submission_handler( $form ) {

    if($_POST['input_10'] == '376') {
        // If it's a boat, set the title automatically from boat model.
        $boat_model =  esc_attr(rgpost( 'input_9' ));
        $boat_length = intval(preg_replace("/[^0-9\.]/", "", esc_attr(rgpost( 'input_11' ))));
        $boat_year = preg_replace("/[^0-9\.]/", "", esc_attr(rgpost( 'input_12' )));

        if($boat_length < 3 || $boat_length > 300) {
            $boat_length = 10; // Default 10'
            $_POST['input_11'] = $boat_length;
        }

        $this_year = intval(date ('Y'));
        if($boat_year < 1850 || $boat_year > ($this_year + 2)) {
            // Not a valid year, so set it to 1980?
            $boat_year = 1980;
            $_POST['input_12'] = $boat_year;
        }
        $_POST['input_1'] = "$boat_length' $boat_model, $boat_year";
    }

    // Clean the asking price.
    $ad_asking_price = esc_attr( $_POST['input_8'] ); // Probably want to do some cleaning here as it's a searchable field.
    $ad_asking_price = preg_replace("/[^0-9\.]/", "", $ad_asking_price);
    $_POST['input_8'] = $ad_asking_price;
}

add_action( 'gform_after_create_post_2', 'finish_classy_post', 10, 3 );
function finish_classy_post( $post_id, $entry, $form ) {

    // Set items that are not set automatically by the post.
    $sub_level = stristr(rgar( $entry, '23' ), "|", true);
    update_field('ad_subscription_level', $sub_level , $post_id);

}




