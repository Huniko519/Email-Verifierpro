
<?php
$title = '404 error';
include '../include/header.php';?>
          <!-- 404 Error Text -->
          <div class="text-center">
            <div class="error mx-auto" data-text="404">404</div>
            <p class="lead text-gray-800 mb-5">Page Not Found</p>
            <p class="text-gray-500 mb-0">It looks like you found a glitch in the matrix...</p>
            <a href="../<?php echo $index_page?>">&larr; Back to Dashboard</a>
          </div>
<?php include '../include/footer.php';?>
