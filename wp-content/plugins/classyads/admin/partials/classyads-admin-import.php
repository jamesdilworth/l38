<?php require_once( ABSPATH . 'wp-admin/includes/file.php' ); ?>
<div class="wrap">
    <style>
        .success { background-color:#b9f3ac; }
        .notice  { background-color:#faf380; }
        .spin    { text-align:center; }
    </style>

    <h1>Active Classies</h1>
    <p>Use this page to import individual classified ads!</p>
    <table border="1" cellpadding="5">
        <?php
        // Go through all the active classies
        $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, LASSO_DB);
        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }

        $sql = "SELECT * FROM class_start WHERE adtype LIKE '%Boat%' AND (status = 'Live on Website' OR status = 'Free Ad Approved') ORDER BY id"; // Classy Query.
        if(!$result = $db->query($sql)){
            die('There was an error running the query [' . $db->error . ']');
        }

        $count = 1;
        while($row = $result->fetch_assoc()){
            echo "<tr><td>($count)<br><a href='https://www.latitude38.com/classifieds/admin789/go_edit_boat.lasso?key=" . $row['id'] . "' target='_blank'>" . $row['id'] ."</a></td>";

            // Look up the image
            if(!empty($row['pictureid01'])) {
                $image_sql = "SELECT * FROM class_pix WHERE id = " . $row['pictureid01'];

                if (!$image_result = $db->query($image_sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                }
                while ($image_row = $image_result->fetch_assoc()) {
                    $featured_image_url = 'http://www.latitude38.com/classifieds/uploads/img_classy_576/' . $image_row['this_file'];
                }

                $image_result->free();
                echo "<td><img src='$featured_image_url' width='200'></td>";

            } else {
                echo "<td>No Photo</td>";
            }


            echo "<td><b>" . $row['ad_header'] . "</b><br>";
            echo $row['ad_text'];

            // Magazine Specific
            $issue_month = $row['issue_monthnum'];
            if(strlen($row['issue_monthnum'] == 1)) $issue_month = '0' . $issue_month;
            $mag_show_to = $row['issue_year'] .  $issue_month . '01'; // Print up to this date.

            echo "<br>" . $row['boat_price'] . " / " . $row['category'] . "<br>Show To:" . $mag_show_to . "</td>";

            // Look up User Table
            if(!empty($row['customerid'])) {
                echo "<td><b>" . $row['customerid'] . "</b><br>";
                $user_sql = "SELECT * FROM lat_customers WHERE id = " . $row['customerid'];
                if (!$user_result = $db->query($user_sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                }
                while ($user_row = $user_result->fetch_assoc()) {
                    if($user_row['first']) {
                        echo "<b>" . $user_row['first'] . " " . $user_row['last'] . "</b><br>" . $user_row['email'];
                    } else if($user_row['bill_firstname']) {
                        echo "<b>" . $user_row['bill_firstname'] . " " . $user_row['bill_lastname'] . "</b><br>" . $row['email'];
                    }
                }
                $user_result->free();
                echo "</td>";
            } else {
                echo "<td><b>" . $row['cc_firstname'] . " " . $row['cc_lastname'] . "</b><br>" . $row['email'] . "</td>";
            }

            $wp_post_id = Classyads_Import::get_post_from_lasso_id($row['id']);
            if($wp_post_id) {
                $post = get_post($wp_post_id);
                echo "<td><a href='/classyads/$post->post_name' target='_blank'>$post->ID</a>";
            } else {
                echo "<td class='cell_actions'><input id='btn_" . $row['id'] . "' class='import_ad_btn' type='button' value='Import Ad' data-lassoid='" . $row['id'] . "'></td>";
            }
            echo "</tr>";
            $count++;
        }
        ?>
    </table>

    <script>
        jQuery(document).ready(function($) {
            var timer_addClassy;
            var ajaxurl = '/wp-admin/admin-ajax.php';

            $('.import_ad_btn').click(function() {
                var lassoid = $(this).data('lassoid');
                addClassy(lassoid);
            });

            function addClassy(lassoid) {

                if(!lassoid)
                    return;

                // Hide button and show spinner
                $('#btn_' + lassoid).hide().after('<div class="spin"><img src="/wp-admin/images/wpspin_light-2x.gif"></div>');

                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    timeout: 5000,
                    dataType: 'html',
                    data: "action=add_classy_from_lasso&lasso_id=" + lassoid,
                    error: function(xml) {
                        console.log('Error');
                        $('#btn_' + lassoid).show();
                    },
                    success: function(response) {
                        // Remove the button.
                        if(response != "") {
                            $('#btn_' + lassoid).after('<span class="success">' + response + '</a>').remove();
                            $('.spin').remove();
                        } else {
                            console.log('Returned 0?')
                        }
                    }
                })
            }
        });
    </script>

</div>