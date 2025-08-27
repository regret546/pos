<?php

if($_SESSION["profile"] == "Special"){
  echo '<script>window.location = "index.php?route=home";</script>';
  return;
}

?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Installment Plans Management
      <small>Control Panel</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php?route=home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Installment Plans</li>
    </ol>
  </section>
  
  <section class="content">
    
    <!-- Dashboard Boxes Row -->
    <div class="row">
      <?php
      // Get installment statistics for dashboard boxes
      $installmentStats = ControllerSales::ctrInstallmentStats();
      
      // Get overdue payment statistics
      $overdueSql = "SELECT COUNT(*) as overdue_count, SUM(amount) as overdue_total 
                     FROM installment_payments 
                     WHERE status = 'pending' 
                     AND due_date < CURDATE()";
      $overdueStmt = Connection::connect()->prepare($overdueSql);
      $overdueStmt->execute();
      $overdueStats = $overdueStmt->fetch();
      
      // Get due today statistics  
      $dueTodaySql = "SELECT COUNT(*) as due_today_count, SUM(amount) as due_today_total 
                      FROM installment_payments 
                      WHERE status = 'pending' 
                      AND due_date = CURDATE()";
      $dueTodayStmt = Connection::connect()->prepare($dueTodaySql);
      $dueTodayStmt->execute();
      $dueTodayStats = $dueTodayStmt->fetch();
      ?>
      
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3>₱<?php echo number_format($installmentStats["paid_total"] ? $installmentStats["paid_total"] : 0, 2); ?></h3>
            <p>Total Paid</p>
          </div>
          <div class="icon">
            <i class="fa fa-check-circle"></i>
          </div>
          <div class="small-box-footer" style="background-color: rgba(0,0,0,0.1); color: #fff; padding: 8px; text-align: center;">
            <?php echo number_format($installmentStats["paid_count"] ? $installmentStats["paid_count"] : 0); ?> payments completed
          </div>
        </div>
      </div>
      
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
          <div class="inner">
            <h3>₱<?php echo number_format($overdueStats["overdue_total"] ? $overdueStats["overdue_total"] : 0, 2); ?></h3>
            <p>Overdue Payments</p>
          </div>
          <div class="icon">
            <i class="fa fa-exclamation-triangle"></i>
          </div>
          <div class="small-box-footer" style="background-color: rgba(0,0,0,0.1); color: #fff; padding: 8px; text-align: center;">
            <?php echo number_format($overdueStats["overdue_count"] ? $overdueStats["overdue_count"] : 0); ?> payments overdue
          </div>
        </div>
      </div>
      
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-orange">
          <div class="inner">
            <h3>₱<?php echo number_format($dueTodayStats["due_today_total"] ? $dueTodayStats["due_today_total"] : 0, 2); ?></h3>
            <p>Due Today</p>
          </div>
          <div class="icon">
            <i class="fa fa-calendar"></i>
          </div>
          <div class="small-box-footer" style="background-color: rgba(0,0,0,0.1); color: #fff; padding: 8px; text-align: center;">
            <?php echo number_format($dueTodayStats["due_today_count"] ? $dueTodayStats["due_today_count"] : 0); ?> payments due today
          </div>
        </div>
      </div>
      
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3>₱<?php echo number_format($installmentStats["pending_total"] ? $installmentStats["pending_total"] : 0, 2); ?></h3>
            <p>Total Pending</p>
          </div>
          <div class="icon">
            <i class="fa fa-clock-o"></i>
          </div>
          <div class="small-box-footer" style="background-color: rgba(0,0,0,0.1); color: #fff; padding: 8px; text-align: center;">
            <?php echo number_format($installmentStats["pending_count"] ? $installmentStats["pending_count"] : 0); ?> payments pending
          </div>
        </div>
      </div>
    </div>
    
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Active Installment Plans</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-primary btn-sm" id="btnFixAllPayments" title="Fix all installment plans missing payment records">
            <i class="fa fa-wrench"></i> Fix Missing Payment Records
          </button>
        </div>
      </div>
      <div class="box-body">
        
        <table class="table table-bordered table-striped dt-responsive tables" width="100%" id="installmentTable">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Bill Number</th>
              <th>Customer</th>
              <th>Total Amount</th>
              <th>Downpayment</th>
              <th>Payment Amount</th>
              <th>Interest Rate</th>
              <th>Payment Frequency</th>
              <th>Payments Made</th>
              <th>Credits/Advance</th>
              <th>Next Due</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            
            <?php
            
            try {
              // Check if the installment tables exist
              $stmt = Connection::connect()->prepare("SHOW TABLES LIKE 'installment_plans'");
              $stmt->execute();
              $tableExists = $stmt->fetch();
              
              if($tableExists) {
                // Get installment plans where the associated sale still exists
                $stmt = Connection::connect()->prepare("SELECT ip.* FROM installment_plans ip 
                                                      INNER JOIN sales s ON ip.sale_id = s.id 
                                                      ORDER BY ip.id DESC");
                $stmt->execute();
                $installmentPlans = $stmt->fetchAll();
                

                
                if($installmentPlans && count($installmentPlans) > 0) {
                  
                  foreach ($installmentPlans as $key => $plan) {
                    
                    $customerStmt = Connection::connect()->prepare("SELECT name FROM customers WHERE id = :id");
                    $customerStmt->bindParam(":id", $plan["customer_id"], PDO::PARAM_INT);
                    $customerStmt->execute();
                    $customer = $customerStmt->fetch();
                    
                    $customerName = $customer ? $customer["name"] : "Unknown Customer";
                    
                    // Count paid payments and calculate advance payments
                    $paidStmt = Connection::connect()->prepare("SELECT COUNT(*) as paid_count FROM installment_payments WHERE installment_plan_id = :plan_id AND status = 'paid'");
                    $paidStmt->bindParam(":plan_id", $plan["id"], PDO::PARAM_INT);
                    $paidStmt->execute();
                    $paidResult = $paidStmt->fetch();
                    $paidCount = $paidResult ? $paidResult["paid_count"] : 0;
                    
                    // Calculate TRUE advance payments (only overpayments applied to future or payments made early)
                    $currentDate = date('Y-m-d');
                    $originalPaymentAmount = floatval($plan["payment_amount"]);
                    
                    // Method 1: Check for payments with reduced amounts (indicating overpayment was applied)
                    $reducedStmt = Connection::connect()->prepare("
                        SELECT COUNT(*) as reduced_count, SUM(? - amount) as advance_applied
                        FROM installment_payments 
                        WHERE installment_plan_id = ? 
                        AND status = 'pending'
                        AND amount < ?
                    ");
                    $reducedStmt->execute([$originalPaymentAmount, $plan["id"], $originalPaymentAmount]);
                    $reducedResult = $reducedStmt->fetch();
                    
                    // Method 2: Check for payments made ahead of their due date AND beyond natural payment progression
                    $earlyStmt = Connection::connect()->prepare("
                        SELECT COUNT(*) as early_count, SUM(amount) as early_amount
                        FROM installment_payments p1
                        WHERE p1.installment_plan_id = ? 
                        AND p1.status = 'paid'
                        AND p1.due_date > ?
                        AND EXISTS (
                            SELECT 1 FROM installment_payments p2 
                            WHERE p2.installment_plan_id = ? 
                            AND p2.payment_number < p1.payment_number 
                            AND p2.status = 'pending'
                        )
                    ");
                    $earlyStmt->execute([$plan["id"], $currentDate, $plan["id"]]);
                    $earlyResult = $earlyStmt->fetch();
                    
                    // Combine both methods to get total advance
                    $advanceFromReduction = $reducedResult ? floatval($reducedResult["advance_applied"]) : 0;
                    $advanceFromEarly = $earlyResult ? floatval($earlyResult["early_amount"]) : 0;
                    $earlyCount = $earlyResult ? intval($earlyResult["early_count"]) : 0;
                    
                    $totalAdvanceAmount = $advanceFromReduction + $advanceFromEarly;
                    $advanceCount = $earlyCount;
                    
                    // Add count for reduced payments if there are any
                    if($advanceFromReduction > 0) {
                        $advanceCount += ($reducedResult ? intval($reducedResult["reduced_count"]) : 0);
                    }
                    
                    // Get payment frequency
                    $paymentFrequency = isset($plan["payment_frequency"]) ? $plan["payment_frequency"] : "30th";
                    $frequencyDisplay = "";
                    switch($paymentFrequency) {
                        case "15th":
                            $frequencyDisplay = "Every 15th";
                            break;
                        case "30th":
                            $frequencyDisplay = "Every 30th";
                            break;
                        case "both":
                            $frequencyDisplay = "15th & 30th";
                            break;
                        default:
                            $frequencyDisplay = "Every 30th";
                    }

                    // Handle downpayment display
                    $downpaymentAmount = isset($plan["downpayment_amount"]) ? floatval($plan["downpayment_amount"]) : 0;
                    $downpaymentDisplay = $downpaymentAmount > 0 ? "₱".number_format($downpaymentAmount, 2) : "None";

                    // Format advance payment display
                    $advanceDisplay = "None";
                    if($totalAdvanceAmount > 0) {
                        if($advanceFromReduction > 0 && $advanceFromEarly > 0) {
                            $advanceDisplay = '<span class="label label-success">Credits Applied</span><br><small>₱'.number_format($totalAdvanceAmount, 2).'</small>';
                        } elseif($advanceFromReduction > 0) {
                            $advanceDisplay = '<span class="label label-info">Credit Applied</span><br><small>₱'.number_format($advanceFromReduction, 2).'</small>';
                        } elseif($advanceFromEarly > 0) {
                            $advanceDisplay = '<span class="label label-warning">'.$earlyCount.' Early Payment(s)</span><br><small>₱'.number_format($advanceFromEarly, 2).'</small>';
                        }
                    }

                    // Get next due payment information
                    $nextPaymentStmt = Connection::connect()->prepare("
                        SELECT * FROM installment_payments 
                        WHERE installment_plan_id = :plan_id 
                        AND status = 'pending' 
                        ORDER BY payment_number ASC 
                        LIMIT 1
                    ");
                    $nextPaymentStmt->bindParam(":plan_id", $plan["id"], PDO::PARAM_INT);
                    $nextPaymentStmt->execute();
                    $nextPayment = $nextPaymentStmt->fetch();
                    
                    $nextDueDisplay = "All Paid";
                    if($nextPayment) {
                        $dueDate = $nextPayment["due_date"];
                        $dueDateFormatted = date('M j, Y', strtotime($dueDate));
                        $dueDateShort = date('M j', strtotime($dueDate));
                        $amount = number_format($nextPayment["amount"], 2);
                        $today = date('Y-m-d');
                        
                        // Calculate days difference
                        $daysDiff = (strtotime($dueDate) - strtotime($today)) / (60 * 60 * 24);
                        
                        if($daysDiff < 0) {
                            // Overdue - Red
                            $daysOverdue = abs(floor($daysDiff));
                            $nextDueDisplay = '<span class="label label-danger">OVERDUE</span><br>
                                             <small>'.$dueDateShort.' (+'.$daysOverdue.' days)</small><br>
                                             <small>₱'.$amount.'</small>';
                        } elseif($daysDiff <= 3) {
                            // Due soon (within 3 days) - Orange
                            $daysLeft = floor($daysDiff);
                            if($daysLeft == 0) {
                                $nextDueDisplay = '<span class="label label-warning">DUE TODAY</span><br>
                                                 <small>'.$dueDateShort.'</small><br>
                                                 <small>₱'.$amount.'</small>';
                            } else {
                                $nextDueDisplay = '<span class="label label-warning">DUE SOON</span><br>
                                                 <small>'.$dueDateShort.' ('.$daysLeft.' days)</small><br>
                                                 <small>₱'.$amount.'</small>';
                            }
                        } elseif($daysDiff <= 7) {
                            // Due this week - Yellow
                            $daysLeft = floor($daysDiff);
                            $nextDueDisplay = '<span class="label label-info">THIS WEEK</span><br>
                                             <small>'.$dueDateShort.' ('.$daysLeft.' days)</small><br>
                                             <small>₱'.$amount.'</small>';
                        } else {
                            // Future payment - Green
                            $daysLeft = floor($daysDiff);
                            $nextDueDisplay = '<span class="label label-success">UPCOMING</span><br>
                                             <small>'.$dueDateShort.' ('.$daysLeft.' days)</small><br>
                                             <small>₱'.$amount.'</small>';
                        }
                    }

                    echo '<tr>
                            <td>'.($key+1).'</td>
                            <td><strong>'.($plan["bill_number"] ?: 'N/A').'</strong></td>
                            <td>'.$customerName.'</td>
                            <td>₱'.number_format($plan["total_amount"], 2).'</td>
                            <td>'.$downpaymentDisplay.'</td>
                            <td>₱'.number_format($plan["payment_amount"], 2).'</td>
                            <td>'.$plan["interest_rate"].'%</td>
                            <td><span class="label label-info">'.$frequencyDisplay.'</span></td>
                            <td>'.$paidCount.'/'.$plan["number_of_payments"].'</td>
                            <td>'.$advanceDisplay.'</td>
                            <td>'.$nextDueDisplay.'</td>
                            <td><span class="label label-success">'.$plan["status"].'</span></td>
                            <td>
                              <div class="btn-group">
                                <button class="btn btn-info btn-xs btnViewPayments" data-toggle="modal" data-target="#modalViewPayments" planId="'.$plan["id"].'" customerName="'.$customerName.'" title="View Payments">
                                  <i class="fa fa-eye"></i>
                                </button>
                                <button class="btn btn-success btn-xs btnMarkPayment" data-toggle="modal" data-target="#modalMarkPayment" planId="'.$plan["id"].'" customerName="'.$customerName.'" title="Mark Payment">
                                  <i class="fa fa-check"></i>
                                </button>';
                                
                                // Add acknowledgment receipt button for all installment plans
                                if($plan["bill_number"]) {
                                  echo '<button class="btn btn-warning btn-xs btnPrintAcknowledgment" saleCode="'.$plan["bill_number"].'" title="Print Acknowledgment Receipt">
                                          <i class="fa fa-file-text"></i>
                                        </button>';
                                }
                                
                                echo '<button class="btn btn-danger btn-xs btnDeletePlan" data-toggle="modal" data-target="#modalDeletePlan" planId="'.$plan["id"].'" customerName="'.$customerName.'" saleId="'.$plan["sale_id"].'" title="Delete Plan">
                                  <i class="fa fa-trash"></i>
                                </button>
                              </div>
                            </td>
                          </tr>';
                  }
                  
                } else {
                  echo '<tr>
                          <td colspan="12" class="text-center">
                            <h4>No installment plans found</h4>
                            <p>Create a sale with installment payment to see plans here.</p>
                          </td>
                        </tr>';
                }
              } else {
                echo '<tr>
                        <td colspan="12" class="text-center">
                          <h4>Database tables not found</h4>
                          <p>Please import the installment_tables.sql file first.</p>
                        </td>
                      </tr>';
              }
              
            } catch(Exception $e) {
              echo '<tr>
                      <td colspan="12" class="text-center">
                        <h4>Database connection error</h4>
                        <p>Please check your database configuration.</p>
                        <small style="color: #999;">Error: '.$e->getMessage().'</small>
                      </td>
                    </tr>';
            }
            
            ?>

          </tbody>
        </table>

      </div>
    </div>
  </section>
</div>

<!--=====================================
MODAL VIEW PAYMENTS
======================================-->

<div id="modalViewPayments" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form role="form" method="post">
        
        <!--=====================================
        MODAL HEADER
        ======================================-->
        
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Payment History - <span id="viewCustomerName"></span></h4>
        </div>
        
        <!--=====================================
        MODAL BODY
        ======================================-->
        
        <div class="modal-body">
          <div class="box-body">
            <div id="paymentHistoryContent">
              <p class="text-center">Loading payment history...</p>
            </div>
          </div>
        </div>
        
        <!--=====================================
        MODAL FOOTER
        ======================================-->
        
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
        </div>
        
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL MARK PAYMENT
======================================-->

<div id="modalMarkPayment" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        
        <!--=====================================
        MODAL HEADER
        ======================================-->
        
        <div class="modal-header" style="background:#00a65a; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Mark Payment as Paid - <span id="markCustomerName"></span></h4>
        </div>
        
        <!--=====================================
        MODAL BODY
        ======================================-->
        
        <div class="modal-body">
          <div class="box-body">
            
            <div id="nextPaymentContent">
              <p class="text-center">Loading next payment...</p>
            </div>
            
            <div class="form-group">
              <label>Payment Type:</label>
              <div class="radio">
                <label>
                  <input type="radio" name="paymentType" value="exact" checked> Pay Exact Amount Due
                </label>
              </div>
              <div class="radio">
                <label>
                  <input type="radio" name="paymentType" value="custom"> Pay Custom Amount
                </label>
              </div>
            </div>
            
            <div class="form-group" id="customAmountGroup" style="display: none;">
              <label>Custom Payment Amount:</label>
              <div class="input-group">
                <span class="input-group-addon">₱</span>
                <input type="number" class="form-control" name="customAmount" id="customAmount" min="0" step="0.01" placeholder="Enter payment amount">
              </div>
              <small class="help-block">Amount must be greater than 0. Overpayments will be applied to future payments.</small>
            </div>
            
            <div class="form-group">
              <label>Payment Date:</label>
              <input type="date" class="form-control" name="paymentDate" id="paymentDate" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <input type="hidden" name="paymentId" id="paymentId">
            <input type="hidden" name="planId" id="planId">
            
          </div>
        </div>
        
        <!--=====================================
        MODAL FOOTER
        ======================================-->
        
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
          <button type="submit" name="markPaymentPaid" class="btn btn-success" id="processPaymentBtn">
            <i class="fa fa-check"></i> Process Payment
          </button>
        </div>
        
      </form>
      
      <?php
      
      // Handle mark payment as paid
      if(isset($_POST["markPaymentPaid"])){
        
        $paymentId = $_POST["paymentId"];
        $paymentDate = $_POST["paymentDate"];
        $planId = $_POST["planId"];
        
        try {
          // Update payment status
          $stmt = Connection::connect()->prepare("UPDATE installment_payments SET status = 'paid', payment_date = :payment_date WHERE id = :id");
          $stmt->bindParam(":payment_date", $paymentDate, PDO::PARAM_STR);
          $stmt->bindParam(":id", $paymentId, PDO::PARAM_INT);
          
          if($stmt->execute()){
            
            // Check if all payments are completed
            $checkStmt = Connection::connect()->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid FROM installment_payments WHERE installment_plan_id = :plan_id");
            $checkStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
            $checkStmt->execute();
            $result = $checkStmt->fetch();
            
            if($result["total"] == $result["paid"]){
              // All payments completed, update plan status
              $updatePlanStmt = Connection::connect()->prepare("UPDATE installment_plans SET status = 'completed' WHERE id = :id");
              $updatePlanStmt->bindParam(":id", $planId, PDO::PARAM_INT);
              $updatePlanStmt->execute();
            }
            
            echo '<script>
              swal({
                type: "success",
                title: "Payment marked as paid!",
                showConfirmButton: true,
                confirmButtonText: "Close"
              }).then(function(result){
                window.location = "index.php?route=installments";
              });
            </script>';
            
          } else {
            echo '<script>
              swal({
                type: "error",
                title: "Error updating payment",
                showConfirmButton: true,
                confirmButtonText: "Close"
              });
            </script>';
          }
          
        } catch(Exception $e) {
          echo '<script>
            swal({
              type: "error",
              title: "Database error",
              text: "Please try again",
              showConfirmButton: true,
              confirmButtonText: "Close"
            });
          </script>';
        }
      }
      
      ?>
      
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    
    // Handle payment type radio button changes
    $('input[name="paymentType"]').change(function() {
        if($(this).val() === 'custom') {
            $('#customAmountGroup').show();
            $('#customAmount').prop('required', true);
        } else {
            $('#customAmountGroup').hide();
            $('#customAmount').prop('required', false).val('');
        }
    });
    
    // Update payment content display when modal opens
    $(document).on('click', '.btnMarkPayment', function() {
        var planId = $(this).attr('planId');
        var customerName = $(this).attr('customerName');
        
        $('#planId').val(planId);
        $('#markCustomerName').text(customerName);
        
        // Load payment information with advance payment details
        $.ajax({
            url: 'ajax/get-next-payment.php',
            method: 'POST',
            data: { planId: planId },
            dataType: 'json',
            success: function(response) {
                if(response.success && response.payment) {
                    var paymentHtml = '<div class="alert alert-info">' +
                        '<h4><i class="fa fa-info-circle"></i> Next Payment Due</h4>' +
                        '<p><strong>Payment #:</strong> ' + response.payment.payment_number + '</p>';
                    
                    // Show advance payment information if exists
                    if(response.advance_info && response.advance_info.has_advance) {
                        paymentHtml += '<div class="alert alert-success" style="margin: 10px 0;">' +
                            '<h5><i class="fa fa-credit-card"></i> Advance Payment Applied</h5>' +
                            '<p><strong>Original Amount:</strong> ₱' + response.advance_info.original_amount.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</p>' +
                            '<p><strong>Advance Applied:</strong> <span class="text-success">-₱' + response.advance_info.advance_applied.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</span></p>' +
                            '<p><strong>Remaining Balance:</strong> ₱' + response.advance_info.remaining_amount.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</p>' +
                        '</div>';
                        
                        paymentHtml += '<p><strong>Amount Due:</strong> <span class="text-primary">₱' + parseFloat(response.payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</span></p>';
                    } else {
                        paymentHtml += '<p><strong>Amount:</strong> ₱' + parseFloat(response.payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</p>';
                    }
                    
                    // Show prepaid future payments if any
                    if(response.advance_info && response.advance_info.prepaid_payments.count > 0) {
                        paymentHtml += '<div class="alert alert-warning" style="margin: 10px 0;">' +
                            '<h5><i class="fa fa-calendar-check-o"></i> Future Payments Already Paid</h5>' +
                            '<p><strong>Payments Prepaid:</strong> ' + response.advance_info.prepaid_payments.count + ' payment(s)</p>' +
                            '<p><strong>Total Prepaid:</strong> ₱' + response.advance_info.prepaid_payments.amount.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</p>' +
                        '</div>';
                    }
                    
                    paymentHtml += '<p><strong>Due Date:</strong> ' + response.payment.due_date + '</p>' +
                        '</div>';
                    
                    $('#nextPaymentContent').html(paymentHtml);
                    $('#paymentId').val(response.payment.id);
                } else {
                    $('#nextPaymentContent').html('<div class="alert alert-danger">Error loading payment information: ' + (response.message || 'Unknown error') + '</div>');
                }
            },
            error: function() {
                $('#nextPaymentContent').html('<div class="alert alert-danger">Failed to load payment information</div>');
            }
        });
    });
    
    // Handle payment form submission with AJAX
    $(document).on('submit', '#modalMarkPayment form', function(e) {
        e.preventDefault();
        
        var paymentType = $('input[name="paymentType"]:checked').val();
        var customAmount = $('#customAmount').val();
        var paymentId = $('#paymentId').val();
        var planId = $('#planId').val();
        var paymentDate = $('#paymentDate').val();
        
        // Validate custom amount if selected
        if(paymentType === 'custom') {
            if(!customAmount || parseFloat(customAmount) <= 0) {
                swal({
                    type: 'error',
                    title: 'Invalid Amount',
                    text: 'Please enter a valid payment amount greater than 0.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        }
        
        // Prepare form data
        var formData = {
            paymentId: paymentId,
            planId: planId,
            paymentDate: paymentDate,
            paymentType: paymentType
        };
        
        if(paymentType === 'custom') {
            formData.customAmount = customAmount;
        }
        
        // Show loading
        swal({
            title: 'Processing Payment...',
            text: 'Please wait while we process your payment.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: function() {
                swal.showLoading();
            }
        });
        
        // Process payment via AJAX
        $.ajax({
            url: 'ajax/process-installment-payment.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    swal({
                        type: 'success',
                        title: 'Payment Processed!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        $('#modalMarkPayment').modal('hide');
                        window.location.reload();
                    });
                } else {
                    swal({
                        type: 'error',
                        title: 'Payment Failed',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                swal({
                    type: 'error',
                    title: 'Connection Error',
                    text: 'Unable to process payment. Please try again.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
    
    // Additional button click handler to ensure the button works
    $(document).on('click', '#processPaymentBtn', function(e) {
        e.preventDefault();
        $(this).closest('form').submit();
    });
    
    // Ensure payment button is always visible
    $('#modalMarkPayment').on('shown.bs.modal', function() {
        $('#processPaymentBtn').show().prop('disabled', false);
    });
    
});
</script>

<style>
#processPaymentBtn {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.modal-footer {
    display: block !important;
}

.modal-footer .btn {
    display: inline-block !important;
}

/* Due date indicator styling */
.label-danger {
    animation: pulse-red 2s infinite;
}

.label-warning {
    animation: pulse-orange 2s infinite;
}

@keyframes pulse-red {
    0% { background-color: #d9534f; }
    50% { background-color: #c9302c; }
    100% { background-color: #d9534f; }
}

@keyframes pulse-orange {
    0% { background-color: #f0ad4e; }
    50% { background-color: #ec971f; }
    100% { background-color: #f0ad4e; }
}

/* Responsive table for due dates */
@media (max-width: 768px) {
    #installmentTable th:nth-child(6),
    #installmentTable td:nth-child(6),
    #installmentTable th:nth-child(7),
    #installmentTable td:nth-child(7) {
        display: none;
    }
}
</style>

<!--=====================================
MODAL DELETE INSTALLMENT PLAN
======================================-->

<div id="modalDeletePlan" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        
        <!--=====================================
        MODAL HEADER
        ======================================-->
        
        <div class="modal-header" style="background:#dd4b39; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Delete Installment Plan - <span id="deleteCustomerName"></span></h4>
        </div>
        
        <!--=====================================
        MODAL BODY
        ======================================-->
        
        <div class="modal-body">
          <div class="box-body">
            
            <div class="alert alert-warning">
              <h4><i class="icon fa fa-warning"></i> Warning!</h4>
              You are about to permanently delete this installment plan. This action will:
              <ul>
                <li>Delete the installment plan</li>
                <li>Delete all associated payment records</li>
                <li>Reset the sale's payment method to regular payment</li>
              </ul>
              <strong>This action cannot be undone!</strong>
            </div>
            
            <div id="deletePlanInfo">
              <p class="text-center">Loading plan information...</p>
            </div>
            
            <input type="hidden" name="deletePlanId" id="deletePlanId">
            <input type="hidden" name="deleteSaleId" id="deleteSaleId">
            
          </div>
        </div>
        
        <!--=====================================
        MODAL FOOTER
        ======================================-->
        
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
          <button type="submit" name="deleteInstallmentPlan" class="btn btn-danger">
            <i class="fa fa-trash"></i> Delete Plan
          </button>
        </div>
        
      </form>
      
      <?php
      
      // Handle delete installment plan
      if(isset($_POST["deleteInstallmentPlan"])){
        
        $planId = $_POST["deletePlanId"];
        $saleId = $_POST["deleteSaleId"];
        
        try {
          
          $pdo = Connection::connect();
          $pdo->beginTransaction();
          
          // First, delete all installment payments
          $stmt = $pdo->prepare("DELETE FROM installment_payments WHERE installment_plan_id = :plan_id");
          $stmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
          $paymentsDeleted = $stmt->execute();
          
          // Then, delete the installment plan
          $stmt = $pdo->prepare("DELETE FROM installment_plans WHERE id = :id");
          $stmt->bindParam(":id", $planId, PDO::PARAM_INT);
          $planDeleted = $stmt->execute();
          
          // Finally, update the sale's payment method to remove installment
          $stmt = $pdo->prepare("UPDATE sales SET paymentMethod = 'cash' WHERE id = :sale_id");
          $stmt->bindParam(":sale_id", $saleId, PDO::PARAM_INT);
          $saleUpdated = $stmt->execute();
          
          if($paymentsDeleted && $planDeleted && $saleUpdated) {
            $pdo->commit();
            
            echo '<script>
              swal({
                type: "success",
                title: "Installment plan deleted successfully!",
                text: "The plan and all related payments have been removed.",
                showConfirmButton: true,
                confirmButtonText: "Close"
              }).then(function(result){
                window.location = "index.php?route=installments";
              });
            </script>';
            
          } else {
            $pdo->rollback();
            
            echo '<script>
              swal({
                type: "error",
                title: "Error deleting installment plan",
                text: "Please try again",
                showConfirmButton: true,
                confirmButtonText: "Close"
              });
            </script>';
          }
          
        } catch(Exception $e) {
          $pdo->rollback();
          
          echo '<script>
            swal({
              type: "error",
              title: "Database error",
              text: "Please try again later",
              showConfirmButton: true,
              confirmButtonText: "Close"
            });
          </script>';
        }
      }
      
      ?>
      
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  
  // View payments button
  $('.btnViewPayments').click(function() {
    var planId = $(this).attr('planId');
    var customerName = $(this).attr('customerName');
    
    $('#viewCustomerName').text(customerName);
    
    // Load payment history via AJAX
    $.ajax({
      url: 'ajax/get-payment-history.php',
      method: 'POST',
      data: { planId: planId },
      success: function(response) {
        $('#paymentHistoryContent').html(response);
      },
      error: function() {
        $('#paymentHistoryContent').html('<p class="text-danger">Error loading payment history</p>');
      }
    });
  });
  
  // This handler is now redundant since we have the enhanced one above
  
  // Delete plan button
  $('.btnDeletePlan').click(function() {
    var planId = $(this).attr('planId');
    var customerName = $(this).attr('customerName');
    var saleId = $(this).attr('saleId');
    
    $('#deleteCustomerName').text(customerName);
    $('#deletePlanId').val(planId);
    $('#deleteSaleId').val(saleId);
    
    // Load plan information for the delete confirmation
    $.ajax({
      url: 'ajax/get-plan-info.php',
      method: 'POST',
      data: { planId: planId },
      dataType: 'json',
      success: function(response) {
        if(response.success) {
          $('#deletePlanInfo').html(
            '<div class="alert alert-info">' +
            '<strong>Plan Details:</strong><br>' +
            'Total Amount: ₱' + parseFloat(response.plan.total_amount).toFixed(2) + '<br>' +
            'Monthly Payment: ₱' + parseFloat(response.plan.payment_amount).toFixed(2) + '<br>' +
            'Number of Payments: ' + response.plan.number_of_payments + '<br>' +
            'Payments Made: ' + response.paid_count + '/' + response.plan.number_of_payments + '<br>' +
            'Status: ' + response.plan.status +
            '</div>'
          );
        } else {
          $('#deletePlanInfo').html('<p class="text-warning">Could not load plan details</p>');
        }
      },
      error: function() {
        $('#deletePlanInfo').html('<p class="text-warning">Could not load plan details</p>');
      }
    });
  });
  
});
</script> 