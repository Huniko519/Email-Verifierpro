<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
include '../config/'.$config;
include '../config/'.$database;
$db = new database();

$filename = $_POST['filename'];
$user_id = $_POST['uid'];
if(!empty($filename)){
  $delete_task_sql = "SELECT * FROM user_email_list WHERE csv_file_name = '$filename' AND user_id = '$user_id' ";
  $delete_task_read = $db->select($delete_task_sql);
  $count_task = mysqli_num_rows($delete_task_read);
  if ($count_task > 0) {
    $task_delete = "DELETE FROM user_email_list WHERE csv_file_name = '$filename' AND user_id = '$user_id'";
    $read_task_delete = $db->delete($task_delete);
    $delete_task_sql = "SELECT * FROM task WHERE csv_name = '$filename' AND user_id = '$user_id' ";
    $delete_task_read = $db->select($delete_task_sql);
    $count_task = mysqli_num_rows($delete_task_read);
    if ($count_task > 0) {
      $task_delete = "DELETE FROM task WHERE csv_name = '$filename' AND user_id = '$user_id'";
      $read_task_delete = $db->delete($task_delete);
    }
  }
}
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
?>