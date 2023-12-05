<?php include("./includes/header.php") ?>

<div class="jumbotron">
  <h1 class="text-center"><?php display_message(); ?></h1>
</div>

<?php
$sql = "SELECT * FROM users";
$result = query($connect, $sql);
$row = fetch_array($result);
?>

<?php include("./includes/footer.php") ?>