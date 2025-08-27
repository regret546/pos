<?php

session_start();

require_once "../models/connection.php";

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $paymentId = $_POST["paymentId"];
        $planId = $_POST["planId"];
        $paymentDate = $_POST["paymentDate"];
        $paymentType = $_POST["paymentType"];
        
        // Get the current payment details
        $stmt = Connection::connect()->prepare("SELECT * FROM installment_payments WHERE id = :id");
        $stmt->bindParam(":id", $paymentId, PDO::PARAM_INT);
        $stmt->execute();
        $currentPayment = $stmt->fetch();
        
        if(!$currentPayment) {
            throw new Exception("Payment not found");
        }
        
        $dueAmount = floatval($currentPayment['amount']);
        $paymentAmount = 0;
        
        if($paymentType === 'exact') {
            $paymentAmount = $dueAmount;
        } else if($paymentType === 'custom') {
            $paymentAmount = floatval($_POST["customAmount"]);
            
            if($paymentAmount <= 0) {
                throw new Exception("Payment amount must be greater than 0");
            }
        } else {
            throw new Exception("Invalid payment type");
        }
        
        // Check if all payments are already completed
        $checkStmt = Connection::connect()->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid FROM installment_payments WHERE installment_plan_id = :plan_id");
        $checkStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
        $checkStmt->execute();
        $planStatus = $checkStmt->fetch();
        
        if($planStatus['total'] == $planStatus['paid']) {
            throw new Exception("All installment payments have already been completed!");
        }
        
        // Start transaction
        $pdo = Connection::connect();
        $pdo->beginTransaction();
        
        $overpaymentAmount = 0;
        $message = "";
        
        if($paymentAmount >= $dueAmount) {
            // Payment covers the full amount due (exact or overpayment)
            
            // Check if actual_amount column exists
            $columnCheckStmt = $pdo->prepare("SHOW COLUMNS FROM installment_payments LIKE 'actual_amount'");
            $columnCheckStmt->execute();
            $columnExists = $columnCheckStmt->fetch();
            
            // Mark current payment as paid
            if($columnExists) {
                $updateStmt = $pdo->prepare("UPDATE installment_payments SET status = 'paid', payment_date = :payment_date, actual_amount = :actual_amount WHERE id = :id");
                $updateStmt->bindParam(":actual_amount", $paymentAmount, PDO::PARAM_STR);
            } else {
                $updateStmt = $pdo->prepare("UPDATE installment_payments SET status = 'paid', payment_date = :payment_date WHERE id = :id");
            }
            $updateStmt->bindParam(":payment_date", $paymentDate, PDO::PARAM_STR);
            $updateStmt->bindParam(":id", $paymentId, PDO::PARAM_INT);
            $updateStmt->execute();
            
            $overpaymentAmount = $paymentAmount - $dueAmount;
            
            if($overpaymentAmount > 0) {
                // Handle overpayment - apply to next due payments
                $appliedOverpayment = $overpaymentAmount;
                
                // Get next pending payments in order
                $nextStmt = $pdo->prepare("SELECT * FROM installment_payments WHERE installment_plan_id = :plan_id AND status = 'pending' ORDER BY payment_number ASC");
                $nextStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
                $nextStmt->execute();
                $nextPayments = $nextStmt->fetchAll();
                
                $paymentsApplied = 0;
                foreach($nextPayments as $nextPayment) {
                    if($appliedOverpayment <= 0) break;
                    
                    $nextDueAmount = floatval($nextPayment['amount']);
                    
                    if($appliedOverpayment >= $nextDueAmount) {
                        // Overpayment can fully cover this payment
                        if($columnExists) {
                            $applyStmt = $pdo->prepare("UPDATE installment_payments SET status = 'paid', payment_date = :payment_date, actual_amount = :actual_amount WHERE id = :id");
                            $applyStmt->bindParam(":actual_amount", $nextDueAmount, PDO::PARAM_STR);
                        } else {
                            $applyStmt = $pdo->prepare("UPDATE installment_payments SET status = 'paid', payment_date = :payment_date WHERE id = :id");
                        }
                        $applyStmt->bindParam(":payment_date", $paymentDate, PDO::PARAM_STR);
                        $applyStmt->bindParam(":id", $nextPayment['id'], PDO::PARAM_INT);
                        $applyStmt->execute();
                        
                        $appliedOverpayment -= $nextDueAmount;
                        $paymentsApplied++;
                    } else {
                        // Partial payment - reduce the amount of next payment
                        $newAmount = $nextDueAmount - $appliedOverpayment;
                        $partialStmt = $pdo->prepare("UPDATE installment_payments SET amount = :new_amount WHERE id = :id");
                        $partialStmt->bindParam(":new_amount", $newAmount, PDO::PARAM_STR);
                        $partialStmt->bindParam(":id", $nextPayment['id'], PDO::PARAM_INT);
                        $partialStmt->execute();
                        
                        $appliedOverpayment = 0;
                        break;
                    }
                }
                
                if($paymentsApplied > 0) {
                    $message = "Payment processed successfully! Overpayment of ₱" . number_format($overpaymentAmount, 2) . " was applied to {$paymentsApplied} future payment(s).";
                } else {
                    $message = "Payment processed successfully! Overpayment of ₱" . number_format($overpaymentAmount, 2) . " was applied to reduce the next payment amount.";
                }
            } else {
                $message = "Payment of ₱" . number_format($paymentAmount, 2) . " processed successfully!";
            }
            
        } else {
            // Partial payment - reduce the current payment amount
            $newAmount = $dueAmount - $paymentAmount;
            $partialStmt = $pdo->prepare("UPDATE installment_payments SET amount = :new_amount WHERE id = :id");
            $partialStmt->bindParam(":new_amount", $newAmount, PDO::PARAM_STR);
            $partialStmt->bindParam(":id", $paymentId, PDO::PARAM_INT);
            $partialStmt->execute();
            
            $message = "Partial payment of ₱" . number_format($paymentAmount, 2) . " processed. Remaining balance: ₱" . number_format($newAmount, 2);
        }
        
        // Check if all payments are now completed
        $finalCheckStmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid FROM installment_payments WHERE installment_plan_id = :plan_id");
        $finalCheckStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
        $finalCheckStmt->execute();
        $finalResult = $finalCheckStmt->fetch();
        
        if($finalResult["total"] == $finalResult["paid"]){
            // All payments completed, update plan status
            $updatePlanStmt = $pdo->prepare("UPDATE installment_plans SET status = 'completed' WHERE id = :id");
            $updatePlanStmt->bindParam(":id", $planId, PDO::PARAM_INT);
            $updatePlanStmt->execute();
            
            $message .= " All installment payments have been completed!";
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'overpayment_applied' => $overpaymentAmount
        ]);
        
    } catch(Exception $e) {
        // Rollback on error
        if(isset($pdo)) {
            $pdo->rollback();
        }
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

?>
