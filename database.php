<?php

define('DB_FILE', '../orders.db');
define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'pickles');

class Database {
    public function __construct() {
        $isFirstTime = !file_exists(DB_FILE);
        
        $this->db = new SQLite3(DB_FILE);
        $this->db->enableExceptions();

        if ($isFirstTime) {
            $this->createSchema();
            $this->seed();
        }
    }

    private function createSchema() {
        $this->db->exec(<<<'EOD'
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY,
                username TEXT NOT NULL,
                password_hash TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                price REAL NOT NULL
            );

            CREATE TABLE IF NOT EXISTS order_items (
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                price REAL NOT NULL,
                FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY(product_id) REFERENCES products(id)
            );
            
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                customer_id INTEGER NOT NULL,
                shipping_address TEXT NOT NULL,
                FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
            );
            
            CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL
            );
        EOD);
    }

    private function seed() {
        // Create admin account.
        $stmt = $this->db->prepare('INSERT INTO users(username, password_hash) VALUES (:username, :passwordHash)');
        $stmt->bindValue(':username', DEFAULT_ADMIN_USERNAME, SQLITE3_TEXT);
        $stmt->bindValue(':passwordHash', password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_BCRYPT), SQLITE3_TEXT);
        $stmt->execute();
        $stmt->close();

        // Create customers.
        $this->db->exec("INSERT INTO customers(first_name, last_name) VALUES ('John', 'Doe')");
        $this->db->exec("INSERT INTO customers(first_name, last_name) VALUES ('Jane', 'Doe')");

        // Create some products.
        $this->db->exec("INSERT INTO products(`name`, price) VALUES ('Toy', 125.0)");
        $this->db->exec("INSERT INTO products(`name`, price) VALUES ('Apple', 200.0)");
        $this->db->exec("INSERT INTO products(`name`, price) VALUES ('Blanket', 300.0)");

        // Create orders.
        $this->db->exec("INSERT INTO orders(customer_id, shipping_address) VALUES (1, '1 Apple Way')");
        $this->db->exec("INSERT INTO order_items(order_id, product_id, quantity, price) VALUES (1, 1, 2, 400.0)");
        $this->db->exec("INSERT INTO order_items(order_id, product_id, quantity, price) VALUES (1, 2, 8, 1000.0)");
        $this->db->exec("INSERT INTO orders(customer_id, shipping_address) VALUES (2, '1 Orange Way')");
        $this->db->exec("INSERT INTO order_items(order_id, product_id, quantity, price) VALUES (2, 1, 8, 1000.0)");
        $this->db->exec("INSERT INTO order_items(order_id, product_id, quantity, price) VALUES (2, 2, 100, 200.0)");
    }

    public function logIn(string $username, string $password) {
        $stmt = $this->db->prepare('SELECT password_hash FROM users WHERE username=:username LIMIT 1');
        $stmt->bindParam(':username', $username);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_NUM);
        $verified = !empty($row) && password_verify($password, $row[0]);
        $stmt->close();
        return $verified;
    }

    public function getAllOrders(): array {
        $orders = array();
        $ordersResult = $this->db->query('SELECT orders.*, customers.first_name, customers.last_name FROM orders INNER JOIN customers ON orders.customer_id = customers.id');
        while ($order = $ordersResult->fetchArray(SQLITE3_ASSOC)) {
            $orders[] = $order;
        }
        return $orders;
    }

    public function getAllProducts(): array {
        $products = array();
        $result = $this->db->query('SELECT * FROM products');
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $products []= $row;
        }
        return $products;
    }

    public function getProductById(int $id): array {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id=:id');
        $stmt->bindValue(':id', $id);
        $result = $stmt->execute();
        $product = $result->fetchArray(SQLITE3_ASSOC);
        $stmt->close();
        return $product;
    }

    public function insertProduct(string $name, float $price) {
        $stmt = $this->db->prepare('INSERT INTO products (name, price) VALUES (:name, :price)');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    public function updateProduct(int $id, string $name, float $price) {
        $stmt = $this->db->prepare('UPDATE products SET name=:name, price=:price WHERE id=:id');
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':price', $price);
        $stmt->execute();
    }

    public function deleteProduct(int $id) {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id=:id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function insertCustomer(string $firstName, string $lastName): int|null {
        $stmt = $this->db->prepare('INSERT INTO customers (first_name, last_name) VALUES (:firstName, :lastName)');
        $stmt->bindValue(':firstName', $firstName, SQLITE3_TEXT);
        $stmt->bindValue(':lastName', $lastName, SQLITE3_TEXT);
        $stmt->execute();
        $stmt->close();
        return $this->db->lastInsertRowID();
    }

    private function insertOrderItems(int $orderId, array $items) {
        $stmt = $this->db->prepare('INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (:orderId, :productId, :price, :quantity)');
        $stmt->bindValue(':orderId', $orderId);
        foreach ($items as $item) {
            $stmt->bindValue(':productId', $item['productId'], SQLITE3_INTEGER);
            $stmt->bindValue(':price', $item['price'], SQLITE3_FLOAT);
            $stmt->bindValue(':quantity', $item['quantity'], SQLITE3_INTEGER);
            $stmt->execute();
        }
        $stmt->close();
    }

    public function insertOrder(int $customerId, string $shippingAddress, array $items): bool {
        $stmt = $this->db->prepare('INSERT INTO orders (customer_id, shipping_address) VALUES (:customerId, :shippingAddress)');
        $stmt->bindValue(':customerId', $customerId, SQLITE3_INTEGER);
        $stmt->bindValue(':shippingAddress', $shippingAddress, SQLITE3_TEXT);
        $stmt->execute();
        $stmt->close();
        $orderId = $this->db->lastInsertRowID();

        $this->insertOrderItems($orderId, $items);

        return $orderId;
    }

    public function updateOrder(
        int $orderId,
        string $customerFirstName,
        string $customerLastName,
        string $shippingAddress,
        array $items): bool {
        // Note: Multi-table update not supported by SQLite.

        // Update Customer Info.
        $stmt = $this->db->prepare('UPDATE customers SET first_name=:firstName, last_name=:lastName WHERE id IN (SELECT customer_id FROM orders WHERE id=:orderId)');
        $stmt->bindValue(':orderId', $orderId);
        $stmt->bindValue(':firstName', $customerFirstName);
        $stmt->bindValue(':lastName', $customerLastName);
        $stmt->execute();
        $stmt->close();

        // Update Order.
        $stmt = $this->db->prepare('UPDATE orders SET shipping_address=:shippingAddress, updated_at=CURRENT_TIMESTAMP WHERE id=:orderId');
        $stmt->bindValue(':orderId', $orderId);
        $stmt->bindValue(':shippingAddress', $shippingAddress);
        $stmt->execute();
        $stmt->close();

        // First delete all previous items.
        $stmt = $this->db->prepare('DELETE FROM order_items WHERE order_id=:orderId');
        $stmt->bindValue('orderId', $orderId);
        $stmt->execute();
        $stmt->close();

        // Now insert the new items.
        $this->insertOrderItems($orderId, $items);

        return true;
    }

    public function deleteOrder(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM orders WHERE id=:orderId');
        $stmt->bindValue('orderId', $id);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public function getOrderById(int $id): array|bool {
        $stmt = $this->db->prepare('SELECT * FROM orders INNER JOIN customers ON orders.customer_id = customers.id WHERE orders.id=:orderId LIMIT 1');
        $stmt->bindValue('orderId', $id);
        $result = $stmt->execute();
        $order = $result->fetchArray(SQLITE3_ASSOC);
        $stmt->close();
        return $order;
    }

    public function getOrderItems(int $orderId): array {
        $items = array();
        $queryResult = $this->db->query('SELECT * FROM order_items INNER JOIN products ON product_id=products.id WHERE order_id='.$orderId);
        while ($item = $queryResult->fetchArray(SQLITE3_ASSOC)) {
            $items []= $item;
        }
        return $items;
    }

    
}

$db = new Database();
