<?php
// view_id.php

// 1. Show all errors (so you can debug 500s)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Validate incoming ID
if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    exit('<div class="alert alert-danger">Invalid or missing user ID.</div>');
}
$userId = (int)$_GET['id'];

// 3. Connect to your DB
$db_server = "localhost";
$db_user   = "root";
$db_pass   = "";
$db_name   = "psabe";

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
    http_response_code(500);
    exit('<div class="alert alert-danger">DB Connection failed: '.mysqli_connect_error().'</div>');
}

// 4. Fetch user record
$sql  = "SELECT email, name, designation, university, id_photo_url 
         FROM users 
         WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    exit('<div class="alert alert-danger">Prepare failed: '.mysqli_error($conn).'</div>');
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $email, $name, $designation, $university, $photoUrl);

if (! mysqli_stmt_fetch($stmt)) {
    http_response_code(404);
    exit('<div class="alert alert-warning">User not found.</div>');
}
mysqli_stmt_close($stmt);
mysqli_close($conn);

// 5. Output the HTML for the modal body
?>
<div class="text-center">
  <div id="id-card" class="mx-auto" style="width:320px;">
    <?php if (!empty($photoUrl)): ?>
      <img src="<?= htmlspecialchars($photoUrl) ?>" class="img-fluid mb-3" alt="ID Photo">
    <?php else: ?>
      <div class="bg-secondary text-white py-5 mb-3">No Photo Available</div>
    <?php endif; ?>

    <h5 class="mb-1"><?= htmlspecialchars($name) ?></h5>
    <p class="mb-0"><?= htmlspecialchars($designation) ?></p>
    <p class="small text-muted"><?= htmlspecialchars($university) ?></p>
    <p class="small text-break"><?= htmlspecialchars($email) ?></p>
  </div>
</div>
