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
    $grantToken = '1000.86f63945fbfdb797bc70db29ee7314f0.9d23faaef6cfa2d61ba584ed3ad614a6';
    $oAuthTokens = $oAuthClient->generateAccessToken($grantToken);
    echo 'Token generated and app authorised successfully.';

} catch (Exception $e) {
    echo "Token did not generated:\n" . $e ;
}