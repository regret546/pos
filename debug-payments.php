<?php

require_once "models/connection.php";

if (!isset($_GET['plan_id'])) {
    die("Please provide plan_id parameter: debug-payments.php?plan_id=19");
}

$planId = $_GET['plan_id'];

echo "<h2>Debug Payment Records for Plan ID: $planId</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .json { background: #f5f5f5; padding: 10px; white-space: pre-wrap; font-family: monospace; }
</style>";

try {
    $pdo = Connection::connect();
    
    // Get installment plan details
    echo "<h3>Installment Plan Details</h3>";
    $planStmt = $pdo->prepare("SELECT * FROM installment_plans WHERE id = ?");
    $planStmt->execute([$planId]);
    $plan = $planStmt->fetch();
    
    if ($plan) {
        echo "<table>";
        foreach ($plan as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Plan not found!</p>";
        exit;
    }
    
    // Get payment records
    echo "<h3>Payment Records</h3>";
    $paymentStmt = $pdo->prepare("SELECT * FROM installment_payments WHERE installment_plan_id = ? ORDER BY payment_number ASC");
    $paymentStmt->execute([$planId]);
    $payments = $paymentStmt->fetchAll();
    
    echo "<p>Found " . count($payments) . " payment records</p>";
    
    if (count($payments) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Payment #</th><th>Amount</th><th>Due Date</th><th>Payment Date</th><th>Status</th></tr>";
        
        foreach ($payments as $payment) {
            echo "<tr>";
            echo "<td>{$payment['id']}</td>";
            echo "<td>{$payment['payment_number']}</td>";
            echo "<td>₱" . number_format($payment['amount'], 2) . "</td>";
            echo "<td>{$payment['due_date']}</td>";
            echo "<td>" . ($payment['payment_date'] ?: 'Not paid') . "</td>";
            echo "<td>{$payment['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Simulate the AJAX response
        echo "<h3>Simulated AJAX Response</h3>";
        
        // Process payments like the AJAX script does
        $today = date('Y-m-d');
        foreach($payments as &$payment) {
            if($payment['status'] === 'pending' && $payment['due_date'] < $today) {
                $payment['status'] = 'overdue';
            }
        }
        
        $response = [
            'success' => true,
            'payments' => $payments
        ];
        
        echo "<div class='json'>" . json_encode($response, JSON_PRETTY_PRINT) . "</div>";
        
    } else {
        echo "<p>No payment records found.</p>";
        
        // Try to create payment records
        echo "<h3>Auto-Fix Attempt</h3>";
        echo "<p><a href='?plan_id=$planId&auto_fix=1' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none;'>Run Auto-Fix</a></p>";
        
        if (isset($_GET['auto_fix'])) {
            // Include the auto-fix function
            include_once "ajax/get-payment-history.php";
            
            echo "<p>Running auto-fix...</p>";
            if (autoFixMissingPaymentRecords($planId)) {
                echo "<p style='color: green;'>Auto-fix completed! <a href='?plan_id=$planId'>Reload page</a></p>";
            } else {
                echo "<p style='color: red;'>Auto-fix failed!</p>";
            }
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

?>

<p><a href="debug-payments.php?plan_id=<?php echo $planId; ?>">Refresh</a></p>
