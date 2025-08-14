<?php
/**
 * Installment Plans Diagnostic Script
 * This script helps diagnose installment payment issues
 */

require_once "models/connection.php";

echo "<h2>Installment Plans Diagnostic Report</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .error { color: red; }
    .success { color: green; }
    .warning { color: orange; }
</style>";

try {
    // Check database connection
    $pdo = Connection::connect();
    echo "<p class='success'>✓ Database connection successful</p>";
    
    // Check if installment tables exist
    echo "<h3>Table Status</h3>";
    $tables = ['installment_plans', 'installment_payments'];
    foreach($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetch();
        
        if($exists) {
            echo "<p class='success'>✓ Table '$table' exists</p>";
            
            // Count records
            $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM `$table`");
            $countStmt->execute();
            $count = $countStmt->fetch()['count'];
            echo "<p>Records in '$table': $count</p>";
        } else {
            echo "<p class='error'>✗ Table '$table' does not exist</p>";
        }
    }
    
    // Check installment plans
    echo "<h3>Installment Plans Analysis</h3>";
    $plansStmt = $pdo->prepare("SELECT id, customer_id, total_amount, number_of_payments, payment_frequency, status FROM installment_plans ORDER BY id");
    $plansStmt->execute();
    $plans = $plansStmt->fetchAll();
    
    if(count($plans) > 0) {
        echo "<table>";
        echo "<tr><th>Plan ID</th><th>Customer ID</th><th>Total Amount</th><th>Number of Payments</th><th>Frequency</th><th>Status</th><th>Payment Records</th><th>Issue</th></tr>";
        
        foreach($plans as $plan) {
            // Check payment records for this plan
            $paymentStmt = $pdo->prepare("SELECT COUNT(*) as count FROM installment_payments WHERE installment_plan_id = ?");
            $paymentStmt->execute([$plan['id']]);
            $paymentCount = $paymentStmt->fetch()['count'];
            
            $issue = '';
            if($paymentCount == 0) {
                $issue = "<span class='error'>Missing payment records</span>";
            } elseif($paymentCount != $plan['number_of_payments']) {
                $issue = "<span class='warning'>Payment count mismatch (expected: {$plan['number_of_payments']})</span>";
            } else {
                $issue = "<span class='success'>OK</span>";
            }
            
            echo "<tr>";
            echo "<td>{$plan['id']}</td>";
            echo "<td>{$plan['customer_id']}</td>";
            echo "<td>₱" . number_format($plan['total_amount'], 2) . "</td>";
            echo "<td>{$plan['number_of_payments']}</td>";
            echo "<td>{$plan['payment_frequency']}</td>";
            echo "<td>{$plan['status']}</td>";
            echo "<td>$paymentCount</td>";
            echo "<td>$issue</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Summary
        $totalPlans = count($plans);
        $plansWithMissingPayments = 0;
        foreach($plans as $plan) {
            $paymentStmt = $pdo->prepare("SELECT COUNT(*) as count FROM installment_payments WHERE installment_plan_id = ?");
            $paymentStmt->execute([$plan['id']]);
            $paymentCount = $paymentStmt->fetch()['count'];
            if($paymentCount == 0) {
                $plansWithMissingPayments++;
            }
        }
        
        echo "<h3>Summary</h3>";
        echo "<p>Total installment plans: $totalPlans</p>";
        echo "<p>Plans with missing payment records: $plansWithMissingPayments</p>";
        
        if($plansWithMissingPayments > 0) {
            echo "<p class='warning'>⚠ $plansWithMissingPayments plan(s) need to be fixed</p>";
            echo "<p><a href='ajax/fix-all-installment-payments.php' target='_blank' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;'>Run Auto-Fix</a></p>";
        } else {
            echo "<p class='success'>✓ All installment plans have proper payment records</p>";
        }
        
    } else {
        echo "<p>No installment plans found in the database.</p>";
    }
    
    // Check recent payment activities
    echo "<h3>Recent Payment Activities</h3>";
    $recentStmt = $pdo->prepare("
        SELECT ip.payment_number, ip.amount, ip.due_date, ip.payment_date, ip.status, 
               pl.id as plan_id, c.name as customer_name 
        FROM installment_payments ip 
        JOIN installment_plans pl ON ip.installment_plan_id = pl.id 
        LEFT JOIN customers c ON pl.customer_id = c.id 
        ORDER BY ip.id DESC 
        LIMIT 10
    ");
    $recentStmt->execute();
    $recentPayments = $recentStmt->fetchAll();
    
    if(count($recentPayments) > 0) {
        echo "<table>";
        echo "<tr><th>Plan ID</th><th>Customer</th><th>Payment #</th><th>Amount</th><th>Due Date</th><th>Payment Date</th><th>Status</th></tr>";
        
        foreach($recentPayments as $payment) {
            echo "<tr>";
            echo "<td>{$payment['plan_id']}</td>";
            echo "<td>{$payment['customer_name']}</td>";
            echo "<td>{$payment['payment_number']}</td>";
            echo "<td>₱" . number_format($payment['amount'], 2) . "</td>";
            echo "<td>{$payment['due_date']}</td>";
            echo "<td>" . ($payment['payment_date'] ?: 'Not paid') . "</td>";
            echo "<td>{$payment['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No payment records found.</p>";
    }
    
} catch(Exception $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><small>Generated on: " . date('Y-m-d H:i:s') . "</small></p>";
?>
