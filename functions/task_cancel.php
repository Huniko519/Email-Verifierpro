<?php
include 'pagename.php';
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

$filename = test_input($_POST['filename']);
$user_id = test_input($_POST['uid']);
if(!empty($filename)){
  $delete_task_sql = "SELECT * FROM task WHERE csv_name = '$filename' AND user_id = '$user_id' ";
  $delete_task_read = $db->select($delete_task_sql);
  $count_task = mysqli_num_rows($delete_task_read);
  if ($count_task > 0) {
    $task_delete = "DELETE FROM task WHERE csv_name = '$filename' AND user_id = '$user_id'";
    $read_task_delete = $db->delete($task_delete);
  }
}
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
?>
