<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
Session::Check_auth();
include '../config/'.$config;
include '../config/'.$database;
$db = new database();

function mailsend($username,$mail,$action){
  $domain = $_SERVER['HTTP_HOST'];
  $domain_protocol= $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
  $from = "account@".$domain;
  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers.= "Content-type:text/html;charset=UTF-8" . "\r\n";
  // More headers
  $headers.= 'From: <' . $from . '>' . "\r\n";
  $subject = ($action == 'suspend' ) ? "suspend account" : "active account";
  $message = '<html><body>';
  $message .= '<div style="width:100%;text-align:center;">';
  $message .= "<h3>Hi ".$username."</h3>";
  $message .= "<p>EMail Verifier Pro Let you know that ".(($action == 'suspend' ) ? "you are suspended" : "your account is active again!")."</p>";
  $message .= "<p>".(($action == 'suspend' ) ? "From this" : "please visit the site")."</p>";
  $message .= "<div>link</div>";
  $message .= "<div style='border-radius: 10px;cursor:pointer;background:#F7C9C9; display:inline-block;padding:10px;'>".$domain."</div>";
  $message .= "</div>";
  $message .= "</body></html>";
  if (mail($mail, $subject, $message, $headers)) {
    return true;
  }
  return false;
}

function RandomString($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function test_input($data) { //filter value function
    $db = new database();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strtolower($data);
    $data = mysqli_real_escape_string($db->link, $data);
    return $data;
}
$error = false;
$message = '';
$action_std = false;
$action_cat = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['active-btn']))  {
          $action_std = true;
          $action_cat = 'exicute';
          $target_id = test_input($_POST['target_id']);
          $fname = test_input($_POST['fname']);
          $email = test_input($_POST['email']);
          $user_update_sql = "UPDATE admin SET status = 'active' WHERE id = '$target_id' ";
          $user_insert_read = $db -> update($user_update_sql);
          if ($user_insert_read) {
            $error = false;
            mailsend($fname,$email,'active');
            $message = "User active successfully";
          }else{
            $error = true;
            $message = "Database connection error!";
          }
        }elseif (isset($_POST['suspend-btn']))  {
          $action_std = true;
          $action_cat = 'exicute';
          $target_id = test_input($_POST['target_id']);
          $fname = test_input($_POST['fname']);
          $email = test_input($_POST['email']);
          $user_update_sql = "UPDATE admin SET status = 'suspend' WHERE id = '$target_id' ";
          $user_insert_read = $db -> update($user_update_sql);
          if ($user_insert_read) {
            $error = false;
            mailsend($fname,$email,'suspend');
            $message = "User suspend successfully";
          }else{
            $error = true;
            $message = "Database connection error!";
          }
        }else{
          header("Location: ../app/".$error_404_page);
        }
        if($action_std && !empty($action_cat)){
          Session::set("action_cat", $action_cat);
          Session::set("action", $error);
          Session::set("action_message", $message);
          header("Location: ../app/".$user_management_page);
        }else{
          header("Location: ../app/".$error_404_page);
        }
      }else{
        header("Location: ../app/".$error_404_page);
      }
?>