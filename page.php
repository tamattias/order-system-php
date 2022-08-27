<?php

require_once('account.php');

$page = $_GET['page'] ?? '';

if ($page !== 'login' && !$account->isLoggedIn()) {
    header('location: /?page=login');
    exit;
}

switch ($page) {
    default:
        http_response_code(404);
        require('pages/not_found.php');
        exit;

    case '':
        require('pages/home.php');
        break;

    case 'login':
        require('pages/login.php');
        break;

    case 'logout':
        require('pages/logout.php');
        break;

    case 'orders':
        require('pages/orders.php');
        break;

    case 'order':
        require('pages/order.php');
        break;

    case 'products':
        require('pages/products.php');
        break;

    case 'product':
        require('pages/product.php');
        break;
}
