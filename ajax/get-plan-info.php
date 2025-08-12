<?php

session_start();

require_once "../models/connection.php";

if(isset($_POST["planId"])) {
    
    $planId = $_POST["planId"];
    
    try {
        // Get installment plan details
        $planStmt = Connection::connect()->prepare("SELECT ip.*, c.name as customer_name FROM installment_plans ip LEFT JOIN customers c ON ip.customer_id = c.id WHERE ip.id = :plan_id");
        $planStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
        $planStmt->execute();
        $plan = $planStmt->fetch();
        
        if($plan) {
            // Count paid payments
            $paidStmt = Connection::connect()->prepare("SELECT COUNT(*) as paid_count FROM installment_payments WHERE installment_plan_id = :plan_id AND status = 'paid'");
            $paidStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
            $paidStmt->execute();
            $paidResult = $paidStmt->fetch();
            $paidCount = $paidResult ? $paidResult["paid_count"] : 0;
            
            $response = array(
                "success" => true,
                "plan" => $plan,
                "paid_count" => $paidCount
            );
            
        } else {
            $response = array(
                "success" => false,
                "message" => "Installment plan not found"
            );
        }
        
    } catch(Exception $e) {
        $response = array(
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        );
    }
    
} else {
    $response = array(
        "success" => false,
        "message" => "Invalid request"
    );
}

header('Content-Type: application/json');
echo json_encode($response);

?>
