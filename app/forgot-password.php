<?php
include '../functions/pagename.php';
include '../functions/'.$session;
Session::checkSession_log();
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

// registration enable check
$reg_check = false;
$reg_opt_chk_sql = "SELECT * FROM registration WHERE id = 1";
$reg_opt_chk_read = $db->select($reg_opt_chk_sql);
if($reg_opt_chk_read){
  $reg_opt_chk_row = $reg_opt_chk_read->fetch_assoc();
  $reg_action = $reg_opt_chk_row['action'];
  if($reg_action == 'active'){
    $reg_check = true;
  }
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

function mailsend($username,$mail,$resettoken){
  $domain = $_SERVER['HTTP_HOST'];
  $domain_protocol= $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
  $from = "account@".$domain;
  global $forgot_password_page;
  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers.= "Content-type:text/html;charset=UTF-8" . "\r\n";
  // More headers
  $headers.= 'From: <' . $from . '>' . "\r\n";
  $subject = "Reset Password";
  $message = '<html><body>';
  $message .= '<div style="width:100%;text-align:center;">';
  $message .= "<h3>Hi ".$username."</h3>";
  $message .= "<p>EMail Verifier Pro has received a request to reset the password for your account.</p>";
  $message .= "<p>If you did not request to reset your password, please ignore this email.</p>";
  $message .= "<p>For Reset Password</p>";
  $link = $domain_protocol."://".$domain."/app/".$forgot_password_page."?resettoken=".$resettoken."&email=".$mail;
  $message .= "<div>click below</div>";
  $message .= "<div style='border-radius: 10px;cursor:pointer;background:#F7C9C9; display:inline-block;padding:10px;'>".$link."</div>";
  $message .= "</div>";
  $message .= "</body></html>";
  if (mail($mail, $subject, $message, $headers)) {
    return true;
  }
  return false;
}

$reset_token_check = false;
$resetpass_btn = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if(isset($_POST['resetpass-btn'])){
    $email = test_input($_POST['email']);
    $query = "SELECT * FROM admin WHERE email = '$email'";
    $read = $db->select($query);
    $reset_error = true;
    $reset_error_status = 'Something is wrong! Can not send mail.';
    if ($read != false) {
      $value = mysqli_fetch_array($read);
      $row = mysqli_num_rows($read);
      if ($row > 0) {
        $username = $value['fname'];
        $user_id = $value['id'];
        $reset_token  = RandomString(20);
        $check_token_query = "SELECT * FROM reset_pass WHERE email = '$email'";
        $check_token_read = $db->select($check_token_query);
        if ($check_token_read != false) {
          $token_row = mysqli_num_rows($check_token_read);
          if($token_row >0 ){
            $token_update_query = "UPDATE reset_pass SET user_id = '$user_id', token = '$reset_token' WHERE email = '$email' ";
            $token_update_read = $db->update($token_update_query);
            if ($token_update_read) {
              if(mailsend($username,$email,$reset_token)){
                $reset_error = false;
                $message = 'Mail Send';
                $submessage = "Password reset link have send on your email.";
              }
            }
          }else{
            $token_store_query = "INSERT INTO reset_pass (email, token, user_id) VALUES ('$email','$reset_token', '$user_id')";
            $write_token_store = $db->insert($token_store_query);
            if ($write_token_store) {
              if(mailsend($username,$email,$reset_token)){
                $reset_error = false;
                $message = 'Mail Send';
                $submessage = "Password reset link have send on your email.";
              }
            }
          }
        }
      }else{
        $reset_error_status = 'User Not exits';
      }
    }
    if($reset_error){
      $resetpass_btn = true;
    }else{
      $action_cat = 'token';
      Session::set("action_cat", $action_cat);
      Session::set("action_message", $message);
      Session::set("action_submessage", $submessage);
      header("Location: ".$login_page);
    }
  }elseif (isset($_POST['changepass-btn'])) {
    $email = test_input($_POST['email']);
    $token = test_input($_POST['token']);
    $user_id = test_input($_POST['user_id']);
    $new_pass = test_input($_POST['new_pass']);
    $con_pass = test_input($_POST['con_pass']);
    $query = "SELECT * FROM reset_pass WHERE email = '$email' AND user_id = '$user_id' AND token = '$token' ";
    $read = $db->select($query);
    $reset_error = true;
    $reset_error_status = 'Something is wrong! Please try again';
    if($new_pass == $con_pass){
      if ($read != false) {
        $value = mysqli_fetch_array($read);
        $row = mysqli_num_rows($read);
        if ($row > 0) {
          $check_token_query = "SELECT * FROM admin WHERE id = '$user_id'";
          $check_token_read = $db->select($check_token_query);
          if ($check_token_read != false) {
            $token_row = mysqli_num_rows($check_token_read);
            if($token_row >0 ){
              $new_pass = md5($new_pass);
              $token_update_query = "UPDATE admin SET password = '$new_pass' WHERE id = '$user_id' ";
              $token_update_read = $db->update($token_update_query);
              $delete_reset_query = "DELETE FROM reset_pass WHERE user_id = '$user_id' ";
              $delete_reset_read = $db->update($delete_reset_query);
              if ($token_update_read) {
                  $reset_error = false;
                  $message = 'Password change successful';
                  $submessage = "Please login now";
              }
            }
          }
        }else{
          $reset_error_status = 'User Not exits';
        }
      }
    }else{
      $reset_error_status = 'Confirm password do not match!';
      $reset_token_check = true;
      $confirm_token = $token;
      $confirm_email = $email;
      $current_user_id = $user_id;
    }
    if($reset_error){
      $resetpass_btn = true;
    }else{
      $action_cat = 'token';
      Session::set("action_cat", $action_cat);
      Session::set("action_message", $message);
      Session::set("action_submessage", $submessage);
      header("Location: ".$login_page);
    }
  }
}elseif(isset($_GET['resettoken']) && isset($_GET['email'])){
  $confirm_token = test_input($_GET['resettoken']);
  $confirm_email = test_input($_GET['email']);
  $confirm_token_email = "SELECT * FROM reset_pass WHERE email = '$confirm_email' AND token = '$confirm_token' ";
  $confirm_token_read = $db->select($confirm_token_email);
  if ($confirm_token_read != false) {
    $confirm_token_row = mysqli_num_rows($confirm_token_read);
    if($confirm_token_row >0 ){
      $confirm_token_row = $confirm_token_read->fetch_assoc();
      $current_user_id = $confirm_token_row['user_id'];
      $reset_token_check = true;
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
  <title>Forgot Password</title>
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
    <!-- Password Reset - Outer Row -->
    <div class="access_box row justify-content-center">
      <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block text-center m-auto"> <img src="../assets/app-img/evp_fpass.jpg" alt="..." class="img-thumbnail"> </div>
              <div class="col-lg-6">
                <div class="p-5">
                  <?php if($reset_token_check){ ?>
                    <div class="text-center">
                      <h1 class="h4 text-gray-900 mb-4">Change Password</h1>
                    </div>
                    <form class="user" action="<?php echo $forgot_password_page;?>" method="post">
                      <input required hidden type="email" name="email" value="<?php echo $confirm_email;?>">
                      <input required hidden type="text" name="token" value="<?php echo $confirm_token;?>">
                      <input required hidden type="text" name="user_id" value="<?php echo $current_user_id;?>">
                      <div class="form-group">
                        <input type="password" name="new_pass" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter New Password...">
                      </div>
                      <div class="form-group">
                        <input type="password" name="con_pass" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Confirm Password...">
                      </div>
                      <?php if($resetpass_btn){
                        echo '<p class="text-danger">'.$reset_error_status.'</p>';
                      }?>
                      <button type="submit" name="changepass-btn" class="btn btn-primary btn-user btn-block">
                        Save
                      </button>
                    </form>
                  <?php }else{ ?>
                    <div class="text-center">
                      <h1 class="h4 text-gray-900 mb-2">Forgot Your Password?</h1>
                      <p class="mb-4">We get it, stuff happens. Just enter your email address below and we'll send you a link to reset your password!</p>
                    </div>
                    <form class="user" action="<?php echo $forgot_password_page;?>" method="post">
                      <div class="form-group">
                        <input type="email" name="email" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address...">
                      </div>
                      <?php if($resetpass_btn){
                        echo '<p class="text-danger">'.$reset_error_status.'</p>';
                      }?>
                      <button type="submit" name="resetpass-btn" class="btn btn-primary btn-user btn-block">
                        Reset Password
                      </button>
                    </form>
                  <?php }?>

                  <hr>
                  <?php if($reg_check){?>
                    <div class="text-center">
                      <a class="small" href="<?php echo $register_page;?>">Create an Account!</a>
                    </div>
                  <?php }?>
                  <div class="text-center">
                    <a class="small" href="<?php echo $login_page;?>">Already have an account? Login!</a>
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
  <script src="../assets/js/sb-admin-2.min.js"></script>
</body>
</html>