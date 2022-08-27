<?php

require_once('../account.php');

$account->logOut();

header('location: /');
