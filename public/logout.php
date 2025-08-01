<?php
session_start();

// Xóa tất cả session variables
$_SESSION = array();

// Xóa session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Thêm headers để prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: 0");

// Redirect về trang login
header("Location: pages/login.php");
exit();
?>
