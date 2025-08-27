<?php

session_start();

// Try both possible paths for connection
if (file_exists("../models/connection.php")) {
    require_once "../models/connection.php";
} else {
    require_once "models/connection.php";
}

header('Content-Type: application/json');

/*=============================================
AUTO-FIX MISSING PAYMENT RECORDS FOR INSTALLMENT PLAN
=============================================*/
function autoFixMissingPaymentRecords($planId) {
    try {
        error_log("Auto-fixing missing payment records for plan ID: " . $planId);
        
        // Start transaction for better performance and data integrity
        $pdo = Connection::connect();
        $pdo->beginTransaction();
        
        // Get the installment plan details
        $planStmt = Connection::connect()->prepare("SELECT * FROM installment_plans WHERE id = :plan_id");
        $planStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
        $planStmt->execute();
        $plan = $planStmt->fetch();
        
        if(!$plan) {
            error_log("Plan not found for ID: " . $planId);
            return false;
        }
        
        $paymentFrequency = $plan['payment_frequency'];
        $actualNumberOfPayments = intval($plan['number_of_payments']);
        
        // Calculate payment amount based on installment amount only (excluding downpayment)
        $totalAmount = floatval($plan['total_amount']);
        $downpaymentAmount = isset($plan['downpayment_amount']) ? floatval($plan['downpayment_amount']) : 0;
        $installmentAmount = $totalAmount - $downpaymentAmount;
        $paymentAmount = round($installmentAmount / $actualNumberOfPayments, 2);
        $startDate = $plan['start_date'];
        
        // Calculate original months
        $originalMonths = $paymentFrequency === 'both' ? $actualNumberOfPayments / 2 : $actualNumberOfPayments;
        
        error_log("Plan details - Frequency: $paymentFrequency, Payments: $actualNumberOfPayments, Amount: $paymentAmount, Months: $originalMonths");
        
        $paymentCount = 0;
        for($i = 1; $i <= $originalMonths; $i++) {
            if($paymentFrequency === "15th" || $paymentFrequency === "both") {
                $paymentCount++;
                $currentMonth = date('m', strtotime($startDate));
                $currentYear = date('Y', strtotime($startDate));
                $targetMonth = $currentMonth + $i;
                $targetYear = $currentYear;
                if($targetMonth > 12) {
                    $targetYear += floor(($targetMonth - 1) / 12);
                    $targetMonth = (($targetMonth - 1) % 12) + 1;
                }
                $dueDate = date('Y-m-d', mktime(0, 0, 0, $targetMonth, 15, $targetYear));
                
                $paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:plan_id, :payment_number, :amount, :due_date, 'pending')");
                $paymentStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
                $paymentStmt->bindParam(":payment_number", $paymentCount, PDO::PARAM_INT);
                $paymentStmt->bindParam(":amount", $paymentAmount, PDO::PARAM_STR);
                $paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
                
                if($paymentStmt->execute()) {
                    error_log("AUTO-FIX: Created payment {$paymentCount} (15th): {$dueDate} - Amount: {$paymentAmount}");
                } else {
                    error_log("AUTO-FIX: Failed to create payment {$paymentCount} (15th)");
                }
            }
            
            if($paymentFrequency === "30th" || $paymentFrequency === "both") {
                $paymentCount++;
                $currentMonth = date('m', strtotime($startDate));
                $currentYear = date('Y', strtotime($startDate));
                $targetMonth = $currentMonth + $i;
                $targetYear = $currentYear;
                if($targetMonth > 12) {
                    $targetYear += floor(($targetMonth - 1) / 12);
                    $targetMonth = (($targetMonth - 1) % 12) + 1;
                }
                $lastDayOfMonth = date('t', mktime(0, 0, 0, $targetMonth, 1, $targetYear));
                $dueDate = date('Y-m-d', mktime(0, 0, 0, $targetMonth, min(30, $lastDayOfMonth), $targetYear));
                
                $paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:plan_id, :payment_number, :amount, :due_date, 'pending')");
                $paymentStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
                $paymentStmt->bindParam(":payment_number", $paymentCount, PDO::PARAM_INT);
                $paymentStmt->bindParam(":amount", $paymentAmount, PDO::PARAM_STR);
                $paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
                
                if($paymentStmt->execute()) {
                    error_log("AUTO-FIX: Created payment {$paymentCount} (30th): {$dueDate} - Amount: {$paymentAmount}");
                } else {
                    error_log("AUTO-FIX: Failed to create payment {$paymentCount} (30th)");
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        error_log("AUTO-FIX: Completed creating {$paymentCount} payment records for plan {$planId}");
        return true;
        
    } catch(Exception $e) {
        // Rollback transaction on error
        if (isset($pdo)) {
            $pdo->rollback();
        }
        error_log("AUTO-FIX: Error creating missing payment records: " . $e->getMessage());
        return false;
    }
}

try {
    // Get all installment plans
    $stmt = Connection::connect()->prepare("SELECT id FROM installment_plans ORDER BY id");
    $stmt->execute();
    $plans = $stmt->fetchAll();
    
    $fixedCount = 0;
    $totalPlans = count($plans);
    
    foreach($plans as $plan) {
        $planId = $plan['id'];
        
        // Check if payment records exist
        $paymentStmt = Connection::connect()->prepare("SELECT COUNT(*) as payment_count FROM installment_payments WHERE installment_plan_id = :plan_id");
        $paymentStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
        $paymentStmt->execute();
        $paymentResult = $paymentStmt->fetch();
        
        if($paymentResult['payment_count'] == 0) {
            if(autoFixMissingPaymentRecords($planId)) {
                $fixedCount++;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Fixed {$fixedCount} out of {$totalPlans} installment plans.",
        'total_plans' => $totalPlans,
        'fixed_plans' => $fixedCount
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

?>
