<?php

ob_start();

session_start();

include("db.php");
include("./functions/functions.php");

if ($connect->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
