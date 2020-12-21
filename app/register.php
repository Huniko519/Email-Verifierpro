<?php
include '../functions/pagename.php';
include '../config/'.$config;
include "../config/".$database;
include "../functions/".$session;
Session::checkSession_log();
$db = new database();
$error = false;
// registration enable check
$reg_check = true;
$reg_opt_chk_sql = "SELECT * FROM registration WHERE id = 1";
$reg_opt_chk_read = $db->select($reg_opt_chk_sql);
if($reg_opt_chk_read){
  $reg_opt_chk_row = $reg_opt_chk_read->fetch_assoc();
  $reg_action = $reg_opt_chk_row['action'];
  if($reg_action != 'active'){
    header("Location: ".$login_page);
  }
}
function test_input($data) {
    $db = new database();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($db->link, $data);
    return $data;
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
function mailsend($username,$mail,$resettoken,$action){
  $domain = $_SERVER['HTTP_HOST'];
  $domain_protocol= $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
  $from = "account@".$domain;
  global $mail_token_func;
  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers.= "Content-type:text/html;charset=UTF-8" . "\r\n";
  // More headers
  $headers.= 'From: <' . $from . '>' . "\r\n";
  $subject = "Email Verify";
  $message = '<html><body>';
  $message .= '<div style="width:100%;text-align:center;">';
  $message .= "<h3>Hi ".$username."</h3>";
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
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
$dates = new DateTime('now', new DateTimeZone('UTC') ); //php international timezone
$dates = $dates->format('Y-m-d H:i:s'); //formate as 2019-7-23 12:34:12
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if(isset($_POST['register_action'])){
    $fname = test_input($_POST['fname']);
    $fname = strtolower($fname);
    $lname = test_input($_POST['lname']);
    $lname = strtolower($lname);
    $email = test_input($_POST['email']);
    $email = strtolower($email);
    $password = test_input($_POST['pass']);
    $password = strtolower($password);
    $con_password = test_input($_POST['con_pass']);
    $con_password = strtolower($con_password);
    if(strlen($password) < 6){
      $error = true;
      $message = 'Password should be minimum 6 characters';
    }elseif($password == $con_password){
      if(!empty($fname) && !empty($lname) && !empty($email) && !empty($password)){
        $password_md5 = md5($password);
        $user_check = "SELECT id FROM admin WHERE email = '$email'";
        $check_read = $db->insert($user_check);
        $count_user = mysqli_num_rows($check_read);

        if($count_user > 0){
          $error = true;
          $message = 'User already exits';
        }else{
          $token = RandomString(20);
          $user_ip = get_client_ip();
          $user_query = "INSERT INTO admin (fname, lname, email, status, category, password, join_date, user_ip) VALUES ('$fname', '$lname', '$email', 'unverified', 'user', '$password_md5', '$dates', '$user_ip')";
          $user_read = $db->insert($user_query);
          $email_token_check = "SELECT id FROM verify_email WHERE email = '$email'";
          $email_token_read = $db->insert($email_token_check);
          $count_cemail_token = mysqli_num_rows($email_token_read);
          if($count_cemail_token > 0){
            $email_token_row = $email_token_read->fetch_assoc();
            $id = $email_token_row['id'];
            $update_email_token = "UPDATE verify_email SET email = '$email', token = '$token' WHERE id = '$id'";
            $email_token_read = $db->update($update_email_token);
          }else{
            $email_token_query = "INSERT INTO verify_email (email, token) VALUES ('$email', '$token')";
            $email_token_read = $db->insert($email_token_query);
          }
          if ($user_read && $email_token_read) {
              mailsend($fname,$email,$token,'verify_email');
              $action_cat = 'token';
              $message =  "registeration success";
              $submessage = 'please check your email address to confirm.';
              Session::set("action_cat", $action_cat);
              Session::set("action_message", $message);
              Session::set("action_submessage", $submessage);
              header("Location: ".$login_page);
          }else{
            $error = true;
            $message = 'Database connection failed';
          }
        }
      }else{
        $error = true;
        $message = 'Please Fill all input fields';
      }
    }else{
      $error = true;
      $message = 'Password and confirm password does not match';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Register</title>
  <!-- Custom fonts for this template-->
  <link href="../assets/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="../assets/css/sb-admin-2.css" rel="stylesheet">
  <!-- custom css -->
  <link href="../assets/css/style.css" rel="stylesheet">
  <!-- Bootstrap core JavaScript-->
  <script src="../assets/js/jquery.min.js"></script>
  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/chart.js"></script>
</head>
<body class="bg-gradient-primary">
  <div class="container">
      <div class="access_box row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

    <div class="card o-hidden border-0 shadow-lg my-5">
      <div class="card-body p-0">
        <!-- Register - Nested Row within Card Body -->
        <div class="row">
          <div class="col-lg-5 d-none d-lg-block text-center m-auto"> <img src="../assets/app-img/evp_account.jpg" alt="..." class="img-thumbnail"> </div>
          <div class="col-lg-7">
            <div class="p-5">
              <div class="text-center">
                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
              </div>
              <form class="user" action="" method="post">
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="text" name="fname" class="form-control form-control-user" id="exampleFirstName" placeholder="First Name">
                  </div>
                  <div class="col-sm-6">
                    <input type="text" name="lname" class="form-control form-control-user" id="exampleLastName" placeholder="Last Name">
                  </div>
                </div>
                <div class="form-group">
                  <input type="email" name="email" class="form-control form-control-user" id="exampleInputEmail" placeholder="Email Address">
                </div>
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="password" name="pass" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password">
                  </div>
                  <div class="col-sm-6">
                    <input type="password" name="con_pass" class="form-control form-control-user" id="exampleRepeatPassword" placeholder="Repeat Password">
                  </div>
                </div>
                <p class="text-danger"><?php if($error){ echo $message; }?></p>
                <button type="submit" name="register_action" class="btn btn-primary btn-user btn-block">
                  Register Account
                </button>
              </form>
              <hr>
              <div class="text-center">
                <a class="small" href="<?php echo $forgot_password_page?>">Forgot Password?</a>
              </div>
              <div class="text-center">
                <a class="small" href="<?php echo $login_page?>">Already have an account? Login!</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
	      </div>
    </div>
  </div>
  <!-- Custom scripts for all pages-->
  <script src="./assets/js/sb-admin-2.min.js"></script>
</body>
</html>
