<?php
$title = 'dashboard';
include 'include/header.php';
$data = array();
$all_email_data_sql = "SELECT email_status, csv_file_name, create_date FROM user_email_list WHERE user_id = '$user_id' AND email_status != 'Not Verify' ORDER BY id DESC ";
$all_email_data_read = $db->select($all_email_data_sql);
$count_email = mysqli_num_rows($all_email_data_read);
$i = 0;
if ($count_email > 0) {
  while($all_email_result = $all_email_data_read->fetch_assoc()){
    $data[$i] = ['email_status' => $all_email_result['email_status'],'csv_file_name' => $all_email_result['csv_file_name'],'create_date' => $all_email_result['create_date']];
    $i++;
  }
}
// ---------------------------------------
include 'functions/'.$dashboard_func;

$month_data = array();
$year_month_data = array();
$week_data = array();
// ---------------------moth value for chart------------------
$all_month_date = GetCurrentMonthDates();
for($row = 0; $row < count($all_month_date); $row++ ){
  $month_data[$all_month_date[$row]] = email_count_date($data,$all_month_date[$row],'create_date');
}
// ---------------------------end month-----------

// --------------------------week value for chart--------------------
$all_week_date = GetCurrentWeekDates();
for($row = 0; $row < count($all_week_date); $row++ ){
  $week_data[$all_week_date[$row]] = email_count_date($data,$all_week_date[$row],'create_date');
}
// --------------------------end week-------------------------------

// -------------------year-month-value count------------------------
$now = new \DateTime('now');
$year = $now->format('Y');
$all_year_month = array($year.'-01',$year.'-02',$year.'-03',$year.'-04',$year.'-05',$year.'-06',$year.'-07',$year.'-08',$year.'-09',$year.'-10',$year.'-11',$year.'-12');
for($row = 0; $row < count($all_year_month); $row++ ){
  $year_month_data[$all_year_month[$row]] = email_count_year($data,$all_year_month[$row],'create_date');
}
// -------------------end year count---------------------------------

// --------------------data-------------------------------
$total = count($data);
$valid = email_count($data,'valid','email_status');
$invalid = email_count($data,'invalid','email_status');
$unknown = email_count($data,'unknown','email_status');
$catch_all = email_count($data,'catch all','email_status');
$total_file = file_count($data, 'csv_file_name');

if($total > 0){
  $estimate_rate = $total * (!empty($d_estimated_cost) ? (float)$d_estimated_cost : 0.005);
  $bounce_rate = (($invalid + $unknown + ($catch_all/2)) / $total) * 100;
  $bounce_rate = round($bounce_rate);
}else{
  $estimate_rate = 0;
  $bounce_rate = 0;
}



// ---------------chart-value-------------
$chart_data_month = chart_data($month_data,'month');
$chart_data_year = chart_data($year_month_data,'year');
$chart_data_week = chart_data($week_data,'week');

?>
          <!-- Page Heading -->
          <div class="dashboard_head d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <div class="pull-right">
              <?php if(isset($_SESSION['license_check']) && isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == false){ ?>
                <a href="#" id="quick_validation" data-toggle="modal" data-target="#quickvalidation"  class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"> Quick Validation</a>
                <a href="#" id="add_list" data-toggle="modal" data-target="#addList" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"> Add List</a>
              <?php }else{ ?>
                <button disabled class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"> Quick Validation</button>
                <button disabled class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"> Add List</button>
              <?php } ?>

            </div>
        </div>

          <!-- Content Row -->
          <div class="row dashboard_content">

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total verified</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $valid;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Bounce Rate Detaction</div>
                      <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                          <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $bounce_rate;?>%</div>
                        </div>
                        <div class="col">
                          <div class="progress progress-sm mr-2">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $bounce_rate;?>%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-adjust fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Estimated Cost Saved</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo $estimate_rate;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pending Requests Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Listing File</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_file;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pending Requests Card Example -->

          </div>

          <!-- Content Row -->

          <div class="">

            <!-- Area Chart -->
            <div class="">
              <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Earnings Overview</h6>
                  <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                      <div class="dropdown-header">Select Chart date type:</div>
                      <button class="dropdown-item btn btn-link" id="year_chart">This Year</button>
                      <button class="dropdown-item btn btn-link" id='month_chart'>This Month</button>
                      <button class="dropdown-item btn btn-link" id='week_chart'>This Week</button>
                    </div>
                  </div>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                  <div class="chart-area">
                    <canvas id="myChart"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Content Row -->
  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>
<?php if(isset($_SESSION['license_check']) && isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == false){ ?>
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
                <input type="file" name="filename" id="filename">
            </div>
            <p class="uplogin_file"><i class="start fas fa-circle-notch fa-spin"></i> &nbsp; File Uploading total- <span class="ml-2 text-primary" id="count_total_export"></span> </p>
            <div style="margin-top: 20px; " class="uploading_succes">
              <h3>Thank you - Your list has been uploaded successfully.</h3>
              <p class="" id="store-status"> </p>
              <p><a href="app/<?php echo $my_list_page;?>">Go To My Lists</a></p>
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

<!-- quick validation model -->
<div class="modal fade" id="quickvalidation"  role="dialog">
  <div class="modal-dialog modal-lg min-vh-75">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h5>Quick Validation</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-4">
          <div class="">
            <div class="" id="quick-mail-list">

            </div>
            <div class="text-right">
              <button class="btn btn-sm btn-warning mb-1" id="delete_all_email_count">Remove all</button>
            </div>
            <input class="form-control" type="text" id="email_type_quick_validatior" onkeypress="return AvoidSpace(event)" placeholder="Enter one or more email addresses or paste from clipboard to validate">
            <div id="limit-notice">
              <small class="text-danger">You cannot add more than maximum limit at a time</small>
            </div>
            <small><strong>Note:</strong> Maximum 10 email address allowed per validation</small>
            <div class="q-validate">
              <button id="validate_quick_mail" class="btn btn-info mt-2 mb-2 btn-sm">Validate</button>

              <div id="load_verify_quick_mail" class="ml-2">
                 <i class="fas fa-spinner fa-spin"></i>
              </div>
            </div>

          </div>

			<div class="pull-right">
              <button class="btn btn-light btn-sm float-right mb-2" id="csv_download_quick_mail">Download</button>
           </div>
          <div class="mt-5" id="table-value-sec">

            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Email</th>
                  <th>Safe to Send</th>
                  <th>Status</th>
                  <th>Account type</th>
                  <th>Reason</th>
                </tr>
              </thead>
              <tbody id="tbody-data">

              </tbody>
            </table>
          </div>
      </div>
    </div>

  </div>
</div>

<!-- quick validation modal -->
<script>
  $('#email_type_quick_validatior').keypress(function(e){
      $('#limit-notice').css('display','none');
    if(e.which == 32 || e.which == 13 || e.which == 44){
      var total_count = $('.quick-mail').length;
      var data = $(this).val();
      var data = $.trim(data);
      if(data.length !== 0){
        if(total_count < 10 ){
          $('#limit-notice').css('display','none');
          $('#quick-mail-list').append("<div class='quick-mail d-inline-block bg-dark text-white rounded mr-1 p-1 mb-2'><small class='quick-mail-data'>" + data + "</small> <button style='width:25px; font-size:9px;' class='quick-mail-erash btn btn-sm btn-default h-50'>X</button></div>");
          $('#delete_all_email_count').css('display','inline-block');
          $(this).val('');
        }else{
          $('#limit-notice').css('display','block');
        }
      }
    }
    $('.quick-mail').click(function(){
      var total_count = $('.quick-mail').length;
      if(total_count <= 0){
        $('#delete_all_email_count').css('display','none');
      }
      $(this).remove();
      $('#limit-notice').css('display','none');
    });
  });
  function AvoidSpace(event) {
      var k = event ? event.which : window.event.keyCode;
      if (k == 32 || k == 44) return false;
  }
  $('#delete_all_email_count').click(function(){
    $('#quick-mail-list').html('');
    $('#delete_all_email_count').css('display','none');
  });
  $('#validate_quick_mail').click(function(){
    var from_mail = '<?php echo $scan_mail;?>';
    var time_out = '<?php echo (!empty($scan_timeout) ? $scan_timeout: 10);?>';
    if(from_mail.length == 0){
      alert('Please set time out and a from mail in settings page for continue scan');
      return false;
    }
    // -------------
    var total_count = $('.quick-mail').length;
    var data = $('#email_type_quick_validatior').val();
    var data = $.trim(data);
    if(data.length !== 0){
      if(total_count < 10 ){
        $('#quick-mail-list').append("<div class='quick-mail d-inline-block bg-dark text-white rounded mr-1 p-1 mb-2'><small class='quick-mail-data'>" + data + "</small> <button style='width:25px; font-size:9px;' class='quick-mail-erash btn btn-sm btn-default h-50'>X</button></div>");

        $('#email_type_quick_validatior').val('');
      }
    }
    // ------------
    $('#tbody-data').html('');
    var total_count_email = $('.quick-mail-data').length;
    if(total_count_email > 0){
      $('#load_verify_quick_mail').css('display','inline-block');
      $('#validate_quick_mail').css('display','none');
      $('#csv_download_quick_mail').css('display','none');
      var t = 0;
      var c = 0;
      $(".quick-mail-data").each(function() {
          var mail_data = $(this).text();
          mail_data = $.trim(mail_data);
          // ---------------------------------------------------------------------------------------------------
          var token = '12345';
          if(mail_data){
              $('#table-value-sec').css('display','block')
              $("#tbody-data").append("<tr><td>" + (mail_data)+ "</td><td class='safe-" + t +"'>-</td><td class='status-" + t +"'>-</td><td  class='type-" + t +"'>-</td><td  class='reasons-" + t +"'>-</td></tr>");

              if (mail_data != '') {
                $.ajax({
                  url: "functions/<?php echo $quick_verification_func?>",
                  type: "post",
                  data: {
                    email: mail_data,
                    index: t,
                    token: token,
                    frommail:from_mail,
                    timeout:time_out
                  },
                }).done(function(result){
                      c++;
                      var result = jQuery.parseJSON (result);
                      $(".safe-" + result.index).text(result.safetosend);
                      $(".status-" + result.index).text(result.status);
                      $(".type-" + result.index).text(result.type);
                      $(".reasons-" + result.index).text(result.reasons);
                      if(t == c){
                        $('#csv_download_quick_mail').css('display','inline-block');
                        $('#load_verify_quick_mail').css('display','none');
                        $('#validate_quick_mail').css('display','inline-block');
                      }
                })

              }
            }
            // --------------------------------------------------------------------------------------
            t++;

      });
    }
  });

</script>
<script>
  //export csv file
  function download_csv(csv, filename) {
    var csvFile;
    var downloadLink;

    // CSV FILE
    csvFile = new Blob([csv], {type: "text/csv"});

    // Download link
    downloadLink = document.createElement("a");

    // File name
    downloadLink.download = filename;

    // We have to create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Make sure that the link is not displayed
    downloadLink.style.display = "none";

    // Add the link to your DOM
    document.body.appendChild(downloadLink);

    // Lanzamos
    downloadLink.click();
  }

  function export_table_to_csv(html, filename) {
  var csv = [];
  var row = $("table tr");
  var rows = new Array();
  // csv.push('Email,Safe to send, Status, Account type, Reasons\n');
  row.each(function(){
      if($(this).css('display') == 'none'){

      }else{
        rows.push($(this));
      }
  });
    for (var i = 0; i < rows.length; i++) {
    var row = [], cols = rows[i].children("td, th");

        for (var j = 0; j < cols.length; j++)
            row.push(cols[j].innerText);

    csv.push(row.join(","));
  }

    // Download CSV
    download_csv(csv.join('\n'), filename);
  }

  document.querySelector("#csv_download_quick_mail").addEventListener("click", function () {
    var html = document.querySelector("table").outerHTML;
  export_table_to_csv(html, "table.csv");
  });
</script>
<!-- end quick validation modal -->



<script>
$(document).ready(function(){
  $(".uplogin_file").hide();
  $(".uploading_succes").hide();
  $("#filename").change(function(e) {
    $('#store-status').html('');
  var ext = $("input#filename").val().split(".").pop().toLowerCase();
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
    $('#count_save_export').text('');
    $('#count_total_export').text('');
      var lines = e.target.result.split('\r\n');
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
               url: "functions/<?php echo 'save_email.php';?>",
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


<script>
var ctx = document.getElementById('myChart').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'line',
    data: {

        labels:<?php echo $chart_data_year[0]; ?>,
        datasets: [{
            label: 'Valid',
            data: <?php echo $chart_data_year[1]; ?>,
            fill: false,
            borderColor: '#C6E377',
            borderWidth: 1
        },{
            label: 'Catch all',
            data: <?php echo $chart_data_year[2]; ?>,
            fill: false,
            borderColor: '#75CAC3',
            borderWidth: 1
        },{
            label: 'Invalid',
            data: <?php echo $chart_data_year[3]; ?>,
            fill: false,
            borderColor: '#F16F6F',
            borderWidth: 1
        },{
            label: 'Unknown',
            data: <?php echo $chart_data_year[4]; ?>,
            fill: false,
            borderColor: '#C0C0C0',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        legend:{
          display:true,
          position: 'bottom'
        },
        maintainAspectRatio: false,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});

function removeData(chart) {
  chart.data.labels.length = 0;
  chart.data.datasets.forEach((dataset) => {
      dataset.data[0].length = 0;
      dataset.data[1].length = 0;
      dataset.data[2].length = 0;
      dataset.data[3].length = 0;
  });
  chart.update();
}
$('#year_chart').click(function(){
  removeData(myChart);
  var data_year_labels = <?php echo $chart_data_year[0]; ?> ;
  var data_year_valid = <?php echo $chart_data_year[1]; ?>;
  var data_year_invalid = <?php echo $chart_data_year[2]; ?>;
  var data_year_catchall = <?php echo $chart_data_year[3]; ?>;
  var data_year_unknown = <?php echo $chart_data_year[4]; ?>;
  myChart.data.labels = data_year_labels;
  myChart.data.datasets[0].data = data_year_valid;
  myChart.data.datasets[1].data = data_year_invalid;
  myChart.data.datasets[2].data = data_year_catchall;
  myChart.data.datasets[3].data = data_year_unknown;
  myChart.update();
});
$('#month_chart').click(function(){
  removeData(myChart);
  var data_month_labels = <?php echo $chart_data_month[0]; ?> ;
  var data_month_valid = <?php echo $chart_data_month[1]; ?>;
  var data_month_invalid = <?php echo $chart_data_month[2]; ?>;
  var data_month_catchall = <?php echo $chart_data_month[3]; ?>;
  var data_month_unknown = <?php echo $chart_data_month[4]; ?>;
  myChart.data.labels = data_month_labels;
  myChart.data.datasets[0].data = data_month_valid;
  myChart.data.datasets[1].data = data_month_invalid;
  myChart.data.datasets[2].data = data_month_catchall;
  myChart.data.datasets[3].data = data_month_unknown;
  myChart.update();
});
$('#week_chart').click(function(){
  removeData(myChart);
  var data_week_labels = <?php echo $chart_data_week[0]; ?> ;
  var data_week_valid = <?php echo $chart_data_week[1]; ?>;
  var data_week_invalid = <?php echo $chart_data_week[2]; ?>;
  var data_week_catchall = <?php echo $chart_data_week[3]; ?>;
  var data_week_unknown = <?php echo $chart_data_week[4]; ?>;
  myChart.data.labels = data_week_labels;
  myChart.data.datasets[0].data = data_week_valid;
  myChart.data.datasets[1].data = data_week_invalid;
  myChart.data.datasets[2].data = data_week_catchall;
  myChart.data.datasets[3].data = data_week_unknown;
  myChart.update();
});

</script>
<?php include 'include/footer.php'?>
