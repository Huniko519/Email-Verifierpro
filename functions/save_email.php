<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
include '../config/'.$config;
include '../config/'.$database;
$db = new database();
set_time_limit(0);
function test_input($data) { //filter value function
    $db = new database();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($db->link, $data);
    return $data;
}
$array_data = $_POST['email'];
$filename = test_input($_POST['filename']);
$user_id = test_input($_POST['uid']);
$data_arr = explode (",", $array_data);
$em_status = "Not Verify";
$dt = date("Y-m-d h:i:s");
$duplicate = 0;
$unsave = 0;
$save = 0;
$total = count($data_arr);
$str_arr = array_unique($data_arr);
$count_after_uniq = count($str_arr);
$duplicate = $total - $count_after_uniq;
$coma_check = false;
$save_data_sql = "INSERT INTO user_email_list (csv_file_name,email_name,email_status,create_date,user_id) values";
foreach ($str_arr as $value) {
  $email = test_input($value);
  if(!empty($email)){
    $save++;
    if($coma_check){
      $save_data_sql .= ',';
    }else{

    }
    $coma_check = true;
    $save_data_sql .=" ('$filename','$email','$em_status','$dt','$user_id')";
  }
}

if($coma_check){
      $save_data_read = $db->insert($save_data_sql);
}
$end_dt = date("Y-m-d h:i:s");
$unsave = $total - ($save + $duplicate);
echo json_encode(['save' => $save, 'total' => $total, 'unsave' => $unsave, 'duplicate' => $duplicate , 'end_time' => $end_dt, 'start_time' => $dt]);
exit;
?>