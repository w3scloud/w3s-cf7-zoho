<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://w3scloud.com/
 * @since      1.0.0
 *
 * @package    W3s_Cf7_Zoho
 * @subpackage W3s_Cf7_Zoho/includes
 */

/**
 * The core zoho plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    W3s_Cf7_Zoho
 * @subpackage W3s_Cf7_Zoho/includes
 * @author     W3S Cloud Technology, shohag121 <info@w3scloud.com>
 */


use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\api\handler\MetaDataAPIHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\oauth\ZohoOAuth;


class W3s_Cf7_Zoho_Conn {

	private $authInfoInstance;

	/**
	 * Zoho Settings configuration
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $zohoConfig = array();

	/**
	 * Zoho Authentication
	 *
	 * @since 1.0.0
	 * @var bool|mixed
	 */
	private $auth = false;

	/**
	 * W3s_Cf7_Zoho_Conn constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->authInfoInstance = new ZohoAuthInfos();
		$this->auth             = $this->authInfoInstance->getInfo( 'zoho_authorized' );
		$this->setConfig();
	}

	/**
	 * this function include the vendor auto load file
	 * and initialize the Zoho functionality
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function include_zoho() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/zoho-conn/vendor/autoload.php';
		$this->init_zoho();
	}

	/**
	 * this function initialize ZCRMRestClient
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_zoho() {
		if ( $this->auth ) {
			try {
				ZCRMRestClient::initialize( $this->zohoConfig );
			} catch ( ZCRMException $exception ) {
				$this->auth = false;
				add_action( 'admin_notices', array( $this, 'noticeAdmin' ) );
				exit;
			}
		}
	}

	/**
	 * this function creates or update record to Zoho CRM on selected module
	 *
	 * @param array  $dataArray
	 * @param bool   $upsert
	 * @param string $module
	 * @param array  $files
	 */
	public function createRecord( $dataArray, $upsert = false, $module = 'Leads', $files = array() ) {
		try {

			$this->include_zoho();

			$moduleIns = ZCRMRestClient::getInstance()->getModuleInstance( $module );
			$records   = array();

			foreach ( $dataArray as $data ) {
				$record = ZCRMRecord::getInstance( $module, null );

				foreach ( $data as $key => $value ) {

					$record->setFieldValue(
						$this->removeDataType( $key ),
						$this->prepareData(
							$value[0],
							$this->getDataType( $key ),
							$value[1]
						)
					);
				}

				array_push( $records, $record );
			}

			if ( ! $upsert ) {
				$responseIn = $moduleIns->createRecords( $records );
				do_action( 'w3s_cf7_zoho_on_create_record', $responseIn );
			} else {
				$responseIn = $moduleIns->upsertRecords( $records );
				do_action( 'w3s_cf7_zoho_on_update_record', $responseIn );
			}

			$entityResponse = $responseIn->getEntityResponses()[0];
			if ( 'success' == $entityResponse->getStatus() ) {
				$createdRecordInstance = $entityResponse->getData();
				$entityID              = $createdRecordInstance->getEntityId();

				if ( ! empty( $files ) ) {
					foreach ( $files as $fileName => $filePath ) {
						$recordToUpload  = ZCRMRecord::getInstance( $module, $entityID );
						$fileresponseIns = $recordToUpload->uploadAttachment( $filePath );
						do_action( 'w3s_cf7_zoho_after_file_uploaded_to_record', $fileresponseIns );
					}
				}
			}

			do_action( 'w3s_cf7_zoho_after_create_or_update_record', $responseIn, $module );

		} catch ( ZCRMException $exception ) {
			add_action( 'admin_notices', array( $this, 'noticeAdmin' ) );
		}
	}

	/**
	 * get all the fields of selected module
	 *
	 * @since 1.0.0
	 * @param string $module
	 * @return array
	 */
	public function getZohoFields( $module = 'Leads' ) {

		try {
			$this->include_zoho();

			$moduleIns   = ZCRMModule::getInstance( $module );
			$apiResponse = $moduleIns->getAllFields();
			$fields      = $apiResponse->getData();

			$formatedFields = array();

			foreach ( $fields as $field ) {
				// get api name and data type
				$apiName                                       = $field->getApiName();
				$apiDataType                                   = $field->getDataType();
				$formatedFields[ "{$apiDataType}_{$apiName}" ] = "{$apiName} ({$apiDataType})";
			}

			return $formatedFields;
		} catch ( ZCRMException $e ) {
			add_action( 'admin_notices', array( $this, 'noticeAdmin' ) );
			return array();
		}
	}


	/**
	 * get the fields of selected contact form
	 *
	 * @since 1.0.0
	 * @param $cf7_id
	 * @return array|mixed|void
	 */
	public function getCF7Fields( $cf7_id ) {
		if ( $cf7_id == null ) {
			return array();
		}
		$current_cf7 = WPCF7_ContactForm::get_instance( $cf7_id );
		$form        = $current_cf7->prop( 'form' );
		$re          = '/(?<=\[)([^\]]+)/';
		preg_match_all( $re, $form, $matches, PREG_SET_ORDER, 0 );
		$cf7Fields = array();

		foreach ( $matches as $match ) {
			if ( $match[0] == '/acceptance' ) {
				continue;
			}
			$field = explode( ' ', str_replace( '*', '', $match[0] ) );
			if ( $field[0] == 'submit' ) {
				continue;
			}
			if ( $field[0] == 'file' ) {
				continue;
			}
			$cf7Fields[ "{$field[0]}_{$field[1]}" ] = "{$field[1]} ({$field[0]})";
		}

		$cf7Fields = apply_filters( 'w3s_cf7_zoho_cf7_fields', $cf7Fields );

		return $cf7Fields;
	}

	/**
	 * get all modules
	 *
	 * @since 1.1.2
	 * @return array
	 */
	public function getModules() {
		try {
			$this->include_zoho();
			$formatedModules = array();
			$moduleArr       = ZCRMRestClient::getInstance()->getAllModules()->getData();
			foreach ( $moduleArr as $module ) {
				if ( ! ( $module->isEditable() && $module->isViewable() && $module->isCreatable() ) ) {
					continue;
				}

				$formatedModules[ $module->getAPIName() ] = $module->getModuleName();
			}

			return $formatedModules;
		} catch ( ZCRMException $e ) {
			add_action( 'admin_notices', array( $this, 'noticeAdmin' ) );
			return array();
		}
	}


	/**
	 *
	 * @since 1.1.2
	 * @param string $sourceDataType
	 * @param string $zohoDataType
	 * @param mixed  $data
	 * @return mixed
	 */
	private function prepareData( $sourceDataType, $zohoDataType, $data ) {
		switch ( $zohoDataType ) {
			case 'textarea':
			case 'picklist':
			case 'email':
			case 'phone':
			case 'website':
			case 'lookup':
			case 'wonerlookup':
			case 'bigint':
			case 'text':
				if ( $sourceDataType == 'text' ) {
					return $data;
				} elseif ( $sourceDataType == 'email' ) {
					return $data;
				} elseif ( $sourceDataType == 'url' ) {
					return $data;
				} elseif ( $sourceDataType == 'tel' ) {
					return $data;
				} elseif ( $sourceDataType == 'number' ) {
					return $data;
				} elseif ( $sourceDataType == 'date' ) {
					return $data;
				} elseif ( $sourceDataType == 'textarea' ) {
					return $data;
				} elseif ( $sourceDataType == 'select' ) {
					return $data;
				} elseif ( $sourceDataType == 'checkbox' ) {
					return implode( ', ', $data );
				} elseif ( $sourceDataType == 'radio' ) {
					return $data[0];
				} elseif ( $sourceDataType == 'acceptance' ) {
					return $data;
				} elseif ( $sourceDataType == 'quiz' ) {
					return $data;
				} else {
					return $data;
				}
				break;

			case 'multiselectpicklist':
				if ( $sourceDataType == 'text' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'email' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'url' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'tel' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'number' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'date' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'textarea' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'select' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'checkbox' ) {
					return $data;
				} elseif ( $sourceDataType == 'radio' ) {
					return $data;
				} elseif ( $sourceDataType == 'acceptance' ) {
					return array( $data );
				} elseif ( $sourceDataType == 'quiz' ) {
					return array( $data );
				} else {
					return array( $data );
				}
				break;

			case 'date':
				if ( $sourceDataType == 'text' ) {
					return date( 'Y-m-d', strtotime( $data ) );
				} elseif ( $sourceDataType == 'email' ) {
					return null;
				} elseif ( $sourceDataType == 'url' ) {
					return null;
				} elseif ( $sourceDataType == 'tel' ) {
					return null;
				} elseif ( $sourceDataType == 'number' ) {
					return null;
				} elseif ( $sourceDataType == 'date' ) {
					return date( 'Y-m-d', strtotime( $data ) );
				} elseif ( $sourceDataType == 'textarea' ) {
					return date( 'Y-m-d', strtotime( $data ) );
				} elseif ( $sourceDataType == 'select' ) {
					return $data;
				} elseif ( $sourceDataType == 'checkbox' ) {
					return null;
				} elseif ( $sourceDataType == 'radio' ) {
					return date( 'Y-m-d', strtotime( $data[0] ) );
				} elseif ( $sourceDataType == 'acceptance' ) {
					return date( 'Y-m-d', strtotime( $data ) );
				} elseif ( $sourceDataType == 'quiz' ) {
					return date( 'Y-m-d', strtotime( $data ) );
				} else {
					return date( 'Y-m-d', strtotime( $data ) );
				}
				break;

			case 'datetime':
				date_default_timezone_set( get_option( 'timezone_string' ) );
				if ( $sourceDataType == 'text' ) {
					return date( 'c', strtotime( $data ) );
				} elseif ( $sourceDataType == 'email' ) {
					return null;
				} elseif ( $sourceDataType == 'url' ) {
					return null;
				} elseif ( $sourceDataType == 'tel' ) {
					return null;
				} elseif ( $sourceDataType == 'number' ) {
					return null;
				} elseif ( $sourceDataType == 'date' ) {
					return date( 'c', strtotime( $data ) );
				} elseif ( $sourceDataType == 'textarea' ) {
					return date( 'c', strtotime( $data ) );
				} elseif ( $sourceDataType == 'select' ) {
					return $data;
				} elseif ( $sourceDataType == 'checkbox' ) {
					return null;
				} elseif ( $sourceDataType == 'radio' ) {
					return date( 'c', strtotime( $data[0] ) );
				} elseif ( $sourceDataType == 'acceptance' ) {
					return date( 'c', strtotime( $data ) );
				} elseif ( $sourceDataType == 'quiz' ) {
					return date( 'c', strtotime( $data ) );
				} else {
					return date( 'c', strtotime( $data ) );
				}
				break;

			case 'integer':
				if ( $sourceDataType == 'text' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'email' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'url' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'tel' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'number' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'date' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'textarea' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'select' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'checkbox' ) {
					return null;
				} elseif ( $sourceDataType == 'radio' ) {
					return (int) $data[0];
				} elseif ( $sourceDataType == 'acceptance' ) {
					return (int) $data;
				} elseif ( $sourceDataType == 'quiz' ) {
					return (int) $data;
				} else {
					return intval( $data );
				}
				break;

			case 'boolean':
				if ( $sourceDataType == 'text' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'email' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'url' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'tel' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'number' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'date' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'textarea' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'select' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'checkbox' ) {
					return boolval( $data[0] );
				} elseif ( $sourceDataType == 'radio' ) {
					return boolval( $data[0] );
				} elseif ( $sourceDataType == 'acceptance' ) {
					return boolval( $data );
				} elseif ( $sourceDataType == 'quiz' ) {
					return boolval( $data );
				} else {
					return boolval( $data );
				}
				break;

			case 'double':
				if ( $sourceDataType == 'text' ) {
					return floatval( $data );
				} elseif ( $sourceDataType == 'email' ) {
					return null;
				} elseif ( $sourceDataType == 'url' ) {
					return null;
				} elseif ( $sourceDataType == 'tel' ) {
					return floatval( $data );
				} elseif ( $sourceDataType == 'number' ) {
					return floatval( $data );
				} elseif ( $sourceDataType == 'date' ) {
					return null;
				} elseif ( $sourceDataType == 'textarea' ) {
					return floatval( $data );
				} elseif ( $sourceDataType == 'select' ) {
					return floatval( $data );
				} elseif ( $sourceDataType == 'checkbox' ) {
					return floatval( $data[0] );
				} elseif ( $sourceDataType == 'radio' ) {
					return floatval( $data[0] );
				} elseif ( $sourceDataType == 'acceptance' ) {
					return floatval( $data );
				} elseif ( $sourceDataType == 'quiz' ) {
					return floatval( $data );
				} else {
					return floatval( $data );
				}
				break;

			default:
				return $data;
		}
	}

	/**
	 * remove datatype at start of the string and return actual key
	 *
	 * @since   1.1.2
	 * @param $key
	 * @return string
	 */
	public function removeDataType( $key ) {
		$keyArray = explode( '_', $key, 2 );
		return $keyArray[1];
	}


	/**
	 * return only data type from start of the key
	 *
	 * @since    1.1.2
	 * @param $key
	 * @return string
	 */
	public function getDataType( $key ) {
		$keyArray = explode( '_', $key, 2 );
		return $keyArray[0];
	}



	private function setConfig() {
		/*
		$upload = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . '/w3s-cf7-zoho';

		if (file_exists($upload_dir.'/config.php')){

			$confFile = $upload_dir .'/config.php';

			$conf = require $confFile;
			if(!empty($conf)){
				$this->auth = true;
				$this->zohoConfig = $conf;
			}
		} else {
			$this->auth = false;
		}*/// ss00;

		$getConfig = get_option( '_zoho_config' );// ss00
		if ( $getConfig ) {
			$this->auth       = true;
			$this->zohoConfig = unserialize( $getConfig );
		} else {
			$this->auth = false;
		}

	}

	public function noticeAdmin() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'Problem in your Zoho Authentication.', 'w3s-cf7-zoho' ); ?></p>
		</div>
		<?php
	}

	public function genToken( $grantToken, $config ) {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/zoho-conn/vendor/autoload.php';

		try {
			ZCRMRestClient::initialize( $config );
			$oAuthClient = ZohoOAuth::getClientInstance();
			$oAuthTokens = $oAuthClient->generateAccessToken( $grantToken );

			do_action( 'w3s_cf7_zoho_after_token_generation' );

			return true;
		} catch ( ZCRMException $exception ) {
			add_action( 'admin_notices', array( $this, 'noticeAdmin' ) );
			return false;
		}

	}
}
