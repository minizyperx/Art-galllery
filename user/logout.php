<?php
session_start();

class SessionManager {
    // Logout function
    public function logout($redirectPage = '../index.php') {
        // Unset all session variables
        $_SESSION = [];

        // Delete session cookie (if exists)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy the session completely
        session_destroy();

        // Redirect to login or homepage
        $this->redirect($redirectPage);
    }

    // Private redirect helper
    private function redirect($page) {
        header("Location: " . $page);
        exit();
    }
}

// Create object and call logout
$session = new SessionManager();
$session->logout('../index.php');
?>
