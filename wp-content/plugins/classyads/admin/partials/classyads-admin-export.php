<div class="wrap">
    <style>
        .unselectable {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -o-user-select:none;
            user-select: none;
        }
        .selectable {
            -webkit-touch-callout: all;
            -webkit-user-select: all;
            -khtml-user-select: all;
            -moz-user-select: all;
            -ms-user-select: all;
            -o-user-select:all;
            user-select: all;
        }

        .section_title  { color:#fff; background-color:#018db9; text-transform:uppercase; font-weight:bold; font-size:130%; }
        .export_table td { padding: 2px 5px; }
    </style>

    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p>You should be able to copy paste the text below into InDesign for the copy.</p>

    <?php $this->output_classyads(); ?>

</div>