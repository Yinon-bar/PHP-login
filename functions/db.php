<?php

// חיבור לדאטהבייס בבית
$connect = mysqli_connect('localhost', 'root', '', 'login_edwin_diaz');

// חיבור לדאטהבייס ברשת
// $connect = mysqli_connect('srv1048.hstgr.io', 'u528206822_inon', 'INONab@053508384', 'u528206822_tutorials');

function row_count($result)
{
  return mysqli_num_rows($result);
}

function query($query)
{
  global $connect;
  return mysqli_query($connect,  $query);
}

function escape($string)
{
  global $connect;
  return mysqli_real_escape_string($connect, $string);
}

function fetch_array($result)
{
  return mysqli_fetch_array($result);
}


function confirm_data($result)
{
  if (!$result) {
    global $connect;
    die("Query faild" . mysqli_error($connect));
  }
}
