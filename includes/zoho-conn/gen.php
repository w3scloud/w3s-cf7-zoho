<?php

/**
 * This file generate user access tokens
 * just change $grantToken value
 * empty the file zcrm_oathtoken.txt
 * run only once in the beginning 
 * 
 * Generate grant token with scopes: 
 * ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,aaaserver.profile.READ
 */

use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\oauth\ZohoOAuthClient;
use zcrmsdk\oauth\ZohoOAuth;

if (isset($_GET['code'])){

# No need for the template engine
define( 'WP_USE_THEMES', false );
# Load WordPress Core
// Assuming we're in a subdir: "~/wp-content/plugins/current_dir"
    require_once dirname(__FILE__).'../../../../../../wp-load.php';

include_once 'vendor/autoload.php';
// include_once 'includes.php';

// get instance of w3s-cf7-zoho
    $titan = TitanFramework::getInstance('w3s-cf7-zoho');


    $apiBase = '';
    if ($_GET['location'] == 'us'){
        $apiBase = 'www.zohoapis.com';
    } elseif ($_GET['location'] == 'eu'){
        $apiBase = 'www.zohoapis.eu';
    } elseif ($_GET['location'] == 'cn'){
        $apiBase = 'www.zohoapis.com.cn';
    } elseif ($_GET['location'] == 'in'){
        $apiBase = 'www.zohoapis.in';
    } else {
        $apiBase = 'www.zohoapis.com';
    }


    $titan->setOption('zoho_api_base_url', $apiBase);
    $titan->setOption('zoho_account_url', $_GET['accounts-server']);


    $authLog = dirname(__FILE__).'/authlog/';

    $config = array(
        'apiBaseUrl' => $titan->getOption('zoho_api_base_url'),
        'client_id'=> $titan->getOption('zoho_client_id'),
        'client_secret'=> $titan->getOption('zoho_client_secret'),
        'redirect_uri'=> $titan->getOption('zoho_redirect_url'),
        'accounts_url'=> $titan->getOption('zoho_account_url'),
        'currentUserEmail' => $titan->getOption('zoho_user_email'),
        'token_persistence_path'=> $authLog,
        'applicationLogFilePath'=> $authLog.'ZCRMClientLibrary.log',
        'access_type'=> 'offline',
        'apiVersion' => 'v2'
    );


    $configContent = "<?php
    // Zoho CRM API Required Configuration
    \$conf = array(
        'apiBaseUrl' => '{$titan->getOption('zoho_api_base_url')}',
        'client_id' => '{$titan->getOption('zoho_client_id')}',
        'client_secret' => '{$titan->getOption('zoho_client_secret')}',
        'redirect_uri' => '{$titan->getOption('zoho_redirect_url')}',
        'accounts_url' => '{$titan->getOption('zoho_account_url')}',
        'currentUserEmail' => '{$titan->getOption('zoho_user_email')}',
        'token_persistence_path' => '{$authLog}',
        'applicationLogFilePath' => '{$authLog}ZCRMClientLibrary.log',
        'access_type'=> 'offline',
        'apiVersion' => 'v2'
    );
    if(\$conf['client_id'] == ''){
        return array();
    } else {
        return \$conf;
    }
    ";

    // dd($conf);



// Assign the email id access
    $_SERVER['user_email_id'] = $titan->getOption('zoho_user_email');
    $redirectToAdmin = admin_url( 'edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho');
    $redirectToAdminTab = admin_url( 'edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho&tab=integration');
    


//Generating access tokens
    try {
        ZCRMRestClient::initialize($config);
        $oAuthClient = ZohoOAuth::getClientInstance();
        $grantToken = $_GET['code'];
        $oAuthTokens = $oAuthClient->generateAccessToken($grantToken);
        echo 'Token generated and app authorised successfully.';

        $titan->setOption('zoho_authorised', true);
        //set admin notice
        // Notice to admin
        function w3s_cf7_zoho_admin_notice__success() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Zoho Authentication Done!', 'w3s-cf7-zoho' ); ?></p>
            </div>
            <?php
        }
        add_action( 'admin_notices', 'w3s_cf7_zoho_admin_notice__success' );


        //Write config file with correct credentials
        $fp = fopen('config.php', 'w');
        fwrite($fp, $configContent);
        fclose($fp);

// die();
        // redirect to admin page
        header("Location: $redirectToAdminTab");
        

    } catch (Exception $e) {
        
        function w3s_cf7_zoho_admin_notice__error() {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e( 'Zoho Authentication Failed!', 'w3s-cf7-zoho' ); ?></p>
            </div>
            <?php
        }
        add_action( 'admin_notices', 'w3s_cf7_zoho_admin_notice__error' );
//         header("Location: $redirectToAdmin");

        var_dump($e);
    }


/*
 * ?code=1000.dd19d82ba3f95ea0db5613c4302a3a26.acb17973f2c784e521befefcb5257eff&location=us&accounts-server=https%3A%2F%2Faccounts.zoho.com&
 */

}
