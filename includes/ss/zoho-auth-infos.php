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

}
