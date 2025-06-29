<?php
session_start();
require_once 'includes/functions.php';

logoutUser();
showAlert('Đăng xuất thành công!', 'success');
redirectTo('index.php');
?>