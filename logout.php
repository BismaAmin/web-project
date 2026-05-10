<?php
session_start();
session_destroy();   //delete session 

// Clear cookies
setcookie('user_id', '', time() - 3600, "/");
setcookie('username', '', time() - 3600, "/");

// Redirect to home
header('Location: index.html');
exit();
?>