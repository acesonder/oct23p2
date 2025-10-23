<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

clearUserSession();
header('Location: /modules/auth/login.php');
exit;
?>
