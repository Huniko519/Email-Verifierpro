<?php
// ----------------*******functions*********-----------
// ------------------chart function-----------------
function chart_data($date_data,$target){
  $labels = '[';
  $std = true;
  $i=0;
  $valid_data = '[';
  $invalid_data = '[';
  $catchall_data = '[';
  $unknown_data = '[';
  $valid_std = true;
  $invalid_std = true;
  $catchal_std = true;
  $unknown_std = true;

    foreach( $date_data as $key => $value)
    {
      foreach($value as $subkey => $subvalue){
        if($subkey == 'valid'){
          if($valid_std){
            $valid_data .= "'".$subvalue."'";
            $valid_std = false;
          }else{
            $valid_data .= ", '".$subvalue."'";
          }
        }elseif ($subkey == 'invalid') {
          if($invalid_std){
            $invalid_data .= "'".$subvalue."'";
            $invalid_std = false;
          }else{
            $invalid_data .= ", '".$subvalue."'";
          }
        }elseif ($subkey == 'catch all') {
          if($catchal_std){
            $catchall_data .= "'".$subvalue."'";
            $catchal_std = false;
          }else{
            $catchall_data .= ", '".$subvalue."'";
          }
        }elseif ($subkey == 'unknown') {
          if($unknown_std){
            $unknown_data .= "'".$subvalue."'";
            $unknown_std = false;
          }else{
            $unknown_data .= ", '".$subvalue."'";
            $unknown_std = false;
          }
        }
      }
      $mykey = $key;
      $mytime = strtotime($mykey);
      if($target == 'month'){
        $mytime = date('M-d',$mytime);
      }elseif($target == 'week'){
        $mytime = date('D',$mytime);
      }elseif($target == 'year'){
        $mytime = date('Y-M',$mytime);
      }
      if($std){
        $labels .= "'".$mytime."'";
        $std = false;
      }else{
        $labels .= ", '".$mytime."'";
      }
    }
    $valid_data .= ']';
    $invalid_data .= ']';
    $catchall_data .= ']';
    $unknown_data .= ']';
  $labels .= ']';

  return array($labels,$valid_data,$invalid_data,$catchall_data,$unknown_data);
}
// -------------------end chart function------------
// ------------------mail-count-function--------------------
function email_count($data,$status,$target){
  $return_count = 0;
  for ($row = 0; $row < count($data); $row++) {
      if($data[$row][$target]==$status) {
           $return_count++;
      }
  }
  return $return_count;
}
// ------------------end---------------mail-count-function--------------------
// ------------------csv file count function-----------------
function file_count($data,$target){
  $temp_array = array();
  $return_count = 0;
  $i = 0;
  for ($row = 0; $row < count($data); $row++) {
    $val = $data[$row][$target];
        if (!in_array($val, $temp_array)) {
            $temp_array[$i] = $val;
            $i++;
        }
  }
  return count($temp_array);
}
// ------------------end file count---------------
function GetCurrentMonthDates(){
  $list=array();
  for($d=1; $d<=31; $d++)
  {
      $time=mktime(12, 0, 0, date('m'), $d, date('Y'));
      if (date('m', $time)==date('m'))
          $list[]=date('Y-m-d', $time);
  }
  return $list;
}
// -------------------------get week date----------------------
function GetCurrentWeekDates()
{
    if (date('D') != 'Mon') {
        $startdate = date('Y-m-d', strtotime('last Monday'));
    } else {
        $startdate = date('Y-m-d');
    }

//always next saturday
    if (date('D') != 'Sat') {
        $enddate = date('Y-m-d', strtotime('next Saturday'));
    } else {
        $enddate = date('Y-m-d');
    }

    $DateArray = array();
    $timestamp = strtotime($startdate);
    while ($startdate <= $enddate) {
        $startdate = date('Y-m-d', $timestamp);
        $DateArray[] = $startdate;
        $timestamp = strtotime('+1 days', strtotime($startdate));
    }
    return $DateArray;
}
// --------------end week date count---------------


// ------------------ date wise email count function----------------------
function email_count_date($data,$status,$target){
  $valid_count = 0;
  $invalid_count = 0;
  $catchall_count = 0;
  $unknown_count = 0;
  for ($row = 0; $row < count($data); $row++) {
      if($data[$row][$target]==$status) {
        if($data[$row]['email_status'] == 'valid'){
          $valid_count++;
        }elseif ($data[$row]['email_status'] == 'invalid') {
          $invalid_count++;
        }elseif ($data[$row]['email_status'] == 'catch all') {
          $catchall_count++;
        }elseif ($data[$row]['email_status'] == 'unknown') {
          $unknown_count++;
        }
      }
  }
  return array('valid' => $valid_count, 'invalid' => $invalid_count, 'catch all' => $catchall_count,'unknown' => $unknown_count);
}
//-------------------end date wise count function---------------------------------

// ----------------------------year month count function------------
function email_count_year($data,$status,$target){
  $valid_count = 0;
  $invalid_count = 0;
  $catchall_count = 0;
  $unknown_count = 0;
  for ($row = 0; $row < count($data); $row++) {
    $this_date = $data[$row][$target];
    $this_date = explode('-', trim($this_date));
    $this_date = $this_date[0].'-'.$this_date[1];
      if($this_date == $status) {
        if($data[$row]['email_status'] == 'valid'){
          $valid_count++;
        }elseif ($data[$row]['email_status'] == 'invalid') {
          $invalid_count++;
        }elseif ($data[$row]['email_status'] == 'catch all') {
          $catchall_count++;
        }elseif ($data[$row]['email_status'] == 'unknown') {
          $unknown_count++;
        }
      }
  }
  return array('valid' => $valid_count, 'invalid' => $invalid_count, 'catch all' => $catchall_count,'unknown' => $unknown_count);
}
// ---------------end year month count function-----------
// ----------------*******end functions*********-----------