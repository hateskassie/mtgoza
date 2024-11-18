<?php
require_once '../classes/connection.php'; 

session_start(); 


if (!isset($_SESSION['user_id'])) {
    echo "Please log in to view the menu.";
    exit();
}

$database = new Database();
$conn = $database->getConnection(); 


if (!$conn) {
    die("Database connection failed.");
}


$sql = "SELECT * FROM menu_items";  
$stmt = $conn->prepare($sql);     
$stmt->execute();                 
$result = $stmt->fetchAll(PDO::FETCH_ASSOC); 


$cartCount = isset($_SESSION['cart'][$_SESSION['user_id']]) ? array_sum(array_column($_SESSION['cart'][$_SESSION['user_id']], 'quantity')) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Menu</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <style>
        body {
            background-color: #f4f4f4; 
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 40px;
        }

        h1 {
            color: #e74c3c; 
            font-size: 2.5em;
            font-weight: bold;
            text-align: center;
        }

        .card {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 20px;
            text-align: center;
        }

        .card-title {
            color: #e74c3c;
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .card-text {
            color: #f39c12; 
            font-size: 1.2em;
        }

        .btn-primary {
            background-color: #f39c12;
            border-color: #f39c12;
            width: 100%;
            font-size: 1.2em;
        }

        .btn-primary:hover {
            background-color: #e68920;
            border-color: #e68920;
        }

        .btn-success {
            background-color: #e74c3c;
            border-color: #e74c3c;
            color: white;
            font-size: 1.2em;
        }

        .btn-success:hover {
            background-color: #d63a2f;
            border-color: #d63a2f;
        }

        .d-flex {
            justify-content: space-between;
            align-items: center;
        }

        .mr-3 {
            margin-right: 20px;
        }

        .ml-3 {
            margin-left: 20px;
        }

        footer {
            background-color: #e74c3c;
            color: white;
            padding: 15px;
            text-align: center;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        .cart-count {
            font-size: 1.2em;
            color: #fff;
            background-color: #f39c12;
            border-radius: 50%;
            padding: 5px 10px;
            position: relative;
            top: -5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Restaurant Menu</h1>
        <div class="d-flex">
            
            <a href="view_cart.php" class="btn btn-success mr-3">
                Your Cart <span id="cart-count" class="cart-count"><?php echo $cartCount; ?></span>
            </a>
          
            <a href="../index.php" class="btn btn-primary ml-3">Log Out</a>
        </div>
    </div>

    <div class="row">
        <?php foreach ($result as $item): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p class="card-text">Price: Php <?php echo htmlspecialchars($item['price']); ?></p>

                        
                        <form method="POST" action="add_to_cart.php" class="add-to-cart-form">
                            <div class="form-group">
                                <label for="quantity_<?php echo $item['item_id']; ?>">Quantity:</label>
                                <input type="number" name="quantity" id="quantity_<?php echo $item['item_id']; ?>" min="1" value="1" class="form-control mb-2" required>
                            </div>
                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<footer>
    <p>&copy; 2024 Hot To Go | All Rights Reserved</p>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    
    $(".add-to-cart-form").on("submit", function(e) {
        e.preventDefault(); 

        var form = $(this);
        var itemId = form.find("input[name='item_id']").val();
        var quantity = form.find("input[name='quantity']").val();

      
        $.ajax({
            url: "add_to_cart.php",
            type: "POST",
            data: {
                item_id: itemId,
                quantity: quantity
            },
            success: function(response) {
               
                var cartCount = response.cartCount;
                $("#cart-count").text(cartCount); 
            }
        });
    });
});
</script>

</body>
</html>
