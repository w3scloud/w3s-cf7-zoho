<?php

require "vendor/autoload.php";
$conf =  require_once "config.php";

// ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,aaaserver.profile.READ


// Assign the email id access
$_SERVER['user_email_id'] = $conf['currentUserEmail'];

//Generating access tokens
try {
    ZCRMRestClient::initialize($conf);
    $oAuthClient = ZohoOAuth::getClientInstance();
    $grantToken = '1000.12f392b5bb3f428e767a67ee46fcf2a2.dab32cc64ec76bda039a75a63436b0cf';
    $oAuthTokens = $oAuthClient->generateAccessToken($grantToken);
    echo 'Token generated and app authorised successfully.';

} catch (Exception $e) {
    echo "Grant token did not generated:\n" . $e ;
}