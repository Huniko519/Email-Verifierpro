<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
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
  $subject = "Email verify";
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
function dataAdd($domain, $type, $user_id){
  global $db;
  $save = false;
  $type = ucwords($type);
  $catch_all_check = ($type == 'Free Account') ? 0 : 1;
  $domain_check_sql = "SELECT * FROM email_category WHERE user_id = '$user_id' AND name = '$domain' ";
  $domain_check_read = $db->select($domain_check_sql);
  if ($domain_check_read) {
      $count = mysqli_num_rows($domain_check_read);
      if ($count <= 0) {
        $query_insert = "INSERT INTO email_category ( name, e_type, catch_all_check, user_id) VALUES ('$domain','$type','$catch_all_check','$user_id')";
        $read_insert = $db->insert($query_insert);
        if ($read_insert) {
          $save = true;
        }
      }
    }
    return $save;
}
function dataUpdate($domain, $type, $target_id, $user_id){
  global $db;
  $update = false;
  $type = ucwords($type);
  $catch_all_check = ($type == 'Free Account') ? 0 : 1;
  $data_update_sql = "UPDATE email_category SET name = '$domain', e_type = '$type', catch_all_check = '$catch_all_check' WHERE user_id = '$user_id' AND id = '$target_id' ";
  $data_update_read = $db->update($data_update_sql);
  if ($data_update_read) {
      $update = true;
  }
  return $update;
}

$error = false;
$message = '';
$action_std = false;
$action_cat = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['name_btn'])) { //admin send mail range and time update
            $action_std = true;
            $action_cat = 'name';
            $user_id = test_input($_POST['user_id']);
            $fname = test_input($_POST['fname']);
            $lname = test_input($_POST['lname']);
            if(!empty($fname) && !empty($lname) && !empty($user_id)){
              $data_update_sql = "UPDATE admin SET fname = '$fname', lname = '$lname' WHERE id = '$user_id' ";
              $data_update_read = $db->update($data_update_sql);
              if ($data_update_read) {
                $error = false;
                $message = 'Name change successfull';
              }else{
                $error = true;
                $message = 'Databse connection error!';
              }
            }else{
              $error = true;
              $message = 'Data not found!';
            }
        }elseif (isset($_POST['image_btn'])) {
          $action_std = true;
          $action_cat = 'name';
          $user_id = test_input($_POST['user_id']);
          // ------------------------------------------------------------
          $img_url= test_input($_FILES['img_url']['name']);
          if(!empty($img_url)){
            // ---img functions---
            // Get Image Dimension
            $fileinfo = @getimagesize($_FILES["img_url"]["tmp_name"]);
            $width = $fileinfo[0];
            $height = $fileinfo[1];
            $allowed_image_extension = array(
                "png",
                "jpg",
                "jpeg"
            );
            // Get image file extension
            $file_extension = pathinfo($_FILES["img_url"]["name"], PATHINFO_EXTENSION);
            $file_extension = strtolower($file_extension);
            // Validate file input to check if is not empty
            if (! file_exists($_FILES["img_url"]["tmp_name"])) {
                  $error = true;
                  $message = "Choose image file to upload.";

            }    // Validate file input to check if is with valid extension
            else if (! in_array($file_extension, $allowed_image_extension)) {
                    $error = true;
                    $message = "Upload valiid images. Only PNG, JPG and JPEG are allowed.";
            }    // Validate image file size
            else if (($_FILES["img_url"]["size"] > 500000)) {

              $error = true;
              $message = "Image size exceeds 500KB";

            }else {
                 $img_url = $user_id.".".$file_extension;
                 $admin_query = "SELECT * FROM admin WHERE id = '$user_id'";
                 $admin_read = $db->select($admin_query);
                 if ($admin_read) {
                     $count_job = mysqli_num_rows($admin_read);
                     if($count_job > 0){
                       $worker_row = $admin_read->fetch_assoc();
                       $user_image_url = $worker_row['image'];
                       $user_name = $worker_row['fname'];
                       $img_url = $user_name.'_'.$img_url;
                       $target = "../uploads/" .$img_url;
                       if(!empty($user_image_url) && $user_image_url != 0){
                         unlink('../uploads/'.$user_image_url);
                       }
                       if (move_uploaded_file($_FILES["img_url"]["tmp_name"], $target)) {
                         $user_update_sql = "UPDATE admin SET image = '$img_url' WHERE id = '$user_id' ";
                         $user_insert_read = $db -> update($user_update_sql);
                         if ($user_insert_read) {
                           $error = false;
                           $message = "Image uploaded successfully.";
                         }else{
                           $error = true;
                           $message = "Problem in uploading database files.";
                         }

                       } else {

                          $error = true;
                          $message = "Problem in uploading image files.";

                       }
                     }
                   }
            }
          }else{
            $error = true;
            $message = "No Image found";
          }
          // -------------------------------------------------------------
        }elseif (isset($_POST['pass_btn'])) {
          $action_std = true;
          $action_cat = 'name';
          $user_id = test_input($_POST['user_id']);
          $current_pass = test_input($_POST['current_pass']);
          $new_pass = test_input($_POST['new_pass']);
          $con_pass = test_input($_POST['con_pass']);
          $current_pass = md5($current_pass);
          if(strlen($new_pass) < 6){
            $error = true;
            $message = 'Password should be minimum 6 characters';
          }elseif($new_pass == $con_pass){
            $pass_check_sql = "SELECT * FROM admin WHERE id = '$user_id' AND password = '$current_pass'";
            $pass_check_read = $db->select($pass_check_sql);
            if ($pass_check_sql) {
              $count_user = mysqli_num_rows($pass_check_read);
              if($count_user > 0){
                $new_pass = md5($new_pass);
                $user_update_sql = "UPDATE admin SET password = '$new_pass' WHERE id = '$user_id' ";
                $user_insert_read = $db -> update($user_update_sql);
                if ($user_insert_read) {
                  $error = false;
                  $message = "Password change successfully.";
                }else{
                  $error = true;
                  $message = "Database connection error!";
                }
              }else{
                $error = true;
                $message = "Wrong password!";
              }
            }else{
              $error = true;
              $message = "Database connection error!";
            }
          }else{
            $error = true;
            $message = "confirm password not match";
          }
        }elseif (isset($_POST['email_btn'])) {
          $action_std = true;
          $action_cat = 'name';
          $user_id = test_input($_POST['user_id']);
          $email = test_input($_POST['email']);
          $token = RandomString(20);
          if(!empty($user_id) && !empty($email)){
            $same_email_check_sql = "SELECT * FROM admin WHERE id = '$user_id' AND email = '$email'";
            $same_email_check_read = $db->select($same_email_check_sql);

            if ($same_email_check_read) {
              $same_email_check_count = mysqli_num_rows($same_email_check_read);

              if($same_email_check_count <= 0){
                $exits_email_check_sql = "SELECT * FROM admin WHERE email = '$email'";
                $exits_email_check_read = $db->select($exits_email_check_sql);

                if ($exits_email_check_read) {
                  $exits_email_check_count = mysqli_num_rows($exits_email_check_read);

                  if($exits_email_check_count <= 0){
                    $check_request_mail_sql = "SELECT * FROM email_change WHERE email = '$email'";
                    $check_request_mail_read = $db->select($check_request_mail_sql);

                    if ($check_request_mail_read) {
                      $check_request_mail_count = mysqli_num_rows($check_request_mail_read);

                      if($check_request_mail_count <= 0){
                        $exits_req_check_sql = "SELECT * FROM email_change WHERE user_id = '$user_id'";
                        $exits_req_check_read = $db->select($exits_req_check_sql);

                        if ($exits_req_check_read) {
                          $exits_req_check_count = mysqli_num_rows($exits_req_check_read);

                          if($exits_req_check_count <= 0){
                            $isert_email_request_sql = "INSERT INTO email_change (user_id, email, token) VALUES ('$user_id', '$email', '$token')";
                            $isert_email_request_read = $db -> insert($isert_email_request_sql);

                            if ($isert_email_request_read) {
                              mailsend($email,$token,'update_email');
                              $error = false;
                              $message = "A verification mail send on your requested email! please check your email.";
                            }else{
                              $error = true;
                              $message = "Database connection error!";
                            }
                          }else{
                            $user_update_sql = "UPDATE email_change SET email = '$email', token = '$token' WHERE user_id = '$user_id' ";
                            $user_insert_read = $db -> update($user_update_sql);
                            if ($user_insert_read) {
                              mailsend($email,$token,'update_email');
                              $error = false;
                              $message = "Change requested mail. please check your requested mail inbox";
                            }else{
                              $error = true;
                              $message = "Database connection error!";
                            }
                          }
                        }else{
                          $error = true;
                          $message = "Database Connection error!";
                        }
                      }else{
                        $error = true;
                        $message = "This email is already requested to update";
                      }
                    }else{
                      $error = true;
                      $message = "Database Connection error!";
                    }
                  }else{
                    $error = true;
                    $message = "this email is already exits.";
                  }
                }else{
                  $error = true;
                  $message = "Database Connection error!";
                }
              }else{
                $req_delete_sql = "DELETE FROM email_change WHERE user_id = '$user_id'";
                $req_delete_read = $db -> delete($req_delete_sql);
                if ($req_delete_read) {
                  $error = false;
                  $message = "Cancel mail change request.";
                }else{
                  $error = true;
                  $message = "Database connection error!";
                }
              }
            }else{
              $error = true;
              $message = "Database Connection error!";
            }
          }else{
            $error = true;
            $message = "No data found";
          }
        }elseif (isset($_POST['email_change_remove_btn'])) {
          $action_std = true;
          $action_cat = 'name';
          $user_id = test_input($_POST['user_id']);
          if(!empty($user_id)){
            $req_delete_sql = "DELETE FROM email_change WHERE user_id = '$user_id'";
            $req_delete_read = $db -> delete($req_delete_sql);
            if ($req_delete_read) {
              $error = false;
              $message = "Cancel mail change request.";
            }else{
              $error = true;
              $message = "Database connection error!";
            }
          }else{
            $error = true;
            $message = "No data found";
          }
        }elseif (isset($_POST['email_change_mail_btn'])) {
          $action_std = true;
          $action_cat = 'name';
          $user_id = test_input($_POST['user_id']);
          if(!empty($user_id)){
            $exits_req_check_sql = "SELECT * FROM email_change WHERE user_id = '$user_id'";
            $exits_req_check_read = $db->select($exits_req_check_sql);
            if ($exits_req_check_read) {
              $exits_req_check_count = mysqli_num_rows($exits_req_check_read);
              if($exits_req_check_count > 0){
                $req_data = $exits_req_check_read->fetch_assoc();
                $req_email = $req_data['email'];
                $req_token = $req_data['token'];
                mailsend($req_email,$req_token,'update_email');
                $error = false;
                $message = "Verification sms resend";
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
          Session::set("action_cat", $action_cat);
          Session::set("action", $error);
          Session::set("action_message", $message);
          header("Location: ../app/".$profile_page);
        }else{
          header("Location: ../app/".$error_404_page);
        }
      }else{
        header("Location: ../app/".$error_404_page);
      }
?>