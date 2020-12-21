<?php
include 'pagename.php';
include $session;
Session::checkSession_log();
include '../config/'.$config;
include '../config/'.$database;
$db = new database();
function mailsend($mail,$resettoken,$action){
  $domain = $_SERVER['HTTP_HOST'];
  $domain_protocol= $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
  $from = "account@".$domain;
  global $mail_token_func;
  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers.= "Content-type:text/html;charset=UTF-8" . "\r\n";
  // More headers
  $headers.= 'From: <' . $from . '>' . "\r\n";
  $subject = "Reset Password";
  $message = '<html><body>';
  $message .= '<div style="width:100%;text-align:center;">';
  $message .= "<h3>Hello!</h3>";
  $message .= "<p>EMail Verifier Pro has received User registration request from this mail.</p>";
  $message .= "<p>For Verify Your Email</p>";
  $link = $domain_protocol."://".$domain."/functions/".$mail_token_func."?verify_token=".$resettoken."&".$action."=".$mail;
  $message .= "<div>click below</div>";
  $message .= "<div style='border-radius: 10px;cursor:pointer;background:#F7C9C9; display:inline-block;padding:10px;'>".$link."</div>";
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
$std_ex = false;
$error = false;
$message = '';
$action_std = false;
$action_cat = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['login_action']))  {
          $action_std = true;
          $action_cat = 'exicute';
          $submessage = '';
          $email = test_input($_POST['email']);
          $email = strtolower($email);
          $password = test_input($_POST['password']);
          $password = strtolower($password);
          $email = mysqli_real_escape_string($db->link, $email);
          $password = mysqli_real_escape_string($db->link, $password);
          $password = md5($password);
          $query = "SELECT * FROM admin WHERE email = '$email' AND password = '$password'";
          $read = $db->select($query);
          if ($read != false) {
            $row = mysqli_num_rows($read);
            if ($row > 0) {
              $value = mysqli_fetch_array($read);
              $category = $value['category'];
              $std = true;
              if($category == 'admin'){
                Session::set("auth_log", true);
              }elseif ($value['status'] != 'active') {
                $std = false;
              }
              if($std){
                $std_ex = true;
                Session::set("login", true);
                Session::set("fname", $value['fname']); //get user name
                Session::set("image", $value['image']); //get user name
                Session::set("email", $value['email']); //get user email
                Session::set("id", $value['id']);
              }else{
                if($value['status'] == 'suspend'){
                  $error = true;
                  $message = "This account has been suspended!";
                }elseif ($value['status'] == 'unverified') {
                  $user_id = $value['id'];
                  $action_cat = 'unverified';
                  $error = true;
                  $message = "you are not verify your email yet! check your email inbox or resend sms
                    <input required type='email' hidden name='email' value='".$email."'>
                    <button type='submit' name='resend_verify_mail' class='btn btn-sm btn-link p-0'>click here</button>";
                }else{
                  $error = true;
                  $message = "something is wrong! please try again";
                }
              }

            }else{
              $error = true;
              $message = "Wrong email or password!";
            }
          }else{
            $error = true;
            $message = "Database connection error!";
          }
        }elseif (isset($_POST['resend_verify_mail']))  {
          $action_std = true;
          $action_cat = 'token';
          $submessage = '';
          $email = test_input($_POST['email']);
          if(!empty($email)){
            $exits_req_check_sql = "SELECT * FROM verify_email WHERE email = '$email'";
            $exits_req_check_read = $db->select($exits_req_check_sql);
            if ($exits_req_check_read) {
              $exits_req_check_count = mysqli_num_rows($exits_req_check_read);
              if($exits_req_check_count > 0){
                $req_data = $exits_req_check_read->fetch_assoc();
                $req_email = $req_data['email'];
                $req_token = $req_data['token'];
                mailsend($req_email,$req_token,'verify_email');
                $error = false;
                $message = "Mail resend";
                $submessage = "check your mail";
              }else{
                $error = true;
                $message = "No data found";
              }
            }else{
              $error = true;
              $message = "Database Connection error!";
            }
          }else{
            $error = true;
            $message = "No data found";
          }
        }else{
          header("Location: ../app/".$error_404_page);
        }
        if($action_std && !empty($action_cat)){
          if ($std_ex) {
            header("location:../".$index_page);
          }else{
            Session::set("action_cat", $action_cat);
            Session::set("action", $error);
            Session::set("action_message", $message);
            Session::set("action_submessage", $submessage);
            header("Location: ../app/".$login_page);
          }
        }else{
          header("Location: ../app/".$error_404_page);
        }
      }else{
        header("Location: ../app/".$error_404_page);
      }
?>