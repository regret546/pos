<!DOCTYPE html>
<html>
<head>
    <title>Debug Installment Plans</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .box { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; }
    </style>
</head>
<body>
    <h1>Installment Plans Debug</h1>
    
    <?php
    require_once 'models/connection.php';
    
    try {
        $pdo = Connection::connect();
        
        // Get the most recent sale
        $stmt = $pdo->prepare('SELECT * FROM sales ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        $sale = $stmt->fetch();
        
        if($sale) {
            echo "<div class='box'>";
            echo "<h2>Most Recent Sale</h2>";
            echo "<strong>Sale ID:</strong> {$sale['id']}<br>";
            echo "<strong>Sale Code:</strong> {$sale['code']}<br>";
            echo "<strong>Payment Method:</strong> {$sale['paymentMethod']}<br>";
            echo "<strong>Total Price:</strong> {$sale['totalPrice']}<br>";
            echo "<strong>Date:</strong> {$sale['saledate']}<br>";
            echo "</div>";
            
            // Check if this is an installment sale
            if(strpos($sale['paymentMethod'], 'installment') === 0) {
                echo "<div class='box success'>";
                echo "<h3>✅ This is an installment sale</h3>";
                echo "</div>";
                
                // Check for installment plan
                $planStmt = $pdo->prepare('SELECT * FROM installment_plans WHERE sale_id = :sale_id');
                $planStmt->bindParam(':sale_id', $sale['id'], PDO::PARAM_INT);
                $planStmt->execute();
                $plan = $planStmt->fetch();
                
                if($plan) {
                    echo "<div class='box success'>";
                    echo "<h3>✅ Installment plan found</h3>";
                    echo "<strong>Plan ID:</strong> {$plan['id']}<br>";
                    echo "<strong>Bill Number:</strong> {$plan['bill_number']}<br>";
                    echo "<strong>Number of Payments:</strong> {$plan['number_of_payments']}<br>";
                    echo "<strong>Payment Amount:</strong> {$plan['payment_amount']}<br>";
                    echo "<strong>Payment Frequency:</strong> {$plan['payment_frequency']}<br>";
                    echo "<strong>Total Amount:</strong> {$plan['total_amount']}<br>";
                    echo "<strong>Interest Rate:</strong> {$plan['interest_rate']}%<br>";
                    echo "</div>";
                    
                    // Check for payment records
                    $paymentStmt = $pdo->prepare('SELECT COUNT(*) as payment_count FROM installment_payments WHERE installment_plan_id = :plan_id');
                    $paymentStmt->bindParam(':plan_id', $plan['id'], PDO::PARAM_INT);
                    $paymentStmt->execute();
                    $paymentResult = $paymentStmt->fetch();
                    
                    if($paymentResult['payment_count'] == 0) {
                        echo "<div class='box error'>";
                        echo "<h3>❌ NO PAYMENT RECORDS FOUND!</h3>";
                        echo "<p>This explains the 'No Payment Records' error.</p>";
                        echo "<p>Payment count: {$paymentResult['payment_count']}</p>";
                        echo "</div>";
                        
                        // Offer to fix it
                        if(isset($_GET['fix']) && $_GET['fix'] == 'true') {
                            echo "<div class='box info'>";
                            echo "<h3>🔧 Attempting to fix...</h3>";
                            
                            $paymentFrequency = $plan['payment_frequency'];
                            $actualNumberOfPayments = intval($plan['number_of_payments']);
                            $totalAmount = floatval($plan['total_amount']);
                            $paymentAmount = round($totalAmount / $actualNumberOfPayments, 2);
                            $startDate = $plan['start_date'];
                            
                            // Calculate original months
                            $originalMonths = $paymentFrequency === 'both' ? $actualNumberOfPayments / 2 : $actualNumberOfPayments;
                            
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
                                    
                                    $paymentCreateStmt = $pdo->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (?, ?, ?, ?, 'pending')");
                                    $paymentCreateStmt->execute([$plan['id'], $paymentCount, $paymentAmount, $dueDate]);
                                    echo "Created payment {$paymentCount} (15th): {$dueDate} - Amount: {$paymentAmount}<br>";
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
                                    
                                    $paymentCreateStmt = $pdo->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (?, ?, ?, ?, 'pending')");
                                    $paymentCreateStmt->execute([$plan['id'], $paymentCount, $paymentAmount, $dueDate]);
                                    echo "Created payment {$paymentCount} (30th): {$dueDate} - Amount: {$paymentAmount}<br>";
                                }
                            }
                            
                            echo "<p class='success'>✅ Created {$paymentCount} payment records!</p>";
                            echo "</div>";
                            
                            echo "<script>setTimeout(function(){ window.location.href = 'debug-installment.php'; }, 2000);</script>";
                        } else {
                            echo "<div class='box'>";
                            echo "<a href='debug-installment.php?fix=true' style='padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;'>Fix This Installment Plan</a>";
                            echo "</div>";
                        }
                        
                    } else {
                        echo "<div class='box success'>";
                        echo "<h3>✅ Payment records exist</h3>";
                        echo "<p>Payment count: {$paymentResult['payment_count']}</p>";
                        echo "</div>";
                        
                        // Show the payment records
                        $paymentListStmt = $pdo->prepare('SELECT * FROM installment_payments WHERE installment_plan_id = :plan_id ORDER BY payment_number');
                        $paymentListStmt->bindParam(':plan_id', $plan['id'], PDO::PARAM_INT);
                        $paymentListStmt->execute();
                        $payments = $paymentListStmt->fetchAll();
                        
                        echo "<div class='box'>";
                        echo "<h3>Payment Records:</h3>";
                        foreach($payments as $payment) {
                            echo "Payment #{$payment['payment_number']}: ₱{$payment['amount']} due {$payment['due_date']} ({$payment['status']})<br>";
                        }
                        echo "</div>";
                    }
                    
                } else {
                    echo "<div class='box error'>";
                    echo "<h3>❌ No installment plan found for sale ID: {$sale['id']}</h3>";
                    echo "</div>";
                }
                
            } else {
                echo "<div class='box error'>";
                echo "<h3>❌ This is not an installment sale</h3>";
                echo "</div>";
            }
            
        } else {
            echo "<div class='box error'>";
            echo "<h3>No sales found</h3>";
            echo "</div>";
        }
        
    } catch(Exception $e) {
        echo "<div class='box error'>";
        echo "<h3>Error: " . $e->getMessage() . "</h3>";
        echo "</div>";
    }
    ?>
    
    <div class="box">
        <h3>Instructions:</h3>
        <ol>
            <li>Create a new installment sale in your POS system</li>
            <li>Refresh this page to see the most recent sale</li>
            <li>If payment records are missing, click "Fix This Installment Plan"</li>
            <li>Check the installment plans page to verify it's working</li>
        </ol>
    </div>
    
    <div class="box">
        <a href="installments" target="_blank" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">Go to Installments Page</a>
        <a href="debug-installment.php" style="padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">Refresh Debug</a>
    </div>
    
    <div class="box">
        <h3>Recent Error Logs (Last 20 lines)</h3>
        <div style="background: #000; color: #0f0; padding: 10px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;">
            <?php
            // Try to read recent error logs
            $logFiles = [
                'php_errors.log',
                'error.log',
                '../logs/error.log',
                '../../logs/error.log',
                '/tmp/php_errors.log'
            ];
            
            $logFound = false;
            foreach($logFiles as $logFile) {
                if(file_exists($logFile)) {
                    $lines = file($logFile);
                    $recentLines = array_slice($lines, -20);
                    foreach($recentLines as $line) {
                        if(strpos($line, 'installment') !== false || strpos($line, 'FALLBACK') !== false || strpos($line, 'AUTO-FIX') !== false) {
                            echo htmlspecialchars($line) . "<br>";
                        }
                    }
                    $logFound = true;
                    break;
                }
            }
            
            if(!$logFound) {
                echo "Error log not found in common locations.<br>";
                echo "Check your web server's error log for installment-related messages.<br>";
            }
            ?>
        </div>
    </div>
</body>
</html>
