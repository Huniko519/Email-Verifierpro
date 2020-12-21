<?php
include 'pagename.php';
include '../config/'.$config;
include '../config/'.$database;
$db = new database();
if(isset($_POST['filename']) && isset($_POST['uid'])){
  $csv_file_name = $_POST['filename'];
  $user_id = $_POST['uid'];
  $verify_per_status = 0;
  $csv_file_info = "SELECT COUNT(email_name) as t_email, MIN(create_date) as create_time,
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'valid' AND user_id = '$user_id') AS 'count_valid',
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'invalid' AND user_id = '$user_id') AS 'count_invalid',
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'catch all' AND user_id = '$user_id') AS 'count_catchall',
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'unknown' AND user_id = '$user_id') AS 'count_unknown',
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'Not Verify' AND user_id = '$user_id') AS 'count_not_verify',
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND verification_response = 'syntax error' AND user_id = '$user_id') AS 'count_syntax',
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_type = 'Free Account' AND user_id = '$user_id') AS 'count_free',
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_type = 'Role Account' AND user_id = '$user_id') AS 'count_role',
  (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_type = 'Disposable Account' AND user_id = '$user_id') AS 'count_disposable'
   FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND user_id = '$user_id' ";
  $csv_info = $db->select($csv_file_info);
  $count_check = mysqli_num_rows($csv_info);
  if ($count_check > 0) {
    $csv_result = $csv_info->fetch_assoc();
    $t_email = $csv_result['t_email'];
    $count_valid = $csv_result['count_valid'];
    $count_invalid = $csv_result['count_invalid'];
    $count_catchall = $csv_result['count_catchall'];
    $count_unknown = $csv_result['count_unknown'];
    $csv_name_ex = preg_replace('/\\.[^.\\s]{3,4}$/', '', $csv_file_name);
    $total_check_validation = $count_valid + $count_invalid + $count_catchall + $count_unknown;
    $verify_per_status = ceil((($count_valid + $count_invalid + $count_catchall + $count_unknown) / $t_email) * 100);
  }
  echo json_encode(['percent' => $verify_per_status, 'total_verify' => $total_check_validation]);
}
?>
