<?php

session_start();

// Try both possible paths for connection
if (file_exists("../models/connection.php")) {
    require_once "../models/connection.php";
} else {
    require_once "models/connection.php";
}

header('Content-Type: application/json');

if(isset($_POST["planId"])) {
    
    $planId = $_POST["planId"];
    
    try {
        // Get the next pending payment
        $stmt = Connection::connect()->prepare("SELECT * FROM installment_payments WHERE installment_plan_id = :plan_id AND status = 'pending' ORDER BY payment_number ASC LIMIT 1");
        $stmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
        $stmt->execute();
        $nextPayment = $stmt->fetch();
        
        if($nextPayment) {
            echo json_encode([
                'success' => true,
                'payment' => [
                    'id' => $nextPayment['id'],
                    'payment_number' => $nextPayment['payment_number'],
                    'amount' => $nextPayment['amount'],
                    'due_date' => date('M j, Y', strtotime($nextPayment['due_date']))
                ]
            ]);
        } else {
            // Check if all payments are completed
            $checkStmt = Connection::connect()->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid FROM installment_payments WHERE installment_plan_id = :plan_id");
            $checkStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
            $checkStmt->execute();
            $result = $checkStmt->fetch();
            
            if($result['total'] == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No payment records found for this installment plan. Please contact administrator to fix this issue.'
                ]);
            } elseif($result['total'] == $result['paid']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'All payments have been completed for this installment plan!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No pending payments found for this installment plan.'
                ]);
            }
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
} 