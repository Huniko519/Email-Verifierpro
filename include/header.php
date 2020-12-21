<?php
if(isset($title)){
include (($title == 'dashboard') ? '': '../').'functions/pagename.php';
include (($title == 'dashboard') ? '': '../').'functions/'.$session;
if($title == 'dashboard'){
  Session::checkSession_d(); //check login session for index page
}else{
  Session::checkSession(); // check login for others page
}

// ------check admin login---------
if($title == 'User Management'){
  Session::Check_auth();
}
if(isset($_GET['action']) && $_GET['action'] == "logout"){
  if($title == 'dashboard'){
    Session:: destroy_d();
  }else{
    Session:: destroy();
  }
}

if($title == 'dashboard' && !isset($_SESSION['license_check']) && !isset($_SESSION['licance_error'])) {
  header('location:app/'.$license_check);
}

$user_id = $_SESSION['id'];
$fname = $_SESSION['fname'];
$image = $_SESSION['image'];

include (($title == 'dashboard') ? '': '../').'config/'.$config;
include (($title == 'dashboard') ? '': '../').'config/'.$database;
$db = new database();

$user_data_sql = "SELECT * FROM admin WHERE id = '$user_id'";
$user_data_read = $db->select($user_data_sql);
if($user_data_read){
  $user_data_row = $user_data_read->fetch_assoc();
  $suspend_check = $user_data_row['status'];
  $fname = $user_data_row['fname'];
  $image = $user_data_row['image'];
  if($suspend_check == 'suspend'){
    echo "<script>alert('Your account has been suspended by the authorities.')</script>";
    if($title == 'dashboard'){
      Session:: destroy_d();
    }else{
      Session:: destroy();
    }
  }
}

$scan_timeout = '';
$scan_mail = '';
$d_estimated_cost = '';
$logo_title_sql = "SELECT * FROM logo_title ";
$logo_title_read = $db->select($logo_title_sql);
if ($logo_title_read) {
    $logo_title_check = mysqli_num_rows($logo_title_read);
    if ($logo_title_check > 0) {
      $logo_title_row = $logo_title_read->fetch_assoc();
      $logo_image = $logo_title_row['logo'];
      $app_title = $logo_title_row['site_title'];
      $scan_timeout = $logo_title_row['scan_time_out'];
      $scan_mail = $logo_title_row['scan_mail'];
      $d_estimated_cost = $logo_title_row['estimated_cost'];
      if(empty($logo_image)){
        $logo_image = 'default_logo.png';
      }
      if(empty($app_title)){
        $app_title = 'Email Verifier Pro';
      }
    }else{
      $logo_image = 'default_logo.png';
      $app_title = 'Email Verifier Pro';
    }
  }

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title><?php echo ucfirst($title); ?></title>
  <!-- Custom fonts for this template-->
  <link href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/css/sb-admin-2.css" rel="stylesheet">
  <!-- custom css -->
  <link href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/css/style.css" rel="stylesheet">
  <!-- Bootstrap core JavaScript-->
  <script src="<?php echo (($title == 'dashboard') ? '': '../')?>assets/js/jquery.min.js"></script>
  <script src="<?php echo (($title == 'dashboard') ? '': '../')?>assets/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo (($title == 'dashboard') ? '': '../')?>assets/js/chart.js"></script>
</head>

<body id="page-top" class="sidebar-toggled">
  <noscript>
    <div class="js_stop">
      <strong>Browser Do Not support JavaScript! </strong>
      We're sorry, but 'EMail Verifier Pro' doesn't work without JavaScript enabled. If you can't enable JavaScript in this browser then try a different browser which support JavaScript.
    </div>
      <style>#wrapper { display:none; }</style>
   </noscript>
  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav sidebar sidebar-dark accordion toggled" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo (($title == 'dashboard') ? '': '../').$index_page;?>">
        <div class="sidebar-brand-icon rotate-n-15">
          <img class="logo-img" src="<?php echo (($title == 'dashboard') ? '': '../').'';?>assets/app-img/<?php echo $logo_image?>" alt="">
        </div>
        <div class="sidebar-brand-text mx-3"><?php echo $app_title?></div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item <?php echo (($title == 'dashboard') ? 'active' : '')?>">
        <a class="nav-link" href="<?php echo (($title == 'dashboard') ? '': '../').$index_page;?>">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
      </li>
       <li class="nav-item <?php echo (($title == 'My List') ? 'active' : '')?>">
        <a class="nav-link" href="<?php echo (($title == 'dashboard') ? 'app/': '').$my_list_page;?>">
          <i class="fas fa-fw fa-table"></i>
          <span>My Listing</span></a>
      </li>
      <li class="nav-item <?php echo (($title == 'Email Listing') ? 'active' : '')?>">
       <a class="nav-link" href="<?php echo (($title == 'dashboard') ? 'app/': '').$lear_management_page;?>">
         <i class="fas fa-fw fa-table"></i>
         <span>Lead Management</span></a>
     </li>
     <li class="nav-item <?php echo (($title == 'Send Mail') ? 'active' : '')?>">
      <a class="nav-link" href="<?php echo (($title == 'dashboard') ? 'app/': '').$send_mail_page;?>">
        <i class="fas fa-fw fa-table"></i>
        <span>Send Mail</span></a>
    </li>
    <?php if(isset($_SESSION['auth_log']) && $_SESSION['auth_log'] == true){ ?>
      <li class="nav-item <?php echo (($title == 'User Management') ? 'active' : '')?>">
       <a class="nav-link" href="<?php echo (($title == 'dashboard') ? 'app/': '').$user_management_page?>">
         <i class="fas fa-fw fa-table"></i>
         <span>User Management</span></a>
     </li>
    <?php }?>

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-lg-block">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>
    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" style="overflow-x: inherit;" class="d-flex flex-column">
      <?php if(isset($_SESSION['license_check']) && isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == true){ ?>
        <div class="alert bg-danger text-light text-center"><?php echo $_SESSION['licanse_error_msg']; ?></div>
      <?php } ?>
      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-lg-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">
            <div class="topbar-divider d-none d-sm-block"></div>
            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $fname; ?></span>
                <img class="img-profile rounded-circle" src="<?php echo (($title == 'dashboard') ? '': '../')?>uploads/<?php echo (!empty($image) && $image != '0' ) ? $image : 'thumb.png'; ?>">
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?php echo (($title == 'dashboard') ? 'app/': '').$profile_page?>">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="<?php echo (($title == 'dashboard') ? 'app/': '').$settings_page?>">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>

          </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

<?php }?>
