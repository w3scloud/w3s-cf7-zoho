<?php
    // Zoho CRM API Required Configuration
    return $conf = array(
        'apiBaseUrl' => 'www.zohoapis.com',
        'client_id' => '1000.T1JX0Y4KNUEW96879SLIRFSVN64H0H',
        'client_secret' => 'f09c48c6528d11ffa67b3c01d275dc2ab7087e5cc1',
        'redirect_uri' => 'https://plugin.wp/wp-content/plugins/w3s-cf7-zoho/includes/zoho-conn/gen.php',
        'accounts_url' => 'https://accounts.zoho.com',
        'currentUserEmail' => 'shohag@w3scloud.com',
        'token_persistence_path' => dirname(__FILE__).'/authlog/',
        'applicationLogFilePath' => dirname(__FILE__).'/authlog/'
    );