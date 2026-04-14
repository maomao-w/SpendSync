<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Restrict access to unauthorized pages
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?show=form&error=unauthorized");
    exit();
}

// 2. Session Timeout Logic (Set to 30 minutes / 1800 seconds)
$timeout_duration = 1800; 

if (isset($_SESSION['last_activity'])) {
    // Check if the time since last activity exceeds the timeout duration
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    if ($elapsed_time > $timeout_duration) {
        // Session expired: Unset and destroy session
        session_unset();
        session_destroy();
        header("Location: index.php?show=form&error=timeout");
        exit();
    }
}

// 3. Update last activity timestamp every time the user interacts with the page
$_SESSION['last_activity'] = time();
?>
