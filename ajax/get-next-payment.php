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
        
        // Double-check if payment records already exist (avoid duplicates)
        $existingStmt = $pdo->prepare("SELECT COUNT(*) as count FROM installment_payments WHERE installment_plan_id = :plan_id");
        $existingStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
        $existingStmt->execute();
        $existingCount = $existingStmt->fetch()['count'];
        
        if($existingCount > 0) {
            error_log("Payment records already exist for plan $planId (count: $existingCount), skipping auto-fix");
            $pdo->commit();
            return true;
        }
        
        $paymentFrequency = $plan['payment_frequency'];
        $actualNumberOfPayments = intval($plan['number_of_payments']);
        $totalAmount = floatval($plan['total_amount']);
        $paymentAmount = round($totalAmount / $actualNumberOfPayments, 2);
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
                // Try to auto-fix missing payment records
                error_log("No payment records found for plan $planId, attempting auto-fix...");
                
                if(autoFixMissingPaymentRecords($planId)) {
                    // Add a small delay to ensure database changes are committed
                    usleep(500000); // 0.5 second delay
                    
                    // Retry getting the next payment after auto-fix with fresh connection
                    $stmt = Connection::connect()->prepare("SELECT * FROM installment_payments WHERE installment_plan_id = :plan_id AND status = 'pending' ORDER BY payment_number ASC LIMIT 1");
                    $stmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
                    $stmt->execute();
                    $nextPayment = $stmt->fetch();
                    
                    if($nextPayment) {
                        error_log("Auto-fix successful for plan $planId, found next payment ID: " . $nextPayment['id']);
                        echo json_encode([
                            'success' => true,
                            'payment' => [
                                'id' => $nextPayment['id'],
                                'payment_number' => $nextPayment['payment_number'],
                                'amount' => $nextPayment['amount'],
                                'due_date' => date('M j, Y', strtotime($nextPayment['due_date']))
                            ],
                            'auto_fixed' => true
                        ]);
                    } else {
                        error_log("Auto-fix completed but no pending payments for plan $planId");
                        echo json_encode([
                            'success' => false,
                            'message' => 'Payment records were created but no pending payments found. All payments may be completed.'
                        ]);
                    }
                } else {
                    error_log("Auto-fix failed for plan $planId");
                    echo json_encode([
                        'success' => false,
                        'message' => 'No payment records found for this installment plan. Auto-fix failed - please contact administrator.'
                    ]);
                }
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