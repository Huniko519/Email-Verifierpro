<?php
include 'pagename.php';
include $session;
Session::checkSession_f();
extract($_POST);
$user_id = $_SESSION['id'];
if(is_numeric($emails)){
  include '../config/'.$config;
  include "../config/".$database;
$db = new database();
$dates = new DateTime('now', new DateTimeZone('UTC') ); //php international timezone
$dates = $dates->format('Y-m-d H:i:s'); //formate as 2019-7-23 12:34:12
$user_query3 =  "SELECT * FROM timer WHERE user_id = '$user_id' ";
$user_read3 = $db->select($user_query3);
	if($user_read3){
	  $row3 = $user_read3->fetch_assoc();
	  $time_count = $row3['time_count'];
	  $t_dates = strtotime($dates);
	  $start_date = strtotime($time_count);
	  $time_left =($t_dates - $start_date)/60; //formate time in minute
	  if($time_left > $row3['time_range']){
		$query18 = "UPDATE timer SET last_send = '$emails', time_count = '$dates' WHERE user_id = '$user_id' " ; // update send mail counting and time;
		$read18 = $db->update($query18);
		if ($read18) {
		  echo 'ok';
		}
	  }else{
		$range = $row3['e_range'];
		$send = $row3['last_send'];
		$email_left = $range - $send;
		if($email_left >= $emails){
		  $count_email = $send + $emails;
		  $query19 = "UPDATE timer SET last_send = '$count_email' WHERE user_id = '$user_id' " ; // update send mail if time exits;
		  $read19 = $db->update($query19);
		  if ($read19) {
			echo 'ok';
		  }
		}else{
		  echo 'error';
		}
	  }

	}
} else {
  echo 'unknown_error';
}
?>