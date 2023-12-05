<?php include("./includes/header.php") ?>

<div class="container">

  <div class="jumbotron">
    <h1 class="text-center"><?= logged_in() ? "Logged in" : redirect("index.php"); ?></h1>
  </div>

  <?php include("./includes/footer.php") ?>