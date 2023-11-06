<?php
class ZohoAuthInfos {

	private $infos = array(
		'data_center'        => '',
		'zoho_client_id'     => '',
		'zoho_client_secret' => '',
		'zoho_user_email'    => '',
		'zoho_redirect_url'  => '',
		'zoho_api_base_url'  => '',
		'zoho_account_url'   => '',
		'zoho_authorized'    => '',
		'refresh_token'      => '',
		'access_token'       => '',
		'expires_in'    	 => '',
		'time'               => '',

	);

	function __construct() {
		$this->setAll();
		$this->infos['time'] = time(); // this field no need. only for make unique
	}

	public function setInfo( $key, $val ) {
		if ( array_key_exists( $key, $this->infos ) ) {
			$this->infos[ $key ] = $val;
		}
		return $this;
	}

	public function getInfo( $key ) {
		return $this->infos[ $key ] ?? '';
	}

	public function storeInfo( $data = null ) {
		
		if ( isset( $data['store_zoho_info'] ) ) {
			$this->setInfo( 'data_center', sanitize_text_field( $data['data_center'] ) );
			$this->setInfo( 'zoho_client_id', sanitize_text_field( $data['zoho_client_id'] ) );
			$this->setInfo( 'zoho_client_secret', sanitize_text_field( $data['zoho_client_secret'] ) );
			$this->setInfo( 'zoho_user_email', sanitize_text_field( $data['zoho_user_email'] ) );
			$this->setInfo( 'zoho_redirect_url', sanitize_text_field(  admin_url('edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho') ) );
			$store = update_option( '_zoho_auth_infos', serialize( $this->infos ) );
			$this->message( $store );

		} else {
			$this->setAll();
			update_option( '_zoho_auth_infos', serialize( $this->infos ) );
		}
	}


	private function setAll() {
		$infos = get_option( '_zoho_auth_infos' );
		// $this->infos = array_merge($this->infos,unserialize($infos));
		if ( $infos ) {
			foreach ( unserialize( $infos ) as $k => $v ) {
				if ( ! $this->infos[ $k ] ) {
					$this->infos[ $k ] = $v;
				}
			}
		}
	}

	public function message( $true ) {
		$message = '';
		$class   = '';

		if ( $true ) {
			$message = 'Settings saved.';
			$class   = 'notice-success';
		} else {
			$message = 'Something Wrong';
			$class   = 'notice-error';
		}
		$notice  = '';
		$notice .= "<div class='notice is-dismissible $class'>";
		$notice .= "<p><strong>$message</strong></p>";
		$notice .= "<button type='button' class='notice-dismiss' onClick=\"this.closest('.notice').outerHTML='' \"></button>";
		$notice .= '</div>';

		add_action(
			'_message_',
			function() use ( $notice ) {
				echo $notice;
			}
		);

	}



	public function refreshToken($get)
    {
		if(isset($get['code'])){
			
			$postInput = [
				'client_id' => $this->infos['zoho_client_id'],
				'client_secret' => $this->infos['zoho_client_secret'],
				'code' => $get['code'],
				'redirect_uri' => $this->infos['zoho_redirect_url'],
				'grant_type' => 'authorization_code',
			];
			$url = $this->infos['zoho_account_url'].'/oauth/v2/token';
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postInput));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			$responseBody = json_decode($response, true);
			if (isset($responseBody['refresh_token']) && isset($responseBody['access_token'])) {
				$expires_in = time()+$responseBody['expires_in']-60;
				$this->setInfo( 'refresh_token', sanitize_text_field($responseBody['refresh_token']));
				$this->setInfo( 'access_token', sanitize_text_field($responseBody['access_token']));
				$this->setInfo( 'expires_in', sanitize_text_field($expires_in));
				$store = update_option( '_zoho_auth_infos', serialize( $this->infos ) );
				$this->message(true);

			} else {
				$this->message(false);
			}
		}
       
    }
	public function getAccess()
    {
	
		if(time() > $this->infos['expires_in']  ){

			$postInput = [
				'client_id' => $this->infos['zoho_client_id'],
				'client_secret' => $this->infos['zoho_client_secret'],
				'refresh_token' => $this->infos['refresh_token'],
				'grant_type' => 'refresh_token',
			];
			$url = $this->infos['zoho_account_url'].'/oauth/v2/token';
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postInput));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			$responseBody = json_decode($response, true);
			if (isset($responseBody['access_token'])) {
				$expires_in = time()+$responseBody['expires_in']-60;
				$this->setInfo( 'access_token', sanitize_text_field($responseBody['access_token']));
				$this->setInfo( 'expires_in', sanitize_text_field($expires_in));
				$store = update_option( '_zoho_auth_infos', serialize( $this->infos ) );

				return $this->infos;
			} 
		}
		return $this->infos;
       
    }
	public function zohoGet($module, $quary = [], $response = 'data')
    {
		$setting = $this->infos;
        $apiURL = $setting['zoho_api_base_url'].'/crm/v2/'.$module;

        $headers = [
            'Authorization' => 'Zoho-oauthtoken '.$setting['access_token'],
            'Content-Type' => 'application/json',
        ];
		$ch = curl_init($apiURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		var_dump($apiURL);
		var_dump($headers);
		if ($statusCode === 200) {
			$responseBody = json_decode($response, true);
			$result = isset($responseBody[$response]) ? $responseBody[$response] : null;
			return $result;
		}
		return null;

       
    }
	public function zohoFields($module)
    {

		$setting = $this->getAccess();
		$url = $setting['zoho_api_base_url'] . '/crm/v5/settings/fields?module='.$module;
        $headers = [
            'Authorization: Zoho-oauthtoken '.$setting['access_token'],
            'Content-Type: application/json',
        ];
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => $headers,
		));
		
		$response = curl_exec($curl);
		
		curl_close($curl);
		$responseBody = json_decode($response, true);
		return isset($responseBody['fields']) ? $responseBody['fields'] : [];

       
    }
	public function zohoUpsert($module, $fields)
    {

		$setting = $this->getAccess();
		$url = $setting['zoho_api_base_url'] . '/crm/v5/'.$module.'/upsert';
        $headers = [
            'Authorization: Zoho-oauthtoken '.$setting['access_token'],
            'Content-Type: application/json',
        ];

		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>json_encode(["data" => [$fields]]),
		  CURLOPT_HTTPHEADER => $headers,
		));
		$response = curl_exec($curl);
		
		curl_close($curl);
		$responseBody = json_decode($response, true);

		if(isset($responseBody['data'][0]['status'])){
			return $responseBody['data'][0]['message'];

		}else{
			if(isset($responseBody['message'])){
				return $responseBody['message'];
			}else{
				return 'something worng';
			}
			
		}
		
       
    }


}