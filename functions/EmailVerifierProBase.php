<?php
class EmailVerifierProBase {
	public $key = "2073CE7FFAFDD47B";
	private $product_id = "1";
	private $product_base = "email-verifier-pro";
	private $server_host = "https://creativedevstudio.com/wp-json/licekey/";
	/* @var self*/
    private static $selfobj=null;
    function __construct()
    {
        $this->initActionHandler();
    }
    function initActionHandler(){
        $handler=hash("crc32b",$this->product_id.$this->key.$this->getDomain())."_handle";
        if(isset($_GET['action']) && $_GET['action']==$handler){
            $this->handleServerRequest();
            exit;
        }
    }
    function handleServerRequest(){
        $type=isset($_GET['type'])?strtolower($_GET['type']):"";
        switch ($type){
            case "rl": //remove license
                $this->removeOldResponse();
                $obj=new stdClass();
                $obj->product=$this->product_id;
                $obj->status=true;
                echo $this->encryptObj($obj);
                return;
            case "dl": //delete app
                $obj          = new stdClass();
                $obj->product = $this->product_id;
                $obj->status  = true;
                $this->removeOldResponse();
                echo $this->encryptObj( $obj);
                return;
            default:
                return;
        }
    }
    function __plugin_updateInfo() {
	    if ( function_exists( "file_get_contents" ) ) {
		    $body         = file_get_contents( $this->server_host . "product/update/" . $this->product_id );
		    $responseJson = json_decode( $body );
		    if ( is_object( $responseJson ) && ! empty( $responseJson->status ) && ! empty( $responseJson->data->new_version ) ) {

			    $responseJson->data->new_version = ! empty( $responseJson->data->new_version ) ? $responseJson->data->new_version : "";
			    $responseJson->data->version     = $responseJson->data->new_version;
			    $responseJson->data->url         = ! empty( $responseJson->data->url ) ? $responseJson->data->url : "";
			    $responseJson->data->package     = ! empty( $responseJson->data->download_link ) ? $responseJson->data->download_link : "";

			    $responseJson->data->sections = (array) $responseJson->data->sections;
			    //$responseJson->data->plugin      = "";
			    $responseJson->data->icons       = (array) $responseJson->data->icons;
			    $responseJson->data->banners     = (array) $responseJson->data->banners;
			    $responseJson->data->banners_rtl = (array) $responseJson->data->banners_rtl;

			    return $responseJson->data;
		    }
	    }
	    return NULL;
    }
   static function GetPluginUpdateInfo()
    {
    	$obj=static::getInstance();
        return $obj->__plugin_updateInfo();
    }

    /**
     * @param $plugin_base_file
     *
     * @return ElementPackBase|null
     */
    static function &getInstance() {
        if(empty(static::$selfobj)){
	        static::$selfobj = new static();
        }
        return static::$selfobj;
    }

    private function encrypt($plainText,$password='') {
        if(empty($password)){
            $password=$this->key;
        }
        $plainText=rand(10,99).$plainText.rand(10,99);
        $method = 'aes-256-cbc';
        $key = substr( hash( 'sha256', $password, true ), 0, 32 );
        $iv = substr(strtoupper(md5($password)),0,16);
        return base64_encode( openssl_encrypt( $plainText, $method, $key, OPENSSL_RAW_DATA, $iv ) );
    }
    private function decrypt($encrypted,$password='') {
        if(empty($password)){
      		$password=$this->key;
      	}
        $method = 'aes-256-cbc';
        $key = substr( hash( 'sha256', $password, true ), 0, 32 );
        $iv = substr(strtoupper(md5($password)),0,16);
        $plaintext=openssl_decrypt( base64_decode( $encrypted ), $method, $key, OPENSSL_RAW_DATA, $iv );
        return substr($plaintext,2,-2);
    }

    function encryptObj( $obj ) {
        $text = serialize( $obj );

        return $this->encrypt( $text );
    }

    private function decryptObj( $ciphertext ) {
        $text = $this->decrypt( $ciphertext );

        return unserialize( $text );
    }
    private function getDomain() {
	    $base_url = ( ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == "on" ) ? "https" : "http" );
	    $base_url .= "://" . $_SERVER['HTTP_HOST'];
	    $base_url .= str_replace( basename( $_SERVER['SCRIPT_NAME'] ), "", $_SERVER['SCRIPT_NAME'] );
	    return $base_url;

    }

    private function getEmail() {
        return '';
    }

    private function processs_response($response){
        $resbk="";
          if ( ! empty( $response ) ) {
              if ( ! empty( $this->key ) ) {
                $resbk=$response;
                  $response = $this->decrypt( $response );
              }
              $response = json_decode( $response );

              if ( is_object( $response ) ) {
                  return $response;
              } else {
                $response=new stdClass();
                $response->status = false;
                $bkjson=@json_decode($resbk);
                if(!empty($bkjson->msg)){
                    $response->msg    = $bkjson->msg;
                }else{
                    $response->msg    = "Response Error, contact with the author or update the plugin or theme";
                }
                  $response->data = NULL;
                  return $response;

              }
          }
          $response=new stdClass();
          $response->msg    = "unknown response";
          $response->status = false;
          $response->data = NULL;

          return $response;
    }
    private function _request( $relative_url, $data, &$error = '' ) {
        $response         = new stdClass();
        $response->status = false;
        $response->msg    = "Empty Response";
        $curl             = curl_init();
        $finalData        = json_encode( $data );
        if ( ! empty( $this->key ) ) {
            $finalData = $this->encrypt( $finalData );
        }
        $url = rtrim( $this->server_host, '/' ) . "/" . ltrim( $relative_url, '/' );

        //curl when fall back
        curl_setopt_array( $curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $finalData,
            CURLOPT_HTTPHEADER     => array(
                "Content-Type: text/plain",
                "cache-control: no-cache"
            ),
        ) );
        $serverResponse = curl_exec( $curl );
        //echo $response;
        $error = curl_error( $curl );
        curl_close( $curl );
        if ( ! empty( $serverResponse ) ) {
            return $this->processs_response($serverResponse);
        }
        $response->msg    = "unknown response";
        $response->status = false;
        $response->data = NULL;
        return $response;
    }

    private function getParam( $purchase_key, $app_version, $admin_email = '' ) {
        $req               = new stdClass();
        $req->license_key  = $purchase_key;
        $req->email        = ! empty( $admin_email ) ? $admin_email : $this->getEmail();
        $req->domain       = $this->getDomain();
        $req->app_version  = $app_version;
        $req->product_id   = $this->product_id;
        $req->product_base = $this->product_base;

        return $req;
    }

    function SaveResponse( $response ) {
	    $key  = hash( 'crc32b', $this->getDomain() . $this->product_id . "LIC" );
	    $data = $this->encrypt( serialize( $response ), $this->getDomain() );
	    file_put_contents( dirname( __FILE__ ) . "/" . $key, $data);
    }
    function getOldResponse() {
        $key=hash('crc32b',$this->getDomain().$this->product_id."LIC");
        if(file_exists(dirname( __FILE__ ) . "/" . $key)) {
	        $response = file_get_contents( dirname( __FILE__ ) . "/" . $key );
	        if ( !empty( $response ) ) {
		        return unserialize($this->decrypt($response,$this->getDomain()));
	        }
        }
        return null;
    }
    private function removeOldResponse() {
        $key=hash('crc32b',$this->getDomain().$this->product_id."LIC");
        if(file_exists(dirname( __FILE__ ) . "/" . $key)){
        	unlink(dirname( __FILE__ ) . "/" . $key);
        }
        return true;
    }
    public static function RemoveLicenseKey(&$message = "",$version="") {
        $obj=self::getInstance();
        return $obj->_removePluginLicense($message,$version);
    }
    public static function CheckLicense($purchase_key,&$error = "", &$responseObj = null,$app_version="", $admin_email="") {
        $obj=self::getInstance();
        return $obj->_CheckLicense($purchase_key,  $error, $responseObj,$app_version,$admin_email);
    }
    final function _CheckLicense( $purchase_key,&$error = "", &$responseObj = null ,$app_version="", $admin_email="") {
        if(empty($purchase_key)){
            $this->removeOldResponse();
            $error="";
            return false;
        }

        return true;

        $oldRespons=$this->getOldResponse();
        $isForce=false;
        if(!empty($oldRespons)) {
	        if ( ! empty( $oldRespons->expire_date ) && strtolower( $oldRespons->expire_date ) != "no expiry" && strtotime( $oldRespons->expire_date ) < time() ) {
		        $isForce = true;
	        }
	        if ( ! $isForce && ! empty( $oldRespons->is_valid ) && $oldRespons->next_request > time() && ( ! empty( $oldRespons->license_key ) && $purchase_key == $oldRespons->license_key ) ) {
		        $responseObj = clone $oldRespons;
		        unset( $responseObj->next_request );

		        return true;
	        }
        }


        $param    = $this->getParam( $purchase_key, $app_version,$admin_email);
        $response = $this->_request( 'product/active/'.$this->product_id, $param, $error );
        if(empty($response->code)) {
            if ( ! empty( $response->status ) ) {
                if ( ! empty( $response->data ) ) {
                    $serialObj   = $this->decrypt( $response->data, $param->domain );

                    $licenseObj = unserialize( $serialObj );
                    if ( $licenseObj->is_valid ) {
                        $responseObj = new stdClass();
                        $responseObj->is_valid = $licenseObj->is_valid;
                        if($licenseObj->request_duration>0) {
                            $responseObj->next_request = strtotime("+ {$licenseObj->request_duration} hour");
                        }else{
                            $responseObj->next_request=time();
                        }
                        $responseObj->expire_date = $licenseObj->expire_date;
                        $responseObj->support_end = $licenseObj->support_end;
                        $responseObj->license_title = $licenseObj->license_title;
                        $responseObj->license_key = $purchase_key;
                        $responseObj->msg = $response->msg;
                        $this->SaveResponse($responseObj);
                        unset($responseObj->next_request);
                        return true;
                    }else {
	                    $this->removeOldResponse();
                        $error = !empty($response->msg)?$response->msg:"";
                    }
                } else {
                    $error = "Invalid data";
                }

            } else {
                $error = $response->msg;
            }
        }else{
            $error=$response->message;
        }

        return false;
    }
	final function _removePluginLicense(&$message='',$version=''){
		$oldRespons=$this->getOldResponse();
		if(!empty($oldRespons->is_valid)) {
			if ( ! empty( $oldRespons->license_key ) ) {
				$param    = $this->getParam( $oldRespons->license_key, $version );
				$response = $this->_request( 'product/deactive/'.$this->product_id, $param, $message );
				if ( empty( $response->code ) ) {
					if ( ! empty( $response->status ) ) {
						$message = $response->msg;
						$this->removeOldResponse();
						return true;
					}else{
						$message = $response->msg;
					}
				}else{
					$message=$response->message;
				}
			}
		}
		return false;

	}
	public static function GetRegisterInfo() {
		if(!empty(static::$selfobj)){
			return static::$selfobj->getOldResponse();
		}
		return null;

	}
}