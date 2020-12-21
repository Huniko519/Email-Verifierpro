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
        if (isset($_POST['timer_btn'])) { //admin send mail range and time update
            $action_std = true;
            $action_cat = 'send_mail';
            $range = test_input($_POST['range']);
            $user_id = test_input($_POST['user_id']);
            $time_range = test_input($_POST['time_range']);
            $time_range = (int)$time_range;
            echo $time_range;
            $time_range = $time_range * 60;
            $query2 = "SELECT * FROM timer WHERE user_id = '$user_id' ";
            $read2 = $db->select($query2);
            if ($read2) {
                $count = mysqli_num_rows($read2);
                if ($count > 0) {
                    $query18 = "UPDATE timer SET e_range = '$range', time_range = '$time_range' WHERE user_id = '$user_id' ";
                    $read18 = $db->update($query18);
                    if ($read18) {
                        $error = false;
                        $message = 'Update successfully';
                    }else{
                      $error = true;
                      $message = 'Database Connection Error';
                    }
                } else {
                    $query_insert = "INSERT INTO timer ( user_id, e_range, time_range, 	last_send)
          VALUES ('$user_id','$range','$time_range','0')";
                    $read_insert = $db->insert($query_insert);
                    if ($read_insert) {
                      $error = false;
                      $message = 'Insert successfully';
                    }else{
                      $error = false;
                      $message = 'Database Connection Error';
                    }
                }
            }else{
              $error = false;
              $message = 'Database Connection Error';
            }
        }elseif (isset($_POST['save_button'])) {
          $action_std = true;
          $action_cat = 'save_mail';
          $listing_value = $_POST['listing_value'];
          $user_id = test_input($_POST['user_id']);
          $std = false;
          $correct = 0;
          $wrong = 0;
          foreach($listing_value as $value) {
            $value = test_input($value);
            $domain = 'name_'.$value;
            $type = 'type_'.$value;
            $domain =  test_input($_POST[$domain]);
            $type =  test_input($_POST[$type]);
            if(!empty($domain) && !empty($type) && !empty($user_id)){
              $std = true;
              $result = dataAdd($domain, $type, $user_id);
              if($result){
                $correct++;
              }else{
                $wrong++;
              }
            }else{
              $wrong++;
            }
          }
          if($std){
            $error = ($correct > 0) ? false : true ;
            $message = (($correct > 0) ? $correct.' data save successfully, ' : '').(($wrong > 0) ? $wrong.' data not save!' : '');

          }else{
            $error = true;
            $message = 'No Data Found';
          }
        }elseif (isset($_POST['estimated_cost_btn'])) {
          $action_std = true;
          $action_cat = 'estimated_cost';
          $estimated_cost = test_input($_POST['estimated_cost']);
          if(!empty($estimated_cost) && is_numeric($estimated_cost)){
            $logo_title_query = "SELECT * FROM logo_title";
            $logo_title_read = $db->select($logo_title_query);
            if ($logo_title_read) {
                $logo_title_count = mysqli_num_rows($logo_title_read);
                if($logo_title_count > 0){
                    $logo_update_sql = "UPDATE logo_title SET estimated_cost = '$estimated_cost' ";
                }else{
                    $logo_update_sql = "INSERT INTO logo_title (estimated_cost) VALUES ('$estimated_cost') ";
                }
                $logo_update_read = $db -> update($logo_update_sql);
                if ($logo_update_read) {
                  $error = false;
                  $message = "Update successfully.";
                }else{
                  $error = true;
                  $message = "Database connection failed";
                }
              }else{
                $error = true;
                $message = "Database connection failed";
              }

          }else{
            $error = true;
            $message = "Data not found";
          }


          // ----------------------------------------------------------------------------------------------------logo_title-----------------
        }elseif (isset($_POST['scan_mail_settings'])) {
          $action_std = true;
          $action_cat = 'scan_mail_settings';
          $scan_from = test_input($_POST['scan_from']);
          $timeout = test_input($_POST['timeout']);
          if(!empty($scan_from) && !empty($timeout)){
            $logo_title_query = "SELECT * FROM logo_title";
            $logo_title_read = $db->select($logo_title_query);
            if ($logo_title_read) {
                $logo_title_count = mysqli_num_rows($logo_title_read);
                if($logo_title_count > 0){
                    $logo_update_sql = "UPDATE logo_title SET scan_time_out = '$timeout',scan_mail = '$scan_from' ";
                }else{
                    $logo_update_sql = "INSERT INTO logo_title (scan_time_out, scan_mail) VALUES ('$timeout', '$scan_from') ";
                }
                $logo_update_read = $db -> update($logo_update_sql);
                if ($logo_update_read) {
                  $error = false;
                  $message = "Update successfully.";
                }else{
                  $error = true;
                  $message = "Database connection failed";
                }
              }else{
                $error = true;
                $message = "Database connection failed";
              }

          }else{
            $error = true;
            $message = "Data not found";
          }


          // ----------------------------------------------------------------------------------------------------logo_title-----------------
        }elseif (isset($_POST['logo_title_change'])) {
          $action_std = true;
          $action_cat = 'logo_site';
          $logo_exits = false;
          $site_title = test_input($_POST['site_title']);
          $logo= test_input($_FILES['logo_image']['name']);
          if(!empty($logo)){
            $logo_exits = true;
            // ---img functions---
            // Get Image Dimension
            $fileinfo = @getimagesize($_FILES["logo_image"]["tmp_name"]);
            $width = $fileinfo[0];
            $height = $fileinfo[1];
            $allowed_image_extension = array(
                "png",
                "jpg",
                "jpeg"
            );
            // Get image file extension
            $file_extension = pathinfo($_FILES["logo_image"]["name"], PATHINFO_EXTENSION);
            $file_extension = strtolower($file_extension);
            // Validate file input to check if is not empty
            if (! file_exists($_FILES["logo_image"]["tmp_name"])) {
                  $error = true;
                  $message = "Choose image file to upload.";

            }    // Validate file input to check if is with valid extension
            else if (! in_array($file_extension, $allowed_image_extension)) {
                    $error = true;
                    $message = "Upload valiid images. Only PNG, JPG and JPEG are allowed.";
            }    // Validate image file size
            else if (($_FILES["logo_image"]["size"] > 500000)) {

              $error = true;
              $message = "Image size exceeds 500KB";

            }else {
                 $logo = "logo.".$file_extension;
                 $target = "../assets/app-img/" .$logo;
                 unlink($target);
                 if (move_uploaded_file($_FILES["logo_image"]["tmp_name"], $target)) {
                   $error = false;
                   $message = "Image uploaded successfully.";

                 } else {

                    $error = true;
                    $message = "Problem in uploading image files.";

                 }

            }
          }
          if(!$error){
            $logo_title_query = "SELECT * FROM logo_title";
            $logo_title_read = $db->select($logo_title_query);
            if ($logo_title_read) {
                $logo_title_count = mysqli_num_rows($logo_title_read);
                if($logo_title_count > 0){
                  if($logo_exits){
                    $logo_update_sql = "UPDATE logo_title SET logo = '$logo',site_title = '$site_title' ";
                  }else{
                    $logo_update_sql = "UPDATE logo_title SET site_title = '$site_title' ";
                  }
                }else{
                  if($logo_exits){
                    $logo_update_sql = "INSERT INTO logo_title (logo, site_title) VALUES ('$logo', '$site_title') ";
                  }else{
                    $logo_update_sql = "INSERT INTO logo_title (site_title) VALUES ('$site_title') ";
                  }
                }
              }


            $logo_update_read = $db -> update($logo_update_sql);
            if ($logo_update_read) {
              $error = false;
              $message = "Update successfully.";
            }else{
              $error = true;
              $message = "Problem in uploading database files.";
            }
          }else{

          }
          // ----------------------------------------------------------------------------------------------------logo_title-----------------
        }elseif (isset($_POST['edit_button'])) {
          $action_std = true;
          $action_cat = 'save_mail';
          $user_id = test_input($_POST['user_id']);
          $target_id = test_input($_POST['target_id']);
          $name = test_input($_POST['name']);
          $type = test_input($_POST['type']);
          if(!empty($name) && !empty($type) && !empty($target_id) && !empty($user_id)){
            $result = dataUpdate($name, $type,$target_id, $user_id);
            if($result){
              $error = false;
              $message = 'Update successfull';
            }else{
              $error = true;
              $message = 'Update is not successfull';
            }

          }else{
            $error = true;
            $message = 'Data Not Found';
          }
        }elseif (isset($_POST['registration_btn'])) {
          $action_std = true;
          $action_cat = 'registration';
          $user_id = test_input($_POST['user_id']);
          $registration = test_input($_POST['registration']);
          $error = true;
          $message = 'Something is wrong!';
          if(!empty($user_id) && !empty($registration) && ($registration == 'off' || $registration == 'active')){
            $admin_check_sql = "SELECT * FROM admin WHERE id = '$user_id' ";
            $admin_check_read = $db->select($admin_check_sql);
            if ($admin_check_read) {
              $admin_check_count = mysqli_num_rows($admin_check_read);
              if ($admin_check_count > 0) {
                $admin_check_row = $admin_check_read->fetch_assoc();
                $admin_category = $admin_check_row['category'];
                if ($admin_category == 'admin') {
                  $registration_check_sql = "SELECT * FROM registration WHERE id = 1 ";
                  $registration_check_read = $db->select($registration_check_sql);
                  if ($registration_check_read) {
                    $registration_check_count = mysqli_num_rows($registration_check_read);
                    if ($registration_check_count > 0) {
                      $update_registration_sql = "UPDATE registration SET action = '$registration' WHERE id = '$user_id' ";
                      $update_registration_read = $db->update($update_registration_sql);
                      if($update_registration_read){
                        $error = false;
                        $message = 'registraion status update successfully';
                      }else{
                        $error = true;
                        $message = 'Database connection failed';
                      }
                    }else{
                      $update_registration_sql = "INSERT INTO registration (action) VALUES ('$registration')";
                      $update_registration_read = $db->update($update_registration_sql);
                      if($update_registration_read){
                        $error = false;
                        $message = 'registraion status update successfully';
                      }else{
                        $error = true;
                        $message = 'Database connection failed';
                      }
                    }
                  }

                }
              }else{

              }
            }
          }else{
            $error = true;
            $message = 'Data Not Found';
          }
        }elseif (isset($_POST['delete-btn'])) {
          $action_std = true;
          $action_cat = 'save_mail';
          $user_id = test_input($_POST['user_id']);
          $target_id = test_input($_POST['target_id']);
          if(!empty($user_id) && !empty($target_id)){
            $delete_data_sql = "DELETE FROM email_category WHERE user_id = '$user_id' AND id = '$target_id' ";
            $delete_data_read = $db->delete($delete_data_sql);
            if($delete_data_read){
              $error = false;
              $message = 'Delete successfull';
            }else{
              $error = true;
              $message = 'Database connection failed';
            }
          }else{
            $error = true;
            $message = 'Data Not Found';
          }

        }else{
          header("Location: ../app/".$error_404_page);
        }
        if($action_std && !empty($action_cat)){
          Session::set("action_cat", $action_cat);
          Session::set("action", $error);
          Session::set("action_message", $message);
          header("Location: ../app/".$settings_page);
        }else{
          header("Location: ../app/".$error_404_page);
        }
      }else{
        header("Location: ../app/".$error_404_page);
      }
?>