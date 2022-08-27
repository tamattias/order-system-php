<?php

require_once('../database.php');

if (isset($_GET['productId'])) {
    $productId = filter_input(INPUT_GET, 'productId', FILTER_VALIDATE_INT);
    if (empty($productId)) {
        require('not_found.php');
        return;
    }
    $product = $db->getProductById(intval($_GET['productId']));
    if (empty($product)) {
        require('not_found.php');
        return;
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'delete') {
        $db->deleteProduct($productId);
        header('location: ?page=products');
        exit;
    }
} else {
    $action = !empty($productId) ? 'update' : 'create';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = filter_input(INPUT_POST, 'name');
    $productPrice = filter_input(INPUT_POST, 'price');
    if ($action === 'update') {
        $db->updateProduct($productId, $productName, $productPrice);
        header('location: /?page=product&productId='.$productId);
        exit;
    } else {
        $productId = $db->insertProduct($productName, floatval($productPrice));
    }
    $location = '/?page=product&create_status=' . (!empty($productId) ? 'ok' : 'error');
    if (!empty($productId)) {
        $location .= '&productId='.$productId;
    }
    header('location: ' . $location);
    exit;
}

?>

<form method="POST" class="my-5">
    <h2><?php echo ($action === 'create') ? 'Create New Product' : 'Update Existing Product'; ?></h2>

    <?php
    if (isset($_GET['create_status'])) {
        $ok = $_GET['create_status'] === 'ok';
        ?>
        <div class="alert <?php echo $ok ? 'alert-success' : 'alert-danger'?>">
            <?php
            if ($ok) { ?>
                Product created successfully.
            <?php } else { ?>
                Could not create product.
            <?php } ?>
        </div>
    <?php } ?>

    <div class="mb-3">
        <label for="product-form__name" class="form-control-label">Name</label> 
        <input id="product-form__name" class="form-control" name="name" <?php if (!empty($product)) echo 'value="'.$product['name'].'"'; ?> required>
    </div>

    <div class="mb-3">
        <label for="product-form__price" class="form-control-label">Price</label> 
        <input id="product-form__price" class="form-control" type="number" min="0" name="price" <?php if (!empty($product)) echo 'value="'.$product['price'].'"'; ?> required>
    </div>

    <div class="mb-3 btn-group">
        <button class="btn btn-primary"><?php echo $action === 'update' ? 'Update' : 'Create'; ?></button>
        <a class="btn btn-secondary" href="/?page=products">View All Products</a>
    </div>
</form>
    

