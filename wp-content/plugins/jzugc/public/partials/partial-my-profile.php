<!-- Profile Page -->
<div class="main-photo editable">
    <div class="main-photo-preview"><?= $user_img_tag; ?></div>

    <div class="main-image-fields">
        <form id="main-photo-form" action="handler.php" method="POST">
            <label for="main_photo_input" class="">Change Main Photo (Max 2MB)</label>
            <input type="file" id="main_photo_input" name="main_photo_input" accept="image/*" />
            <input type="hidden" id="ajax_action" name="ajax_action" value="update_profile_mainphoto">
            <?php wp_nonce_field( 'update-mainphoto', '_mainphoto_nonce' ) ?>
        </form>
    </div>
</div>
<div class="main-content">
    <div class="name"><?= $current_user->display_name; ?></div>
    <div class="location"><?= $current_user->user_location; ?></div>
    <div class="desc"><?= $current_user->description; ?></div>
</div>
