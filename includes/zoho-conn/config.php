<?php
    // Zoho CRM API Required Configuration
    $conf = array(
        'apiBaseUrl' => 'https://www.zohoapis.com',
        'client_id' => '1000.1GOGXG96V2J425575M9JZO4UXOESRH',
        'client_secret' => 'b58f94815963baf6de8db5f72e2cd96b219bc38838',
        'redirect_uri' => 'https://plugin.wp/wp-content/plugins/w3s-cf7-zoho/includes/zoho-conn/gen.php',
        'accounts_url' => 'https://accounts.zoho.com',
        'currentUserEmail' => 'shohag@w3scloud.com',
        'token_persistence_path' => dirname(__FILE__).'/authlog/',
        'applicationLogFilePath' => dirname(__FILE__).'/authlog/'
    );
    
    if ($conf['client_id'] == ""){
        return array();
    } else {
        return $conf;
    }