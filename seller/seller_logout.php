<?php
session_start();

class UserSession {
    // Method to handle logout process
    public function logout() {
        // Clear all session variables
        $_SESSION = [];

        // Delete session cookie (if exists)
        if (session_id() != "" || isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session completely
        session_destroy();

        // Redirect to home page
        $this->redirectToHome();
    }

    // Redirect method (kept private for encapsulation)
    private function redirectToHome() {
        header("Location: ../index.php");
        exit();
    }
}

// Create an object and call the logout function
$userSession = new UserSession();
$userSession->logout();
?>
