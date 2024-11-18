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
    <title>Checkout</title>
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
        .payment-options {
            margin-top: 20px;
        }
        .user-details {
            margin-bottom: 20px;
        }
        
        .modal-content {
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Checkout</h1>

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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>Php <?php echo htmlspecialchars($item['price']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>Php <?php echo htmlspecialchars($item['price'] * $item['quantity']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total: Php <?php echo $totalPrice; ?></h3>

       
        <form action="process_order.php" method="POST">
            <div class="user-details">
                <h4>User Details</h4>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="address">Full Address</label>
                    <input type="text" id="address" name="address" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="delivery_note">Delivery Note</label>
                    <textarea id="delivery_note" name="delivery_note" class="form-control"></textarea>
                </div>
            </div>

            
            <div class="payment-options">
                <h4>Select Payment Method</h4>
                <div>
                    <label>
                        <input type="radio" name="payment_method" value="cash_on_delivery" required> Cash on Delivery
                    </label>
                </div>
                <div>
                    <label>
                        <input type="radio" name="payment_method" value="credit_card" id="credit_card"> Credit Card
                    </label>
                </div>
                <div>
                    <label>
                        <input type="radio" name="payment_method" value="gcash" id="gcash"> GCash
                    </label>
                </div>
                <div>
                    <label>
                        <input type="radio" name="payment_method" value="paymaya" id="paymaya"> PayMaya
                    </label>
                </div>

                <div class="mt-4">
                    <a href="../classes/order_confirmation.php" class="btn btn-success">Confirm Order</a>
                    <a href="view_cart.php" class="btn btn-primary">Go Back to Cart</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>


<div class="modal" tabindex="-1" id="paymentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <div class="form-group">
                        <label for="payment_detail">Enter Payment Details</label>
                        <input type="text" class="form-control" id="payment_detail" name="payment_detail" placeholder="Payment details" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitPaymentDetails()">Submit</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
   
    $("input[name='payment_method']").on('change', function() {
        var selectedPaymentMethod = $(this).val();
        if (selectedPaymentMethod == 'credit_card' || selectedPaymentMethod == 'gcash' || selectedPaymentMethod == 'paymaya') {
            $('#paymentModal').modal('show');
        }
    });
});

function submitPaymentDetails() {
    var paymentDetails = $('#payment_detail').val();
    if (paymentDetails) {
        alert("Payment details submitted: " + paymentDetails);
        $('#paymentModal').modal('hide');
    } else {
        alert("Please enter payment details.");
    }
}
</script>

</body>
</html>
