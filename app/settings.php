<?php
$title = 'Settings';
include '../include/header.php';
function test_input($data) { //filter value function
    $db = new database();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strtolower($data);
    $data = mysqli_real_escape_string($db->link, $data);
    return $data;
}
$get_type = '';
$limit = 5;
$user_id = $_SESSION['id'];
$get_default_type = '';
if (isset($_GET["type"]) && ($_GET["type"] == 'free' || $_GET["type"] == 'free' || $_GET["type"] == 'role' || $_GET["type"] == 'disposable')) {
    $get_type = test_input($_GET['type']);
}
if (isset($_GET["action_type"]) && $_GET["action_type"] == 'default') {
    $get_default_type = test_input($_GET['action_type']);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['filter'])) {
          if (isset($_POST["type"]) && ($_POST["type"] == 'free' || $_POST["type"] == 'free' || $_POST["type"] == 'role' || $_POST["type"] == 'disposable')) {
              $get_type = test_input($_POST['type']);
          }
        }
}

if (isset($_GET["page"]) && is_numeric($_GET["page"]) && $_GET["page"] > 0) {
    $pn = test_input($_GET["page"]);
} else {
    $pn = 1;
};


$start_from = ($pn - 1) * $limit;
$i = $start_from;

if(!empty($get_default_type)){
  $sql_w_l = "SELECT * FROM email_category WHERE user_id = 'all'";
}else{
  $sql_w_l = "SELECT * FROM email_category WHERE user_id = '$user_id'";
}
if(!empty($get_type)){
  $account_type = ucfirst($get_type).' '.'Account';
  $sql_w_l .= " AND e_type = '$account_type'";
  $sql = $sql_w_l." LIMIT ".$start_from.",".$limit;
}else{
  $sql = $sql_w_l." LIMIT ".$start_from.",".$limit;
}
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">Settings</h1>
</div>
<div class="">
  <div class="row">
    <div class="card shadow p-4 col-lg-6 m-3">
      <h5 class="">Send Mail Settings</h5>
      <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'send_mail'){ ?>
        <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
      <?php } ?>

      <div class="setting-box mt-4">

        <?php
                  $user_query3 =  "SELECT * FROM timer WHERE user_id = '$user_id' ";
                  $user_read3 = $db->select($user_query3);
                  if($user_read3){
                    $row3 = $user_read3->fetch_assoc();
                  ?>
        <form action="../functions/<?php echo $settings_func;?>" method="post">
          <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Email Amount</label>
            <div class="col-sm-8">
              <input type="number" required name="range" class="form-control" value="<?php echo $row3['e_range']?>" placeholder="How many email can send per hour">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Time Limit (Hour)</label>
            <div class="col-sm-8">
              <input type="number" required name="time_range" class="form-control" value="<?php echo $row3['time_range']/60;?>" placeholder="Limit of Hour">
            </div>
          </div>
          <div class="form-group">
            <div class="float-right">
              <button type="submit" name="timer_btn" class="btn btn-light">Save</button>
              <?php }?>
            </div>
          </div>
        </form>
      </div> <!-- /.setting-box -->

    </div>
    <?php if(isset($_SESSION['auth_log']) && $_SESSION['auth_log'] == true){ ?>
    <div class="rounded bg-light border shadow pt-4 pl-4 pr-4 m-3 col-lg-5 row">
      <div class="col-6">
        <h5 class="">Registration</h5>
        <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'registration'){ ?>
          <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
        <?php } ?>
          <?php
          // registration enable check
          $reg_check = true;
          $reg_opt_chk_sql = "SELECT * FROM registration WHERE id = 1";
          $reg_opt_chk_read = $db->select($reg_opt_chk_sql);
          if($reg_opt_chk_read){
            $reg_opt_chk_row = $reg_opt_chk_read->fetch_assoc();
            $reg_action = $reg_opt_chk_row['action'];
          ?>
          <form action="../functions/<?php echo $settings_func;?>" method="post">
            <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="registration" id="exampleRadios1" value="active" <?php echo (($reg_action == 'active') ? 'checked': '')?>>
              <label class="form-check-label" for="exampleRadios1">
                Enabled <?php echo (($reg_action == 'active') ? '<small class="text-success">(active)</small>': '')?>
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="registration" id="exampleRadios2" value="off" <?php echo (($reg_action == 'off') ? 'checked': '')?>>
              <label class="form-check-label" for="exampleRadios2">
                disabled <?php echo (($reg_action == 'off') ? '<small class="text-success">(active)</small>': '')?>
              </label>
            </div>
            <div class="form-group">
              <div class="mt-2">
                <button type="submit" name="registration_btn" class="btn btn-sm btn-info">Save</button>
            <?php }?>
              </div>
            </div>
          </form>
      </div>
      <div class="col-6">
        <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'logo_site'){ ?>
          <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
        <?php } ?>
          <form  action="../functions/<?php echo $settings_func;?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label for="exampleInputEmail1">Logo Change</label>
              <input type="file" name="logo_image" class="form-control-file">
            </div>
            <div class="form-group">
              <label for="exampleInputPassword1">Site title</label>
              <input required type="text" class="form-control form-control-sm" name="site_title" placeholder="Enter site title" value="<?php echo $app_title;?>">
            </div>
            <button type="submit" name="logo_title_change" class="btn btn-sm btn-info">Save</button>
          </form>
      </div>

    </div>
    <?php }?>
  </div>
  <div class="">
    <div class="card shadow p-4 m-1">
      <h5 class="">Account Type Settings</h5>
      <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'save_mail'){ ?>
        <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
      <?php } ?>
      <div class="mt-4 row">
        <div class="col-lg-6 border-right pr-4">
          <form class="form-inline" action="<?php echo $settings_page.(!empty($get_default_type) ? '?action_type=default':'');?>" method="post">
            <div class="form-group mr-3 mb-2">
              <select name="type" class="form-control form-control-sm" placeholder="Password">
                <option value="">Select Type All</option>
                <option <?php if($get_type == 'free'){echo 'selected';}?> value="free">Free Account</option>
                <option <?php if($get_type == 'role'){echo 'selected';}?> value="role">Role Account</option>
                <option <?php if($get_type == 'disposable'){echo 'selected';}?> value="disposable">Disposable</option>
              </select>
            </div>
            <button type="submit" name="filter" class="btn btn-primary mb-2 btn-sm">Filter</button>
            <?php if(!empty($get_default_type)){ ?>
              <a href="<?php echo $settings_page;?>" class="btn btn-sm btn-light ml-3 mb-1">Yours</a>
            <?php }else{?>
              <a href="<?php echo $settings_page;?>?action_type=default" class="btn btn-sm btn-light ml-3 mb-1">Default value</a>
            <?php }?>
          </form>
          <table class="table table-sm">
            <thead>
              <tr>
                <th scope="col">SL</th>
                <th scope="col">Domain Name</th>
                <th scope="col">Account Type</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $user_read = $db->select($sql);
              $count4 = mysqli_num_rows($user_read);
              if($count4 > 0){
                while ($row = $user_read->fetch_assoc()) {
                  $i++; ?>
              <tr>
                <th scope="row"><?php echo $i ;?></th>
                <td id="name_<?php echo $i ;?>"><?php echo $row['name'] ;?></td>
                <td id="type_<?php echo $i ;?>"><?php echo $row['e_type'] ;?></td>
                <td>
                  <?php if ($row['user_id'] == 'all') { ?>
                    <span class="badge badge-light">Default</button>
                  <?php }else{ ?>
                    <form class="" action="../functions/<?php echo $settings_func;?>" method="post">
                      <span table-id = "<?php echo $i ;?>" data-target = "<?php echo $row['id'] ;?>" class="edit-btn cursor-pointer mr-2"><i class="fas fa-edit"></i></span>
                      <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
                      <input type="text" hidden name="target_id" value="<?php echo $row['id'] ;?>">
                      <button type="submit" name="delete-btn" class="btn btn-sm btn-light" onclick="return confirm('you want to delete?');"><i class="far fa-trash-alt"></i></button>
                    </form>
                  <?php }?>
                   </td>
              </tr>
            <?php }} ?>

            </tbody>
          </table>
          <!-- pagination section -->

          <div class="pagination-sec">

            <ul class="pagination float-right">

            <?php
              $sql_read_p = $db->select($sql_w_l);
              $count_row = mysqli_num_rows($sql_read_p);
              $total_pages = ceil($count_row / $limit);
              $k = (($pn+1>$total_pages)?$total_pages-1:(($pn-1<1)?2:$pn));
              $pagLink = "";
              if($total_pages > 1){
              if($pn>=2){
                  echo "<li class='page-item'><a class='page-link' href='".$settings_page."?page=".($pn-1).(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'> < </a></li>";
                  echo "<li class='page-item'><a class='page-link' href='".$settings_page."?page=1".(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'> 1 </a></li>";
                  if(($pn-1) > 2){
                    echo "<li class='page-item'><a class='page-link' href='".$settings_page."?page=".($pn-2).(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'> ... </a></li>";
                  }
              }
              if($pn == 1){
                echo "<li class='page-item active'><a class='page-link' href='".$settings_page."?page=1" .(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'> 1 </a></li>";
              }
              for ($i=-1; $i<=1; $i++) {
                  if($k+$i != 1 && $k+$i != $total_pages && $k+$i < $total_pages && $k+$i > 0){
                    if($k+$i==$pn)
                      $pagLink .= "<li class='page-item active'><a class='page-link' href='".$settings_page."?page=".($k+$i).(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'>".($k+$i)."</a></li>";
                    else
                      $pagLink .= "<li class='page-item'><a class='page-link' href='".$settings_page."?page=".($k+$i).(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'>".($k+$i)."</a></li>";
                  }
              };
              echo $pagLink;
              if($pn == $total_pages){
                echo "<li class='page-item active'><a class='page-link' href='".$settings_page."?page=".$total_pages.(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'> ".$total_pages." </a></li>";
              }
              if($pn<$total_pages){
                  if(($total_pages-$pn) > 2){
                    echo "<li class='page-item'><a class='page-link' href='".$settings_page."?page=".($pn+2).(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'> ... </a></li>";
                  }
                  echo "<li class='page-item'><a class='page-link' href='".$settings_page."?page=".$total_pages.(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'> ".$total_pages." </a></li>";
                  echo "<li class='page-item'><a class='page-link' href='".$settings_page."?page=".($pn+1).(!empty($get_type) ? '&type='.$get_type : '').(!empty($get_default_type) ? '&action_type=default':''). "'> > </a></li>";
              }
            }

            ?>

            </ul>

           </div>
        </div>
        <div id="add-sec" class="col-lg-6 pl-lg-4">
          <div class="">
            <h6 class="">Add new Account type <button id="add-more-btn" class="btn btn-sm btn-link"><i class="far fa-plus-square"></i> Add multiple</button> </h6>
          </div>
          <div class="text-danger" id="add-more-error">

          </div>
          <div id="" class="form-group mt-3">
          <form class="" action="../functions/<?php echo $settings_func;?>" method="post">
            <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
            <div class="row">
              <label for="" class="col-6">Domain Name</label>
              <label for="" class="col-6">Account Type</label>
            </div>
            <div id="add-account-type-sec">
              <div class="row mb-2">
                <input required hidden name="listing_value[]" value="0">
                <div class="col-6">
                  <input required type="text" name="name_0" class="form-control form-control-sm" value="" placeholder="Enter Domain Name">
                </div>
                <div class="col-6">
                  <select required name="type_0" class="form-control form-control-sm">
                    <option value="" selected>Select Account type</option>
                    <option value="Free Account">Free Account</option>
                    <option value="Role Account">Role Account</option>
                    <option value="Disposable Account">Disposable</option>
                  </select>
                </div>
              </div>
            </div>
            <button type="submit" name="save_button" class="btn btn-sm btn-info pl-4 pr-4 mt-2">Save</button>
          </form>
          </div>
        </div>
        <!-- update -->
        <div id="edit-sec" class="col-lg-6 pl-lg-4">
          <div class="">
            <h6 id="edit-target-value"> </h6>
          </div>
          <div id="" class="form-group mt-3">
            <div class="row">
              <label for="" class="col-6">Domain Name</label>
              <label for="" class="col-6">Account Type</label>
            </div>
            <form class="" action="../functions/<?php echo $settings_func;?>" method="post">
              <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
              <input type="text" id="target-id" hidden name="target_id" value="">
            <div>
              <div class="row mb-2">
                <div class="col-6">
                  <input required id="edit-target-name" type="text" name="name" class="form-control form-control-sm" value="" placeholder="Enter Domain Name">
                </div>
                <div class="col-6">
                  <select required id="edit-target-type" name="type" class="form-control form-control-sm">
                    <option value="" selected>Select Account type</option>
                    <option value="Free Account">Free Account</option>
                    <option value="Role Account">Role Account</option>
                    <option value="Disposable Account">Disposable</option>
                  </select>
                </div>
              </div>
            </div>
            <button type="submit" name="edit_button" class="btn btn-sm btn-info pl-4 pr-4 mt-2">Update</button>
            <a id="cancel-edit" class="btn btn-sm btn-dark pl-4 pr-4 mt-2 text-white cursor-pointer">Cancel</a>
          </form>
          </div>
        </div>
        <!--end update -->
      </div>

    </div>
  </div>
  <?php if(isset($_SESSION['auth_log']) && $_SESSION['auth_log'] == true){ ?>
    <div class="row">
      <div class="col-md-6">
        <div class="card shadow p-4 ml-1 mt-3 mb-3">
          <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'scan_mail_settings'){ ?>
            <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
          <?php } ?>
          <h5 class="">Scan Mail Settings</h5>
          <div class="setting-box mt-4">
            <form action="../functions/<?php echo $settings_func;?>" method="post">
                <div class="form-group row">
                  <label class="col-sm-4 col-form-label">Scan from mail</label>
                  <div class="col-sm-8">
                    <input type="email" required name="scan_from" class="form-control" value="<?php echo (!empty($scan_mail) ? $scan_mail : '');?>" placeholder="enter your scan from mail">
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-4 col-form-label">scan timeout per email (in sec)</label>
                  <div class="col-sm-8">
                    <input type="number" required name="timeout" class="form-control" value="<?php echo (!empty($scan_timeout) ? $scan_timeout: '');?>" placeholder="Enter timeout value (default 10 sec)">
                  </div>
                </div>
                <div class="form-group">
                  <div class="float-right">
                    <button type="submit" name="scan_mail_settings" class="btn btn-sm btn-info">Save</button>
                  </div>
                </div>
            </form>
          </div> <!-- /.setting-box -->
          <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'estimated_cost'){ ?>
            <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
          <?php } ?>
          <h5 class="">Estimated cost settings</h5>
          <div class="setting-box mt-4">
            <form action="../functions/<?php echo $settings_func;?>" method="post">
                <div class="form-group row">
                  <label class="col-sm-4 col-form-label">Estimated Cost Saved Per Email</label>
                  <div class="col-sm-8">
                    <input type="text" required name="estimated_cost" class="form-control" value="<?php echo (!empty($d_estimated_cost) ? $d_estimated_cost: '');?>" placeholder="enter your value (default $ 0.005)">
                  </div>
                </div>
                <div class="form-group">
                  <div class="float-right">
                    <button type="submit" name="estimated_cost_btn" class="btn btn-sm btn-info">Save</button>
                  </div>
                </div>
            </form>
          </div> <!-- /.setting-box -->

        </div>
      </div>
      <div class="col-md-6">
        <div class="card shadow p-4 ml-1 mt-3 mb-3">
          <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'licanse_status'){ ?>
            <span class="text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
          <?php } ?>
          <?php if(isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == true){ ?>
            <div class="alert bg-danger text-light text-center">License Deactivate</div>
          <?php }else{ ?>
            <div class="alert bg-success text-light text-center">License Active</div>
          <?php } ?>
          <div class="setting-box mt-4">
            <?php
            require_once('../functions/'.$enc_dec);
            $license = file_get_contents('../config/.lic');
            $license_code =  encrypt($license);
            ?>
            <form action="<?php echo $licanse_update;?>" method="post">
              <?php if(isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == true){ ?>
                <div class="form-group row">
                  <label class="col-sm-4 col-form-label">License code</label>
                  <div class="col-sm-8">
                    <input type="text" required name="license_key" class="form-control" placeholder="enter your purches/license code" value="065163064d438331674a9d641491d517">
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-4 col-form-label">Your email</label>
                  <div class="col-sm-8">
                    <input type="email" required name="email" class="form-control" placeholder="enter your email" value="weev@babiato.org">
                  </div>
                </div>
                <div class="form-group">
                  <div class="float-right">
                    <button type="submit" name="active_licanse" class="btn btn-success">Active</button>
                  </div>
                </div>
              <?php }else{ ?>
                <div class="form-group">
                  <div class="float-right">
                    <button type="submit" name="inactive_licanse" class="btn btn-danger">Deactivate</button>
                  </div>
                </div>
              <?php } ?>
            </form>
          </div> <!-- /.setting-box -->

        </div>
      </div>
    </div>

  <?php } ?>

</div>
<script>
  $('#add-more-btn').click(function(){
    var count_more_cont = $('.add-more-type-cont').length;
    if(count_more_cont <= 0){
      var index = 1;
    }else{
      var last_count = $('.add-more-type-cont').last().attr('id');
      var last_count = last_count.substring(last_count.lastIndexOf("-") + 1, last_count.length);
      var index = parseInt(last_count) +1;
    }
    if(count_more_cont < 9){
        $('#add-account-type-sec').append('<div id="add-more-'+index+'" class="row mb-2 add-more-type-cont"> <input required hidden name="listing_value[]" value="'+index+'"> <div class="col-6"><input required type="text" name="name_'+index+'" class="form-control form-control-sm" value="" placeholder="Enter Domain Name"></div><div class="col-6"><div class="input-group input-group-sm"><select required name="type_'+index+'" class="custom-select" id="inputGroupSelect04" aria-label="Example select with button addon"><option value="" selected>Select Account type</option><option value="Free Account">Free Account</option><option value="Role Account">Role Account</option><option value="Disposable Account">Disposable</option></select><div class="input-group-append ml-1"><button id="'+index+'" class="btn btn-outline-secondary cancel-btn-more-type" type="button"><i class="fas fa-times"></i></button></div></div></div></div>');
    }else{
      $('#add-more-error').text('Cannot add more than 10 item at a time!');
    }
    $('.cancel-btn-more-type').click(function(){
      var get_id_value = $(this).attr('id');
      console.log(get_id_value);
      $('#add-more-'+get_id_value).remove();
      $('#add-more-error').text('');
    })
  });
  $('.edit-btn').click(function(){
    var target_id = $(this).attr('data-target');
    var index_id = $(this).attr('table-id');
    var target_name = document.getElementById("name_"+index_id).innerHTML;
    var target_type = document.getElementById("type_"+index_id).innerHTML;
    $('#edit-target-value').text(index_id + '. Update Value');
    console.log(target_id);
    $('#edit-target-name').val(target_name);
    $("#edit-target-type").val(target_type);
    $("#target-id").val(target_id);
    $('#edit-sec').css('display','block');
    $('#add-sec').css('display','none');
  });
  $('#cancel-edit').click(function(){
    $('#edit-sec').css('display','none');
    $('#add-sec').css('display','block');
  });
</script>
<?php include '../include/footer.php';?>
<?php unset($_SESSION['action'])?>
<?php unset($_SESSION['action_message'])?>
<?php unset($_SESSION['action_cat'])?>