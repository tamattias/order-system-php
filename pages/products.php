<?php

require_once('../database.php');

?>

<main class="my-5">
    <h2>Products</h2>
    <table class="table caption-top mb-3">
        <thead>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Actions</th>
        </thead>
        <tbody>
            <?php
            $products = $db->getAllProducts();
            foreach ($products as $product) { ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo $product['name']; ?></td>
                    <td><?php echo $product['price']; ?></td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-secondary" href="/?page=product&productId=<?php echo $product['id']; ?>">Modify</a>
                            <a class="btn btn-danger" href="/?page=product&action=delete&productId=<?php echo $product['id']; ?>">Delete</a>
                        </div> 
                    </td>
                </tr>
            <?php }  ?>
        </tbody>
    </table>
    <div class="mb-3 btn-group">
        <a class="btn btn-primary" href="/?page=product">Create New Product</a>
        <a class="btn btn-secondary" href="/">Home</a>
    </div>
</main>
