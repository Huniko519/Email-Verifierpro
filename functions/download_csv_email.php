<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
include '../config/'.$config;
include '../config/'.$database;
$db = new database();

$or_std = false;
$all_std = false;
$and_std = false;
if(isset($_POST['user_id'])){
  $user_id = $_POST['user_id'];
  $sql = "SELECT a.email_name, a.email_status, a.safe_to_send, a.verification_response, a.score, a.bounce_type, a.email_type, a.email_acc, a.email_dom FROM user_email_list a INNER JOIN (SELECT email_name, MIN(id) as id FROM user_email_list GROUP BY email_name ) AS b ON a.email_name = b.email_name AND a.id = b.id AND ";
}else{
  header("Location: ".$error_404_page);
}
    function test_input($data) {
        $db = new database();
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = strtolower($data);
        $data = mysqli_real_escape_string($db->link, $data);
        return $data;
    }
    if(isset($_POST['download_type'])){
      if($_POST['download_type'] == 'this' || $_POST['download_type'] == 'all'){
        $start_from = $_POST['start_from'];
        $limit = $_POST['limit'];
        $email_type = $_POST['email_type'];
        $email_status = $_POST['email_status'];
        $account_type = ucfirst($email_type).' '.'Account';
        if(!empty($email_type) && !empty($email_status)){
          $and_std = true;
          $sql .= ' a.email_type ="'.$account_type.'" AND a.email_status="'.$email_status.'" AND ';
        }elseif(!empty($email_type)){
          $and_std = true;
          $sql .= ' a.email_type ="'.$account_type.'" AND ';
        }elseif(!empty($email_status)){
          $and_std = true;
          $sql .= ' a.email_status="'.$email_status.'" AND ';
        }else{
          $all_std = ture;
        }
        $sql .= "a.user_id = '$user_id' ";
        if($_POST['download_type'] == 'this'){
          $sql .= ' LIMIT '.$start_from.','.$limit;
        }
      }elseif ($_POST['download_type'] == 'custom') {
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
        if($or_std){
          $sql .= ") AND ";
        }
        $sql .= "a.user_id = '$user_id' ";
      }
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