<?php 
require_once '../classes/connection.php';
session_start();  


if (!isset($_SESSION['user_id'])) {
   
    echo json_encode(['cartCount' => 0]); 
    exit();
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['item_id']) && is_numeric($_POST['item_id']) && $_POST['item_id'] > 0) {
        $itemId = intval($_POST['item_id']);
        $quantity = intval($_POST['quantity']);
        
       
        if ($quantity <= 0) {
            $quantity = 1;
        }

      
        if (!isset($_SESSION['cart'][$_SESSION['user_id']])) {
            $_SESSION['cart'][$_SESSION['user_id']] = [];
        }

        
        if (isset($_SESSION['cart'][$_SESSION['user_id']][$itemId])) {
          
            $_SESSION['cart'][$_SESSION['user_id']][$itemId]['quantity'] += $quantity;
        } else {
           
            $stmt = $conn->prepare("SELECT name, price FROM menu_items WHERE item_id = :item_id");
            $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item) {
                
                $_SESSION['cart'][$_SESSION['user_id']][$itemId] = [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $quantity
                ];
            } else {
                echo json_encode(['error' => 'Item not found.']);
                exit();
            }
        }

        
        $cartCount = array_sum(array_column($_SESSION['cart'][$_SESSION['user_id']], 'quantity'));

        
        echo json_encode(['cartCount' => $cartCount]);
    } else {
       
        echo json_encode(['error' => 'Invalid item ID.']);
        exit();
    }
} else {
    
    echo json_encode(['error' => 'Invalid request method.']);
    exit();
}
?>
