<?php

require_once('../database.php');

function ordersNavMenu() {
    ?>
    <nav class="btn-group my-2" role="group">
        <a href="/?page=order" class="btn btn-primary" aria-role="button">New Order</a>
        <a href="/" class="btn btn-secondary" aria-role="button">Home</a>
        <a type="button" class="btn btn-secondary" href="/?page=logout">Log Out</a>
    </nav>
    <?php
}

?>

<main class="my-5">
    <?php ordersNavMenu(); ?>

    <h2>Orders</h2>

    <table class="table">
        <thead>
            <th>ID</th>
            <th>Customer</th>
            <th>Time</th>
            <th>Actions</th>
        </thead>
        <tbody>
            <?php
                $orders = $db->getAllOrders();
                foreach ($orders as &$order) {
                    ?>
                    <tr>
                        <td><?php echo($order['id']); ?></td>
                        <td><?php echo($order['first_name']); ?> <?php echo($order['last_name']); ?></td>
                        <td><?php echo($order['created_at']); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/?page=order&action=update&orderId=<?php echo $order['id']; ?>" class="btn btn-secondary">Modify</a>
                                <a href="/?page=order&action=delete&orderId=<?php echo $order['id']; ?>" class="btn btn-danger">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>

    <?php ordersNavMenu(); ?>
</main>