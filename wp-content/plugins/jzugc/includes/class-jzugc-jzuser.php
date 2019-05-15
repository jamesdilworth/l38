<?php

class JZ_User extends WP_User {
    // No constructor so WP_User's default constructor is used.

    function getGender() {
        if(!isset($this->data->gender)) {
            $this->data->gender = get_user_meta($this->ID, 'gender', true);
        }
        return $this->data->gender;
    }

    function getCimProfileID() {
        if(!isset($this->data->cim_profile_id)) {
            $this->data->cim_id = get_user_meta($this->ID, 'cim_profile_id', true);
        }
        return $this->data->cim_profile_id;
    }

    function getPaymentProfiles() {
        if(!isset($this->data->cim_payment_profiles)) {
            $this->data->cim_payment_profiles = get_user_meta($this->ID, 'cim_payment_profile');
        }
        return $this->data->cim_payment_profiles; // This should be an array of profiles.
    }

    function getCustomField($key) {
        // function getPostMeta($this->)
        // return $value
    }

    // Magic method to detect variables.
    function __get($key) {
        switch ($key) {
            case 'gender' :
                return $this->getGender();
            case 'cim_profile_id' :
                return $this->getCimProfileID();
            case 'cim_payment_profiles' :
                return $this->getPaymentProfiles();
            default :
                return parent::__get($key);
        }
    }

}