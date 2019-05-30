<?php
/**
 * All include file goes here
 */

include_once 'vendor/autoload.php';
$conf = include_once 'config.php';
include_once 'healper.php';



//Inatiating  Zoho crm rest client
ZCRMRestClient::initialize($conf);
