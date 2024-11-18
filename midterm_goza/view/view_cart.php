<?php
require_once '../classes/connection.php';
session_start();

$database = new Database();
$conn = $database->getConnection();

$cart = [];
$totalPrice = 0;


if (!isset($_SESSION['user_id'])) {
    echo "Please log in to view your cart.";
    exit();
}

if (isset($_GET['remove_item_id']) && is_numeric($_GET['remove_item_id'])) {
    $itemIdToRemove = intval($_GET['remove_item_id']);
    
    if (isset($_SESSION['cart'][$_SESSION['user_id']][$itemIdToRemove])) {
        unset($_SESSION['cart'][$_SESSION['user_id']][$itemIdToRemove]); 
    }

    header('Location: view_cart.php');
    exit();
}

if (!empty($_SESSION['cart'][$_SESSION['user_id']])) {
    
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'][$_SESSION['user_id']])));
    
   
    $query = "SELECT * FROM menu_items WHERE item_id IN ($ids)";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $itemId = $item['item_id'];
        $itemName = $item['name'];
        $itemPrice = $item['price'];
        $itemQuantity = $_SESSION['cart'][$_SESSION['user_id']][$itemId]['quantity'];
        
        $totalPrice += $itemPrice * $itemQuantity; 
        $cart[] = [
            'id' => $itemId,
            'name' => $itemName,
            'price' => $itemPrice,
            'quantity' => $itemQuantity
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            margin-top: 50px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            color: #e74c3c;
        }
        .table {
            margin-top: 20px;
        }
        .btn-primary {
            background-color: #f39c12;
            border-color: #f39c12;
        }
        .btn-primary:hover {
            background-color: #e67e22;
            border-color: #e67e22;
        }
        .btn-success {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
            border-color: #27ae60;
        }
        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
        .mt-4 {
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Your Cart</h1>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>Php <?php echo htmlspecialchars($item['price']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>Php <?php echo htmlspecialchars($item['price'] * $item['quantity']); ?></td>
                        <td><a href="view_cart.php?remove_item_id=<?php echo $item['id']; ?>" class="btn btn-danger">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total: Php <?php echo $totalPrice; ?></h3>
    <?php endif; ?>

   
    <div class="mt-4">
        <a href="../view/view_restaurant.php" class="btn btn-primary">Go Back to Menu</a>
        <a href="../view/view_order.php" class="btn btn-success">Proceed to Checkout</a>
    </div>
</div>
</body>
</html>
