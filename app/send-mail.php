<?php
$title = 'Send Mail';
include '../include/header.php';
$dates = new DateTime('now', new DateTimeZone('UTC') ); //php UTC timezone
$dates = $dates->format('Y-m-d H:i:s');
$user_id = $_SESSION['id'];
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">Send Mail</h1>
</div>
<div class="bg-white rounded shadow p-4 mb-4">
<div class="mb-4">

  <h3 class="mb-4">Step 1.</h3>
  <form class="csv_import" action="" method="post" enctype="multipart/form-data">
  <div class="row">
    <div class="input-group mb-3 col-md-6 pr-md-4">
        <div class="custom-file">
          <input required type="file" name="csv" class="custom-file-input" id="fileInput">
          <label class="custom-file-label" id="filename" for="fileInput">Choose file</label>
        </div>
        <div class="input-group-append">
          <button type="submit" class="input-group-text" id="">Extract</span>
        </div>
    </div>
  </div>
</form>

</div>
<div class="mb-4">
  <h3 class="mb-4">Step 2.</h3>
  <div class="row">
    <div class="col-md-6 pr-md-4">
      <form action="<?php echo 'app/'.$mailsend;?>" method="post">
        <div class="form-group row">
          <label for="inputEmail3" class="col-sm-3 col-form-label">From</label>
          <div class="col-sm-9">
            <input required type="email" class="form-control" id="from" placeholder="Your Email">
          </div>
        </div>
        <div class="form-group row">
          <label for="inputPassword3" class="col-sm-3 col-form-label">To</label>
          <div class="col-sm-9">
            <div class="input-group mb-3">
              <input type="text" class="form-control" aria-label="Recipient's username" aria-describedby="basic-addon2" name="to" placeholder="where to?" id="tomail" required value="<?php

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $statment = true;
                        $statmenttwo = false;
                        $filename = $_FILES['csv']['name'];
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        $fname = $_FILES['csv']['tmp_name'];
                        if ($ext == 'csv') {
                            $file = fopen($fname, 'r');
                            $fistrow = fgetcsv($file);
                            $count = count($fistrow);
                            for ($x = 0;$x < $count;$x++) {
                                $rowname = strtolower($fistrow[$x]);
                                if ($rowname == 'email' || $rowname == 'mail' || $rowname == 'gmail') {
                                    while ($row = fgetcsv($file)) {
                                        if (!$statment) {
                                            $statment = true;
                                        } else {
                                            $email = $row[$x];
                                            if (!$statmenttwo) {
                                                $statmenttwo = true;
                                                echo $email;
                                            } else {
                                                echo ',' . $email;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                 } ?>">
              <div class="input-group-append">
                <span class="btn btn-outline-secondary cursor-pointer" id="clear-to-mail"><i class="fas fa-times"></i></span>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputPassword3" class="col-sm-3 col-form-label">Subject</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="subject" placeholder="Your Subject">
          </div>
        </div>
        <div class="form-group row">
          <label for="inputPassword3" class="col-sm-3 col-form-label">Message</label>
          <div class="col-sm-9">
            <textarea required id="message" name="message"></textarea>
          </div>
        </div>
<!-- notify -->

                      <div class="form-group row">
                        <div class="col-3">
                        </div>
                        <div class="col-sm-9">
                          <?php
                          $email_left = 0;
                          $user_query3 =  "SELECT * FROM timer WHERE user_id = '$user_id' ";
                          $user_read3 = $db->select($user_query3);
                          if($user_read3){
                            $row3 = $user_read3->fetch_assoc();
                            $time_count = $row3['time_count'];
                            if(!empty($time_count)){
                              $t_dates = strtotime($dates);
                              $start_date = strtotime($time_count);
                              $time_left =($t_dates - $start_date)/60;
                            }else{
                              $time_left = 0;
                            }
                            $range = $row3['e_range'];
                            $send = $row3['last_send'];
                            $time_range = $row3['time_range'];
                            $email_left = $range - $send;
                            $time_remining = $time_range - $time_left;
                          ?>
                          <?php if(isset($_SESSION['license_check']) && isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == false){ ?>
                          <?php if($email_left <= 0){ ?>
                            <button disabled class="btn btn-primary">Send</button>
                            <label class="d-block bg-light rounded p-1 mt-2">You have not set any limit! Please set your sending limit <a class="text-danger" href="<?php echo $settings_page;?>" target="_self">here</a></label>
                          <?php }else{ ?>
                            <button type="submit" class="btn btn-primary"  id="send_mail">Send</button>
                            <i style="margin-left: 10px;font-size:20px;color:#EE800C;display:none;" class="start fas fa-circle-notch fa-spin"></i>
                            <i style="margin-left: 10px;font-size:20px;color:#0EBE30;display:none;" class="end fas fa-check"></i>
                            <label id="email_check" class="text-danger p-1 mt-2" style="display:none;">you can't send more than <?php echo $email_left;?> email within <?php echo round($time_remining, 2);?>min</label>
                          <?php }?>
                          <label class="col-form-label d-block">You can send <?php if(empty($email_left)){ echo 0; }else{ echo $email_left;} ?> mail within <?php echo $time_range/60;?> hours</label>
                        <?php }else{ ?>
                          <button class="btn btn-primary" disabled>Send</button>
                          <label class="col-form-label d-block text-danger">Your license is not active!</label>
                        <?php }?>
                        </div>
                        <?php } ?>
                      </div>


<!-- end notify -->

      </form>
    </div>
    <div class="col-md-6 pl-md-4 pr-md-4">
      <h2>Delivery Report</h2>

      <div class="bg-light text-dark p-2 rounded row">
        <div class="pt-2 col-6">
          <p>Total Email : </p>
          <p>Duplicates : </p>
          <p>Sendable : </p>
          <p>Sent : </p>
          <p>Failed :</p>
        </div>
        <div class="pt-2 col-6">
          <p id="total">0</p>
          <p id="duplicate">0</p>
          <p id="sendable">0</p>
          <p id="send">0</p>
          <p id="notsend">0</p>
        </div>
      </div>

    </div>
  </div>
</div>
</div>
<script src="../assets/ckeditor/ckeditor.js"></script>  <!-- ckeditor library call -->

<?php if(isset($_SESSION['license_check']) && isset($_SESSION['licanse_error']) &&  $_SESSION['licanse_error'] == false){ ?>
<script>
    $("#clear-to-mail").click(function (e)
    {
      $("#tomail").val('');
    });
  	document.getElementById('fileInput').onchange = function ()
  	{
  		f = this.value.replace(/.*[\/\\]/, '');
  		$('#filename').html(f);
  	};
  $(function ()
  {
  	// mail validation check
  	$("#send_mail").click(function (e)
  	{
  		e.preventDefault();
        var temails = $("#tomail").val();
        temails = temails.replace(/</g, "&lt;").replace(/>/g, "&gt;");
  			temails = temails.split(",");
  			var from = $("#from").val();
        from = from.replace(/</g, "&lt;").replace(/>/g, "&gt;");
  			var subject = $("#subject").val();
        subject = subject.replace(/</g, "&lt;").replace(/>/g, "&gt;");
  			var message = CKEDITOR.instances['message'].getData();
  			temails = temails.filter(Boolean);
  			if (temails != '')
  			{
  				$('.start').css('display', 'inline-block');
  				$('.end').css('display', 'none');
  			}
  			emails = temails.filter(function (elem, index, self)
  			{
  				return index === self.indexOf(elem);
  			});
  			var tcount = temails.length;
  			var count = emails.length;
  			var duplicate = tcount - count;
  			$("#total").html(tcount);
  			$("#duplicate").html(duplicate);
  			$("#sendable").html(count);
  			var i = 0;
  			var success = 0;
  			var error = 0;
  			$.ajax(
  			{
  				url: "<?php echo '../functions/'.$timecheck_func;?>",
  				type: "post",
  				data:
  				{
  					emails: count,
  				},
  			}).done(function (data)
  			{
          data = $.trim(data);
          console.log(data);
  				if (data == 'ok')
  				{
  					$.each(emails, function (index, email)
  					{
  						if (email)
  						{
  							$(".tbody-data").append("<tr><td>" + (index + 1) + "</td><td>" + email + "</td><td class='email-" + index + "'>Handling ... </td><td style='display:none' class='status-" + index + "'>server error </td></tr>");
  							email = $.trim(email);
  							if (email != '')
  							{
  								$.ajax(
  								{
  									url: "<?php echo '../functions/'.$mailsend;?>",
  									type: "post",
  									data:
  									{
  										to: email,
  										from: from,
  										subject: subject,
  										message: message,
  									},
  								}).done(function (result)
  								{
  									i++;
  									if (result == 'success')
  									{
  										success++;
  									}
  									else
  									{
  										error++;
  									}
  									if (i == count)
  									{
  										$('.start').css('display', 'none');
  										$('.end').css('display', 'inline-block');
  										$("#send").html(success);
  										$("#notsend").html(error);
  										$("#tomail").val('');
  										$("#from").val('');
  										$("#subject").val('');
  										$("#message").val('');
  									}
  								})
  							}
  						}
  					})
  				}
  				else if(data == 'error')
  				{
  					$('#email_check').css('display', 'block');
  					$('.start').css('display', 'none');
  					$('.end').css('display', 'inline-block');
  				}else{
            $('#email_check').css('display', 'block');
            $('#email_check').text('Unknown error! please try again');
            $('.start').css('display', 'none');
  					$('.end').css('display', 'inline-block');
          }
  			})
  			return false;
  	})
  });
</script>
<?php } ?>
<script>
  CKEDITOR.replace('message');
</script>
<?php include '../include/footer.php';?>