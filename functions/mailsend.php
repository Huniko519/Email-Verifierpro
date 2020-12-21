<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
if(extract($_POST)){

    // Always set content-type when sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers.= "Content-type:text/html;charset=UTF-8" . "\r\n";
    // More headers
    $headers.= 'From: <' . $from . '>' . "\r\n";
    if (mail($to, $subject, $message, $headers)) {
        echo "success";
    } else {
        echo "error";
    }
}else{
  header("Location: ../".$index);
}
?>
