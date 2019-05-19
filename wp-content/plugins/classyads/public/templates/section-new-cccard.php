<?php if(current_user_can('edit_posts')) : ?>
    <div class="field">
        <label for="cim_payment_profile_id">Admin Override?</label>
        <input type="checkbox" name="cim_payment_profile_id" id="card_admin_override" value="admin_override"> Check this to bypass credit card processing.
    </div>
<?php endif; ?>

<div class="fieldset" id="create_add_payment_fields">

    <div class="field">
        <label for="card_number">Credit Card Number</label>
        <input type="text" name="card_number">
    </div>

    <div class="field one-third">
        <label for="expires">Expires</label>
        <div class="inputgroup">
            <select name="card_month" id="" required>
                <option value="">Month</option><option value="1">01</option><option value="2">02</option><option value="3">03</option><option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option><option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option>
            </select>
            <select name="card_year" id="year" required>
                <option value="">Year</option><option value="2019">2019</option><option value="2020">2020</option><option value="2021">2021</option><option value="2022">2022</option><option value="2023">2023</option><option value="2024">2024</option><option value="2025">2025</option><option value="2026">2026</option><option value="2027">2027</option><option value="2028">2028</option><option value="2029">2029</option><option value="2030">2030</option><option value="2031">2031</option><option value="2032">2032</option><option value="2033">2033</option><option value="2034">2034</option><option value="2035">2035</option><option value="2036">2036</option><option value="2037">2037</option><option value="2038">2038</option>
            </select>
        </div>
    </div>
    <div class="field one-third">
        <label for="cvv2">Security Code</label>
        <input type="text" name="cvv2" value="" required autocomplete="off" pattern="[0-9]*" title="Only digits are allowed" placeholder="CVV">
    </div>
    <div class="field">
        <label for="cardholder">Cardholder Name</label>
        <input type="text" name="cardholder" required>
    </div>
    <div class="fieldset">
        <legend>Cardholder Billing Address (Needed for Payment)</legend>
        <div class="field">
            <label for="address1">Address 1</label>
            <input type="text" name="address1" >
        </div>
        <div class="field">
            <label for="address2">Address 2</label>
            <input type="text" name="address2" >
        </div>
        <div class="one-third field">
            <label for="city">City</label>
            <input type="text" name="city" >
        </div>
        <div class="one-third field">
            <label for="state">State</label>
            <select name="state" id="state" ><option value="">State / Province</option><option value="Alabama">Alabama</option><option value="Alaska">Alaska</option><option value="Arizona">Arizona</option><option value="Arkansas">Arkansas</option><option value="California" selected="selected">California</option><option value="Colorado">Colorado</option><option value="Connecticut">Connecticut</option><option value="Delaware">Delaware</option><option value="District of Columbia">District of Columbia</option><option value="Florida">Florida</option><option value="Georgia">Georgia</option><option value="Hawaii">Hawaii</option><option value="Idaho">Idaho</option><option value="Illinois">Illinois</option><option value="Indiana">Indiana</option><option value="Iowa">Iowa</option><option value="Kansas">Kansas</option><option value="Kentucky">Kentucky</option><option value="Louisiana">Louisiana</option><option value="Maine">Maine</option><option value="Maryland">Maryland</option><option value="Massachusetts">Massachusetts</option><option value="Michigan">Michigan</option><option value="Minnesota">Minnesota</option><option value="Mississippi">Mississippi</option><option value="Missouri">Missouri</option><option value="Montana">Montana</option><option value="Nebraska">Nebraska</option><option value="Nevada">Nevada</option><option value="New Hampshire">New Hampshire</option><option value="New Jersey">New Jersey</option><option value="New Mexico">New Mexico</option><option value="New York">New York</option><option value="North Carolina">North Carolina</option><option value="North Dakota">North Dakota</option><option value="Ohio">Ohio</option><option value="Oklahoma">Oklahoma</option><option value="Oregon">Oregon</option><option value="Pennsylvania">Pennsylvania</option><option value="Rhode Island">Rhode Island</option><option value="South Carolina">South Carolina</option><option value="South Dakota">South Dakota</option><option value="Tennessee">Tennessee</option><option value="Texas">Texas</option><option value="Utah">Utah</option><option value="Vermont">Vermont</option><option value="Virginia">Virginia</option><option value="Washington">Washington</option><option value="West Virginia">West Virginia</option><option value="Wisconsin">Wisconsin</option><option value="Wyoming">Wyoming</option><option value="Armed Forces Americas">Armed Forces Americas</option><option value="Armed Forces Europe">Armed Forces Europe</option><option value="Armed Forces Pacific">Armed Forces Pacific</option></select>
        </div>
        <div class="one-third field">
            <label for="zip">Zip</label>
            <input type="text" name="zip" >
        </div>
    </div>
</div>