<?php
$title = 'Email Listing';
include '../include/header.php';

$get_status = '';
$get_type = '';
$user_id = $_SESSION['id'];
function test_input($data) { //filter value function
    $db = new database();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strtolower($data);
    $data = mysqli_real_escape_string($db->link, $data);
    return $data;
}

if (isset($_GET["status"]) && ($_GET["status"] == 'valid' || $_GET["status"] == 'invalid' || $_GET["status"] == 'catch all' || $_GET["status"] == 'unknown')) {
    $get_status = test_input($_GET['status']);
}
if (isset($_GET["type"]) && ($_GET["type"] == 'free' || $_GET["type"] == 'free' || $_GET["type"] == 'role' || $_GET["type"] == 'disposable')) {
    $get_type = test_input($_GET['type']);
}
if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
    $limit = test_input($_GET["limit"]);
} else {
    $limit = 10;
};
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['filter'])) {
          if (isset($_POST["status"]) && ($_POST["status"] == 'valid' || $_POST["status"] == 'invalid' || $_POST["status"] == 'catch all' || $_POST["status"] == 'unknown')) {
              $get_status = test_input($_POST['status']);
          }
          if (isset($_POST["type"]) && ($_POST["type"] == 'free' || $_POST["type"] == 'free' || $_POST["type"] == 'role' || $_POST["type"] == 'disposable')) {
              $get_type = test_input($_POST['type']);
          }
        } else {
            if (isset($_POST['row']) && $_POST['row'] != 0 && is_numeric($_POST['row'])) {
                $limit = test_input($_POST['row']);
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


$sql_w_l = "SELECT a.* FROM user_email_list a INNER JOIN (SELECT email_name, MIN(id) as id FROM user_email_list WHERE user_id = '$user_id' GROUP BY email_name ) AS b ON a.email_name = b.email_name AND a.id = b.id AND a.user_id = '$user_id' AND a.email_status != 'Not Verify'";

$account_type = ucfirst($get_type).' '.'Account';
if(!empty($get_type) && !empty($get_status)){
  $page_parameter = '&type='.$get_type.'&status='.$get_status;
  $sql_w_l .= ' AND a.email_type ="'.$account_type.'" AND a.email_status="'.$get_status.'"';
  $return_limit_get = $lear_management_page.'?type='.$get_type.'&status='.$get_status;
}elseif(!empty($get_type)){
  $sql_w_l .= ' AND a.email_type ="'.$account_type.'"';
  $page_parameter = '&type='.$get_type;
  $return_limit_get = $lear_management_page.'?type='.$get_type;
}elseif(!empty($get_status)){
  $sql_w_l .= ' AND a.email_status="'.$get_status.'"';
  $page_parameter = '&status='.$get_status;
  $return_limit_get = $lear_management_page.'?status='.$get_status;
}else{
  $page_parameter = '';
  $return_limit_get = $lear_management_page;
}
$sql = $sql_w_l.' LIMIT '.$start_from.','.$limit;
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">Lead Management</h1>
</div>
<div class="bg-white rounded shadow p-2">
  <div class="mt-2 mb-2">
    <div class="d-inline-block">
      <form class="form-inline" action="<?php echo $return_limit_get;?>" method="post">
        <div class="form-group mr-sm-1 mb-2">
          <select name="row" class="form-control" placeholder="Password">
            <option value="0">Select Row</option>
            <option <?php if($limit == 10){echo 'selected';}?> value="10">10</option>
            <option <?php if($limit == 20){echo 'selected';}?> value="20">20</option>
            <option <?php if($limit == 30){echo 'selected';}?> value="30">30</option>
            <option <?php if($limit == 50){echo 'selected';}?> value="50">50</option>
            <option <?php if($limit == 100){echo 'selected';}?> value="100">100</option>
          </select>
        </div>
        <button type="submit" class="btn btn-success mb-2">GO</button>
      </form>
    </div>
    <div class="d-inline-block">
      <form class="form-inline" action="<?php echo $lear_management_page;?><?php if($limit != 10){ echo '?limit='.$limit;}?>" method="post">
        <div class="form-group mr-sm-1 mb-2">
          <select name="type" class="form-control" placeholder="Password">
            <option>Select Type</option>
            <option <?php if($get_type == 'free'){echo 'selected';}?> value="free">Free Account</option>
            <option <?php if($get_type == 'role'){echo 'selected';}?> value="role">Role Account</option>
            <option <?php if($get_type == 'disposable'){echo 'selected';}?> value="disposable">Disposable</option>
          </select>
        </div>
        <div class="form-group mr-sm-1 mb-2">
          <select name="status" class="form-control" placeholder="Password">
            <option>Select Status</option>
            <option <?php if($get_status == 'valid'){echo 'selected';}?> value="valid">Valid</option>
            <option <?php if($get_status == 'invalid'){echo 'selected';}?> value="invalid">Invalid</option>
            <option <?php if($get_status == 'catch all'){echo 'selected';}?> value="catch all">Catch All</option>
            <option <?php if($get_status == 'unknown'){echo 'selected';}?> value="unknown">Unknown</option>
          </select>
        </div>
        <button type="submit" name="filter" class="btn btn-info mb-2">Filter</button>
      </form>
    </div>
    <div class="d-inline-block float-lg-right">
      <button type="button" class="btn btn-light" data-toggle="modal" data-target="#model">Export as CSV</button>
    </div>
  </div>
  <div class="">
    <div style="overflow-x:auto" class="">
    <table class="table table-sm">
      <thead class="thead-light">
        <tr>
          <th scope="col">Serial</th>
          <th scope="col">Email</th>
          <th scope="col">Email Status</th>
          <th scope="col">Email Type</th>
          <th scope="col">Safe to Send</th>
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
              <th scope="row"><?php echo $i;?></th>
              <td><?php echo $row['email_name'];?></td>
              <td><?php echo $row['email_status'];?></td>
              <td><?php echo $row['email_type'];?></td>
              <td><?php echo $row['safe_to_send'];?></td>
            </tr>
          <?php }
        }

        ?>
      </tbody>
    </table>
    </div>
  </div>

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
          echo "<li class='page-item'><a class='page-link' href='".$lear_management_page."?page=".($pn-1)."&limit=" . $limit .(!empty($page_parameter) ? $page_parameter : ''). "'> < </a></li>";
          echo "<li class='page-item'><a class='page-link' href='".$lear_management_page."?page=1&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'> 1 </a></li>";
          if(($pn-1) > 2){
            echo "<li class='page-item'><a class='page-link' href='".$lear_management_page."?page=".($pn-2)."&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'> ... </a></li>";
          }
      }
      if($pn == 1){
        echo "<li class='page-item active'><a class='page-link' href='".$lear_management_page."?page=1&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'> 1 </a></li>";
      }
      for ($i=-1; $i<=1; $i++) {
          if($k+$i != 1 && $k+$i != $total_pages && $k+$i < $total_pages && $k+$i > 0){
            if($k+$i==$pn)
              $pagLink .= "<li class='page-item active'><a class='page-link' href='".$lear_management_page."?page=".($k+$i)."&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'>".($k+$i)."</a></li>";
            else
              $pagLink .= "<li class='page-item'><a class='page-link' href='".$lear_management_page."?page=".($k+$i)."&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'>".($k+$i)."</a></li>";
          }
      };
      echo $pagLink;
      if($pn == $total_pages){
        echo "<li class='page-item active'><a class='page-link' href='".$lear_management_page."?page=".$total_pages."&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'> ".$total_pages." </a></li>";
      }
      if($pn<$total_pages){
          if(($total_pages-$pn) > 2){
            echo "<li class='page-item'><a class='page-link' href='".$lear_management_page."?page=".($pn+2)."&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'> ... </a></li>";
          }
          echo "<li class='page-item'><a class='page-link' href='".$lear_management_page."?page=".$total_pages."&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'> ".$total_pages." </a></li>";
          echo "<li class='page-item'><a class='page-link' href='".$lear_management_page."?page=".($pn+1)."&limit=" . $limit . (!empty($page_parameter) ? $page_parameter : '') . "'> > </a></li>";
      }
    }

    ?>

    </ul>

   </div>

</div>

<!-- Modal -->
<div class="modal fade" id="model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">CSV Download</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../functions/<?php echo $download_email_func;?>" method="post">
        <input hidden type="number" name="start_from" value="<?php echo $start_from; ?>">
        <input hidden type="number" name="limit" value="<?php echo $limit; ?>">
        <input hidden type="text" name="email_type" value="<?php echo $get_type; ?>">
        <input hidden type="text" name="email_status" value="<?php echo $get_status; ?>">
        <input hidden type="number" name="user_id" value="<?php echo $user_id; ?>">
      <div class="modal-body">
        <div class="pb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="download_type" id="thispage" checked value="this" onchange="thispageChange()">
            <label class="form-check-label" for="thispage">This Page</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="download_type" id="thispageall" value="all" onchange="thispageChange()">
            <label class="form-check-label" for="thispageall">All Page</label>
          </div>
        </div>
        <div class="pb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="download_type" id="custompage" value="custom" onchange="custompageChange()">
            <label class="form-check-label" for="custompage">Custom</label>
          </div>
        </div>
        <div class="row border-top pt-2" id="customfiled">
          <div class="col-sm-6">
            <div class="form-group form-check">
              <input type="checkbox" name="all" class="form-check-input check_all" value="all" checked onchange="allChange(this)" id="checkbox1">
              <label class="form-check-label" for="checkbox1">ALL</label>
            </div>
            <div class="form-group form-check">
              <input type="checkbox" name="valid" value="valid" class="form-check-input check_sub" onchange="cbChange(this)" id="checkbox2">
              <label class="form-check-label" for="checkbox2">Deliverables</label>
            </div>
            <div class="form-group form-check">
              <input type="checkbox" name="invalid" value="invalid" class="form-check-input check_sub" onchange="cbChange(this)" id="checkbox3">
              <label class="form-check-label" for="checkbox3">Non Deliverables</label>
            </div>
            <div class="form-group form-check">
              <input type="checkbox" name="catchall" value="catchall" class="form-check-input check_sub" onchange="cbChange(this)" id="checkbox4">
              <label class="form-check-label" for="checkbox4">Deliverables with Risk</label>
            </div>

          </div>
          <div class="col-sm-6">
            <div class="form-group form-check">
              <input type="checkbox" name="free" value="free" class="form-check-input check_sub" onchange="cbChange(this)" id="checkbox5">
              <label class="form-check-label" for="checkbox5">Free Account</label>
            </div>
            <div class="form-group form-check">
              <input type="checkbox" name="role" value="role" class="form-check-input check_sub" onchange="cbChange(this)" id="checkbox6">
              <label class="form-check-label" for="checkbox6">Role Account</label>
            </div>
            <div class="form-group form-check">
              <input type="checkbox" name="disposable" value="disposable" class="form-check-input check_sub" onchange="cbChange(this)" id="checkbox7">
              <label class="form-check-label" for="checkbox7">Disposable Account</label>
            </div>
            <div class="form-group form-check">
              <input type="checkbox" name="syntax" value="syntax" class="form-check-input check_sub" onchange="cbChange(this)" id="checkbox8">
              <label class="form-check-label" for="checkbox8">Syntex Error</label>
            </div>
          </div>
        </div>
      </div>

      <script>
        function cbChange(obj){
          var cbs = document.getElementsByClassName("check_all");
          cbs[0].checked = false;
        }
        function allChange(obj){
          var cbs = document.getElementsByClassName("check_sub");
          for (var i = 0; i < cbs.length; i++) {
              cbs[i].checked = false;
          }
        }
        function thispageChange(){
          $('#customfiled').css('display','none');
        }
        function custompageChange(){
          $('#customfiled').css('display','flex');
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
<?php include '../include/footer.php'?>