<?php
include '../functions/pagename.php';
include '../functions/'.$session;
session_start();
// license check----------------
require_once('../functions/'.$lic_verify);
require_once('../functions/'.$enc_dec);
$license = file_get_contents('../config/.lic');
$license_code =  encrypt($license);
$api_key = 'D0B210CC-FC4B115A-E2C69AF8-ECB8E620';

function curlFunc($url, $data){
  $msg = '';
  $status = false;
  $curl             = curl_init();
  // $finalData        = json_encode( $data );
  //curl when fall back
  curl_setopt_array( $curl, array(
      CURLOPT_URL            => $url,
      CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
      // CURLOPT_ENCODING       => "",
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 30,
      // CURLOPT_CUSTOMREQUEST  => "POST",
      CURLOPT_POST           => 1,
      CURLOPT_POSTFIELDS     => $data,
      // CURLOPT_HTTPHEADER     => array(
      //     "Content-Type: text/plain",
      //     "cache-control: no-cache"
      // ),
  ) );
  $serverResponse = curl_exec( $curl );
  //echo $response;
  $error = curl_error( $curl );
  curl_close( $curl );
  if ( ! empty( $serverResponse ) ) {
      $response = $serverResponse;
      $response = json_decode( $response );
      if($response->status > 0){
        $status = true;
      }
      $msg = $response->msg;
  }else{
    $msg = 'No server response';
    $status = false;
  }
  return array($status,$msg);
}

$action_std = false;
$action_cat = '';
$error = true;
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['inactive_licanse'])) {
          $action_std = true;
          $action_cat = 'licanse_status';
          $data = array('api_key' => $api_key, 'license_code' => $license_code, 'status' => 'W');
          $url = 'https://creativedevstudio.com/wp-json/licekey/license/edit';
          if($result = curlFunc($url,$data)){
            if($result[0] > 0){
              $error = false;
              $msg = 'Your license is inactive now';
              Session::set("license_check", true);
              Session::set("licanse_error", true);
              Session::set("licanse_error_msg", $msg);
            }
            $message = $result[1];
          }
        }
        if (isset($_POST['active_licanse'])) {
          $action_std = true;
          $action_cat = 'licanse_status';
          $license_key = $_POST['license_key'];
          $email = $_POST['email'];
          if($license_key == $license_code){
            $data = array('api_key' => $api_key, 'license_code' => $license_code, 'status' => 'A');
            $url = 'https://creativedevstudio.com/wp-json/licekey/license/edit';
            if($result = curlFunc($url,$data)){
              if($result[0] > 0){
                $error = false;
                $msg = 'Your license is Active now';
                Session::set("license_check", true);
                Session::set("licanse_error", false);
                Session::set("licanse_error_msg", $msg);
              }
              $message = $result[1];
            }
          }else{
            $errorMessage="";
            $responseObj=null;
            $version="1.0.1";
            $msg = 'Unknown error! Please contact with the author';
            if(EmailVerifierProBase::CheckLicense($license_key,$errorMessage,$responseObj,$version,$email)) {
              if($responseObj->is_valid == 1){
                $connect_code = decrypt($responseObj->license_key);
                if (!is_writable("../config/".$config)) {
                    $message = "Something went wrong when store license!";
                } else {
                    $fp = fopen('../config/.lic', 'wb');
                    fwrite($fp, $connect_code);
                    fclose($fp);
                    chmod('../config/.lic', 0666);
                    $error = false;
                    Session::set("license_check", true);
                    Session::set("licanse_error", false);
                    $message = $responseObj->msg;
                    Session::set("licanse_error_msg", $message);
                }

              }else{
                $license_error = true;
                $message = $responseObj->msg;
              }
            }else{
              $license_error = true;
              $message = $errorMessage;
            }
            if($license_error){
              Session::set("license_check", true);
              Session::set("licanse_error", true);
              Session::set("licanse_error_msg", $message);
            }
          }

        }
        if($action_std && !empty($action_cat)){
          Session::set("action_cat", $action_cat);
          Session::set("action", $error);
          Session::set("action_message", $message);
          header("Location: ".$settings_page);
        }else{
          header("Location: ".$error_404_page);
        }
      }else{
        header("Location: ".$error_404_page);
      }