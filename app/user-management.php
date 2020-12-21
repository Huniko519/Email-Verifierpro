<?php
$title = 'User Management';
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

if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
    $limit = test_input($_GET["limit"]);
} else {
    $limit = 10;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['row-btn'])) {
    if (isset($_POST['row']) && $_POST['row'] != 0 && is_numeric($_POST['row'])) {
        $limit = test_input($_POST['row']);
    }
  }
}
if (isset($_GET["page"]) && is_numeric($_GET["page"]) && $_GET["page"] > 0) {
    $pn = test_input($_GET["page"]);
} else {
    $pn = 1;
}

$start_from = ($pn - 1) * $limit;
$i = $start_from;
$sql_w_l = "SELECT C.id, C.fname, C.status, C.lname, C.image, C.category, C.email, C.join_date, C.user_ip, count(S.id) AS total_email
FROM admin C LEFT JOIN user_email_list S ON (C.id=S.user_id)
GROUP BY C.id, C.fname";
$sql = $sql_w_l.' LIMIT '.$start_from.','.$limit;
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800"><?php echo $title;?></h1>
</div>
<div class="bg-white rounded shadow p-2">
  <?php if(isset($_SESSION['action']) && isset($_SESSION['action_cat']) && $_SESSION['action_cat'] == 'exicute'){ ?>
    <span class="mb-3 text-<?php echo $_SESSION['action'] ? 'danger' : 'success' ?>"><?php echo isset($_SESSION['action_message']) ? $_SESSION['action_message'] : '' ;?></span>
  <?php } ?>
  <div class="mt-2 mb-2">
    <div class="d-inline-block">
      <form class="form-inline" action="" method="post">
        <div class="form-group mr-sm-1 mb-2">
          <select name="row" class="form-control" placeholder="Password">
            <option value="0">Select Row</option>
            <option <?php if($limit == 20){echo 'selected';}?> value="20">20</option>
            <option <?php if($limit == 30){echo 'selected';}?> value="30">30</option>
            <option <?php if($limit == 50){echo 'selected';}?> value="50">50</option>
          </select>
        </div>
        <button type="submit" name="row-btn" class="btn btn-success mb-2">GO</button>
      </form>
    </div>
  </div>
  <div class="">
    <div style="overflow-x:auto" class="">
    <table class="table table-sm">
      <thead class="thead-light">
        <tr>
          <th scope="col">Serial</th>
          <th scope="col">Name</th>
          <th scope="col">Image</th>
          <th scope="col">Email</th>
          <th scope="col">Join Date</th>
          <th scope="col">Total Verify</th>
          <th scope="col">User ip</th>
          <th scope="col">Status</th>
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
              <th scope="row"><?php echo $i;?></th>
              <td><?php echo $row['fname'].' '.$row['lname'] ;?></td>
              <td>
                <img class="user-img" src="img/<?php echo $row['image'];?>" alt="">
              </td>
              <td><?php echo $row['email'];?></td>
              <td><?php echo $row['join_date'];?></td>
              <td><?php echo $row['total_email'];?></td>
              <td><?php echo $row['user_ip'];?></td>
              <td> <span class="badge badge-<?php echo (($row['status'] == 'unverified') ? 'warning' : (($row['status'] == 'active') ? 'success' : 'dark'));?>"><?php echo $row['status'];?></span></td>
              <td>
                <?php if($row['category'] == 'admin'){
                  echo "Admin";
                }else{
                ?>
                <form class="d-inline-block" action="../functions/<?php echo $user_management_func;?>" method="post">
                  <input type="text" hidden name="target_id" value="<?php echo $row['id'] ;?>">
                  <input type="text" hidden name="email" value="<?php echo $row['email'] ;?>">
                  <input type="text" hidden name="fname" value="<?php echo $row['fname'] ;?>">
                  <?php if($row['status'] == 'suspend'){?>
                    <button type="submit" name="active-btn" class="btn btn-sm btn-success" onclick="return confirm('do you want to active this user?');">Active</button>
                  <?php }elseif($row['status'] == 'active'){ ?>
                    <button type="submit" name="suspend-btn" class="btn btn-sm btn-dark" onclick="return confirm('do you want to suspend this user?');">Suspend</button>
                  <?php }else{ ?>
                    <button disabled class="btn btn-sm btn-dark">Suspend</button>
                  <?php }?>
                </form>
              <?php }?>
              </td>
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
            echo "<li class='page-item'><a class='page-link' href='".$user_management_page."?page=".($pn-1)."&limit=".$limit."'> < </a></li>";
            echo "<li class='page-item'><a class='page-link' href='".$user_management_page."?page=1"."&limit=".$limit."'> 1 </a></li>";
            if(($pn-1) > 2){
              echo "<li class='page-item'><a class='page-link' href='".$user_management_page."?page=".($pn-2)."&limit=".$limit."'> ... </a></li>";
            }
        }
        if($pn == 1){
          echo "<li class='page-item active'><a class='page-link' href='".$user_management_page."?page=1&limit=".$limit."'> 1 </a></li>";
        }
        for ($i=-1; $i<=1; $i++) {
            if($k+$i != 1 && $k+$i != $total_pages && $k+$i < $total_pages && $k+$i > 0){
              if($k+$i==$pn)
                $pagLink .= "<li class='page-item active'><a class='page-link' href='".$user_management_page."?page=".($k+$i)."&limit=".$limit."'>".($k+$i)."</a></li>";
              else
                $pagLink .= "<li class='page-item'><a class='page-link' href='".$user_management_page."?page=".($k+$i)."&limit=".$limit."'>".($k+$i)."</a></li>";
            }
        };
        echo $pagLink;
        if($pn == $total_pages){
          echo "<li class='page-item active'><a class='page-link' href='".$user_management_page."?page=".$total_pages."&limit=".$limit."'> ".$total_pages." </a></li>";
        }
        if($pn<$total_pages){
            if(($total_pages-$pn) > 2){
              echo "<li class='page-item'><a class='page-link' href='".$user_management_page."?page=".($pn+2)."&limit=".$limit."'> ... </a></li>";
            }
            echo "<li class='page-item'><a class='page-link' href='".$user_management_page."?page=".$total_pages."&limit=".$limit."'> ".$total_pages." </a></li>";
            echo "<li class='page-item'><a class='page-link' href='".$user_management_page."?page=".($pn+1)."&limit=".$limit."'> > </a></li>";
        }
      }

      ?>

    </ul>

   </div>

</div>
<?php include '../include/footer.php';?>
<?php unset($_SESSION['action'])?>
<?php unset($_SESSION['action_message'])?>
<?php unset($_SESSION['action_cat'])?>
