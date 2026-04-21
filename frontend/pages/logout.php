<?php
session_start();
session_unset();
session_destroy();
echo "<script>localStorage.removeItem('isLoggedIn'); localStorage.removeItem('lastActivity');</script>";
header("Location: ../../index.php");
exit;
