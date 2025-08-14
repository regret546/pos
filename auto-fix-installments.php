<?php
/*
 * Auto-fix script for installment plans without payment records
 * This script can be run manually or scheduled to run automatically
 */

require_once 'models/connection.php';

try {
    $pdo = Connection::connect();
    
    echo "Starting auto-fix for installment plans without payment records...\n";
    
    // Find installment plans without payment records
    $stmt = $pdo->prepare('
        SELECT ip.id, ip.payment_frequency, ip.number_of_payments, ip.total_amount, ip.start_date, ip.bill_number,
               COUNT(ipy.id) as payment_count 
        FROM installment_plans ip 
        LEFT JOIN installment_payments ipy ON ip.id = ipy.installment_plan_id 
        GROUP BY ip.id 
        HAVING payment_count = 0
        ORDER BY ip.id DESC
    ');
    $stmt->execute();
    $brokenPlans = $stmt->fetchAll();
    
    $fixedCount = 0;
    $totalCount = count($brokenPlans);
    
    echo "Found $totalCount installment plans without payment records.\n";
    
    if($totalCount > 0) {
        foreach($brokenPlans as $plan) {
            echo "Fixing Plan ID: {$plan['id']} (Bill: {$plan['bill_number']})... ";
            
            $paymentFrequency = $plan['payment_frequency'];
            $actualNumberOfPayments = intval($plan['number_of_payments']);
            $totalAmount = floatval($plan['total_amount']);
            $paymentAmount = round($totalAmount / $actualNumberOfPayments, 2);
            $startDate = $plan['start_date'];
            
            // Calculate original months
            $originalMonths = $paymentFrequency === 'both' ? $actualNumberOfPayments / 2 : $actualNumberOfPayments;
            
            $paymentCount = 0;
            $success = true;
            
            try {
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
                        
                        $paymentStmt = $pdo->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (?, ?, ?, ?, 'pending')");
                        if(!$paymentStmt->execute([$plan['id'], $paymentCount, $paymentAmount, $dueDate])) {
                            $success = false;
                            break;
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
                        
                        $paymentStmt = $pdo->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (?, ?, ?, ?, 'pending')");
                        if(!$paymentStmt->execute([$plan['id'], $paymentCount, $paymentAmount, $dueDate])) {
                            $success = false;
                            break;
                        }
                    }
                }
                
                if($success) {
                    echo "✅ Fixed! Created $paymentCount payment records\n";
                    $fixedCount++;
                } else {
                    echo "❌ Failed to create all payment records\n";
                }
                
            } catch(Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n=== SUMMARY ===\n";
        echo "Total broken plans found: $totalCount\n";
        echo "Successfully fixed: $fixedCount\n";
        echo "Failed to fix: " . ($totalCount - $fixedCount) . "\n";
        
    } else {
        echo "✅ No broken installment plans found! All plans have payment records.\n";
    }
    
} catch(Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
}

echo "\nAuto-fix completed.\n";
?>
