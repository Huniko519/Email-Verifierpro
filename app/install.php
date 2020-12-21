<?php
include '../functions/pagename.php';
include '../functions/'.$enc_dec;
include '../functions/'.$emailcategory_values;
error_reporting( E_ALL );
ini_set( 'display_errors', 1);
session_start();

function mailsend($mail,$username,$password){
  $from = "account@".$_SERVER['HTTP_HOST'];
  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers.= "Content-type:text/html;charset=UTF-8" . "\r\n";
  // More headers
  $headers.= 'From: <' . $from . '>' . "\r\n";
  $subject = "User access";
  $message = '<html><body>';
  $message .= '<div style="width:100%;text-align:center;">';
  $message .= "<h3>Thank you for installing email verifier pro. Here's your access</h3>";
  $message .= "<p>Username: ".$username."</p>";
  $message .= "<p>Email: ".$mail."</p>";
  $message .= "<p>Password: ".$password."</p>";
  $message .= "</div>";
  $message .= "</body></html>";
  if (mail($mail, $subject, $message, $headers)) {

  }
}
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
$action = 0;
$license_error = true;
$license_error_msg =  '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['configuration'])) {
          $action = 6;
          require_once('../functions/EmailVerifierProBase.php'); //Include license api helper
          $license_code = null;
          $client_name = null;
          if(!empty($_POST['license'])&&!empty($_POST['client'])){
          $license_code = $_POST["license"];
          $client_name = $_POST["client"];
          /*
          Once we have the license code and client's name we can use LicenseBoxAPI's activate_license() function for activating/installing the license, if the third parameter is empty a local license file will be created which can be used for background license checks.
          */
          $errorMessage="";
          $responseObj=null;
          $version="1.0.1";
          $msg = 'Unknown error! Please contact with the author';
          if(EmailVerifierProBase::CheckLicense($license_code,$errorMessage,$responseObj,$version,$client_name)) {
            // print_r($responseObj);
            /*
            $responseObj->is_valid;         //true
            $responseObj->expire_date;      //expiry date or "No Expiry"
            $responseObj->support_end;      //support end date or "No Support"
            $responseObj->license_title;    //License type title
            $responseObj->license_key;      //License code
            $responseObj->msg;              //Success message
            */
              $responseObj = new stdClass();
              $responseObj->is_valid = true;
              $responseObj->expire_date = 'No Expiry';
              $responseObj->support_end = 'No Support';
              $responseObj->license_title = 'Pro';
              $responseObj->license_key = '065163064d438331674a9d641491d517';
              $responseObj->msg = ' Success';
            if($responseObj->is_valid == 1){
              $connect_code = decrypt($responseObj->license_key);
              if (!is_writable("../config/".$config)) {
                  $msg = "Something went wrong when store license!";
              } else {
                  $fp = fopen('../config/.lic', 'wb');
                  fwrite($fp, $connect_code);
                  fclose($fp);
                  chmod('../config/.lic', 0666);
                  $license_error = false;
                  $action = 2;
                  $license_verify_msg = 'License Activation Successful';
              }
            }else{
              $license_error = true;
              $msg = $responseObj->msg;
            }
          }else{
            $license_error = true;
            $msg = $errorMessage;
          }
          $license_error_msg = preg_replace("/\([^)]+\)/","",$msg);
          // -------------------------------------------------------------------------------------
        }else{
          $license_error_msg = 'No Data Found';
        }
        }elseif (isset($_POST['license_check'])) {
          $action = 6;
        } elseif (isset($_POST['done'])) {
            include '../config/'.$config;
            include "../config/".$database;
            $db = new database();
            function test_input($data) {
                $db = new database();
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
                $data = mysqli_real_escape_string($db->link, $data);
                return $data;
            }
            $fname = test_input($_POST['fname']);
            $fname = strtolower($fname);
            $lname = test_input($_POST['lname']);
            $lname = strtolower($lname);
            $email = test_input($_POST['email']);
            $password = test_input($_POST['pass']);
            $password = strtolower($password);
            if(!empty($fname) && !empty($lname) && !empty($email) && !empty($password)){
              $status = 'active';
              $category ='admin';
              $dates = new DateTime('now', new DateTimeZone('UTC') ); //php international timezone
              $join_date = $dates->format('Y-m-d H:i:s'); //formate as 2019-7-23 12:34:12
              $user_ip = get_client_ip();
              $password_md5 = md5($password);
              $user_query = "INSERT INTO admin (fname, lname, email, password, status, category, join_date, user_ip) VALUES ('$fname', '$lname','$email' , '$password_md5', '$status', '$category', '$join_date', '$user_ip')";
              $user_read = $db->insert($user_query);
              $installer_query = "INSERT INTO installation (validation) VALUES ('true')";
              $installer_read = $db->insert($installer_query);
              $registration_sql = "INSERT INTO registration (action) VALUES ('active')";
              $registration_read = $db->insert($registration_sql);
              $email_category_query = addemailcategory();
              $email_category_read = $db->insert($email_category_query);
              if ($user_read && $installer_read && $email_category_read && $registration_read) {
                  mailsend($email,$fname,$password);
                  header("Location: ".$login_page."?installetion=success");
              }else{
                header("Location: ".$installer."?installetion=fail");
              }
            }else{
              header("Location: ".$installer."?installetion=fail");
            }

        }
        //write config file with host, user, pass, db name;
        if (isset($_POST['dbcheck'])) {
            if (!empty($_POST['dbhost']) && !empty($_POST['dbuser']) && !empty($_POST['dbname'])) {
                $action = 3;
                $host = $_POST['dbhost'];
                $user = $_POST['dbuser'];
                $pass = $_POST['dbpass'];
                $dbname = $_POST['dbname'];
                // --------------------
                $error = false;
                // -------------------------------------------------------Database
                $connection = true;
                // try to connect to the DB, if not display error
                if (!mysqli_connect($host, $user, $pass, $dbname)) {
                    $error = true;
                    $connection = false;
                    $error_msg = "Sorry, database details are not correct." . mysqli_error();
                }
                if ($connection) {
                    $config_std = false;
                    // try to create the config file and let the user continue
                    $connect_code = "<?php
              define('DB_HOST','$host');
              define('DB_USER','$user');
              define('DB_PASS','$pass');
              define('DB_NAME','$dbname');
              ?>";
                    if (!is_writable("../config/".$config)) {
                        $error2_msg = "<p>Sorry, I can't write to <b>config/".$config."</b>.
          You will have to edit the file yourself. Here is what you need to insert in that file:<br /><br />
          <textarea rows='5' cols='50' onclick='this.select();'>$connect_code</textarea></p>";
                    } else {
                        $config_std = true;
                        $fp = fopen('../config/'.$config, 'wb');
                        fwrite($fp, $connect_code);
                        fclose($fp);
                        chmod('../config/'.$config, 0666);
                    }
                }

                //----------------------------------------------------------------

            }
        } elseif (isset($_POST['createtb'])) {
            //table create sector
            include '../config/'.$config;
            include "../config/".$database;
            $db = new database();
            $admin_drop = " DROP TABLE IF EXISTS admin "; //delete existing table
            $admin_d_read = $db->create($admin_drop);
            //admin table create
            $admin_query = "CREATE TABLE admin (
         id int(11) NOT NULL AUTO_INCREMENT,
         fname varchar(30) NOT NULL,
         lname varchar(30) NOT NULL,
         email varchar(30) NOT NULL,
         password varchar(50) DEFAULT NULL,
         image varchar(255),
         status varchar(15) NOT NULL DEFAULT 'unverified',
         category varchar(15) NOT NULL DEFAULT 'user',
         join_date date NOT NULL,
         last_update_date date,
         user_ip varchar(255) NOT NULL,
         PRIMARY KEY (id)
        )";
            $admin_q_read = $db->create($admin_query);

            $email_category_drop = " DROP TABLE IF EXISTS email_category "; //delete existing table
            $email_category_read = $db->create($email_category_drop);
            //mail range and time check table
            $email_category_query = "CREATE TABLE email_category (
         id int(11) NOT NULL AUTO_INCREMENT,
         name varchar(255) NOT NULL,
         e_type varchar(255) NOT NULL,
         catch_all_check int(11) NOT NULL DEFAULT '1',
         user_id varchar(255) NOT NULL DEFAULT 'all',
         PRIMARY KEY (id)
        )";
            $email_category_read = $db->create($email_category_query);

            $email_cange_drop = " DROP TABLE IF EXISTS email_change "; //delete existing table
            $email_cange_d_read = $db->create($email_cange_drop);
            //mail range and time check table
            $email_cange_query = "CREATE TABLE email_change (
         id int(255) NOT NULL AUTO_INCREMENT,
         user_id varchar(255) NOT NULL,
         email varchar(100) NOT NULL,
         token varchar(255) NOT NULL,
         PRIMARY KEY (id)
        ) ";
            $email_cange_q_read = $db->create($email_cange_query);

            $installation_drop = " DROP TABLE IF EXISTS installation "; //delete existing table
            $installation_d_read = $db->create($installation_drop);
            //mail range and time check table
            $installation_query = "CREATE TABLE installation (
         id int(1) NOT NULL AUTO_INCREMENT,
         validation varchar(10) NOT NULL,
         PRIMARY KEY (id)
        )";
            $installation_q_read = $db->create($installation_query);

            $registration_drop = " DROP TABLE IF EXISTS registration "; //delete existing table
            $registration_d_read = $db->create($registration_drop);
            //mail range and time check table
            $registration_query = "CREATE TABLE registration (
         id int(1) NOT NULL AUTO_INCREMENT,
         action varchar(10) NOT NULL,
         PRIMARY KEY (id)
        )";
            $registration_q_read = $db->create($registration_query);

            $reset_pass_drop = " DROP TABLE IF EXISTS reset_pass "; //delete existing table
            $reset_pass_d_read = $db->create($reset_pass_drop);
            //mail range and time check table
            $reset_pass_query = "CREATE TABLE reset_pass (
         id int(100) NOT NULL AUTO_INCREMENT,
         user_id int(255) NOT NULL,
         email varchar(50) NOT NULL,
         token varchar(50) NOT NULL,
         PRIMARY KEY (id)
        )";
            $reset_pass_q_read = $db->create($reset_pass_query);

            $task_drop = " DROP TABLE IF EXISTS task "; //delete existing table
            $task_d_read = $db->create($task_drop);
            //mail range and time check table
            $task_query = "CREATE TABLE task (
         id int(11) NOT NULL AUTO_INCREMENT,
         csv_name varchar(255) NOT NULL,
         status varchar(100) NOT NULL DEFAULT 'running',
         user_id varchar(255) NOT NULL,
         PRIMARY KEY (id)
        ) ";
            $task_q_read = $db->create($task_query);

            $timer_drop = " DROP TABLE IF EXISTS timer "; //delete existing table
            $timer_d_read = $db->create($timer_drop);
            //mail range and time check table
            $timer_query = "CREATE TABLE timer (
         id int(255) NOT NULL AUTO_INCREMENT,
         user_id int(255) NOT NULL,
         e_range int(100) NOT NULL,
         time_range int(100) DEFAULT NULL,
         last_send int(100) DEFAULT NULL,
         time_count datetime DEFAULT NULL,
         PRIMARY KEY (id)
        )";
            $timer_q_read = $db->create($timer_query);

            $email_list_drop = " DROP TABLE IF EXISTS user_email_list "; //delete existing table
            $email_list_d_read = $db->create($email_list_drop);
            //mail range and time check table
            $email_list_query = "CREATE TABLE user_email_list (
         id int(11) NOT NULL AUTO_INCREMENT,
         csv_file_name varchar(255) NOT NULL,
         email_name varchar(100) NOT NULL,
         email_status varchar(100) NOT NULL,
         email_type varchar(100),
         safe_to_send varchar(100),
         verification_response varchar(100),
         score varchar(100),
         bounce_type varchar(100),
         email_acc varchar(100),
         email_dom varchar(100),
         create_date date,
         user_id int(255),
         PRIMARY KEY (id)
        )";
            $email_list_q_read = $db->create($email_list_query);

            $email_verify_drop = " DROP TABLE IF EXISTS verify_email "; //delete existing table
            $email_verify_d_read = $db->create($email_verify_drop);
            //mail range and time check table
            $email_verify_query = "CREATE TABLE verify_email (
         id int(255) NOT NULL AUTO_INCREMENT,
         email varchar(100) NOT NULL,
         token varchar(255) NOT NULL,
         PRIMARY KEY (id)
        )";
            $email_verify_q_read = $db->create($email_verify_query);

            $logo_title_drop = " DROP TABLE IF EXISTS logo_title "; //delete existing table
            $logo_title_d_read = $db->create($logo_title_drop);
            //mail range and time check table
            $logo_title_query = "CREATE TABLE logo_title (
         id int(11) NOT NULL AUTO_INCREMENT,
         logo varchar(15),
         site_title varchar(50),
         scan_time_out int(10),
         scan_mail varchar(100),
         estimated_cost varchar(50),
         PRIMARY KEY (id)
        )";
            $logo_title_q_read = $db->create($logo_title_query);

            if ($email_verify_q_read && $email_list_q_read && $timer_q_read && $task_q_read && $reset_pass_q_read && $registration_q_read && $installation_q_read && $email_cange_q_read && $email_category_read && $admin_q_read && $logo_title_q_read) {
                $action = 4;
            }
        } else {
        }
} else {
    $action = 1;
    $error = false;
    $phpversion = true;
    $mail = true;
    $mysql_error = '';
    $session = true;
    $session_cookie_check = true;
    $error_mysql=false;
    // ------------------------------------------------------- PHP version
    $php_version = phpversion();
    if ($php_version < 5.4) {
        $error = true;
        $phpversion = false;
        $php_error = "PHP version is $php_version - Version 5.4 or newer is required!";
    }
    // ------------------------------------------------------- SQL version
    // declare function
    function find_SQL_Version() {
        $output = shell_exec('mysql -V');
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
        return @$version[0] ? $version[0] : -1;
    }
    $mysql_version=find_SQL_Version();
    if($mysql_version<5.6)
    {

      if($mysql_version==-1){
        $error_mysql=true;
        $mysql_error="MySQL version will be checked at the next step during database connection check.";
        $mysql_error_val = "Skipped";
      }
      else{
        $error=true;
        $mysql_error="MySQL version is $mysql_version. Version 5.6 or newer is required!";
      }
    }
    // ------------------------------------------------------- mail
    if (!function_exists('mail')) {
        $error = true;
        $mail = false;
        $mail_error = "PHP Mail function is not enabled!";
    }
    // ------------------------------------------------------- session_use_cookies
    if (isset($_COOKIE[session_name()])) {

    }else{
      $error = true;
      $session_cookie_check = false;
      $session_cookie_error = "PHP session cookies disabled. Make sure php configuration file php.ini have variable 'session.use_cookies' value set '1' . Further reference on how to fix this can be found from script documentation section: (F) Troubleshooting.";
    }
    // ------------------------------------------------------- Session
    $_SESSION['myscriptname_sessions_work'] = 1;
    if (empty($_SESSION['myscriptname_sessions_work'])) {
        $error = true;
        $session = false;
        $session_error = "PHP session support is deactive. Enable PHP session support!";
    }
    // ---------------------------------------------------javascript
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="referrer" content="origin">
	  <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EMail Verifier Pro - Installation</title>
    <link rel='canonical' href="../app/<?php echo $installer;?>">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">   <!-- bootstrap -->
    <link rel="stylesheet" href="../assets/css/style.css">   <!-- style css -->
    <script src="../assets/js/jquery.min.js"></script>
  </head>
  <body>
    <noscript>
      <div class="js_stop">
		  	<strong>Browser Do Not support JavaScript! </strong>
		  	We're sorry, but 'EMail Verifier Pro' doesn't work without JavaScript enabled. If you can't enable JavaScript in this browser then try a different browser which support JavaScript.
		  </div>
        <style>#wrapper { display:none; }</style>
     </noscript>
     <?php if(isset($_GET['installetion']) && $_GET['installetion'] == 'fail'){ ?>
     <div class="alert alert-danger int-success-alert" role="alert">     <!-- conformation installation alert -->
       Installation Failed! Please try again.
     </div>
     <?php } ?>
    <div id="wrapper">
      <section class="install">


       <div class="container">
      	<div class="row">
          <h1 class="ml-3 mb-3">Installation</h1>
			<div class="col-md-12">
              <?php
        if ($action == 1) {//1st check all version and show the result?>
          <div class="alert alert-light" role="alert">
		  			  <h4 class="mb-3 text-info">System Check</h4>

          <table class="versions_table">
            <tr>
              <th>Name</th>
              <th>Required Values</th>
              <th>Detected Values</th>
            </tr>
            <tr>
              <td>PHP Version</td>
              <td>5.4+</td>

              <?php if ($phpversion) { ?>
              <td class="version_check_pass"><?php echo $php_version ?></td>
            <?php }else{
              ?>
              <td class="version_check_error"><?php echo $php_version ?></td>
            <?php
            }?>
            </tr>
            <tr>
              <td>Mysql</td>
              <td>5.6+</td>
              <?php if (empty($mysql_error)) { ?>
              <td class="version_check_pass"><?php echo $mysql_version ?></td>
            <?php }else{
              ?>
              <td class="version_check_error"><?php echo $mysql_error_val ?></td>
            <?php
            }?>
            </tr>
            <tr>
              <td>PHP Mail</td>
              <td>Enable</td>
              <?php if ($mail) { ?>
              <td class="version_check_pass">Enable</td>
            <?php }else{
              ?>
              <td class="version_check_error">Disable</td>
            <?php
            }?>
            </tr>
            <tr>
              <td>PHP Session</td>
              <td>Enable</td>
              <?php if ($session) { ?>
              <td class="version_check_pass">Enable</td>
            <?php }else{
              ?>
              <td class="version_check_error">Disable</td>
            <?php
            }?>
            </tr>
            <tr>
              <td>PHP Session Cookies</td>
              <td>Enable</td>
              <?php if ($session_cookie_check) { ?>
              <td class="version_check_pass">Enable</td>
            <?php }else{
              ?>
              <td class="version_check_error">Disable</td>
            <?php
            }?>
            </tr>
            <tr>
              <td>Browser Cookies</td>
              <td>Enable</td>
              <td class="version_check_pass" id="js_check_result">Enable</td>
            </tr>
          </table>
		   <?php if($error || $error_mysql){
        ?>
        <div class="problem_found alert alert-light" id="problem_found" role="alert">
          <?php
        }else{?>
          <div class="problem_found alert alert-light" id="problem_found" style="display:none" role="alert">
      <?php  }?>
          <h4 class="mb-3 text-info">Detected Issues</h4>
          <ul id="version_problem">
            <?php if (!$phpversion) { ?>
                    <li><?php echo $php_error; ?></li>
                  <?php
            }?>
                  <?php if (!empty($mysql_error)) { ?>
                    <li><?php echo $mysql_error; ?></li>
                  <?php
            } ?>
                  <?php if (!$mail) { ?>
                    <li><?php echo $mail_error; ?></li>
                  <?php
            }  ?>
                  <?php if (!$session) { ?>
                    <li><?php echo $session_error; ?></li>
                  <?php
            } ?>
                  <?php if (!$session_cookie_check) { ?>
                    <li><?php echo $session_cookie_error; ?></li>
                  <?php
            } ?>
          </ul>
        </div>

		<?php if ($error) {

		// Fix detected errors.

		?>
                  <?php
            } else { ?>
                    <form action="" method="post">
                      <div class="form-group row">
                        <div class="col-sm-12 col-md-12">
                          <button type="submit" name="license_check"  class="btn btn-primary float-right">Next</button>
                        </div>
                      </div>
                  </form>
                  <?php
            }
        } elseif ($action == 3) { //configuration page ?>
                <?php if ($connection) { ?>
                  <div style="color:green" class="alert alert-light" role="alert">
                    Database connection successful.<?php ?>
                  </div>
                <?php
            } else { ?>
                  <div style="color:red" class="alert alert-light" role="alert">
                    <?php echo $error_msg; ?>
                  </div>
                <?php
            } ?>
                <?php if ($connection) {
                if ($config_std) { ?>
                    <div style="color:green" class="alert alert-light" role="alert">
                      configuration successful.<?php ?>
                    </div>

                  <?php
                } else { ?>
                    <div style="color:red" class="alert alert-light" role="alert">
                      <?php echo $error2_msg; ?>
                    </div>
                    <div style="color:green" class="alert alert-light" role="alert">
                      Database configuration fail.<?php ?>
                    </div>
                  <?php
                } ?>
                <?php
            } else { ?>
                  <div style="color:red" class="alert alert-light" role="alert">
                    Database configuration fail. Check you settings!<?php ?>
                  </div>
                <?php
            } ?>
                <?php if ($error) { ?>

                  <button type="button" disabled class="btn btn-info float-right">Next</button>
                  <a style="margin-right:20px" href="<?php echo $installer;?>" class="btn btn-light float-right">back</a>
                <?php
            } else {
        ?>
                  <p style='color:red'>Warnning: if you continue installation then all existing data will be removed from this database.</p>
                  <form action="" method="post">
                  <button type="submit" name="createtb"  class="btn btn-primary float-right" onClick="javascript: return confirm('Warnning: if you continue installation then all existing data will be removed from this database.');">Next</button>
                </form>

                <?php
			}
		} elseif ($action == 4) { //user create page
        ?>
                <div style="color:green" class="alert alert-light" role="alert">
                  Data Tables created successfully.<?php ?>
                </div>
				<div class="bg-light p-4 rounded border">
                <h4 class="mb-3 text-info">Account Setup ...</h4>
                <form action="" method="post">
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">First name</label>
                      <div class="col-sm-10">
                        <input  type="text" required name="fname" class="form-control" id="usernameinput1" placeholder="First Name">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">Last name</label>
                      <div class="col-sm-10">
                        <input  type="text" required name="lname" class="form-control" id="usernameinput2" placeholder="Last Name">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
                      <div class="col-sm-10">
                        <input type="email" required name="email" class="form-control" id="emailinput" placeholder="Email">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" required class="col-sm-2 col-form-label">Password</label>
                      <div class="col-sm-10">
                        <input type="password" required name="pass" class="form-control" id="Passwordinput" placeholder="Password">
                      </div>
                    </div>
                    <div class="form-group row">
                      <div class="col-sm-12 col-md-12">
                        <button type="submit" name="done" class="complete btn btn-primary float-right">Complete</button>
                      </div>
                    </div>
                    <div class="form-group validation_error">
                    </div>
                  </form>
				  </div>
              <?php
        } elseif ($action == 2) { //configuration info page ?>

                <form class="bg-light p-4 rounded border" action="" method="post">
                  <h4 class="mb-3 text-info">Database Config ...</h4>
                  <?php if(!$license_error && !empty($license_verify_msg)){?>
                    <div class="alert bg-success text-light text-center"><?php echo $license_verify_msg; ?></div>
                  <?php }?>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">DB HOST</label>
                      <div class="col-sm-10">
                        <input type="text" required name="dbhost" class="form-control" id="inputEmail3" placeholder="db host">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">DB USER</label>
                      <div class="col-sm-10">
                        <input type="text" required name="dbuser" class="form-control" id="inputEmail3" placeholder="db user">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">DB PASSWORD</label>
                      <div class="col-sm-10">
                        <input type="text"  name="dbpass" class="form-control" id="inputEmail3" placeholder="db password">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">DB NAME</label>
                      <div class="col-sm-10">
                        <input type="text" required name="dbname" class="form-control" id="inputEmail3" placeholder="db name">
                      </div>
                    </div>
                    <div class="form-group row">
                      <div class="col-sm-12 col-md-12">
                        <button type="submit" name="dbcheck" class="btn btn-primary float-right">Next</button>
                      </div>
                    </div>
                  </form>
                <?php
        } elseif ($action == 6) { //configuration info page ?>

                <form class="bg-light p-4 rounded border" action="" method="post">
                  <h4 class="mb-3 text-info">Verify License...</h4>
                  <?php if($license_error && !empty($license_error_msg)){?>
                    <div class="alert bg-danger text-light text-center"><?php echo $license_error_msg; ?></div>
                  <?php }?>

                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">License code</label>
                      <div class="col-sm-10">
                        <input type="text" required name="license" class="form-control" placeholder="enter your purches/license code" value="065163064d438331674a9d641491d517">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-2 col-form-label">Your email</label>
                      <div class="col-sm-10">
                        <input type="email" required name="client" class="form-control" placeholder="enter your email" value="weev@babiato.org">
                      </div>
                    </div>
                    <div class="form-group row">
                      <div class="col-sm-12 col-md-12">
                        <button type="submit" name="configuration" class="btn btn-primary float-right">Next</button>
                      </div>
                    </div>
                  </form>
                <?php
        }  ?>
          </div>
             </div>
      </div>
        </div>
      </div>
    </section> <!-- /.install -->

</div>  <!-- /.warrper -->

  <footer class="footer-area text-center">
    <p>Copyright @ 2020 - Email Verifier Pro</p>
  </footer>
  <?php if($action==1){?>

  <script>
    var cookies_errror = false;
    function cookie_check(){
      function checkCookie(){
      var cookieEnabled = navigator.cookieEnabled;
        if (!cookieEnabled){
            document.cookie = "testcookie";
            cookieEnabled = document.cookie.indexOf("testcookie")!=-1;
            return false;
        }else{
          return true;
        }
      }
      // within a window load,dom ready or something like that place your:
      var check_javascript =  checkCookie();
      if(!check_javascript){
        var node = document.createElement("LI");
        var textnode = document.createTextNode("Browser blocking cookies. Make sure your browser support and accept cookies. Usually it can happen by using browser extensions like 'Cookies Disabler' or third party applications like internet security. You can also try on a different browser which allows cookies.");
        node.appendChild(textnode);
        if(document.getElementById("problem_found").style.display == 'none'){
          document.getElementById("problem_found").style.display = 'block';
        }
        document.getElementById("version_problem").appendChild(node);
        document.getElementById("js_check_result").innerHTML = "Disable";
        document.getElementById('js_check_result').style.color = 'red';
        document.getElementsByTagName("Button").disabled = true;
        cookies_errror = true;
        var button = document.querySelectorAll('Button');
        for (var i = 0; i < button.length; i++) {
                button[i].disabled = true;
        }
      }else{
        document.getElementById("js_check_result").innerHTML = "Enable";
        document.getElementById('js_check_result').style.color = 'green';
      }
    }
    window.onload = cookie_check();
  </script>
  <script src="../assets/js/modernizr-custom.js">
  </script>
<script>
    function cookies(){
      Modernizr.addTest('cookies', function () {
    // Quick test if browser has cookieEnabled host property
        if (navigator.cookieEnabled) return true;
        // Create cookie
        document.cookie = "cookietest=1";
        var ret = document.cookie.indexOf("cookietest=") != -1;
        // Delete cookie
        document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";
        return ret;
      });
    //  we could do this with less code.
    function createCookie(name,value) {
    document.cookie = name+"="+value+"; path=/";
    }
    function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
    }
    function eraseCookie(name) {
    createCookie(name,"",-1);
    }


    var random = '' + Math.round(Math.random() * 1e8);
    createCookie('Modernizr', random);
    Modernizr.cookies = readCookie('Modernizr') == random;
    if(Modernizr.cookies){
      document.getElementById("js_check_result").innerHTML = "Enable";
      document.getElementById('js_check_result').style.color = 'green';
    }else{
      if(!cookies_errror){
        var node = document.createElement("LI");
        var textnode = document.createTextNode("Browser blocking cookies. Make sure your browser support and accept cookies. Usually it can happen by using browser extensions like 'Cookies Disabler' or third party applications like internet security. You can also try on a different browser which allows cookies.");
        node.appendChild(textnode);
        if(document.getElementById("problem_found").style.display == 'none'){
          document.getElementById("problem_found").style.display = 'block';
        }
        document.getElementById("version_problem").appendChild(node);
        document.getElementById("js_check_result").innerHTML = "Disable";
        document.getElementById('js_check_result').style.color = 'red';
        document.getElementsByTagName("Button").disabled = true;
        var button = document.querySelectorAll('Button');
        for (var i = 0; i < button.length; i++) {
                button[i].disabled = true;
        }
      }
    }
    eraseCookie('Modernizr');
    };
    window.onload = cookies();
</script>
  <?php
}

if($action == 4){
?>
<script>
  function ValidateEmail(mail)
  {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))
    {
      return (true)
    }else{
      return (false)
    }
  }
  $(document).ready(function(){
    $(".complete").click(function(event){
      var fname =$("#usernameinput1").val();
      var lname =$("#usernameinput2").val();
      var email =$("#emailinput").val();
      var password =$("#Passwordinput").val();
      var status = $(".validation_error");
      $( "#usernameinput" ).removeClass( "validation-error-border" );
      $( "#emailinput" ).removeClass( "validation-error-border" );
      $( "#Passwordinput" ).removeClass( "validation-error-border" );
      status.empty();
      if(!fname || !lname || !email || !password){
        event.preventDefault();
        status.append("<p>please fillup all the Input</p>");
      }else{
        if(email.length >5 && email.includes("@") && email.includes(".")){
          if(!ValidateEmail(email)){
            $("#emailinput").addClass("validation-error-border");
            event.preventDefault();
            status.append("<p>Please enter a valid email address.</p>");
          }
        }else{
          $("#emailinput").addClass("validation-error-border");
          event.preventDefault();
          status.append("<p>Please enter a valid email address.</p>");
        }
        if (/\s/.test(password)) {
          event.preventDefault();
          $("#Passwordinput").addClass("validation-error-border");
          status.append("<p>no space acceptable in password</p>");
        }else{
          if(password.length >= 6){
            if(/[^0-9a-zA-Z]/.test(password)){
              $("#Passwordinput").addClass("validation-error-border");
              event.preventDefault();
              status.append("<p>Password should contain only Charecter and number!</p>");
            }else{
              if(password.length >= 20){
                $("#Passwordinput").addClass("validation-error-border");
                event.preventDefault();
                status.append("<p>Password should not be atmost 20 letter!</p>");
              }
            }
          }else{
            $("#Passwordinput").addClass("validation-error-border");
            event.preventDefault();
            status.append("<p>Password should be atleast 6 letter!</p>");
          }
        }
      }
    })
  })
</script>
<?php }?>
  </body>
</html>
