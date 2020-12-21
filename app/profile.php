<?php
$title = 'User Profile';
include '../include/header.php';
$email_request = false;
  $user_id = $_SESSION['id'];
  $user_sql =  "SELECT * FROM admin WHERE id = '$user_id' ";
  $user_read = $db->select($user_sql);
  if($user_read){
    $count_row = mysqli_num_rows($user_read);
    if($count_row > 0){
    $row = $user_read->fetch_assoc();
    $user_image = $row['image'];
    $user_fname = $row['fname'];
    $user_lname = $row['lname'];
    $user_email = $row['email'];
    $user_join_date = $row['join_date'];
    $email_update_sql =  "SELECT * FROM email_change WHERE user_id = '$user_id' ";
    $email_update_read = $db->select($email_update_sql);
    if($email_update_read){
      $count_update_email = mysqli_num_rows($email_update_read);
      if($count_update_email > 0){
        $update_email_row = $email_update_read->fetch_assoc();
        $email_request = true;
        $request_email = $update_email_row['email'];
      }
    }
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">User Profile</h1>
</div>
<div class="row">
  <div class="col-lg-3 col-md-4 col-sm-6">
    <div class="">
      <img id="image-sec" src="../uploads/<?php echo ((!empty($user_image) && $user_image != '0' ) ? $user_image : 'thumb.png'); ?>" alt="..." class="img-thumbnail">
      <button id="image-change-btn" class="btn btn-sm btn-light"><i class="fas fa-edit"></i> Change</button>
    </div>
    <div id="change-image" class="change-sec">
      <form class="form-inline" action="../functions/<?php echo $profile_func;?>" method="post" enctype="multipart/form-data">
        <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
        <div class="input-group input-group-sm">
          <div class="custom-file">
            <input required type="file" name="img_url" class="custom-file-input input-sec" id="inputGroupFile14" aria-describedby="inputGroupFileAddon04">
            <label class="custom-file-label" for="inputGroupFile14">Choose file</label>
          </div>
          <div class="input-group-append pl-2">
            <div class="btn-group">
              <button type="submit" name="image_btn" class="btn btn-sm btn-dark">
                <span class="text">Save</span>
              </button>
              <span id="image-change-cancel" class="btn text-white-50 cursor-pointer profile-save-cancel">
                <i class="fas fa-times"></i>
              </span>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <script>
    // Add the following code if you want the name of the file appear on select
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
  </script>
  <div class="col-lg-9 col-md-8 pt-4 pt-md-0">
    <div class="row">
      <div class="card shadow col-lg-7 p-4">
        <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'name'){ ?>
          <span class="mb-3 text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
        <?php } ?>
        <div class="pb-4">
          <div class="d-inline-block float-left pr-4">
            <i class="far fa-user"></i>
          </div>
          <div class="d-inline-block">
            <h6 class="text-xs font-weight-bold text-primary text-uppercase mb-1">Name <span id="change-name-btn" class="ml-2 edit-btn"><i class="fas fa-edit"></i> change</span></h6>
            <p id="data-name" class="h5 mb-0 font-weight-bold text-gray-800 user-data"><?php echo $user_fname.' '.$user_lname?></p>
            <div id="change-name" class=" change-sec">
              <form class="form-group" action="../functions/<?php echo $profile_func;?>" method="post">
                <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
              <div class="input-group input-group-sm mb-2 mt-2">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1">First Name</span>
                </div>
                <input required type="text" class="form-control form-control-sm input-sec" name="fname" value="" placeholder="Enter First Name" aria-label="Username" aria-describedby="basic-addon1">
              </div>
              <div class="input-group input-group-sm mb-2 mt-2">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon2">Last Name</span>
                </div>
                <input required type="text" class="form-control form-control-sm input-sec" name="lname" value="" placeholder="Enter Last Name" aria-label="Username" aria-describedby="basic-addon2">
              </div>
              <div class="btn-group">
                <button type="submit" name="name_btn" class="btn btn-sm btn-dark">
                  <span class="text">Save</span>
                </button>
                <span id="name-change-cancel" class="btn btn-sm text-white-50 profile-save-cancel cursor-pointer">
                  <i class="fas fa-times"></i>
                </span>
              </div>
            </form>
            </div>
          </div>
        </div>
        <div class="pb-4">
          <div class="d-inline-block">
            <div class="d-inline-block float-left pr-4">
              <i class="far fa-envelope-open"></i>
            </div>
            <div class="d-inline-block">
              <h6 class="text-xs font-weight-bold text-primary text-uppercase mb-1">Email <span id="change-email-btn" class="ml-2 edit-btn"><i class="fas fa-edit"></i> change</span></h6>
              <p id="data-email" class="h5 mb-0 font-weight-bold text-gray-800 user-data"><?php echo $user_email ;?></p>
              <?php
              if ($email_request) { ?>
                <div class="">
                  <small>Update request mail:
                    <strong><?php echo $request_email;?></strong>
                    <form class="d-inline-block" action="../functions/<?php echo $profile_func;?>" method="post">
                      <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
                      <button type="submit" name="email_change_remove_btn" class="btn btn-sm btn-link p-0">(remove)</button>
                    </form>, resend verify sms
                    <form class="d-inline-block" action="../functions/<?php echo $profile_func;?>" method="post">
                      <input required type="number" hidden name="user_id" value="<?php echo $user_id;?>">
                      <button type="submit" name="email_change_mail_btn" class="btn btn-sm btn-link p-0">click here</button>
                    </form>
                  </small>
                </div>
              <?php }

              ?>

              <div id="change-email" class="change-sec">
                <form class="form-group" action="../functions/<?php echo $profile_func;?>" method="post">
                  <input required type="number" hidden name="user_id" value="<?php echo $user_id;?>">
                <div class="input-group input-group-sm mb-2 mt-2">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroup-sizing-sm">Email</span>
                  </div>
                  <input required type="email" name="email" value="sajib@gmail.com" class="form-control form-control-sm input-sec" value="" placeholder="Enter new email" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                </div>
                <div class="btn-group">
                  <button type="submit" name="email_btn" class="btn btn-sm btn-dark">
                    <span class="text">Save</span>
                  </button>
                  <span id="email-change-cancel" class="btn btn-sm text-white-50 cursor-pointer profile-save-cancel">
                    <i class="fas fa-times"></i>
                  </span>
                </div>
              </form>
              </div>
            </div>
          </div>
        </div>
        <div class="pb-4">
          <div class="d-inline-block float-left pr-4">
            <i class="far fa-calendar-alt"></i>
          </div>
          <div class="d-inline-block">
            <h6 class="text-xs font-weight-bold text-primary text-uppercase mb-1">Join Date</h6>
            <p class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $user_join_date ;?></p>
          </div>
        </div>
        <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#editprofile"><i class="fas fa-edit"></i> Change Password</button>
      </div>
    </div>
  </div>
</div>
<!-- Logout Modal-->
<div class="modal fade" id="editprofile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Change Password</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <form action="../functions/<?php echo $profile_func;?>" method="post">
        <input type="number" hidden name="user_id" value="<?php echo $user_id;?>">
      <div class="modal-body p-4">
          <div class="" id="pass-change">
            <div class="form-group">
              <label for="exampleInputPassword1">Current Password</label>
              <input required type="password" name="current_pass" class="form-control form-control-sm" id="exampleInputPassword1">
            </div>
            <div class="form-group">
              <label for="exampleInputPassword1">New Password</label>
              <input required type="password" name="new_pass" class="form-control form-control-sm" id="exampleInputPassword1">
            </div>
            <div class="form-group">
              <label for="exampleInputPassword1">Confirm New Password</label>
              <input required type="password" name="con_pass" class="form-control form-control-sm" id="exampleInputPassword1">
            </div>
          </div>
      </div>
      <div class="modal-footer">
        <span class="btn btn-secondary cursor-pointer" data-dismiss="modal">Cancel</span>
        <button class="btn btn-info" type="submit" name="pass_btn">Submit</button>
      </div>
    </form>
    </div>
  </div>
</div>
<?php
  }
}
?>
<script>
$('#image-change-btn').click(function(){
  $('.change-sec').css('display','none');
  $('.user-data').css('display','block');
  $('#change-image').css('display','flex');
  $('.input-sec').val('');
})
$('#image-change-cancel').click(function(){
  $('#change-image').css('display','none');
  $('.input-sec').val('');
})
$('#change-email-btn').click(function(){
  $('.change-sec').css('display','none');
  $('.user-data').css('display','block');
  $('#change-email').css('display','flex');
  $('#data-email').css('display','none');
  $('.input-sec').val('');
})
$('#email-change-cancel').click(function(){
  $('#change-email').css('display','none');
  $('#data-email').css('display','block');
  $('.input-sec').val('');
});
$('#change-name-btn').click(function(){
  $('.change-sec').css('display','none');
  $('.user-data').css('display','block');
  $('#change-name').css('display','flex');
  $('#data-name').css('display','none');
  $('.input-sec').val('');
})
$('#name-change-cancel').click(function(){
  $('#change-name').css('display','none');
  $('#data-name').css('display','block');
  $('.input-sec').val('');
});
</script>
<?php include '../include/footer.php';?>
<?php unset($_SESSION['action'])?>
<?php unset($_SESSION['action_message'])?>
<?php unset($_SESSION['action_cat'])?>