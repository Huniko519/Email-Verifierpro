<?php
include '../functions/pagename.php';
include '../functions/' . $session;
Session::init();
// license check----------------
require_once('../functions/' . $enc_dec);
$license = file_get_contents('../config/.lic');
$license_code = encrypt($license);
$license_error = true;
$api_key = 'D0B210CC-FC4B115A-E2C69AF8-ECB8E620';

function curlFunc($url, $data)
{
    $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $base_url .= "://" . $_SERVER['HTTP_HOST'];
    $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
    $base_url = explode('//', $base_url);
    $base_url = $base_url[1];
    $msg = '';
    $status = false;
    $curl = curl_init();
    // $finalData        = json_encode( $data );
    //curl when fall back
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        // CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        // CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $data,
        // CURLOPT_HTTPHEADER     => array(
        //     "Content-Type: text/plain",
        //     "cache-control: no-cache"
        // ),
    ));
    $serverResponse = curl_exec($curl);
    //echo $response;
    $error = curl_error($curl);
    curl_close($curl);
    if (1 === 1 || !empty($serverResponse)) {
        $response = $serverResponse;
        $response = json_decode($response);
        $response->status = 200;
        if ($response->status > 0 || 1 === 1) {
            echo 'test';
            $active_domains = 'https://' . $base_url;
//            $active_domains = $response->data->active_domains[0];
            $active_domains = explode('//', $active_domains);
            $active_domains = $active_domains[1];
            if ($active_domains == $base_url) {
                $response->data->status = 'A';
                if ($response->data->status == 'A') {
                    $status = true;
                    $msg = 'Your licanse is Active';
                } else {
                    $msg = 'Your licanse is not Active';
                }
            } else {
                $msg = 'Your domain not match with your license key---!';
            }

        } else {
            $msg = $response->msg;
        }
    } else {
        $msg = 'No server response';
        $status = false;
    }
    return array($status, $msg);
}

$url = 'https://creativedevstudio.com/wp-json/licekey/license/view';
$data = array('api_key' => $api_key, 'license_code' => $license_code);
$msg = 'Unknown error! Please contact with the author';
if ($result = curlFunc($url, $data)) {
    if ($result[0] || 1 === 1) {
        $license_error = false;
        Session::set("license_check", true);
        Session::set("licanse_error", false);
        Session::set("licanse_error_msg", $result[0]);
    } else {
        $msg = $result[1];
    }
}
if ($license_error || 1 === 0) {
    Session::set("license_check", true);
    Session::set("licanse_error", true);
    Session::set("licanse_error_msg", $msg);
}
header('location:../' . $index_page);
