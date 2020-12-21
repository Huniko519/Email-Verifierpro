<?php
include '../functions/pagename.php';
include '../config/'.$config;
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$dbname = DB_NAME;
$error = false;

if (!mysqli_connect($host, $user, $pass, $dbname)) //check database configaration
{
    header("Location: ".$installer);
} else {
    include '../config/'.$database;
    $db = new database();
    $query = "SELECT * FROM installation ";
    $read = $db->check($query);
    if ($read != false) {
       $row = $read->fetch_assoc();
       if ($row['validation'] == 'false') {
            header("Location: ".$installer);
        } else {
        }
    } else {
        header("Location: ".$installer);
    }
}

// registration enable check
$reg_check = false;
$reg_opt_chk_sql = "SELECT * FROM registration WHERE id = 1";
$reg_opt_chk_read = $db->select($reg_opt_chk_sql);
if($reg_opt_chk_read){
  $reg_opt_chk_row = $reg_opt_chk_read->fetch_assoc();
  $reg_action = $reg_opt_chk_row['action'];
  if($reg_action == 'active'){
    $reg_check = true;
  }
}
include '../functions/'.$session;
Session::checkSession_log();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Login</title>
  <!-- Custom fonts for this template-->
  <link href="../assets/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="../assets/css/sb-admin-2.css" rel="stylesheet">
  <!-- custom css -->
  <link href="../assets/css/style.css" rel="stylesheet">
  <!-- Bootstrap core JavaScript-->
  <script src="../assets/js/jquery.min.js"></script>
  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/chart.js"></script>
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>

<body class="bg-gradient-primary">

  <div class="container">

    <!-- Login - Outer Row -->
    <div class="access_box row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block text-center m-auto"> <img src="../assets/app-img/evp_login.jpg" alt="..." class="img-thumbnail"> </div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <?php
                    if (isset($_GET["installetion"]) && $_GET["installetion"] == 'success') { ?>
                        <h1 class="h4 text-success mb-4">Installation Successful</h1>
                    <?php }else{ ?>
                      <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                    <?php } ?>

                  </div>
                  <form class="user" action="../functions/<?php echo $login_func;?>" method="post">
                    <div class="form-group">
                      <input type="email" class="form-control form-control-user" name="email" aria-describedby="emailHelp" placeholder="Enter Email Address...">
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control form-control-user" name="password" placeholder="Password">
                    </div>
                    <button type="submit" name = "login_action" class="btn btn-primary btn-user btn-block">
                      Login
                    </button>
                  </form>
                  <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'exicute'){ ?>
                    <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
                  <?php } ?>
                  <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'unverified'){ ?>
                    <form class="" action="../functions/<?php echo $login_func;?>" method="post">
                      <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
                    </form>
                  <?php } ?>
                  <hr>
                  <div class="text-center">
                    <a class="small" href="<?php echo $forgot_password_page;?>">Forgot Password?</a>
                  </div>
                  <?php if($reg_check){?>
                    <div class="text-center">
                      <a class="small" href="<?php echo $register_page;?>">Create an Account!</a>
                    </div>
                  <?php }?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/js/sb-admin-2.js"></script>
  <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'unverified'){ ?>
    <script>
        swal("Email is not verified!", "Check your email inbox!", "warning");
    </script>
  <?php } ?>
  <?php if(isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'token'){ ?>
    <script>
        swal("<?php echo $_SESSION['action_message'];?>", "<?php echo $_SESSION['action_submessage'];?>", "success");
    </script>
  <?php } ?>
</body>
</html>
<?php unset($_SESSION['action'])?>
<?php unset($_SESSION['action_message'])?>
<?php unset($_SESSION['action_cat'])?>
<?php unset($_SESSION['action_submessage'])?>
