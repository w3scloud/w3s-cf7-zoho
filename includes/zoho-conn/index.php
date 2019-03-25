<?php

/**
 * main Zoho operation file
 */

 // file includes
include_once 'includes.php';





$zcrmModuleIns = ZCRMModule::getInstance("Leads");
$bulkAPIResponse=$zcrmModuleIns->getRecords();
$recordsArray = $bulkAPIResponse->getData(); // $recordsArray - array of ZCRMRecord instances


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