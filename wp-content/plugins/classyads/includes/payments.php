<?php

require_once CLASSYADS_PATH . 'vendor/autoload.php';
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
define("AUTHORIZENET_LOG_FILE", CLASSYADS_PATH . "logs/anet_log");

function chargeCreditCard($amount, $data) {
    // Common setup for API credentials
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName("2W3Cph4NwPW"); // Sandbox
    $merchantAuthentication->setTransactionKey("9tK37xm47nEW8FHU"); // Sandbox
    $refId = 'ref' . time();

    // Create the payment data for a credit card
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber($data['card_number'] );
    $creditCard->setExpirationDate( $data['card_year'] . '-' . $data['card_month']);
    $creditCard->setCardCode($data['cvv2']);

    // Add the payment data to a paymentType object
    $paymentOne = new AnetAPI\PaymentType();
    $paymentOne->setCreditCard($creditCard);

    // Create order information
    $order = new AnetAPI\OrderType();
    $order->setInvoiceNumber("123"); // TODO!!! This should be the Transaction ID
    $order->setDescription("Classy Ad"); // TODO!!! Classy Ad: 18932 until << expiration date >>

    // Set the customer's Bill To address
    $cardholder_name = explode(' ', $data['cardholder']);
    $first_name = $cardholder_name[0];
    $last_name = end($cardholder_name);
    reset($cardholder_name); // Just in case.

    $customerAddress = new AnetAPI\CustomerAddressType();
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
    $customerData->setId("1000"); // TODO!!!! Customer ID in the database.
    $customerData->setEmail("EllenJohnson@example.com"); // Customer email.

    // Create a TransactionRequestType object and add the previous objects to it
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("authCaptureTransaction");
    $transactionRequestType->setAmount($amount); // Should be standard decimal notation.
    $transactionRequestType->setPayment($paymentOne);
    $transactionRequestType->setOrder($order);
    $transactionRequestType->setBillTo($customerAddress);
    $transactionRequestType->setCustomer($customerData);

    // Assemble the complete transaction request
    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId( $refId);
    $request->setTransactionRequest($transactionRequestType);

    // Create the controller and get the response
    $controller = new AnetController\CreateTransactionController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

    $output = "";
    if ($response != null) {
        // Check to see if the API request was successfully received and acted upon
        if ($response->getMessages()->getResultCode() == "Ok") {
            // Since the API request was successful, look for a transaction response
            // and parse it to display the results of authorizing the card

            $tresponse = $response->getTransactionResponse();


            if ($tresponse != null && $tresponse->getMessages() != null) {
                $output .= " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
                $output .= " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
                $output .= " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
                $output .= " Auth Code: " . $tresponse->getAuthCode() . "\n";
                $output .= " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";
            } else {
                $output .= "Transaction Failed \n";
                if ($tresponse->getErrors() != null) {
                    $output .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                    $output .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                }
            }
            // Or, print errors if the API request wasn't successful
        } else {
            echo "Transaction Failed \n";
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
    PC::debug($response);
    PC::debug($output);
    return $response;
}