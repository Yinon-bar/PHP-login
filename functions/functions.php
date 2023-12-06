<?php

// Helper functions

function clean_str($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = htmlentities($data);
  return $data;
}

function redirect($location): void
{
  header("location: $location");
}

function set_message($message)
{
  if (!empty($message)) {
    $_SESSION['message'] = $message;
  } else {
    $message = '';
  }
}

function display_message()
{
  if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
  }
}

function token_generator()
{
  $token = $_SESSION['token'] = md5(uniqid(mt_rand(), true));
  return $token;
}

// Email Exist
function email_exist($email): bool
{
  $sql = "SELECT id FROM users WHERE email = '$email'";
  $result = query($sql);
  $user = mysqli_fetch_assoc($result);
  if ($user) {
    return true;
  }
  return false;
}

// Username Exist
function user_name_exist($user_name): bool
{
  $sql = "SELECT id FROM users WHERE user_name = '$user_name'";
  $result = query($sql);
  $user_name = mysqli_fetch_assoc($result);
  if ($user_name) {
    return true;
  }
  return false;
}

// Mailing Function
function send_email($email, $subject, $msg, $headers)
{
  return mail($email, $subject, $msg, $headers);
}

// Validation Functions

function validate_user_registration(): void
{

  $errors = [];

  $min = 3;
  $max = 20;

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fName     = clean_str($_POST['first_name']);
    $lName     = clean_str($_POST['last_name']);
    $userName  = clean_str($_POST['username']);
    $email     = clean_str($_POST['email']);
    $password  = clean_str($_POST['password']);
    $confirm   = clean_str($_POST['confirm_password']);

    if (strlen($fName) < 3) {
      $errors[] = "Your first name cannot be less than $min character <br />";
    }
    if (!empty($errors)) {
      foreach ($errors as $error) {
        echo $error;
      }
    }
    if (email_exist($email)) {
      $errors[] = "Email already in use <br />";
    } else {
      if (register_user($fName, $lName, $userName, $email, $password)) {
        set_message("<p class='bg-success text-center'>Please check your Email or Spam folder for an actiovation link</p>");
        redirect("index.php");
      }
    }
  }
}

function register_user($fName, $lName, $userName, $email, $password)
{
  $fName = escape($fName);
  $lName = escape($lName);
  $userName = escape($userName);
  $email = escape($email);
  $password = escape($password);

  if (email_exist($email)) {
    return false;
  } else if (user_name_exist($userName)) {
    return false;
  } else {
    $password = md5($password);
    $validation = md5($userName . microtime());
    $sql = "INSERT INTO users(first_name, last_name, user_name, email, password, validation_code, active) 
            VALUES ('$fName', '$lName', '$userName', '$email', '$password', '$validation', 0)";
    $result = query($sql);
    send_email($email, "Activate Account", "Please click the link below to activate your account http://localhost/tutorials/login/activate.php?email=$email&code=$validation", "From: noreplay@mywebsite");
    return true;
  }
}

// Activation Function
function activate_user()
{
  if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_GET['email'])) {
      echo $email = $_GET['email'];
      echo $validation = $_GET['code'];
      $sql = "SELECT id FROM users WHERE email = '$email' AND validation_code = '$validation'";
      $result = query($sql);
      confirm_data($result);
      if (row_count($result) == 1) {
        $sql = "UPDATE users SET active = 1, validation_code = 0 WHERE email = '$email' AND validation_code = '$validation'";
        $result = query($sql);
        confirm_data($result);
        set_message("<p class='bg-success'>Your account has been activated! Please login</p>");
        redirect("login.php");
      } else {
        set_message("<p class='bg-danger'>Can't be activated</p>");
        redirect("login.php");
      }
    }
  }
}

// Validate user login Functions

function validate_user_login(): void
{
  $errors = [];
  $min = 3;
  $max = 20;
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = escape($_POST['email']);
    $password = escape($_POST['password']);
    $remember = isset($_POST['remember']) ?? null;

    if (empty($email)) {
      $errors[] = "Email field cannot be empty";
    }
    if (empty($password)) {
      $errors[] = "Password field cannot be empty";
    }
    if (!empty($errors)) {
      foreach ($errors as $error) {
        echo $error;
      }
    } else {
      if (login_user($email, $password, $remember)) {
        echo "Gooooooooood";
        redirect("admin.php");
      } else {
        echo "Your credentials are not correct";
      }
    }
  }
}

function login_user($email, $password, $remember)
{
  $sql = "SELECT password, id FROM users WHERE email = '$email' AND active = 1";
  $result = query($sql);
  if (row_count($result) == 1) {
    $row = fetch_array($result);
    $db_password = $row['password'];
    if ($password === $db_password) {
      if ($remember == "on") {
        setcookie('email', $email, time() + 86400);
      }
      $_SESSION['email'] = $email;
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

// Logged in function
function logged_in()
{
  if (isset($_SESSION['email']) or isset($_COOKIE['email'])) {
    return true;
  }
  return false;
}

// Recover password
function recover_password()
{
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['token']) && $_POST['token'] == $_SESSION['token']) {
      $email = $_POST['email'];
      if (email_exist($email)) {
        $validation_code = md5($email + microtime());
        setcookie('temp_access', $validation_code, time() + 60);
        $sql = "UPDATE users SET validation_code = '$validation_code' WHERE email = '$email'";
        $result = query($sql);
        confirm_data($result);
        $subject = "Email recover";
        $message = "Please enter the code $validation_code in the correct input, Click here to reset your password http://localhost/tutorials/login/login.php?email=$email&code=$validation_code";
        $headers = "From: noreplay@hostinger.com";
        send_email($email, $subject, $message, $headers);
        echo "Check your email";
      } else {
        echo "This email does not exist";
      }
    } else {
      redirect("index.php");
    }
  }
}
