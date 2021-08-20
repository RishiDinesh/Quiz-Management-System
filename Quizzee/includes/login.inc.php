<?php
  session_start();
  include "db.inc.php";

  if (isset($_POST['login-submit'])) {
    // Login processing
    if (empty($_POST['email']) || empty($_POST['password'])) {
      $_SESSION['ERROR'] = "Fill all fields";
      header("Location: ../login");
      return;
    } else {
      $login_query = $conn->prepare("SELECT * FROM Users
                                     WHERE email=:email AND pwd=:pwd AND login=:login");
      $login_query->execute(array(
        ':email' => $_POST['email'],
        ':pwd' => hash('sha256', $salt.$_POST['password']),
        ':login' => 'LOGIN'
      ));
      $result = $login_query->fetch(PDO::FETCH_ASSOC);

      if ($result == false) {
        $_SESSION['ERROR'] = "Wrong email or password";
        header("Location: ../login");
        return;
      } else {
        // Create a cookie for the logged in user if he wants to be remembered
        if ($_POST['remember-me']) {
          setcookie("USERID", $result['uid'], time() + (30*86400), "/");
        }

        // Store the user in the session
        $_SESSION['USERID'] = $result['uid'];
        $_SESSION['NAME'] = $result['fname']." ".$result['lname'];
        $_SESSION['TYPE'] = 'LOGIN';
        if (!empty($result['profile_pic'])) {
          $_SESSION['PROFILE-PICTURE'] = $result['profile_pic'];
        }
        $_SESSION['SUCCESS'] = "Successfully Logged in!";
        $_SESSION['UUID'] = $result['uuid'];
        // header("Location: ../index.php");
        header("Location: ../my/dashboard");
        return;
      }
    }
  } elseif (isset($_GET['code'])) {
    // Google authentication
    require_once "../vendor/config.php";
    google_login($conn);
  } else {
    $_SESSION['ERROR'] = "Please Log in to continue";
    header("Location: ../quizzee");
    return;
  }
?>
