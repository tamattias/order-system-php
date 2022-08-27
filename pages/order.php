<?php

require_once('../database.php');

if (!empty($_GET['orderId'])) {
    $orderId = filter_input(INPUT_GET, 'orderId', FILTER_VALIDATE_INT);
    if (empty($orderId)) {
        require('not_found.php');
        return;
    }
    $order = $db->getOrderById($orderId);
    if (empty($order)) {
        require('not_found.php');
        return;
    }
}

if (!empty($_GET['action'])) {
    $action = filter_input(INPUT_GET, 'action');
} else {
    if (empty($order)) {
        $action = 'create';
    } else {
        $action = 'update';
    }
}
if ($action === 'delete') {
    if (empty($order)) {
        exit('No order to delete.');
    }
    $db->deleteOrder($orderId);
    header('location: /?page=orders');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerFirstName = filter_input(INPUT_POST, 'customer-first-name');
    $customerLastName  = filter_input(INPUT_POST, 'customer-last-name');
    $customerId = $db->insertCustomer($customerFirstName, $customerLastName);

    $shippingAddress = filter_input(INPUT_POST, 'shipping-address');

    $orderItems = array();
    foreach ($_POST['item-product-id'] as $key => $itemProductId) {
        $orderItems []= array(
            'productId' => intval($itemProductId),
            'quantity'  => intval($_POST['item-quantity'][$key]),
            'price'     => floatval($_POST['item-price'][$key]),
        );
    }

    if ($action === 'update') {
        $db->updateOrder($orderId, $customerFirstName, $customerLastName, $shippingAddress, $orderItems);
        header('location: /?page=order&orderId='.$orderId);
    } else {
        $orderId = $db->insertOrder($customerId, $shippingAddress, $orderItems);
        if (!empty($orderId)) {
            // Redirect to the order page.
            $_SESSION['orderPageMessage'] = 'Order created succesfully.';
            header('location: /?page=order&orderId='.$orderId);
        } else {
            // Redirect to the order page.
            $_SESSION['orderPageError'] = 'Could not create order.';
            header('location: /?page=order'); 
        }
    }
    
    exit;
}

?>

<form method="POST" class="my-5">
    <h2><?php echo $action === 'create' ? 'Create New Order' : 'Update Existing Order'; ?></h2>

    <?php
    if (isset($_SESSION['orderPageMessage'])) {
        ?>
        <div class="mb-3 alert alert-success">
            <?php echo($_SESSION['orderPageMessage']); ?>
        </div>
        
        <?php
        // Clear the message.
        unset($_SESSION['orderPageMessage']);
    }
    ?>

    <?php
    if (isset($_SESSION['orderPageError'])) {
        ?>
        <div class="mb-3 alert alert-danger">
            <?php echo($_SESSION['orderPageError']); ?>
        </div>
        
        <?php
        // Clear the message.
        unset($_SESSION['orderPageError']);
    }
    ?>
    
    <div class="mb-3">
        <label for="order-form__first-name" class="form-control-label" required>Customer First Name</label>
        <input id="order-form__first-name" class="form-control" name="customer-first-name" <?php if (isset($order)) echo('value="'.$order['first_name'].'"'); ?>>
    </div>

    <div class="mb-3">
        <label for="order-form__last-name" class="form-control-label" required>Customer Last Name</label>
        <input id="order-form__last-name" class="form-control" name="customer-last-name" <?php if (isset($order)) echo('value="'.$order['last_name'].'"'); ?>> 
    </div>

    <div class="mb-3">
        <label for="order-form__shipping-address" class="form-control-label" required>Shipping Address</label>
        <textarea id="order-form__shipping-address" class="form-control" name="shipping-address" rows="3"><?php if (isset($order)) echo(trim($order['shipping_address'])); ?></textarea>
    </div>

    <fieldset class="mb-3 p-3 border">
        <legend>Order Items</legend>
        <table class="table caption-top" id="order-form__item-table">
            <thead>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </thead>
            <tbody>
                <?php
                if (isset($order)) {
                    $items = $db->getOrderItems(intval($order['id']));
                    foreach ($items as $item) {
                    ?>
                        <tr>
                            <td>
                                <input type="hidden" name="item-product-id[]" value="<?php echo $item['id']; ?>">
                                <?php echo $item['id']; ?>
                            </td>
                            <td>
                                <?php echo $item['name']; ?>
                            </td>
                            <td>
                                <input type="hidden" name="item-price[]" value="<?php echo $item['price']; ?>">
                                <?php echo $item['price']; ?>
                            </td>
                            <td>
                                <input type="hidden" name="item-quantity[]" value="<?php echo $item['quantity']; ?>">
                                <?php echo $item['quantity']; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger order-item-delete-button">Delete</button>
                            </td>
                        </tr>
                    <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </fieldset>

    <fieldset class="mb-3 p-3 form-group border">
        <legend>Add Item to Order</legend>
        <div class="mb-3">
            <label for="order-form__new-item-product">Product</label>
            <select id="order-form__new-item-product" class="form-select">
                <?php
                $products = $db->getAllProducts();
                foreach ($products as $product) {
                    ?>
                    <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="order-form__new-item-price">Price</label>
            <input id="order-form__new-item-price" class="form-control" type="number" value="100" min="0.0">
        </div>
        <div class="mb-3">
            <label for="order-form__new-item-quantity">Quantity</label>
            <input id="order-form__new-item-quantity" class="form-control" type="number" value="1" min="1">
        </div>
        <button id="order-form__new-item-add-button" type="button" class="btn btn-secondary">Add</button>
        <script>
            function deleteItemHandler(button) {
                const row = button.parentNode.parentNode;
                row.remove();
            }

            (function() {
                Array.from(document.getElementsByClassName('order-item-delete-button')).forEach(button => {
                    button.addEventListener('click', function(e) {
                        deleteItemHandler(e.target);
                    });
                });

                const addButton = document.getElementById('order-form__new-item-add-button');
                addButton.addEventListener('click', function() {
                    const productSelect = document.getElementById('order-form__new-item-product')
                    const selectedProduct = productSelect.options[productSelect.selectedIndex];

                    const productId = selectedProduct.value;
                    const price = document.getElementById('order-form__new-item-price').value;
                    const quantity = document.getElementById('order-form__new-item-quantity').value;

                    const table = document.getElementById('order-form__item-table');
                    const body = table.getElementsByTagName('tbody')[0];
                    const row = body.insertRow();
                
                    const productIdCell = row.insertCell();
                    const productIdElement = document.createElement('input');
                    productIdElement.setAttribute('type', 'hidden');
                    productIdElement.setAttribute('name', 'item-product-id[]');
                    productIdElement.value = productId;
                    productIdCell.appendChild(productIdElement);
                    productIdCell.appendChild(document.createTextNode(productId));

                    const productNameCell = row.insertCell();
                    productNameCell.appendChild(document.createTextNode(selectedProduct.label));

                    const productPriceCell = row.insertCell();
                    const productPriceElement = document.createElement('input');
                    productPriceElement.setAttribute('type', 'hidden');
                    productPriceElement.setAttribute('name', 'item-price[]');
                    productPriceElement.value = price;
                    productPriceCell.appendChild(productPriceElement);
                    productPriceCell.appendChild(document.createTextNode(price));

                    const productQtyCell = row.insertCell();
                    const productQtyElement = document.createElement('input');
                    productQtyElement.setAttribute('type', 'hidden');
                    productQtyElement.setAttribute('name', 'item-quantity[]');
                    productQtyElement.value = quantity;
                    productQtyCell.appendChild(productQtyElement);
                    productQtyCell.appendChild(document.createTextNode(quantity));

                    const actionsCell = row.insertCell();
                    const buttonElement = document.createElement('button');
                    buttonElement.setAttribute('type', 'button');
                    buttonElement.classList.add('btn');
                    buttonElement.classList.add('btn-danger');
                    buttonElement.addEventListener('click', function(e) {
                        deleteItemHandler(e.target);
                    });
                    buttonElement.appendChild(document.createTextNode('Delete'));
                    actionsCell.appendChild(buttonElement);
                });
            })();
        </script>
    </fieldset>

    <div class="btn-group mb-3" role="group">
        <button class="btn btn-primary"><?php echo $action === 'update' ? 'Update' : 'Create'; ?></button>
        <a href="/?page=orders" class="btn btn-secondary">View All Orders</a>
    </div>
</form>