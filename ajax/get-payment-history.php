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
            // Get all payments for this plan
            $paymentsStmt = Connection::connect()->prepare("SELECT * FROM installment_payments WHERE installment_plan_id = :plan_id ORDER BY payment_number ASC");
            $paymentsStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
            $paymentsStmt->execute();
            $payments = $paymentsStmt->fetchAll();
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<h4>Plan Details</h4>';
            echo '<table class="table table-bordered">';
            echo '<tr><th>Customer:</th><td>' . $plan["customer_name"] . '</td></tr>';
            echo '<tr><th>Total Amount:</th><td>₱' . number_format($plan["total_amount"], 2) . '</td></tr>';
            echo '<tr><th>Base Amount:</th><td>₱' . number_format($plan["base_amount"], 2) . '</td></tr>';
            echo '<tr><th>Interest Rate:</th><td>' . $plan["interest_rate"] . '%</td></tr>';
            echo '<tr><th>Monthly Payment:</th><td>₱' . number_format($plan["payment_amount"], 2) . '</td></tr>';
            echo '<tr><th>Start Date:</th><td>' . date('M j, Y', strtotime($plan["start_date"])) . '</td></tr>';
            echo '<tr><th>Status:</th><td><span class="label label-success">' . $plan["status"] . '</span></td></tr>';
            echo '</table>';
            echo '</div>';
            
            echo '<div class="col-md-6">';
            echo '<h4>Payment Schedule</h4>';
            
            if($payments) {
                echo '<table class="table table-bordered table-striped">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>#</th>';
                echo '<th>Due Date</th>';
                echo '<th>Amount</th>';
                echo '<th>Status</th>';
                echo '<th>Paid Date</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                foreach($payments as $payment) {
                    $statusClass = '';
                    $statusText = '';
                    
                    switch($payment["status"]) {
                        case 'paid':
                            $statusClass = 'success';
                            $statusText = 'Paid';
                            break;
                        case 'overdue':
                            $statusClass = 'danger';
                            $statusText = 'Overdue';
                            break;
                        default:
                            $statusClass = 'warning';
                            $statusText = 'Pending';
                    }
                    
                    echo '<tr>';
                    echo '<td>' . $payment["payment_number"] . '</td>';
                    echo '<td>' . date('M j, Y', strtotime($payment["due_date"])) . '</td>';
                    echo '<td>₱' . number_format($payment["amount"], 2) . '</td>';
                    echo '<td><span class="label label-' . $statusClass . '">' . $statusText . '</span></td>';
                    echo '<td>' . ($payment["payment_date"] ? date('M j, Y', strtotime($payment["payment_date"])) : '-') . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                
                // Calculate summary
                $totalPaid = 0;
                $paidCount = 0;
                foreach($payments as $payment) {
                    if($payment["status"] == "paid") {
                        $totalPaid += $payment["amount"];
                        $paidCount++;
                    }
                }
                
                $remaining = $plan["total_amount"] - $totalPaid;
                $remainingPayments = $plan["number_of_payments"] - $paidCount;
                
                echo '<div class="alert alert-info">';
                echo '<strong>Summary:</strong><br>';
                echo 'Payments Made: ' . $paidCount . '/' . $plan["number_of_payments"] . '<br>';
                echo 'Amount Paid: ₱' . number_format($totalPaid, 2) . '<br>';
                echo 'Remaining: ₱' . number_format($remaining, 2) . ' (' . $remainingPayments . ' payments)';
                echo '</div>';
                
            } else {
                echo '<p class="text-warning">No payment schedule found.</p>';
            }
            
            echo '</div>';
            echo '</div>';
            
        } else {
            echo '<p class="text-danger">Installment plan not found.</p>';
        }
        
    } catch(Exception $e) {
        echo '<p class="text-danger">Error: ' . $e->getMessage() . '</p>';
    }
    
} else {
    echo '<p class="text-danger">Invalid request.</p>';
} 