<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
include '../config/'.$config;
include '../config/'.$database;
$db = new database();

function test_input($data) {
    $db = new database();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strtolower($data);
    $data = mysqli_real_escape_string($db->link, $data);
    return $data;
}
if(isset($_POST['filename']) && isset($_POST['user_id'])){
  $or_std = false;
  $all_std = false;
  $and_std = false;
  $filename = $_POST['filename'];
  $user_id = $_POST['user_id'];
  $sql = "SELECT email_name, email_status, safe_to_send, verification_response, score, bounce_type, email_type, email_acc, email_dom FROM user_email_list WHERE ";
  if(!empty($_POST['all'])){
    $all_std = true;
  }else{
    if(!empty($_POST['valid'])){
      if($or_std){
        $sql .= "OR ";
      }else{
        $sql .= "( ";
      }
      $sql .= "email_status = 'valid' ";
      $or_std = true;
    }
    if(!empty($_POST['invalid'])){
      if($or_std){
        $sql .= "OR ";
      }else{
        $sql .= "( ";
      }
      $sql .= "email_status = 'invalid' OR email_status = 'unknown' ";
      $or_std = true;
    }
    if(!empty($_POST['catchall'])){
      if($or_std){
        $sql .= "OR ";
      }else{
        $sql .= "( ";
      }
      $sql .= "email_status = 'catch all' ";
      $or_std = true;
    }
    if(!empty($_POST['free'])){
      if($or_std && !$and_std){
        $sql .= ") AND ";
      }
      if($and_std){
        $sql .= "OR ";
      }else{
        $sql .= "( ";
      }
      $sql .= "email_type = 'Free Account' ";
      $and_std = true;
    }
    if(!empty($_POST['role'])){
      if($or_std && !$and_std){
        $sql .= ") AND ";
      }
      if($and_std){
        $sql .= "OR ";
      }else{
        $sql .= "( ";
      }
      $sql .= "email_type = 'Role Account' ";
      $and_std = true;
    }
    if(!empty($_POST['disposable'])){
      if($or_std && !$and_std){
        $sql .= ") AND ";
      }
      if($and_std){
        $sql .= "OR ";
      }else{
        $sql .= "( ";
      }
      $sql .= "email_type = 'Disposable Account' ";
      $and_std = true;
    }
    if(!empty($_POST['syntax'])){
      if($or_std && !$and_std){
        $sql .= ") AND ";
      }
      if($and_std){
        $sql .= "OR ";
      }else{
        $sql .= "( ";
      }
      $sql .= "verification_response = 'syntax error' ";
      $and_std = true;
    }
  }
  if($or_std || $and_std){
    $sql .= ") AND ";
  }
  $sql .= "csv_file_name = '".$filename."' AND user_id = '".$user_id."' ";
  if($all_std || $or_std || $and_std){
    $read22 = $db->select($sql);
    if ($read22) {
        $fn = "csv_" . uniqid() . ".csv"; //make a uniq id for csv file;
        header('Content-type:text/csv;charset=utf-8'); //declear content-type;
        header('Content-Disposition: attachment; filename=' . $fn); //assign file name to csv file;
        echo "\xEF\xBB\xBF"; // BOM header UTF-8
        $file = fopen("php://output", "w"); //write to csv file;
        ob_clean();
        fputcsv($file, array('Email', 'Verification Status', 'Safe to send', 'Verification Response', 'Score', 'Bounce type', 'Account Type', 'Email Account', 'Email Domain')); //headers name
        while ($row22 = $read22->fetch_assoc()) { //write data;
            fputcsv($file, $row22);
        }
        fclose($file); //close file;
    }
  }else{
    header("Location: ../app/".$error_404_page);
  }
}else{
  header("Location: ../app/".$error_404_page);
}
?>