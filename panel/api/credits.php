<?php
require_once '../../includes/auth.php';
require_once '../../includes/security.php';

header('Content-Type: application/json');

if (!check_auth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$security = new Security();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $package_id = $data['package_id'] ?? null;
    
    if (!$package_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Package ID required']);
        exit;
    }
    
    // Get package details
    $packages = [
        1 => ['credits' => 100, 'price' => 50],
        2 => ['credits' => 250, 'price' => 100],
        3 => ['credits' => 500, 'price' => 175],
        4 => ['credits' => 1000, 'price' => 300]
    ];
    
    if (!isset($packages[$package_id])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid package']);
        exit;
    }
    
    $package = $packages[$package_id];
    
    // Start transaction
    $db->begin_transaction();
    
    try {
        // Create payment record
        $stmt = $db->prepare("
            INSERT INTO payments (user_id, amount, credits, payment_method, transaction_id, status) 
            VALUES (?, ?, ?, 'credit_card', ?, 'pending')
        ");
        
        $user_id = $_SESSION['user_id'];
        $transaction_id = bin2hex(random_bytes(16));
        
        $stmt->bind_param("idis", $user_id, $package['price'], $package['credits'], $transaction_id);
        $stmt->execute();
        
        $payment_id = $db->insert_id;
        
        // Here you would normally integrate with a payment gateway
        // For demo purposes, we'll simulate a successful payment
        $payment_successful = true;
        
        if ($payment_successful) {
            // Update payment status
            $db->query("UPDATE payments SET status = 'completed' WHERE id = $payment_id");
            
            // Add credits to user
            $db->query("UPDATE users SET credits = credits + {$package['credits']} WHERE id = $user_id");
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Credits added successfully',
                'transaction_id' => $transaction_id
            ]);
        } else {
            throw new Exception('Payment failed');
        }
    } catch (Exception $e) {
        $db->rollback();
        
        http_response_code(500);
        echo json_encode(['error' => 'Payment processing failed']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'];
    
    // Get user's credit balance
    $credits = get_user_credits($user_id);
    
    // Get recent transactions
    $stmt = $db->prepare("
        SELECT * FROM payments 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'credits' => $credits,
        'transactions' => $transactions
    ]);
}