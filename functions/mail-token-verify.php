<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
include '../config/'.$config;
include '../config/'.$database;
$db = new database();

function test_input($data) { //filter value function
    $db = new database();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strtolower($data);
    $data = mysqli_real_escape_string($db->link, $data);
    return $data;
}
$error = true;
if (isset($_GET["verify_token"]) && isset($_GET["verify_email"])) {
    $token = test_input($_GET['verify_token']);
    $email = test_input($_GET['verify_email']);
    $check_email_verify_sql = "SELECT * FROM verify_email WHERE email = '$email' AND token = '$token'";
    $eheck_email_verify_read = $db->select($check_email_verify_sql);
    $count = mysqli_num_rows($eheck_email_verify_read);
    if($count > 0){
      $update_user_sql = "UPDATE admin SET status = 'active' WHERE email = '$email'";
      $update_user_read = $db->update($update_user_sql);
      $delete_token_sql = "DELETE FROM verify_email WHERE email = '$email'";
      $delete_token_read = $db->delete($delete_token_sql);
      if($update_user_read && $delete_token_read){
        $action_cat = 'token';
        $error = false;
        $message = "Email verify successfully";
        $submessage = "You can login now";
      }
    }
}elseif (isset($_GET["verify_token"]) && isset($_GET["update_email"])) {
  $token = test_input($_GET['verify_token']);
  $email = test_input($_GET['update_email']);
  $check_email_verify_sql = "SELECT * FROM email_change WHERE email = '$email' AND token = '$token'";
  $eheck_email_verify_read = $db->select($check_email_verify_sql);
  $count = mysqli_num_rows($eheck_email_verify_read);
  if($count > 0){
    $eheck_email_verify_row = $eheck_email_verify_read->fetch_assoc();
    $user_id = $eheck_email_verify_row['user_id'];
    $email = $eheck_email_verify_row['email'];
    $check_email_sql = "SELECT * FROM admin WHERE email = '$email'";
    $eheck_email_read = $db->select($check_email_sql);
    $count_user_mail = mysqli_num_rows($eheck_email_read);
    if($count_user_mail <= 0){
      $update_user_sql = "UPDATE admin SET email = '$email' WHERE id = '$user_id'";
      $update_user_read = $db->update($update_user_sql);
      $delete_token_sql = "DELETE FROM email_change WHERE user_id = '$user_id'";
      $delete_token_read = $db->delete($delete_token_sql);
      if($update_user_read && $delete_token_read){
        $action_cat = 'token';
        $error = false;
        $message = "Email Update successfully";
        $submessage = "Please login with new email";
      }
    }
  }
}
if($error){
  Session:: destroy();
  Session::init();
  header("Location: ../app/".$login_page);
}else{
  Session:: destroy();
  Session::init();
  Session::set("action_cat", $action_cat);
  Session::set("action_message", $message);
  Session::set("action_submessage", $submessage);
  header("Location: ../app/".$login_page);
}
?>