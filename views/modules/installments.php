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
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Active Installment Plans</h3>
      </div>
      <div class="box-body">
        
        <table class="table table-bordered table-striped dt-responsive tables" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Customer</th>
              <th>Total Amount</th>
              <th>Monthly Payment</th>
              <th>Interest Rate</th>
              <th>Payments Made</th>
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
                $stmt = Connection::connect()->prepare("SELECT * FROM installment_plans ORDER BY id DESC");
                $stmt->execute();
                $installmentPlans = $stmt->fetchAll();
                
                if($installmentPlans && count($installmentPlans) > 0) {
                  
                  foreach ($installmentPlans as $key => $plan) {
                    
                    $customerStmt = Connection::connect()->prepare("SELECT name FROM customers WHERE id = :id");
                    $customerStmt->bindParam(":id", $plan["customer_id"], PDO::PARAM_INT);
                    $customerStmt->execute();
                    $customer = $customerStmt->fetch();
                    
                    $customerName = $customer ? $customer["name"] : "Unknown Customer";
                    
                    // Count paid payments
                    $paidStmt = Connection::connect()->prepare("SELECT COUNT(*) as paid_count FROM installment_payments WHERE installment_plan_id = :plan_id AND status = 'paid'");
                    $paidStmt->bindParam(":plan_id", $plan["id"], PDO::PARAM_INT);
                    $paidStmt->execute();
                    $paidResult = $paidStmt->fetch();
                    $paidCount = $paidResult ? $paidResult["paid_count"] : 0;
                    
                    echo '<tr>
                            <td>'.($key+1).'</td>
                            <td>'.$customerName.'</td>
                            <td>₱'.number_format($plan["total_amount"], 2).'</td>
                            <td>₱'.number_format($plan["payment_amount"], 2).'</td>
                            <td>'.$plan["interest_rate"].'%</td>
                            <td>'.$paidCount.'/'.$plan["number_of_payments"].'</td>
                            <td><span class="label label-success">'.$plan["status"].'</span></td>
                            <td>
                              <div class="btn-group">
                                <button class="btn btn-info btn-xs btnViewPayments" data-toggle="modal" data-target="#modalViewPayments" planId="'.$plan["id"].'" customerName="'.$customerName.'">
                                  <i class="fa fa-eye"></i>
                                </button>
                                <button class="btn btn-success btn-xs btnMarkPayment" data-toggle="modal" data-target="#modalMarkPayment" planId="'.$plan["id"].'" customerName="'.$customerName.'">
                                  <i class="fa fa-check"></i>
                                </button>
                              </div>
                            </td>
                          </tr>';
                  }
                  
                } else {
                  echo '<tr>
                          <td colspan="8" class="text-center">
                            <h4>No installment plans found</h4>
                            <p>Create a sale with installment payment to see plans here.</p>
                          </td>
                        </tr>';
                }
              } else {
                echo '<tr>
                        <td colspan="8" class="text-center">
                          <h4>Database tables not found</h4>
                          <p>Please import the installment_tables.sql file first.</p>
                        </td>
                      </tr>';
              }
              
            } catch(Exception $e) {
              echo '<tr>
                      <td colspan="8" class="text-center">
                        <h4>Database connection error</h4>
                        <p>Please check your database configuration.</p>
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
          <button type="submit" name="markPaymentPaid" class="btn btn-success">Mark as Paid</button>
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
  
  // Mark payment button
  $('.btnMarkPayment').click(function() {
    var planId = $(this).attr('planId');
    var customerName = $(this).attr('customerName');
    
    $('#markCustomerName').text(customerName);
    $('#planId').val(planId);
    
    // Load next payment via AJAX
    $.ajax({
      url: 'ajax/get-next-payment.php',
      method: 'POST',
      data: { planId: planId },
      dataType: 'json',
      success: function(response) {
        if(response.success) {
          $('#paymentId').val(response.payment.id);
          $('#nextPaymentContent').html(
            '<div class="alert alert-info">' +
            '<strong>Payment #' + response.payment.payment_number + '</strong><br>' +
            'Amount: ₱' + parseFloat(response.payment.amount).toFixed(2) + '<br>' +
            'Due Date: ' + response.payment.due_date +
            '</div>'
          );
        } else {
          $('#nextPaymentContent').html('<p class="text-warning">' + response.message + '</p>');
          $('button[name="markPaymentPaid"]').hide();
        }
      },
      error: function() {
        $('#nextPaymentContent').html('<p class="text-danger">Error loading payment information</p>');
      }
    });
  });
  
});
</script> 