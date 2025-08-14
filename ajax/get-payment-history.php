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
        // Get all payments for this installment plan
        $stmt = Connection::connect()->prepare("SELECT * FROM installment_payments WHERE installment_plan_id = :plan_id ORDER BY payment_number ASC");
        $stmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
        $stmt->execute();
        $payments = $stmt->fetchAll();
        
        if($payments && count($payments) > 0) {
            // Check for overdue payments
            $today = date('Y-m-d');
            foreach($payments as &$payment) {
                if($payment['status'] === 'pending' && $payment['due_date'] < $today) {
                    $payment['status'] = 'overdue';
                }
            }
            
            echo json_encode([
                'success' => true,
                'payments' => $payments
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No payment records found for this installment plan.'
            ]);
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

?>