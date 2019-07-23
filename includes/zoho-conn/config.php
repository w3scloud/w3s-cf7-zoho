<?php
    // Zoho CRM API Required Configuration
    $conf = array(
        'apiBaseUrl' => 'www.zohoapis.com',
        'client_id' => '1000.T1JX0Y4KNUEW96879SLIRFSVN64H0H',
        'client_secret' => 'f09c48c6528d11ffa67b3c01d275dc2ab7087e5cc1',
        'redirect_uri' => 'https%3A%2F%2Fplugin.wp%2Fwp-admin%2Fedit.php%3Fpost_type%3Dw3s_cf7%26page%3Dw3s-cf7-zoho',
        'accounts_url' => 'https://accounts.zoho.com',
        'currentUserEmail' => 'shohag@w3scloud.com',
        'token_persistence_path' => 'D:\xampp\htdocs\work\plugin.wp\wp-content\plugins\w3s-cf7-zoho/includes/zoho-conn/authlog/',
        'applicationLogFilePath' => 'D:\xampp\htdocs\work\plugin.wp\wp-content\plugins\w3s-cf7-zoho/includes/zoho-conn/authlog/',
        'access_type'=> 'offline',
        'apiVersion' => 'v2'
    );
    if($conf['client_id'] == ''){
        return array();
    } else {
        return $conf;
    }
    