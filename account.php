<?php

require_once('database.php');

class Account {
    public function __construct() {
        $this->loggedIn = isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === TRUE);
    }

    /**
     * Logs the user in.
     * 
     * @param string username Username.
     * @param string password Raw password.
     * @return boolean Whether login was successful.
     */
    public function logIn(string $username, string $password) {
        global $db;
        
        // Try to log in using database.
        if (!$db->logIn($username, $password)) {
            // Hande bad login.
            $_SESSION['loggedIn'] = FALSE;
            $_SESSION['loginError'] = 'Bad username or password.';
            $this->loggedIn = false;
            return false;
        }

        // Successul login.
        $_SESSION['loggedIn'] = TRUE;
        $this->loggedIn = true;

        return true;
    }

    public function logOut() {
        unset($_SESSION['loggedIn']);
        $this->loggedIn = false;
        return true;
    }

    public function isLoggedIn() {
        return $this->loggedIn;
    }

    public function getLoginError() {
        return $_SESSION['loginError'] ?? '';
    }

    public function clearLoginError() {
        unset($_SESSION['loginError']);
    }
}

$account = new Account();