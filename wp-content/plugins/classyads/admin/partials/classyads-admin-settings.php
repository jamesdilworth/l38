<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.jamesdilworth.com
 * @since      0.1.0
 *
 * @package    Classyads
 * @subpackage Classyads/admin/partials
 */


?>

<div class="wrap">

    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <h3>Define Plans</h3>

    <!--
    <form method="post" name="classy_options" action="options.php" style="margin-top:20px;">

        <?php
            //Grab existing options
            $options = get_option($this->plugin_name);

            // show_on_home
            $show_on_home = $options['show_on_home'];
            $another_option = $options['another_option'];
            $another_text = $options['another_text'];

            settings_fields($this->plugin_name); // puts in the nonces and stuff
            do_settings_sections($this->plugin_name);
        ?>

        <fieldset>
            <legend class="screen-reader-text"><span>Show Favorite Classies on Home Page?</span></legend>
            <label for="<?php echo $this->plugin_name; ?>-show_on_home">
                <input type="checkbox" id="<?php echo $this->plugin_name; ?>-show_on_home" name="<?php echo $this->plugin_name; ?>[show_on_home]" value="1" <?php checked($show_on_home, 1); ?> />
                <span><?php esc_attr_e('Show on Home Page', $this->plugin_name); ?></span>
            </label>
        </fieldset>

        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Another Test Option', $this->plugin_name); ?></span></legend>
            <label for="<?php echo $this->plugin_name; ?>-another_option">
                <input type="checkbox"  id="<?php echo $this->plugin_name; ?>-another_option" name="<?php echo $this->plugin_name; ?>[another_option]" value="1" <?php checked($another_option,1); ?>/>
                <span><?php esc_attr_e('Another Test Option', $this->plugin_name); ?></span>
            </label>
            <fieldset>
                <p>Example Free-form URL field</p>
                <legend class="screen-reader-text"><span><?php _e('Enter some text', $this->plugin_name); ?></span></legend>
                <input type="email" class="regular-text" id="<?php echo $this->plugin_name; ?>-another_text" name="<?php echo $this->plugin_name; ?>[another_text]" value="<?php if(!empty($another_text)) echo $another_text; ?>"/>
            </fieldset>
        </fieldset>

        <?php submit_button('Save all changes', 'primary','submit', TRUE); ?>

    </form>
    -->

</div>