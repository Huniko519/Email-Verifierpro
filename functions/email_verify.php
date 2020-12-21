<?php
include 'pagename.php';
include '../config/'.$config;
include '../config/'.$database;
$db = new database();
if(isset($_POST['filename']) && isset($_POST['uid']) && isset($_POST['frommail']) && isset($_POST['timeout'])){
  $filename = $_POST['filename'];
  $user_id = $_POST['uid'];
  $fromemail = $_POST['frommail'];
  $timeout = $_POST['timeout'];
  set_time_limit(0);

  ini_set('max_execution_time', '0');
  ini_set('request_terminate_timeout', '0');
  ini_set('fastcgi_read_timeout', '0');
  // --------------------------------------------------------------------------------------------------------------------------------------------------------
        $email_regular_expression="^([-!#\$%&'*+./0-9=?A-Z^_`a-z{|}~])+@([-!#\$%&'*+/0-9=?A-Z^_`a-z{|}~]+\\.)+[a-zA-Z]{2,24}\$";
        $smtp_validation=1;
        $data_timeout=0;
        $localuser="";
        $debug=0;
        $html_debug=0;
        $log_debug = 0;
        $exclude_address="";
        $getmxrr="GetMXRR";
        $email_domains_white_list_file = '';
        $invalid_email_users_file = '';
        $invalid_email_domains_file = '';
        $invalid_email_servers_file = '';
        $suggestions = array();
        $validation_status_code = '0';
        $EMAIL_VALIDATION_STATUS_OK = 0;

        $EMAIL_VALIDATION_STATUS_SYNTAX_ERROR = 'syntax error';
        $EMAIL_VALIDATION_STATUS_DOMAIN_NOT_FOUND = 'domain not found';
        $EMAIL_VALIDATION_STATUS_GET_HOST_FAILED = 'failed to verify host';
        $EMAIL_VALIDATION_STATUS_TEMPORARY_SMTP_REJECTION = 'temporary smtp rejection';
        $EMAIL_VALIDATION_STATUS_SMTP_DIALOG_REJECTION    = 'smtp dialog rejection';
        $EMAIL_VALIDATION_STATUS_SMTP_CONNECTION_FAILED   = 'smtp connection Failed';
        $EMAIL_VALIDATION_STATUS_validation_skip = 'SMTP validation skipped due to configuration';
        $EMAIL_VALIDATION_STATUS_DNS_SERVER_FAILED = 'No Answer Received From Authoritative Server';

        Function PutLine($connection,$line)
        {
          global $debug;
          if($debug)
            OutputDebug("C $line");
          return(@fputs($connection,"$line\r\n"));
        }
        Function VerifyResultLines($connection,$code)
        {
          global $last_code;
          while(($line= GetLine($connection)))
          {
            $end = strcspn($line, ' -');
            $last_code=substr($line, 0, $end);
            if(strcmp($last_code,$code))
              return(0);
            if(!strcmp(substr($line, strlen($last_code), 1)," "))
              return(1);
          }
          return(-1);
        }
          Function SplitAddress($address, &$user, &$domain)
          {
            if(GetType($at = strpos($address, '@')) == 'integer')
            {
              $user = substr($address, 0, $at);
              $domain = substr($address, $at + 1);
            }
            else
            {
              $user = $address;
              $domain = 'localhost';
            }
          }
        Function ValidateEmailAddress($email)
        {
          global $email_regular_expression;
          return(preg_match('/'.str_replace('/', '\\/', $email_regular_expression).'/', $email));
        }
        Function getHost($email){

        }
          Function OutputDebug($message)
          {
            global $log_debug;
            global $html_debug;
            if($log_debug)
              error_log($message);
            else
            {
              $message.="\n";
              if($html_debug)
                $message=str_replace("\n","<br />\n",HtmlEntities($message));
              flush();
              return $message;
            }
          }

          Function GetLine($connection)
          {
            global $debug;
            for($line="";;)
            {
              if(@feof($connection))
                return(0);
              $line.=@fgets($connection,100);
              $length=strlen($line);
              if($length>=2
              && substr($line,$length-2,2)=="\r\n")
              {
                $line=substr($line,0,$length-2);
                if($debug)
                  OutputDebug("S $line");
                return($line);
              }
            }
          }

      Function ValidateEmailBox($email)
      {
        global $localhost;
        global $localuser;
        global $debug;
        global $exclude_address;
        global $smtp_validation;
        global $timeout;
        global $data_timeout;
        global $last_code;
        global $validation_status_code;
        global $EMAIL_VALIDATION_STATUS_TEMPORARY_SMTP_REJECTION;
        global $EMAIL_VALIDATION_STATUS_SMTP_DIALOG_REJECTION;
        global $EMAIL_VALIDATION_STATUS_SMTP_CONNECTION_FAILED;
        global $EMAIL_VALIDATION_STATUS_GET_HOST_FAILED;
        global $EMAIL_VALIDATION_STATUS_DOMAIN_NOT_FOUND;
        global $EMAIL_VALIDATION_STATUS_SYNTAX_ERROR;
        global $EMAIL_VALIDATION_STATUS_DNS_SERVER_FAILED;
        // ------------------------------------------------------------------------------------------------
          // Get the domain of the email recipient
          $email_arr = explode('@', $email);
          $domain = array_slice($email_arr, -1);
          $domain = $domain[0];


          // Trim [ and ] from beginning and end of domain string, respectively
          $domain = ltrim($domain, '[');
          $domain = rtrim($domain, ']');


          if ('IPv6:' == substr($domain, 0, strlen('IPv6:'))) {
              $domain = substr($domain, strlen('IPv6') + 1);
          }
          if($record_a = dns_get_record($domain, DNS_MX)){
            $pir = array_column($record_a, 'pri');
            $min_mxip = $record_a[array_search(min($pir), $pir)];
            $mx_ip = $min_mxip['target'];
          }else{
            $validation_status_code = $EMAIL_VALIDATION_STATUS_DOMAIN_NOT_FOUND;
            return array(0, $validation_status_code);
          }

          if(!empty($mx_ip)){

            // ---------------------------------------------------------------------------------------------------
            if(!strcmp($localhost=$localhost,"")
            && !strcmp($localhost=getenv("SERVER_NAME"),"")
            && !strcmp($localhost=getenv("HOST"),""))
               $localhost="localhost";
            if(!strcmp($localuser=$localuser,"")
            && !strcmp($localuser=getenv("USERNAME"),"")
            && !strcmp($localuser=getenv("USER"),""))
               $localuser="root";
            // ---------------------------------------------------------------------------------------------------
            $domain=$mx_ip;
            if(preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/',$domain))
              $ip=$domain;
            else
            {
              if($debug)
                OutputDebug("Resolving host name \"".$mx_ip."\"...");
              if(!strcmp($ip=@gethostbyname($domain),$domain))
              {
                if($debug)
                  OutputDebug("Could not resolve host name \"".$mx_ip."\".");
                // continue;
              }
            }
            if(strlen($exclude_address)
            && !strcmp(@gethostbyname($exclude_address),$ip))
            {
              if($debug)
                OutputDebug("Host address of \"".$mx_ip."\" is the exclude address");
              // continue;
            }
            if(!$smtp_validation)
            {
              if($debug){
                OutputDebug("SMTP validation skipped due to configuration");
                $validation_status_code = $EMAIL_VALIDATION_STATUS_validation_skip;
              }
              return array(-1, $validation_status_code);
            }
            if($debug)
                OutputDebug("Connecting to host address \"".$ip."\"...");
            if(($connection=($timeout ? @fsockopen($ip,25,$errno,$error,$timeout) : @fsockopen($ip,25))))
            {
              $timeout=($data_timeout ? $data_timeout : $timeout);
              if($timeout
              && function_exists("socket_set_timeout"))
                socket_set_timeout($connection,$timeout,0);
              if($debug)
                OutputDebug("Connected.");
              if(VerifyResultLines($connection,"220")>0
              && PutLine($connection,"HELO $localhost")
              && VerifyResultLines($connection,"250")>0
              && PutLine($connection,"MAIL FROM: <".$localuser."@".$localhost.">")
              && VerifyResultLines($connection,"250")>0
              && PutLine($connection,"RCPT TO: <$email>")
              && ($result=VerifyResultLines($connection,"250"))>=0)
              {
                if($result)
                {
                  if(PutLine($connection,"DATA"))
                    $result=(VerifyResultLines($connection,"354")!=0);
                }
                if(!$result)
                {
                  if(strlen($last_code)
                  && !strcmp($last_code[0],"4"))
                  {
                    $validation_status_code = $EMAIL_VALIDATION_STATUS_TEMPORARY_SMTP_REJECTION;
                    $result=-1;
                  }
                }
                else{
                  $result = 1;
                  $validation_status_code = '1';
                }

                return array($result, $validation_status_code);
              }
              if($debug)
                $email_debug = OutputDebug("Unable to validate the address with this host.");
              @fclose($connection);
              if($debug)
              	OutputDebug("Disconnected.");
              $validation_status_code = $EMAIL_VALIDATION_STATUS_SMTP_DIALOG_REJECTION;
            }
            else
            {
              if($debug)
                $verify_result = OutputDebug("Failed");
              $validation_status_code = $EMAIL_VALIDATION_STATUS_SMTP_CONNECTION_FAILED;
            }
            return array(-1,$validation_status_code);
            // -----------------------------------------------------------------------------------------------------
          }else{
            $validation_status_code = $EMAIL_VALIDATION_STATUS_DOMAIN_NOT_FOUND;
            return array(0, $validation_status_code);
          }
      }
  // -----------------------------------------------------------------------------------------------------

  function generateRandomString($length = 10) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
  }

  // -------------------------------------------------------------------------------------------------------
    $data_timeout=0;
    $formmail = explode('@', $formmail);
    $localuser = $formmail[0];
    $localhost = $formmail[1];
    $debug=1;
    $html_debug=1;

    // ----------------------------------------------------------------------------------------------------------------
    $count_valid = 0;
    $count_catch_all = 0;
    $count_unknown = 0;
    $count_invalid = 0;
    $count_syntax_error = 0;
    $count_disponsable_acc = 0;
    $count_free_acc = 0;
    $count_role_acc = 0;

    // -----------------------------------------------------------------------------------------------------------------


      $task_insert = "INSERT INTO task (csv_name,user_id) VALUES ('$filename', '$user_id')";
      $read_task_insert = $db->insert($task_insert);

      // ------------------------------------------------------------------------------------------------------------------
      $check_email = "SELECT * FROM user_email_list WHERE csv_file_name = '$filename' AND user_id = '$user_id' AND email_status = 'Not Verify' ";
      $email_st = $db->select($check_email);
      $data = array();
      $i=0;
      $count_all_email = mysqli_num_rows($email_st);
      if ($count_all_email > 0) {
      while ($row = $email_st->fetch_assoc()) {
          $email_id = $row['id'];
          $toemail = $row['email_name'];
          $i++;
          $safe_to_send = '';
          $email_score = '';
          $bounce_type = '';
          $email_acc = '';
          $email_dom = '';
          $email_type = '';
          $validation_status_code = '0';
          $toemail = filter_var($toemail, FILTER_SANITIZE_EMAIL);
          // Validate e-mail
          if (filter_var($toemail, FILTER_VALIDATE_EMAIL)) {
            $email_arr = explode('@', $toemail);
            $email_acc = $email_arr[0];
            $email_dom = $email_arr[1];
            $catch_all_check_status = '';
            $email_type_check = "SELECT * FROM email_category WHERE name = '$email_acc' OR name = '$email_dom' ";
            $email_type_read = $db->select($email_type_check);
            if ($email_type_read) {
                $count_type = mysqli_num_rows($email_type_read);
                if ($count_type > 0) {
                  $type_row = $email_type_read->fetch_assoc();
                  $email_type = $type_row['e_type'];
                  $catch_all_check_status = $type_row['catch_all_check'];
                  $trim_email_type = trim($email_type);
                  if($trim_email_type == 'Free Account'){
                    $count_free_acc++;
                  }elseif($trim_email_type == 'Disposable Account'){
                    $count_disponsable_acc++;
                  }elseif($trim_email_type == 'Role Account'){
                    $count_role_acc++;
                  }
                }
              }
            if($catch_all_check_status != '0'){
              $random_c = generateRandomString(10);
              $catch_mail = $random_c.'@'.$email_dom;
              $result = ValidateEmailBox($catch_mail);
              if($result[0] == 1){
                $validation_status_code = 'Catch-All mail server';
                $result = array(2,$validation_status_code);
              }else{
                if($result[1] == 0 ){
                  $result=ValidateEmailBox($toemail);
                }
              }
            }else{
              $result=ValidateEmailBox($toemail);
            }
          }else{
            $count_syntax_error++;
            $validation_status_code = $EMAIL_VALIDATION_STATUS_SYNTAX_ERROR;
            $result = array(0,$validation_status_code);
          }

          if($result[0]<0){
            $count_unknown++;
            $email_verify_result = 'unknown';
            if($result[1] == '0'){
              $email_verify_status = 'Mail server error';
            }else{
              $email_verify_status = $result[1];
            }
          }elseif ($result[0] == 2) {
            $count_catch_all++;
            $email_verify_result = 'catch all';
            $email_verify_status = $result[1];
          }
          else{
            if($result[0] == 1){
              $count_valid++;
              $email_verify_result = 'valid';
              $email_verify_status = 'success';
            }else{
              $count_invalid++;
              $email_verify_result = 'invalid';
              if($result[1] == '0'){
                $email_verify_status = 'mail box not found';
              }else{
                $email_verify_status = $result[1];
              }
            }
          }
          if($email_verify_result == 'valid'){
            $safe_to_send = 'Yes';
            $email_score = 1;
          }elseif($email_verify_result == 'catch all'){
            $safe_to_send = 'Risky';
            $email_score = 0.5;
          }else{
            $safe_to_send = 'NO';
            $email_score = 0;
            $bounce_type = 'hard';
          }
          $token_update_query = "UPDATE user_email_list SET email_status = '$email_verify_result', email_type = '$email_type', safe_to_send = '$safe_to_send',
           verification_response = '$email_verify_status', score = '$email_score', 	bounce_type = '$bounce_type', email_acc = '$email_acc', email_dom = '$email_dom' WHERE id = '$email_id' ";
          $db->update($token_update_query);
      }
    }
      $task_delete = "DELETE FROM task WHERE csv_name = '$filename' AND user_id = '$user_id'";
      $read_task_delete = $db->delete($task_delete);

      echo json_encode(['valid' => $count_valid, 'catch_all' => $count_catch_all, 'invalid' => $count_invalid, 'unknown' => $count_unknown, 'syntax' => $count_syntax_error, 'disposable' => $count_disponsable_acc, 'free' => $count_free_acc, 'role' => $count_role_acc]);

}
?>
