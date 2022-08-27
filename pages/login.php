<?php

require_once('../account.php');

if ($account->loggedIn) {
    header('location: /');
} else {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST': {
            $username = filter_input(INPUT_POST, 'username');
            $password = filter_input(INPUT_POST, 'password');
            if ($account->logIn($username, $password)) {
                // Redirect to homepage on succesful login.
                header('location: /');
            } else {
                // Redirect to same page after error.
                http_response_code(401);
                header('location: /?page=login');
            }
            break;
        }
            
        case 'GET':
            require('../components/login_form.php');
            break;
    }
}

?>
