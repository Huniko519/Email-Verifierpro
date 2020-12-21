<?php
$title = 'My List';
include '../include/header.php';
$user_id = $_SESSION['id'];
// $email_info = "SELECT DISTINCT(csv_file_name) FROM user_email_list WHERE user_id = '$user_id' ORDER BY id DESC ";
$email_info = "SELECT csv_file_name FROM user_email_list WHERE user_id = '$user_id' GROUP BY csv_file_name ORDER BY csv_file_name DESC ";
$info = $db->select($email_info);
?>
		 <!-- Page Heading -->
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">My Listing</h1>
            <div class="pull-right">
              <?php if(isset($_SESSION['license_check']) && isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == false){ ?>
                <a href="#" id="add_list" data-toggle="modal" data-target="#addList" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"> Add List</a>
              <?php }else{ ?>
                <button disabled class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"> Add List</button>
              <?php } ?>

            </div>
        </div>
          <!-- Content Row -->
          <?php
              $i=0;
              $c=0;
              while ($result = $info->fetch_assoc()) {
                $csv_file_name = $result['csv_file_name'];
                $task_check_status = false;
                $task_check_sql = "SELECT * FROM task WHERE csv_name = '$csv_file_name' ";
                $task_check_read = $db->select($task_check_sql);
                $task_count_check = mysqli_num_rows($task_check_read);
                if ($task_count_check > 0) {
                  $task_check_status = true;
                }
                $c++;
                $csv_file_info = "SELECT COUNT(email_name) as t_email, MIN(create_date) as create_time,
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'valid' AND user_id = '$user_id') AS 'count_valid',
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'invalid' AND user_id = '$user_id') AS 'count_invalid',
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'catch all' AND user_id = '$user_id') AS 'count_catchall',
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'unknown' AND user_id = '$user_id') AS 'count_unknown',
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_status = 'Not Verify' AND user_id = '$user_id') AS 'count_not_verify',
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND verification_response = 'syntax error' AND user_id = '$user_id') AS 'count_syntax',
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_type = 'Free Account' AND user_id = '$user_id') AS 'count_free',
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_type = 'Role Account' AND user_id = '$user_id') AS 'count_role',
                (SELECT COUNT(email_name) FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND email_type = 'Disposable Account' AND user_id = '$user_id') AS 'count_disposable'
                 FROM user_email_list WHERE csv_file_name = '$csv_file_name' AND user_id = '$user_id' ";
                $csv_info = $db->select($csv_file_info);
                $csv_result = $csv_info->fetch_assoc();
                $t_email = $csv_result['t_email'];
                $create_time = $csv_result['create_time'];
                $count_valid = $csv_result['count_valid'];
                $count_invalid = $csv_result['count_invalid'];
                $count_catchall = $csv_result['count_catchall'];
                $count_unknown = $csv_result['count_unknown'];
                $count_not_verify = $csv_result['count_not_verify'];
                $count_free = $csv_result['count_free'];
                $count_role = $csv_result['count_role'];
                $count_disposable = $csv_result['count_disposable'];
                $count_syntax = $csv_result['count_syntax'];
                $total_verify = $count_valid + $count_invalid + $count_catchall + $count_unknown;
                $csv_name_ex = preg_replace('/\\.[^.\\s]{3,4}$/', '', $csv_file_name);
                $verify_per_status = ceil((($count_valid + $count_invalid + $count_catchall + $count_unknown) / $t_email) * 100);
          ?>
          <div class="row scan_list" id="mega_bar_<?php echo $csv_name_ex; ?>">

            <!-- File - First Column -->
            <div class="col-md-12 col-lg-3">

              <!-- Custom Text Color Utilities -->
              <div class="card shadow mt-4 mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">File Name : <span id="filename"><?php echo $result['csv_file_name'] ?></span></h6>
                </div>
                <div class="card-body">

                  <p class="text-gray-800 p-3 m-0">Created : <?php echo $create_time ?></p>

                  <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#model_<?php echo $csv_name_ex; ?>">
                   Download
                </button>
                <button class="file_delete_btn btn btn-danger" data-target = '<?php echo $result['csv_file_name']; ?>'>
                 <i class="fas fa-trash"></i>
              </button>
                </div>
              </div>

            </div>

            <!-- Result - Second Column -->
            <div class="col-lg-5">

              <!-- Background Gradient Utilities -->
              <div class="card shadow mt-4 mb-4">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th align="center" colspan="2">Verified Result
                        <?php if($verify_per_status < 100 && $verify_per_status != 0){ ?>
                          <h6 class="d-inline-block float-right text-primary" id="per_count_<?php echo $csv_name_ex;?>" ><?php echo $verify_per_status.'% ('.$total_verify.')';?></h6>
                        <?php }?>
                      </th>
                    </tr>
                    <script type="text/javascript">

                    </script>
                    <tr>
                      <td>
                        <p>Total : <?php echo $csv_result['t_email'] ?></p>
                        <p>Valid : <span id="valid_<?php echo $csv_name_ex;?>"><?php echo $count_valid;?></span></p>
                        <p>Invalid: <span id="invalid_<?php echo $csv_name_ex;?>"><?php echo $count_invalid;?></span></p>
                        <p>Catch All : <span id="catchall_<?php echo $csv_name_ex;?>"><?php echo $count_catchall;?></span></p>
                        <p>Unknown: <span id="unknown_<?php echo $csv_name_ex;?>"><?php echo $count_unknown;?></span></p>
                      </td>
                      <td>
                        <p>Syntax Error :  <span id="syntax_<?php echo $csv_name_ex;?>"><?php echo $count_syntax;?></span></p>
                        <p>Disposable :  <span id="disposable_<?php echo $csv_name_ex;?>"><?php echo $count_disposable;?></span></p>
                        <p>Free Account :  <span id="free_account_<?php echo $csv_name_ex;?>"><?php echo $count_free;?></span></p>
                        <p>Role Account :  <span id="role_account_<?php echo $csv_name_ex;?>"><?php echo $count_role;?></span></p>
                      </td>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>

            </div>
            <!-- Chart - Third Column -->
            <div class="col-lg-4">

              <!-- Grayscale -->
                <?php
                if($count_not_verify != 0){
                  if($task_check_status){ ?>
                    <div class="preloader" style="display:block" id="preloader_<?php echo $csv_name_ex; ?>">
                      <div class="spinner-border text-info" role="status">
                        <span class="sr-only">Loading...</span>
                      </div>
                      <div class="task-cancel-btn">
                        <button data-target='<?php echo $result['csv_file_name']; ?>' type="button" class="btn btn-danger btn-sm task-cancel-btn" name="button">Cancel</button>
                      </div>
                    </div>
                    <button style="display:none;" id="cancel_<?php echo $csv_name_ex; ?>" class="btn btn-danger btn-icon-split verify_email">
                      <span class="text verify-btn" id="" data-target='<?php echo $result['csv_file_name'] ?>'>Verify</span>
                    </button>
                  <?php }else{ ?>
                    <button class="btn btn-danger btn-icon-split verify_email" id="cancel_<?php echo $csv_name_ex; ?>">
                      <span class="text verify-btn" id="" data-target='<?php echo $result['csv_file_name'] ?>'>Verify</span>
                    </button>
                    <div class="preloader" id="preloader_<?php echo $csv_name_ex; ?>">
                      <div class="spinner-border text-info" role="status">
                        <span class="sr-only">Loading...</span>
                      </div>
                      <div class="task-cancel-btn">
                        <button type="button" class="btn btn-danger btn-sm task-cancel-btn" data-target='<?php echo $result['csv_file_name']; ?>' name="button">Cancel</button>
                      </div>
                    </div>
                  <?php }?>
                  <div class="card-body verify-chart" id='chart_<?php echo $csv_name_ex; ?>'>
                    <div class="chart-pie pt-4 pb-2">
                      <canvas id='myChart_<?php echo $csv_name_ex; ?>'></canvas>
                      <canvas id='new_myChart_<?php echo $csv_name_ex; ?>'></canvas>
                    </div>
                  </div>
                <?php }else{  ?>
                <!-- Card Header - Dropdown -->

                <!-- Card Body -->
                <div style="display:block" class="card-body verify-chart" id='chart_<?php echo $csv_name_ex; ?>'>
                  <div class="chart-pie pt-4 pb-2">
                    <canvas class="cart_sizing" id='myChart_<?php echo $csv_name_ex; ?>'></canvas>
                    <canvas class="cart_sizing" id='new_myChart_<?php echo $csv_name_ex; ?>' style="display:none;"></canvas>
                  </div>
                </div>
                <?php } ?>

                <script src="../assets/js/chart.js"></script>
                <script>
                var ctx = document.getElementById('myChart_<?php echo $csv_name_ex; ?>').getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Valid', 'Invalid', 'Catch All', 'Unknown'],
                        datasets: [{
                            label: '# of Votes',
                            data: [<?php echo $count_valid; ?>, <?php echo $count_invalid; ?>, <?php echo $count_catchall; ?>, <?php echo $count_unknown; ?>],
                            backgroundColor: [
                                '#C6E377',
                                '#F16F6F',
                                '#75CAC3',
                                '#C0C0C0'

                            ],
                            borderColor: [
                                '#C6E377',
                                '#F16F6F',
                                '#75CAC3',
                                '#C0C0C0'

                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                      responsive: true,
                      legend:{
                        display:true,
                        position: 'bottom'
                      }
                    }
                });
                </script>

              <?php // } ?>
            </div>

          </div>
          <?php if($verify_per_status < 100 && $verify_per_status != 0){ ?>
            <script>
            $(document).ready(function(){
              var interval_<?php echo $c;?> = null;
              interval_<?php echo $c;?> = setInterval(updatepercent<?php echo $c;?>,3000);
              function updatepercent<?php echo $c;?>(){
                var csvfile_name_<?php echo $c;?> = '<?php echo $csv_file_name; ?>';
                var csv_name_ex_<?php echo $c;?> = '<?php echo $csv_name_ex; ?>';
                var user_id = "<?php echo $user_id?>";
                  $.ajax({
                        url: "../functions/<?php echo $percent_count_func;?>",
                        type: "post",
                        //dataType: "json",
                        data: {filename:csvfile_name_<?php echo $c;?>, uid:user_id} ,
                        success: function (response_<?php echo $c;?>) {
                          console.log(response_<?php echo $c;?>);
                          var obj_<?php echo $c;?> = jQuery.parseJSON (response_<?php echo $c;?>);
                          if(obj_<?php echo $c;?>.percent == '100'){
                            clearInterval(interval_<?php echo $c;?>);
                            $('#per_count_<?php echo $csv_name_ex;?>').html('<h6 class="d-inline-block float-right text-success">Complete <a href = "" class="text-primary">Refresh</a> </h6>');
                          }else{
                            $('#per_count_<?php echo $csv_name_ex;?>').html(obj_<?php echo $c;?>.percent+'% ('+obj_<?php echo $c;?>.total_verify+')');
                          }
                        }
                    });
              }
            });


            </script>
          <?php }?>
          <!-- CSV Download Modal -->
          <div class="csv_download modal fade" id="model_<?php echo $csv_name_ex; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">CSV Download</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form action="../functions/<?php echo $download_file_func;?>" method="post">
                  <input type="text" name="filename" hidden value="<?php echo $result['csv_file_name'] ?>">
                  <input type="text" name="user_id" hidden value="<?php echo $user_id ?>">
                <div class="modal-body row">
                  <div class="col-sm-6">
                    <div class="form-group form-check">
                      <input type="checkbox" name="all" class="form-check-input check_all_<?php echo $i?>" value="all" checked onchange="allChange<?php echo $i?>(this)" id="checkbox_<?php echo $c;?>" <?php if($t_email == 0){ echo 'disabled' ;}?>>
                      <label class="form-check-label" for="checkbox_<?php echo $c;?>">ALL <span class="badge badge-primary"><?php echo $t_email; ?></span></label>
                    </div>
                    <div class="form-group form-check">
                      <input type="checkbox" name="valid" value="valid" class="form-check-input check_sub_<?php echo $i?>" onchange="cbChange<?php echo $i?>(this)" id="checkbox_<?php echo ++$c;?>" <?php if($count_valid == 0){ echo 'disabled' ;}?>>
                      <label class="form-check-label" for="checkbox_<?php echo $c;?>">Deliverables <span class="badge badge-success"><?php echo $count_valid; ?></span></label>
                    </div>
                    <div class="form-group form-check">
                      <input type="checkbox" name="invalid" value="invalid" class="form-check-input check_sub_<?php echo $i?>" onchange="cbChange<?php echo $i?>(this)" id="checkbox_<?php echo ++$c;?>" <?php if($count_invalid == 0){ echo 'disabled' ;}?>>
                      <label class="form-check-label" for="checkbox_<?php echo $c;?>">Non Deliverables <span class="badge badge-danger"><?php echo $count_invalid; ?></span></label>
                    </div>
                    <div class="form-group form-check">
                      <input type="checkbox" name="catchall" value="catchall" class="form-check-input check_sub_<?php echo $i?>" onchange="cbChange<?php echo $i?>(this)" id="checkbox_<?php echo ++$c;?>" <?php if($count_catchall == 0){ echo 'disabled' ;}?>>
                      <label class="form-check-label" for="checkbox_<?php echo $c;?>">Deliverables with Risk <span class="badge badge-info"><?php echo $count_catchall; ?></span></label>
                    </div>

                  </div>
                  <div class="col-sm-6">
                    <div class="form-group form-check">
                      <input type="checkbox" name="free" value="free" class="form-check-input check_sub_<?php echo $i?>" onchange="cbChange<?php echo $i?>(this)" id="checkbox_<?php echo ++$c;?>" <?php if($count_free == 0){ echo 'disabled' ;}?>>
                      <label class="form-check-label" for="checkbox_<?php echo $c;?>">Free Account <span class="badge badge-dark"><?php echo $count_free; ?></span></label>
                    </div>
                    <div class="form-group form-check">
                      <input type="checkbox" name="role" value="role" class="form-check-input check_sub_<?php echo $i?>" onchange="cbChange<?php echo $i?>(this)" id="checkbox_<?php echo ++$c;?>" <?php if($count_role == 0){ echo 'disabled' ;}?>>
                      <label class="form-check-label" for="checkbox_<?php echo $c;?>">Role Account <span class="badge badge-light"><?php echo $count_role; ?></span></label>
                    </div>
                    <div class="form-group form-check">
                      <input type="checkbox" name="disposable" value="disposable" class="form-check-input check_sub_<?php echo $i?>" onchange="cbChange<?php echo $i?>(this)" id="checkbox_<?php echo ++$c;?>" <?php if($count_disposable == 0){ echo 'disabled' ;}?>>
                      <label class="form-check-label" for="checkbox_<?php echo $c;?>">Disposable Account <span class="badge badge-secondary"><?php echo $count_disposable; ?></span></label>
                    </div>
                    <div class="form-group form-check">
                      <input type="checkbox" name="syntax" value="syntax" class="form-check-input check_sub_<?php echo $i?>" onchange="cbChange<?php echo $i?>(this)" id="checkbox_<?php echo ++$c;?>" <?php if($count_syntax == 0){ echo 'disabled' ;}?>>
                      <label class="form-check-label" for="checkbox_<?php echo $c;?>">Syntex Error <span class="badge badge-warning"><?php echo $count_syntax; ?></span></label>
                    </div>
                  </div>
                </div>
                <script>
                  function cbChange<?php echo $i?>(obj){
                    var cbs = document.getElementsByClassName("check_all_<?php echo $i?>");
                    cbs[0].checked = false;
                  }
                  function allChange<?php echo $i?>(obj){
                    var cbs = document.getElementsByClassName("check_sub_<?php echo $i?>");
                    for (var i = 0; i < cbs.length; i++) {
                        cbs[i].checked = false;
                    }
                  }
                </script>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Download</button>
                </div>
                </form>
              </div>
            </div>
          </div>
          <?php $i++; }?>

  <script>
    $(document).ready(function(){
      $(".file_delete_btn").click(function(){
        var csvfile_name_d = $(this).attr("data-target");
        var user_id = "<?php echo $user_id?>";
        console.log(csvfile_name_d);
        var delete_confirm = confirm("Are you sure?");
        if (delete_confirm) {
          $.ajax({
                url: "../functions/<?php echo $delete_csv_file_func;?>",
                type: "post",
                //dataType: "json",
                data: {filename:csvfile_name_d, uid:user_id} ,
                success: function (response) {
                  console.log(response);
                  $('#mega_bar_'+csvfile_name_d).hide();
                }
            });
        }

      });

    });
  </script>

  <?php if(isset($_SESSION['license_check']) && isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == false){ ?>
  <script>
  $(document).ready(function(){
    $(".task-cancel-btn").click(function(){
      var csvfile_name_t = $(this).attr("data-target");
      var user_id = "<?php echo $user_id?>";
      console.log(csvfile_name_t);
        $.ajax({
              url: "../functions/<?php echo $task_cancel_func;?>",
              type: "post",
              //dataType: "json",
              data: {filename:csvfile_name_t, uid:user_id} ,
              success: function (response) {
                console.log(response);
                $('#preloader_'+csvfile_name_t).css('display','none');
                $('#cancel_'+csvfile_name_t).css('display','block');
              }
          });


    });
    $(".verify-btn").click(function(){
      var from_mail = '<?php echo $scan_mail;?>';
      var time_out = '<?php echo (!empty($scan_timeout) ? $scan_timeout: 10);?>';
      if(from_mail.length == 0){
        alert('Please set time out and a from mail in settings page for continue scan');
        return false;
      }
      var csvfile_name = $(this).attr("data-target");
      var csv_name_ex = csvfile_name.replace(/\.[^/.]+$/, "");
      console.log(csvfile_name);
      $(this).parents('.verify_email').hide();
      $('#preloader_'+csv_name_ex).css('display','block');
      var count_valid = parseInt($('#valid_'+csv_name_ex).html());
      var count_invalid = parseInt($('#invalid_'+csv_name_ex).html());
      var count_catchall = parseInt($('#catchall_'+csv_name_ex).html());
      var count_unknown = parseInt($('#unknown_'+csv_name_ex).html());
      var count_syntex = parseInt($('#syntax_'+csv_name_ex).html());
      var count_free = parseInt($('#free_account_'+csv_name_ex).html());
      var count_role = parseInt($('#role_account_'+csv_name_ex).html());
      var count_disponsable = parseInt($('#disposable_'+csv_name_ex).html());
      var user_id = "<?php echo $user_id?>";
        $.ajax({
              url: "../functions/<?php echo $emails_verify_func;?>",
              type: "post",
              //dataType: "json",
              data: {filename:csvfile_name, uid:user_id,frommail:from_mail,timeout:time_out} ,
              success: function (response) {
                console.log(response);

                var obj = jQuery.parseJSON (response);
                var result_valid = parseInt(obj.valid) + count_valid;
                var result_invalid = parseInt(obj.invalid) + count_invalid;
                var result_catchall = parseInt(obj.catch_all) + count_catchall;
                var result_unknown = parseInt(obj.unknown) + count_unknown;


                $('#preloader_'+csv_name_ex).css('display','none');
                $('#chart_'+csv_name_ex).css('display','block');
                $('#valid_'+csv_name_ex).html(result_valid);
                $('#invalid_'+csv_name_ex).html(result_invalid);
                $('#catchall_'+csv_name_ex).html(result_catchall);
                $('#unknown_'+csv_name_ex).html(result_unknown);
                $('#syntax_'+csv_name_ex).html(parseInt(obj.syntax) + count_syntex);
                $('#free_account_'+csv_name_ex).html(parseInt(obj.free) + count_free);
                $('#role_account_'+csv_name_ex).html(parseInt(obj.role) + count_role);
                $('#disposable_'+csv_name_ex).html(parseInt(obj.disposable) + count_disponsable);
                $('#myChart_'+csv_name_ex).hide();
                var ctx = document.getElementById('new_myChart_'+csv_name_ex).getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Valid', 'Invalid', 'Catch All', 'Unknown'],
                        datasets: [{
                            label: '# of Votes',
                            data: [result_valid,result_invalid, result_catchall,result_unknown],
                            backgroundColor: [
                                '#C6E377',
                                '#F16F6F',
                                '#75CAC3',
                                '#C0C0C0'

                            ],
                            borderColor: [
                                '#C6E377',
                                '#F16F6F',
                                '#75CAC3',
                                '#C0C0C0'

                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                      responsive: true,
                      legend:{
                        display:true,
                        position: 'bottom'
                      }
                    }
                });
              }
          });


    });
  });
</script>

<!-- Add List Modal-->
<div class="modal fade" id="addList"  role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h5>Only csv file can add</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
       <ul class="nav nav-tabs" style="border-bottom: 0px;" id="myTab" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true"><i class="fas fa-file-upload"></i> Upload</a>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
          <div style="height: 300px;border: 1px solid #dddfeb;">
              <input type="file" name="filename" id="csv_filename">
          </div>
          <p class="uplogin_file"><i class="start fas fa-circle-notch fa-spin"></i> &nbsp; File Uploading  total- <span class="ml-2 text-primary" id="count_total_export"></span> </p>
          <div style="margin-top: 20px; " class="uploading_succes">
            <h3>Thank you - Your list has been uploaded successfully.</h3>
            <p class="" id="store-status"> </p>
            <p><a href="<?php echo $my_list_page;?>">Refresh page</a></p>
          </div>
          <br>
        </div>
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        </div>
        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
        </div>
      </div>
      </div>
    </div>

  </div>
</div>

  <!-- add list script -->
  <script>
  $(".uplogin_file").hide();
  $(".uploading_succes").hide();
  $(document).ready(function(){
    $("#csv_filename").change(function(e) {
    $('#store-status').html('');
    var ext = $("input#csv_filename").val().split(".").pop().toLowerCase();
    function randomNumberFromRange(min,max)
    {
        return Math.floor(Math.random()*(max-min+1)+min);
    }
    var fname = '<?php echo time() ;?>' +randomNumberFromRange(10,99)+'<?php echo $_SESSION['id']; ?>'+ '_csv_file';
    fname = fname.replace(/\s/g, "_");
    if($.inArray(ext, ["csv"]) == -1) {
      alert('Upload CSV');
      return false;
    }
    $(".uplogin_file").show();
    if (e.target.files != undefined) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $(".uploading_succes").hide();
      $('#count_total_export').text('');
        var lines = e.target.result.split('\r\n');
        // var lines = e.target.result.split(/\r?\n/);
        var c = 0;
        var t = 0;
        var title_row = lines[0].split(',');
        var email_row_check = false;
        for (r = 0; r < title_row.length; r++)
        {
          var column_name = title_row[r].trim();
          column_name = title_row[r].toLowerCase();
          if(column_name == 'email' || column_name == 'mail' || column_name == 'gmail'){
            email_row_check = true;
            var target_column_index = r;
            break;
          }
        }
        var data_array = '';
        if(email_row_check){
          for (i = 1; i < lines.length; ++i)
          {
            var email_column = lines[i].split(',');
            var email = email_column[target_column_index];
            if(email != ''){
              email = $.trim(email);
            }
            if(email != ''){
               t++;
               data_array  += email+',';
               $('#count_total_export').text(t);
               var user_id = '<?php echo $_SESSION['id']; ?>';
            }
          }
          if(data_array.length !== 0){
            $.ajax({
                 url: "../functions/<?php echo 'save_email.php';?>",
                 type: "post",
                 // dataType: "json",
                 data: {email:data_array, filename:fname, uid:user_id} ,
                 success: function (response) {
                   console.log(response);
                   var obj = jQuery.parseJSON (response);
                     $('#store-status').html('Total: <span class="text-primary">'+obj.total+'</span>  Save: <span class="text-success">'+obj.save+'</span>  Duplicate: <span class="text-danger">'+obj.duplicate+'</span> (duplicate emails has been removed from scan queue)');
                     $('#count_total_export').text('');
                     $(".uplogin_file").hide();
                     $(".uploading_succes").show();
                     $("#filename").empty(" ");


                 }
             });
          }else{
            $(".uplogin_file").hide();
            $("#filename").empty(" ");
            alert('No email column found');
          }
        }else{
          $(".uplogin_file").hide();
          $("#filename").empty(" ");
          alert('No email column found');
        }

    };
    reader.readAsText(e.target.files.item(0));
    }
    return false;
    });



    });
  </script>
<?php } ?>
<?php include '../include/footer.php';?>
