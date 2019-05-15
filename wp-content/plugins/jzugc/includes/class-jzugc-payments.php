<?php
/**
 * General purpose billing library to connect with Authorize.net.
 *
 *
 * For PRODUCTION use:
 * $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
 */

require_once JZUGC_PATH . 'vendor/autoload.php';
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

$uploads = wp_get_upload_dir();
define("AUTHORIZENET_LOG_FILE", $uploads['basedir'] . "/logs/anet_log");

class Jzugc_Payment
{
    public $response;
    public $errors;

    private $order_id;
    private $user;

    public function __construct(&$user = null) {
        $this->user = $user; // This should be our JZ_User object.

        /* Create a log file for payments */
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . '/logs';
        if (! is_dir($upload_dir)) {
            mkdir( $upload_dir, 0700 );
        }

    }

    /**
     * Function to make sure we have a correct set of fields ready to send for processing.
     *
     * returns true if everything ok, field errors if not.
     * @param $data... array of user-submitted fields with values.
     * @return true/false
     *
     */
    public function validateFields($data) {

        $success = true;

        if(isset($data['card_number'])) {
            if(!$this->check_cc($data['card_number'])) {
                $this->errors['card_number'] = 'Card not valid. Please Check again';
                $success = false;
            }
        } else {
            $this->errors['card_number'] = 'Please enter a credit card number';
            $success = false;
        }

        if(!isset($data['cardholder'])) {
            $this->errors['cardholder'] = 'Please enter a cardholder name';
            $success = false;
        }

        if(!isset($data['cvv2'])) {
            $this->errors['cvv2'] = 'Please enter the security code';
            $success = false;
        } else {
            // TODO check that it's 3 or 4 numbers.
        }

        if(!isset($data['address1'])) {
            $this->errors['address1'] = 'Please enter an address';
            $success = false;
        }

        /*
        if(!isset($data['zip'])) {
            $this->errors['zip'] = 'Please enter a zip code. ';
            $success = false;
        }
        */

        return $success;
    }

    /**
     * Charge Credit Card is a one-stop transaction request taking the whole order info and processing it.
     * It will try and create a whole new cim_profile.
     * If there's a duplicate, it will fail.
     *
     */
    public function chargeCreditCard($amount, $data, &$user = null) {
        global $wpdb;

        if($user == null)
            $user = $this->user;
        if($user == null)
            $user = new JZ_User($data['post_author']); // Important... it needs to be the users credit card, not the administrators - lol!

        // Check if current user has a cim?
        $order_info = array(
            'user_id' => $user->ID,
            'post_id' => $data['post_id'],
            'gateway' => 'pending',
            'transaction_id' => '0',
            'amount' => $amount,
            'transaction_msg' => 'pending...',
            'description' => $data['payment_description'],
            'created' => date("Y-m-d H:i:s")
        );
        $order_id = $this->initializeOrder($order_info);

        if(!$order_id) {
            // Wasn't able to initialize the order?
            return false;
        }

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(JzugcConfig::MERCHANT_ID);
        $merchantAuthentication->setTransactionKey(JzugcConfig::MERCHANT_KEY);

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber(str_replace(" ", "", $data['card_number']) );
        $creditCard->setExpirationDate( $data['card_year'] . '-' . zeroise($data['card_month'],2));
        $creditCard->setCardCode($data['cvv2']);

        // Add the payment data to a paymentType object
        $paymentCard = new AnetAPI\PaymentType();
        $paymentCard->setCreditCard($creditCard);

        // Create order information
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($order_id);
        $order->setDescription($data['payment_description']);

        // Set the customer's Bill To address
        $customerAddress = new AnetAPI\CustomerAddressType();
        $cardholder_name = explode(' ', $data['cardholder']);
        $first_name = $cardholder_name[0];
        $last_name = end($cardholder_name);
        reset($cardholder_name); // Compensates for the above line, just in case we were to use it again.
        $customerAddress->setFirstName($first_name);
        $customerAddress->setLastName($last_name);
        $customerAddress->setAddress($data['address1']);
        $customerAddress->setCity($data['city']);
        $customerAddress->setState($data['state']);
        $customerAddress->setZip($data['zip']);
        if(isset($data['country'])) $customerAddress->setCountry($data['country']);

        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($user->ID);
        $customerData->setEmail($user->user_email); // Customer email.

        $profile = new AnetAPI\CustomerProfilePaymentType();
        $profile->setCreateProfile(true); // This will create a new customer profile.

        // Create a TransactionRequestType object and add the previous objects to it
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount); // Should be standard decimal notation.
        $transactionRequestType->setPayment($paymentCard);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->setProfile($profile);

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId( $refId);
        $request->setTransactionRequest($transactionRequestType);

        // Create the controller and get the response
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

        $output = "";
        $success = false;
        if ($response != null) {
            // Check to see if the API request was successfully received and acted upon

            if ($response->getMessages()->getResultCode() == "Ok") {
                // Since the API request was successful, look for a transaction response and parse it to display the results of authorizing the card

                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    $output .= " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
                    $output .= " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
                    $output .= " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
                    $output .= " Auth Code: " . $tresponse->getAuthCode() . "\n";
                    $output .= " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";

                    $tx_data = array(
                        'gateway' => 'auth-net',
                        'transaction_id' => $tresponse->getTransId(),
                        'transaction_msg' => $tresponse->getMessages()[0]->getCode(),
                        'created' => current_time( 'mysql' )
                    );

                    $presponse = $response->getProfileResponse();
                    if ($presponse != null && $presponse->getMessages() != null && $presponse->getMessages()->getResultCode() != "Error") {

                        $tx_data['cim_profile_id'] = $presponse->getCustomerProfileId();
                        $payment_profiles = $presponse->getCustomerPaymentProfileIdList(); // should be an array of ID's, but since there's only one payment profile, we'll want the first.

                        $tx_data['cim_payment_profile_id'] = $payment_profiles[0];
                        $tx_data['cim_payment_profile'] = array(
                            'id' => $tx_data['cim_payment_profile_id'],
                            'last4' => substr($data['card_number'], -4),
                            'expires' => zeroise($data['card_month'],2) . "/" . substr($data['card_year'],-2),
                            'default' => true // Since this is the first.
                        );

                        $output .= " Successfully created customer profile with ID: " . $tx_data['cim_profile_id'] . "\n";
                        $output .= " Payment profile ID should be  " . $tx_data['cim_payment_profile_id'] . "\n";

                    } else {
                        // TODO! - This might happen because a profile already exists!... in which case we might want to see if we can find it!
                        $output .= "Couldn\'t create a profile. \n";
                        if ($presponse->getMessages() != null) {
                            $output .= " Error Code  : " . $presponse->getMessages()->getMessage()[0]->getCode() . "\n";
                            $output .= " Error Message : " . $presponse->getMessages()->getMessage()[0]->getText() . "\n";
                        }
                    }

                    $this->concludeOrder($tx_data);

                    // IF SUCCESS, RETURN TRUE FROM THIS FUNCTION!
                    return true;

                } else {
                    // Print errors if the API request wasn't successful
                    $output .= "Transaction Failed \n";
                    if ($tresponse->getErrors() != null) {
                        $output .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                        $output .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                    }
                }
            } else {
                $output .= "Transaction Failed \n";
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getErrors() != null) {
                    $output .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                    $output .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                } else {
                    $output .= " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
                    $output .= " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
                }
            }
        } else {
            $output .=  "No response returned \n";
        }

        // We're only still here if it was a failure.
        $tx_data = array(
            'gateway' => 'auth-net',
            'transaction_msg' => $output,
            'created' => date("Y-m-d H:i:s")
        );
        $this->response = $output;
        $this->concludeOrder($tx_data);
        return $success;
    }

    function createCustomerPaymentProfile($cim_id, $data)
    {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(\JzugcConfig::MERCHANT_LOGIN_ID);
        $merchantAuthentication->setTransactionKey(\JzugcConfig::MERCHANT_TRANSACTION_KEY);

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Create a Customer Profile Request
        //  1. (Optionally) create a Payment Profile
        //  2. (Optionally) create a Shipping Profile
        //  3. Create a Customer Profile (or specify an existing profile)
        //  4. Submit a CreateCustomerProfile Request
        //  5. Validate Profile ID returned

        // Set credit card information for payment profile
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber("4242424242424242");
        $creditCard->setExpirationDate("2038-12");
        $creditCard->setCardCode("142");
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        $billto = new AnetAPI\CustomerAddressType();
        $billto->setFirstName("Ellen".$phoneNumber);
        $billto->setLastName("Johnson");
        $billto->setCompany("Souveniropolis");
        $billto->setAddress("14 Main Street");
        $billto->setCity("Pecan Springs");
        $billto->setState("TX");
        $billto->setZip("44628");
        $billto->setCountry("USA");
        $billto->setPhoneNumber($phoneNumber);
        $billto->setfaxNumber("999-999-9999");

        // Create a new Customer Payment Profile object
        $paymentprofile = new AnetAPI\CustomerPaymentProfileType();
        $paymentprofile->setCustomerType('individual');
        $paymentprofile->setBillTo($billto);
        $paymentprofile->setPayment($paymentCreditCard);
        $paymentprofile->setDefaultPaymentProfile(true);

        $paymentprofiles[] = $paymentprofile;

        // Assemble the complete transaction request
        $paymentprofilerequest = new AnetAPI\CreateCustomerPaymentProfileRequest();
        $paymentprofilerequest->setMerchantAuthentication($merchantAuthentication);

        // Add an existing profile id to the request
        $paymentprofilerequest->setCustomerProfileId($cim_id);
        $paymentprofilerequest->setPaymentProfile($paymentprofile);
        $paymentprofilerequest->setValidationMode("liveMode"); // Use 'testMode' to perform a Luhn mod-10 check on the card number, without further validation. Use liveMode to submit a zero-dollar or one-cent transaction (depending on card type and processor support) to confirm the card number belongs to an active credit or debit account.

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerPaymentProfileController($paymentprofilerequest);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            echo "Create Customer Payment Profile SUCCESS: " . $response->getCustomerPaymentProfileId() . "\n";
        } else {
            echo "Create Customer Payment Profile: ERROR Invalid response\n";
            $errorMessages = $response->getMessages()->getMessage();
            echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";

        }
        return $response;
    }



    function getCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId) {

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(\JzugcConfig::MERCHANT_LOGIN_ID);
        $merchantAuthentication->setTransactionKey(\JzugcConfig::MERCHANT_TRANSACTION_KEY);

        // Set the transaction's refId
        $refId = 'ref' . time();

        //request requires customerProfileId and customerPaymentProfileId
        $request = new AnetAPI\GetCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId( $refId);
        $request->setCustomerProfileId($customerProfileId);
        $request->setCustomerPaymentProfileId($customerPaymentProfileId);

        $controller = new AnetController\GetCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
        $jz_payment_profile = array();
        if(($response != null)){
            if ($response->getMessages()->getResultCode() == "Ok")
            {
                $jz_payment_profile['cim_payment_profile_id'] = $response->getPaymentProfile()->getCustomerPaymentProfileId();
                $jz_payment_profile['card_type'] = $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardType();
                $jz_payment_profile['last4'] = $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardNumber();
           }
            else
            {
                PC::debug("GetCustomerPaymentProfile ERROR :  Invalid response\n");
                $errorMessages = $response->getMessages()->getMessage();
                PC::debug("Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n");
            }
        }
        else{
            return null;
        }
        return $jz_payment_profile;
    }

    function chargeCustomerProfile($profileid, $paymentprofileid, $amount)
    {
        /* Create a merchantAuthenticationType object with authentication details
           retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(\JzugcConfig::MERCHANT_LOGIN_ID);
        $merchantAuthentication->setTransactionKey(\JzugcConfig::MERCHANT_TRANSACTION_KEY);

        // Set the transaction's refId
        $refId = 'ref' . time();

        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($profileid);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($paymentprofileid);
        $profileToCharge->setPaymentProfile($paymentProfile);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType( "authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setProfile($profileToCharge);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId( $refId);
        $request->setTransactionRequest( $transactionRequestType);

        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
        if ($response != null)
        {
            if($response->getMessages()->getResultCode() == "Ok")
            {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null)
                {
                    echo " Transaction Response code : " . $tresponse->getResponseCode() . "\n";
                    echo  "Charge Customer Profile APPROVED  :" . "\n";
                    echo " Charge Customer Profile AUTH CODE : " . $tresponse->getAuthCode() . "\n";
                    echo " Charge Customer Profile TRANS ID  : " . $tresponse->getTransId() . "\n";
                    echo " Code : " . $tresponse->getMessages()[0]->getCode() . "\n";
                    echo " Description : " . $tresponse->getMessages()[0]->getDescription() . "\n";
                }
                else
                {
                    echo "Transaction Failed \n";
                    if($tresponse->getErrors() != null)
                    {
                        echo " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                        echo " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                    }
                }
            }
            else
            {
                echo "Transaction Failed \n";
                $tresponse = $response->getTransactionResponse();
                if($tresponse != null && $tresponse->getErrors() != null)
                {
                    echo " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                    echo " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                }
                else
                {
                    echo " Error code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
                    echo " Error message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
                }
            }
        }
        else
        {
            echo  "No response returned \n";
        }
        return $response;
    }

    private function initializeOrder($order_data) {
        global $wpdb;

        $wpdb->insert( $wpdb->prefix . 'l38_transactions', $order_data );
        $this->order_id = $wpdb->insert_id;
        return $this->order_id;
    }

    private function concludeOrder($tx_data) {
        global $wpdb;

        if(isset($tx_data['cim_payment_profile'])) {
            $cim_payment_profile = $tx_data['cim_payment_profile'];
            unset($tx_data['cim_payment_profile']); // We don't want this going into the DB.
        }

        // Create a log in our transactions table.
        // TODO! - This is fragile and will break if $tx_data changes. Clean $tx_data so nothing else is going in there! - array_intersect_key()
        $wpdb->update( $wpdb->prefix . 'l38_transactions',
            $tx_data,
            array( 'id' => $this->order_id ),
            null,
            array( '%d' )
        );

        if(key_exists('cim_profile_id', $tx_data) && $this->user) {
            add_user_meta($this->user->ID, 'cim_profile_id', $tx_data['cim_profile_id']);
            add_user_meta($this->user->ID, 'cim_payment_profile', $cim_payment_profile);
        }
    }

    private function check_cc($cc, $extra_check = false){
        // From : http://web.archive.org/web/20080918014358/http://www.roughguidetophp.com/10-regular-expressions-you-just-cant-live-without-in-php/
        $cards = array(
            "visa" => "(4\d{12}(?:\d{3})?)",
            "amex" => "(3[47]\d{13})",
            "jcb" => "(35[2-8][89]\d\d\d{10})",
            "maestro" => "((?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?)",
            "solo" => "((?:6334|6767)\d{12}(?:\d\d)?\d?)",
            "mastercard" => "(5[1-5]\d{14})|(2[2-7]\d{14}) ",
            "switch" => "(?:(?:(?:4903|4905|4911|4936|6333|6759)\d{12})|(?:(?:564182|633110)\d{10})(\d\d)?\d?)",
        );
        $names = array("Visa", "American Express", "JCB", "Maestro", "Solo", "Mastercard", "Switch");
        $matches = array();
        $pattern = "#^(?:".implode("|", $cards).")$#";
        $result = preg_match($pattern, str_replace(" ", "", $cc), $matches);
        if($extra_check && $result > 0){
            $result = ($this->validatecard($cc))?1:0;
        }
        return ($result>0)?$names[sizeof($matches)-2]:false;
    }

    /**
     * Additional checks to see if the checksum adds up. Luhn Check
     */
    private function validatecard($cardnumber) {
        $cardnumber=preg_replace("/\D|\s/", "", $cardnumber);  # strip any non-digits
        $cardlength=strlen($cardnumber);
        $parity=$cardlength % 2;
        $sum=0;
        for ($i=0; $i<$cardlength; $i++) {
            $digit=$cardnumber[$i];
            if ($i%2==$parity) $digit=$digit*2;
            if ($digit>9) $digit=$digit-9;
            $sum=$sum+$digit;
        }
        $valid=($sum%10==0);
        return $valid;
    }


}

