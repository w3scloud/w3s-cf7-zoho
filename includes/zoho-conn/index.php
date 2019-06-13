<?php

/**
 * main Zoho operation file
 */

require_once realpath(dirname(__FILE__) ). '/vendor/autoload.php';
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\oauth\ZohoOAuthClient;

$conf =  include_once 'config.php';


ZCRMRestClient::initialize($conf);


$moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("Leads"); // To get module instance
$response = $moduleIns->getRecords(); // to get the field
$recordsArray = $response->getData();



 //dd($recordsArray[0]);

foreach ($recordsArray as $record) {
    echo $record->getentityId() . " ";
    echo $record->getFieldValue('Email') . " ";
    echo $record->getFieldValue('Company') . "<br><br>";
}


/* 
$lead =  ZCRMRecord::getInstance("Leads",'755874000008749014');

$lead = $lead->getRecords();

dd($lead); */