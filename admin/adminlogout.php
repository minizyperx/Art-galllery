<?php
session_start();

class AdminSession {
    // Destroy all session data and redirect
    public function logout() {
        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        if (session_id() != "" || isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();

        // Redirect to login page
        $this->redirectToLogin();
    }

    // Handle redirection separately (for flexibility)
    private function redirectToLogin() {
        header("Location: adminlogin.php");
        exit();
    }
}

// Create object and call logout method
$session = new AdminSession();
$session->logout();
?>
