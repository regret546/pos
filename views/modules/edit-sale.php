<style>
  /* Custom responsive styles */
  @media (max-width: 1200px) {
    .desktop {
      display: none !important;
    }
  }
  
  @media (min-width: 1201px) {
    .table-responsive {
      overflow-x: visible;
    }
    .desktop {
      display: table-cell !important;
    }
  }

  /* Ensure table cells don't wrap content awkwardly */
  .salesTable td, .salesTable th {
    white-space: nowrap;
    min-width: 100px;
  }
  
  /* Make sure image column doesn't get too wide */
  .salesTable td:nth-child(2) {
    width: 60px;
    min-width: 60px;
  }
</style>
<!-- Log on to codeastro.com for more projects! -->
<div class="content-wrapper">

  <section class="content-header">

    <h1>

      Edit Sale

    </h1>

    <ol class="breadcrumb">

      <li><a href="index.php?route=home"><i class="fa fa-dashboard"></i> Home</a></li>

      <li class="active">Edit Sale</li>
		<!-- Log on to codeastro.com for more projects! -->
    </ol>

  </section>

  <section class="content">

    <div class="row">
      
      <!--=============================================
      THE FORM
      =============================================-->
      <div class="col-lg-5 col-xs-12">
        
        <div class="box box-default">

          <div class="box-header with-border"></div>

          <form role="form" method="post" class="saleForm">

            <div class="box-body">
                
                <div class="box">

                  <?php

                    $item = "id";
                    $value = $_GET["idSale"];

                    $sale = ControllerSales::ctrShowSales($item, $value);

                    $itemUser = "id";
                    $valueUser = $sale["idSeller"];

                    $seller = ControllerUsers::ctrShowUsers($itemUser, $valueUser);

                    $itemCustomers = "id";
                    $valueCustomers = $sale["idCustomer"];

                    $customers = ControllerCustomers::ctrShowCustomers($itemCustomers, $valueCustomers);

                    // Get installment plan data if payment method is installment
                    $installmentPlan = null;
                    $hasPaidPayments = false;
                    $paymentHistory = array();
                    
                    if(strpos($sale["paymentMethod"], "installment") !== false) {
                        // Try to get installment plan data directly from database
                        try {
                            $stmt = Connection::connect()->prepare("SELECT * FROM installment_plans WHERE sale_id = :sale_id");
                            $stmt->bindParam(":sale_id", $sale["id"], PDO::PARAM_INT);
                            $stmt->execute();
                            $installmentPlan = $stmt->fetch();
                            
                            if($installmentPlan) {
                                // Check for payment history
                                $paymentStmt = Connection::connect()->prepare("SELECT COUNT(*) as total_payments, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_payments FROM installment_payments WHERE installment_plan_id = :plan_id");
                                $paymentStmt->bindParam(":plan_id", $installmentPlan["id"], PDO::PARAM_INT);
                                $paymentStmt->execute();
                                $paymentHistory = $paymentStmt->fetch();
                                
                                $hasPaidPayments = $paymentHistory && $paymentHistory["paid_payments"] > 0;
                            }

                        } catch (Exception $e) {
                            // If installment data can't be retrieved, continue without it
                            $installmentPlan = null;
                            $hasPaidPayments = false;
                        }
                    }

                ?>

                    <?php if($hasPaidPayments): ?>
                    <!--=====================================
                    =            INSTALLMENT WARNING     =
                    ======================================-->
                    <div class="alert alert-warning">
                        <h4><i class="fa fa-warning"></i> Warning: Cannot Edit Installment Sale</h4>
                        <p>This installment sale cannot be edited because the customer has already made 
                        <strong><?php echo $paymentHistory["paid_payments"]; ?>/<?php echo $paymentHistory["total_payments"]; ?></strong> 
                        payments. Editing installment sales with payment history is not allowed to maintain financial integrity.</p>
                        <p><strong>Options:</strong></p>
                        <ul>
                            <li>View payment history in the <a href="installments" target="_blank">Installments Management</a> page</li>
                            <li>Create a new sale if needed</li>
                            <li>Contact administrator for any adjustments</li>
                        </ul>
                    </div>
                    <script>
                        // Disable all form inputs
                        $(document).ready(function() {
                            $('input, select, textarea, button[type="submit"]').prop('disabled', true);
                            $('input, select, textarea').css({
                                'background-color': '#f9f9f9',
                                'color': '#999'
                            });
                        });
                    </script>
                    <?php endif; ?>

                    <!--=====================================
                    =            SELLER INPUT           =
                    ======================================-->
                  
                    
                    <div class="form-group">

                      <div class="input-group">
                        
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>

                        <input type="text" class="form-control" name="newSeller" id="newSeller" value="<?php echo $seller["name"]; ?>" readonly>

                        <input type="hidden" name="idSeller" value="<?php echo $seller["id"]; ?>">

                      </div>
					<!-- Log on to codeastro.com for more projects! -->
                    </div>


                    <!--=====================================
                    CODE INPUT
                    ======================================-->
                  
                    
                    <div class="form-group">

                      <div class="input-group">
                        
                        <span class="input-group-addon"><i class="fa fa-key"></i></span>

                        <input type="text" class="form-control" id="newSale" name="editSale" value="<?php echo $sale["code"]; ?>" readonly>

                      </div>


                    </div>


                    <!--=====================================
                    =            CUSTOMER INPUT           =
                    ======================================-->
                  
                    <!-- Log on to codeastro.com for more projects! -->
                    <div class="form-group">

                      <div class="input-group">
                        
                        <span class="input-group-addon"><i class="fa fa-users"></i></span>

                        <select class="form-control" name="selectCustomer" id="selectCustomer" required>
                          
                            <option value="<?php echo $customers["id"]; ?>"><?php echo $customers["name"]; ?></option>

                            <?php 

                            $item = null;
                            $value = null;

                            $customers = ControllerCustomers::ctrShowCustomers($item, $value);

                            foreach ($customers as $key => $value) {
                              echo '<option value="'.$value["id"].'">'.$value["name"].'</option>';
                            }


                            ?>

                        </select>

                        <span class="input-group-addon"><button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modalAddCustomer" data-dismiss="modal">Add Customer</button></span>

                      </div>
					<!-- Log on to codeastro.com for more projects! -->
                    </div>

                    <!--=====================================
                    =            PRODUCT INPUT           =
                    ======================================-->
                  
                    
                    <div class="form-group row newProduct">
                      <?php

                        $productList = json_decode($sale["products"], true);

                        foreach ($productList as $key => $value) {

                          $item = "id";
                          $valueProduct = $value["id"];
                          $order = "id";

                          $answer = ControllerProducts::ctrShowproducts($item, $valueProduct, $order);

                          $lastStock = $answer["stock"] + $value["quantity"];
                          
                          echo '<div class="row" style="padding:5px 15px">
                    
                                <div class="col-xs-6" style="padding-right:0px">
                    
                                  <div class="input-group">
                        
                                    <span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct="'.$value["id"].'"><i class="fa fa-trash"></i></button></span>

                                    <input type="text" class="form-control newProductDescription" idProduct="'.$value["id"].'" name="addProduct" value="'.$value["description"].'" readonly required>

                                  </div>

                                </div>

                                <div class="col-xs-3">
                      
                                  <input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="'.$value["quantity"].'" stock="'.$lastStock.'" newStock="'.$value["stock"].'" required>

                                </div>

                                <div class="col-xs-3 enterPrice" style="padding-left:0px">

                                  <div class="input-group">

                                    <span class="input-group-addon">₱</span>
                           
                                    <input type="text" class="form-control newProductPrice" realPrice="'.$answer["sellingPrice"].'" name="newProductPrice" value="'.$value["totalPrice"].'" readonly required>
           
                                  </div>
                       
                                </div>

                              </div>';
                        }


                        ?>

                    </div>

                    <input type="hidden" name="productsList" id="productsList">

                    <!--=====================================
                    =            ADD PRODUCT BUTTON          =
                    ======================================-->
                    
                    <button type="button" class="btn btn-default hidden-lg btnAddProduct">Add Product</button>

                    <hr>

                    <div class="row">
                        <!--=====================================
                        DISCOUNT INPUT
                        ======================================-->
                        <div class="col-xs-6" style="padding-right: 0">
                            <div class="form-group">
                                <label>Discount Amount</label>
                                <div class="input-group">
                                    <span class="input-group-addon">₱</span>
                                    <input type="number" class="form-control" name="saleDiscount" id="saleDiscount" placeholder="0.00" min="0" step="0.01" value="<?php echo isset($sale["discount"]) ? $sale["discount"] : 0; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!--=====================================
                        TOTAL DISPLAY
                        ======================================-->
                        <div class="col-xs-6" style="padding-left: 0">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <div class="input-group">
                                    <span class="input-group-addon">₱</span>
                                    <input type="text" class="form-control" id="totalDisplay" placeholder="0.00" value="<?php echo $sale["totalPrice"]; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden fields for calculations -->
                        <input type="hidden" name="saleTotal" id="saleTotal" value="<?php echo $sale["totalPrice"]; ?>">
                        <input type="hidden" name="newTaxPrice" id="newTaxPrice" value="<?php echo $sale["tax"]; ?>">
                        <input type="hidden" name="newTaxSale" id="newTaxSale" value="0">
                    </div>

                    <hr>

                    <!--=====================================
                      PAYMENT METHOD
                      ======================================-->

                    <div class="form-group row">
                      
                      <div class="col-xs-6" style="padding-right: 0">

                        <div class="input-group">
                      
                          <select class="form-control" name="newPaymentMethod" id="newPaymentMethod" required>
                            
                              <option value="">-Select Payment Method-</option>
                              <option value="cash" <?php if($sale["paymentMethod"] == "cash") echo "selected"; ?>>Cash</option>
                              <option value="QRPH" <?php if($sale["paymentMethod"] == "QRPH" || $sale["paymentMethod"] == "CC") echo "selected"; ?>>QRPH</option>
                              <option value="Card" <?php if($sale["paymentMethod"] == "Card" || $sale["paymentMethod"] == "DC") echo "selected"; ?>>Debit/Credit Card</option>
                              <?php if(strpos($sale["paymentMethod"], "installment") !== false): ?>
                              <option value="installment" selected>Installment</option>
                              <?php endif; ?>

                          </select>

                        </div>

                      </div>

                      <div class="paymentMethodBoxes"></div>

                      <input type="hidden" name="listPaymentMethod" id="listPaymentMethod" required>

                    </div>

                    <br>
                    
                </div>

            </div>
			<!-- Log on to codeastro.com for more projects! -->
            <div class="box-footer">
              <button type="submit" class="btn btn-success pull-right">Save Changes</button>
            </div>
          </form>

          <?php

            $editSale = new ControllerSales();
            $editSale -> ctrEditSale();
            
          ?>

        </div>

      </div>


      <!--=============================================
      =            PRODUCTS TABLE                   =
      =============================================-->


      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
        
          <div class="box box-default">
            
            <div class="box-header with-border"></div>

            <div class="box-body">
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped dt-responsive salesTable">
                    
                  <thead>

                     <tr>
                       
                       <th style="width:10px">#</th>
                       <th>Image</th>
                       <th style="width:30px">Code</th>
                       <th>Description</th>
                       <th>Stock</th>
                       <th class="desktop">Price</th>
                       <th class="desktop">Actions</th>

                     </tr> 

                  </thead>

                </table>
              </div>
            </div>

          </div>


      </div>

    </div>

  </section>

</div>


<!--=====================================
=            module add Customer            =
======================================-->

<!-- Modal -->
<div id="modalAddCustomer" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <form role="form" method="POST">
        <div class="modal-header" style="background: #DD4B39; color: #fff">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add Customer</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">

            <!--Input name -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input class="form-control input-lg" type="text" name="newCustomer" placeholder="Write name" required>
              </div>
            </div>

            <!--Input id document -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                <input class="form-control input-lg" type="number" min="0" name="newIdDocument" placeholder="Write your ID" required>
              </div>
            </div>

            <!--Input email -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input class="form-control input-lg" type="text" name="newEmail" placeholder="Email" required>
              </div>
            </div>

            <!--Input phone -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                <input class="form-control input-lg" type="text" name="newPhone" placeholder="phone" data-inputmask="'mask':'(999) 999-9999'" data-mask required>
              </div>
            </div>

            <!--Input address -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                <input class="form-control input-lg" type="text" name="newAddress" placeholder="Address" required>
              </div>
            </div>


            <!--Input phone -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control input-lg" type="text" name="newBirthdate" placeholder="Birth Date" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Save Customer</button>
        </div>
      </form><!-- Log on to codeastro.com for more projects! -->

      <?php

        $createCustomer = new ControllerCustomers();
        $createCustomer -> ctrCreateCustomer();

      ?>
    </div>

  </div>
</div><!-- Log on to codeastro.com for more projects! -->

<!--====  End of module add Customer  ====-->

<script>
// Function to show transaction field for electronic payments - define globally
window.showTransactionField = function(paymentMethod) {
    console.log('showTransactionField called for:', paymentMethod);
    
    // Clear any existing content first
    $('.paymentMethodBoxes').empty();
    
    var fieldLabel = paymentMethod === 'QRPH' ? 'QRPH Transaction Code' : 'Card Transaction Code';
    var placeholder = paymentMethod === 'QRPH' ? 'Enter QRPH transaction reference' : 'Enter card transaction reference';
    
    var html = '<div class="col-xs-12">' +
               '<label for="transactionCode">' + fieldLabel + ':</label>' +
               '<div class="input-group">' +
               '<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>' +
               '<input type="text" class="form-control" name="transactionCode" id="transactionCode" ' +
               'placeholder="' + placeholder + '" required>' +
               '</div>' +
               '</div>';
    
    console.log('Adding transaction field HTML to paymentMethodBoxes');
    $('.paymentMethodBoxes').html(html);
    
    // Check if the HTML was actually added
    var addedContent = $('.paymentMethodBoxes').html();
    console.log('Transaction field added, content length:', addedContent.length);
    console.log('Transaction field exists:', $('#transactionCode').length > 0);
};

$(document).ready(function() {
    
    // Initialize total display and calculations
    setTimeout(function() {
        addingTotalPrices();
        addTax();
    }, 100);
    
            // Set initial payment method value
        var currentPaymentMethod = '<?php echo $sale["paymentMethod"]; ?>';
        $('#listPaymentMethod').val(currentPaymentMethod);
        
        // Parse installment data if it exists
        var isInstallment = currentPaymentMethod.indexOf('installment') !== -1;
        var installmentMonths = null;
        var installmentInterest = null;
        var installmentFrequency = null;
    
    if (isInstallment) {
        console.log('Installment detected, payment method:', currentPaymentMethod);
        
        // Set the payment method dropdown to installment
        $('#newPaymentMethod').val('installment');
        
        // Get installment data from PHP
        <?php if($installmentPlan && isset($installmentPlan["number_of_payments"])): ?>
            installmentMonths = '<?php echo $installmentPlan["number_of_payments"]; ?>';
            installmentInterest = '<?php echo isset($installmentPlan["interest_rate"]) ? $installmentPlan["interest_rate"] : 0; ?>';
            installmentFrequency = '<?php echo isset($installmentPlan["payment_frequency"]) ? $installmentPlan["payment_frequency"] : "30th"; ?>';
            
            // Get downpayment data
            var existingDownpayment = <?php echo isset($installmentPlan["downpayment_amount"]) ? floatval($installmentPlan["downpayment_amount"]) : 0; ?>;
            var hasExistingDownpayment = existingDownpayment > 0;
            
            console.log('Got installment data from PHP - Months:', installmentMonths, 'Interest:', installmentInterest, 'Frequency:', installmentFrequency, 'Downpayment:', existingDownpayment);
        <?php else: ?>
            // Fallback: Extract months from the payment method (e.g., "installment_6" -> "6")
            var parts = currentPaymentMethod.split('_');
            if (parts.length > 1) {
                installmentMonths = parts[1];
            }
            installmentInterest = 0; // Default to 0 if not available
            installmentFrequency = "30th"; // Default frequency
            
            // Default downpayment values
            var existingDownpayment = 0;
            var hasExistingDownpayment = false;
            
            console.log('Using fallback installment data - Months:', installmentMonths, 'Interest:', installmentInterest, 'Frequency:', installmentFrequency, 'Downpayment:', existingDownpayment);
        <?php endif; ?>
        
        // Manually trigger the installment display immediately
        console.log('Manually showing installment options on page load');
        
        // Directly show installment options without waiting for trigger
        setTimeout(function() {
            console.log('Calling showInstallmentOptions from page load');
            showInstallmentOptions();
        }, 100);
        
        // Wait a moment for the DOM to be ready, then set values
        setTimeout(function() {
            console.log('Setting installment values after DOM ready');
            if (installmentMonths) {
                console.log('Setting months dropdown to:', installmentMonths);
                $('#installmentMonths').val(installmentMonths);
                $('#installmentMonths').trigger('change');
                
                // Also manually set the listPaymentMethod to ensure it's correct
                $('#listPaymentMethod').val('installment_' + installmentMonths);
                console.log('Set listPaymentMethod to:', 'installment_' + installmentMonths);
            }
            if (installmentInterest !== null) {
                console.log('Setting interest rate to:', installmentInterest);
                $('#installmentInterest').val(installmentInterest);
                $('#installmentInterest').trigger('input');
            }
            if (installmentFrequency) {
                console.log('Setting payment frequency to:', installmentFrequency);
                $('#installmentFrequency').val(installmentFrequency);
            }
            
            // Set downpayment values if they exist
            if (hasExistingDownpayment) {
                console.log('Setting existing downpayment:', existingDownpayment);
                $('#hasDownpayment').prop('checked', true);
                $('#downpaymentAmount').prop('disabled', false).val(existingDownpayment);
            } else {
                console.log('No existing downpayment found');
                $('#hasDownpayment').prop('checked', false);
                $('#downpaymentAmount').prop('disabled', true).val('');
            }
        }, 300);
        
        // Also trigger the change event as backup
        setTimeout(function() {
            $('#newPaymentMethod').trigger('change.editSale');
        }, 100);
     } else {
         // For non-installment sales, set the correct payment method and handle transaction fields
         console.log('Non-installment sale detected, payment method:', currentPaymentMethod);
         console.log('Installment option has been removed from dropdown for non-installment sales');
         $('#newPaymentMethod').val(currentPaymentMethod);
         
         // Clear any potential installment elements (shouldn't exist but just in case)
         $('.paymentMethodBoxes').empty();
         
         // Check if this is QRPH or Card payment that needs transaction field
         if (currentPaymentMethod === 'QRPH' || currentPaymentMethod === 'Card') {
             console.log('Electronic payment detected, will show transaction field');
         }
         
         // Explicitly ensure dropdown shows the correct value
         console.log('Setting dropdown to non-installment value:', currentPaymentMethod);
         $('#newPaymentMethod').trigger('change');
         
         // Also trigger the specific change event
         setTimeout(function() {
             $('#newPaymentMethod').trigger('change.editSale');
         }, 100);
     }
    
    // Function to update installment summary - make it globally accessible
    window.updateInstallmentSummary = function() {
        var interest = parseFloat($('#installmentInterest').val()) || 0;
        var months = parseInt($('#installmentMonths').val()) || 0;
        var total = parseFloat($('#saleTotal').val()) || 0;
        var hasDownpayment = $('#hasDownpayment').is(':checked');
        var downpayment = hasDownpayment ? (parseFloat($('#downpaymentAmount').val()) || 0) : 0;
        
        // Show summary even if total is 0 for demonstration purposes
        if (interest >= 0 && months > 0) {
            var monthlyInterest = interest / 100;
            
            if (total > 0) {
                // Calculate with actual total and downpayment
                var remainingAmount = total - downpayment;
                var totalWithInterest = remainingAmount * (1 + (monthlyInterest * months));
                var monthlyPayment = totalWithInterest / months;
                var totalInterestAmount = totalWithInterest - remainingAmount;
                var finalTotalToPay = downpayment + totalWithInterest;
                
                var summaryHTML = '<p><strong>Original Amount:</strong> ₱' + total.toFixed(2) + '</p>';
                
                if (hasDownpayment && downpayment > 0) {
                    summaryHTML += '<p><strong>Downpayment:</strong> ₱' + downpayment.toFixed(2) + '</p>' +
                                 '<p><strong>Remaining for Installment:</strong> ₱' + remainingAmount.toFixed(2) + '</p>';
                }
                
                summaryHTML += '<p><strong>Interest Rate:</strong> ' + interest + '% (' + months + ' months)</p>' +
                             '<p><strong>Interest Amount:</strong> ₱' + totalInterestAmount.toFixed(2) + '</p>' +
                             '<p style="font-size: 16px; color: #00a65a;"><strong>Total to Pay:</strong> ₱' + total.toFixed(2) + ' + ₱' + totalInterestAmount.toFixed(2) + ' interest = ₱' + (total + totalInterestAmount).toFixed(2) + '</p>' +
                             '<p style="font-size: 16px; color: #3c8dbc;"><strong>Monthly Payment:</strong> ₱' + monthlyPayment.toFixed(2) + '</p>';
            } else {
                // Show example with 1000 pesos when no products added yet
                var exampleTotal = 1000;
                var exampleDownpayment = hasDownpayment ? 200 : 0;
                var remainingAmount = exampleTotal - exampleDownpayment;
                var totalWithInterest = remainingAmount * (1 + (monthlyInterest * months));
                var monthlyPayment = totalWithInterest / months;
                var totalInterestAmount = totalWithInterest - remainingAmount;
                var finalTotalToPay = exampleDownpayment + totalWithInterest;
                
                var summaryHTML = '<div class="alert alert-warning" style="margin-bottom: 10px;"><small><strong>Preview:</strong> Add products to see actual calculation</small></div>' +
                                '<p><strong>Example (₱1,000):</strong></p>';
                
                if (hasDownpayment) {
                    summaryHTML += '<p><strong>Example Downpayment:</strong> ₱' + exampleDownpayment.toFixed(2) + '</p>' +
                                 '<p><strong>Remaining for Installment:</strong> ₱' + remainingAmount.toFixed(2) + '</p>';
                }
                
                summaryHTML += '<p><strong>Interest Rate:</strong> ' + interest + '% (' + months + ' months)</p>' +
                             '<p><strong>Interest Amount:</strong> ₱' + totalInterestAmount.toFixed(2) + '</p>' +
                             '<p style="font-size: 16px; color: #00a65a;"><strong>Total to Pay:</strong> ₱' + exampleTotal.toFixed(2) + ' + ₱' + totalInterestAmount.toFixed(2) + ' interest = ₱' + (exampleTotal + totalInterestAmount).toFixed(2) + '</p>' +
                             '<p style="font-size: 16px; color: #3c8dbc;"><strong>Monthly Payment:</strong> ₱' + monthlyPayment.toFixed(2) + '</p>';
            }
            
            // Wait a moment for DOM to be ready, then update
            setTimeout(function() {
                var summaryDiv = $('#installmentSummaryDiv');
                var summaryContent = $('#installmentSummary');
                
                if (summaryDiv.length > 0 && summaryContent.length > 0) {
                    summaryContent.html(summaryHTML);
                    summaryDiv.show();
                    summaryDiv.css({
                        'display': 'block !important',
                        'visibility': 'visible',
                        'opacity': '1'
                    });
                } else {
                    // Create the summary div manually and append it
                    var manualSummaryHtml = '<div class="col-xs-12" id="installmentSummaryDiv" style="display: block !important; margin-top: 10px;">' +
                                          '<div class="alert alert-info" style="margin-bottom: 0; background-color: #f9f9f9; border: 1px solid #ddd; color: #333;">' +
                                          '<strong>Payment Summary:</strong>' +
                                          '<div id="installmentSummary">' + summaryHTML + '</div>' +
                                          '</div>' +
                                          '</div>';
                    $('.paymentMethodBoxes').append(manualSummaryHtml);
                }
            }, 200);
        } else {
            $('#installmentSummaryDiv').hide();
        }
    }
    

     
     // Function to show installment options
     function showInstallmentOptions() {
        console.log('showInstallmentOptions called - creating HTML');
        
        // Clear any existing content first
        $('.paymentMethodBoxes').empty();
        
        var html = '<div class="col-xs-6">' +
                   '<label for="installmentMonths">Payment Plan:</label>' +
                   '<div class="input-group">' +
                   '<select class="form-control" name="installmentMonths" id="installmentMonths" required>' +
                   '<option value="">Select Payment Plan</option>' +
                   '<option value="3">3 Months</option>' +
                   '<option value="6">6 Months</option>' +
                   '<option value="9">9 Months</option>' +
                   '<option value="12">12 Months</option>' +
                   '</select>' +
                   '</div>' +
                   '</div>' +
                   '<div class="col-xs-6" id="downpaymentDiv" style="display: none; margin-top: 10px;">' +
                   '<label for="downpaymentAmount">Downpayment (Optional):</label>' +
                   '<div class="input-group">' +
                   '<span class="input-group-addon">₱</span>' +
                   '<input type="number" class="form-control" name="downpaymentAmount" id="downpaymentAmount" ' +
                   'min="0" step="0.01" placeholder="Enter downpayment amount" disabled>' +
                   '</div>' +
                   '<div class="checkbox" style="margin-top: 5px;">' +
                   '<label>' +
                   '<input type="checkbox" id="hasDownpayment" name="hasDownpayment"> Customer wants to make a downpayment' +
                   '</label>' +
                   '</div>' +
                   '</div>' +
                   '<div class="col-xs-6" id="installmentInterestDiv" style="display: none; margin-top: 10px;">' +
                   '<label for="installmentInterest">Interest Rate (%):</label>' +
                   '<div class="input-group">' +
                   '<span class="input-group-addon"><i class="fa fa-percent"></i></span>' +
                   '<input type="number" class="form-control" name="installmentInterest" id="installmentInterest" ' +
                   'min="0" max="100" step="0.01" placeholder="Enter interest rate" required>' +
                   '</div>' +
                   '</div>' +
                   '<div class="col-xs-6" id="installmentFrequencyDiv" style="display: none; margin-top: 10px;">' +
                   '<label for="installmentFrequency">Payment Frequency:</label>' +
                   '<div class="input-group">' +
                   '<select class="form-control" name="installmentFrequency" id="installmentFrequency" required>' +
                   '<option value="15th">Every 15th of the month</option>' +
                   '<option value="30th" selected>Every 30th of the month</option>' +
                   '<option value="both">Both 15th and 30th</option>' +
                   '</select>' +
                   '</div>' +
                   '</div>' +
                   '<div class="col-xs-12" id="installmentSummaryDiv" style="display: none; margin-top: 10px;">' +
                   '<div class="alert alert-info" style="margin-bottom: 0; background-color: #f9f9f9; border: 1px solid #ddd; color: #333;">' +
                   '<strong>Payment Summary:</strong>' +
                   '<div id="installmentSummary">' +
                   '<p>Calculating...</p>' +
                   '</div>' +
                   '</div>' +
                   '</div>';
        
        console.log('Adding installment HTML to paymentMethodBoxes, HTML length:', html.length);
        $('.paymentMethodBoxes').html(html);
        $('#listPaymentMethod').val('');
        
        // Check if the HTML was actually added
        var addedContent = $('.paymentMethodBoxes').html();
        console.log('Installment options added, content length:', addedContent.length);
        console.log('Months dropdown exists:', $('#installmentMonths').length > 0);
        
        // Re-attach event handlers after adding HTML
        attachInstallmentHandlers();
    }
    
    // Function to attach installment event handlers
    function attachInstallmentHandlers() {
        // Remove existing handlers first
        $(document).off('change', '#installmentMonths');
        $(document).off('input', '#installmentInterest');
        $(document).off('change', '#hasDownpayment');
        $(document).off('input', '#downpaymentAmount');
        
        // Add new handlers
        $(document).on('change', '#installmentMonths', function() {
            var months = $(this).val();
            console.log('Months changed to:', months);
            if (months) {
                $('#downpaymentDiv').show();
                $('#installmentInterestDiv').show();
                $('#installmentFrequencyDiv').show();
                $('#listPaymentMethod').val('installment_' + months);
                console.log('Set listPaymentMethod to:', 'installment_' + months);
                updateInstallmentSummary();
            } else {
                $('#downpaymentDiv').hide();
                $('#installmentInterestDiv').hide();
                $('#installmentFrequencyDiv').hide();
                $('#listPaymentMethod').val('');
                console.log('Cleared listPaymentMethod');
            }
        });
        
        // Handle downpayment checkbox
        $(document).on('change', '#hasDownpayment', function() {
            if ($(this).is(':checked')) {
                $('#downpaymentAmount').prop('disabled', false).focus();
            } else {
                $('#downpaymentAmount').prop('disabled', true).val('');
                updateInstallmentSummary();
            }
        });
        
        // Handle downpayment amount input
        $(document).on('input', '#downpaymentAmount', function() {
            var downpayment = parseFloat($(this).val()) || 0;
            var total = parseFloat($('#saleTotal').val()) || 0;
            
            // Validate downpayment doesn't exceed total
            if (downpayment > total && total > 0) {
                $(this).val(total.toFixed(2));
            }
            updateInstallmentSummary();
        });
        
        $(document).on('input', '#installmentInterest', function() {
            updateInstallmentSummary();
        });
    }
    
    // Remove any existing handlers to prevent conflicts
    $('#newPaymentMethod').off('change.editSale');
    
    $('#newPaymentMethod').on('change.editSale', function() {
        var method = $(this).val();
        
        console.log('Edit-sale payment method changed to:', method);
        console.log('Payment method boxes before clear:', $('.paymentMethodBoxes').html());
        
        // Clear payment method boxes
        $('.paymentMethodBoxes').empty();
        console.log('Payment method boxes after clear:', $('.paymentMethodBoxes').html());
        
         if (method === 'installment') {
             // Reset container classes for installment layout
             $('#newPaymentMethod').parent().parent().removeClass('col-xs-4');
             $('#newPaymentMethod').parent().parent().addClass('col-xs-6');
             
             console.log('Payment method changed to installment - showing installment options');
             showInstallmentOptions();
         } else if (method === 'QRPH' || method === 'Card') {
             // For QRPH and Card payments, show transaction field
             $('#listPaymentMethod').val(method);
             
             // Reset container classes for non-cash layout
             $('#newPaymentMethod').parent().parent().removeClass('col-xs-4');
             $('#newPaymentMethod').parent().parent().addClass('col-xs-6');
             
             console.log('Electronic payment selected:', method, '- showing transaction field');
             showTransactionField(method);
         } else if (method === 'cash') {
             // For cash payments, show cash value and change fields
             $('#listPaymentMethod').val(method);
             
             // Adjust container classes for cash layout
             $('#newPaymentMethod').parent().parent().removeClass('col-xs-6');
             $('#newPaymentMethod').parent().parent().addClass('col-xs-4');
             
             // Create cash payment fields
             var cashHtml = '<div class="col-xs-4">' +
                           '<div class="form-group">' +
                           '<label>Customer Cash</label>' +
                           '<div class="input-group">' +
                           '<span class="input-group-addon"><i class="fa fa-money"></i></span>' +
                           '<input type="text" class="form-control" id="newCashValue" placeholder="0.00" required>' +
                           '</div>' +
                           '</div>' +
                           '</div>' +
                           '<div class="col-xs-4" id="getCashChange" style="padding-left:0px">' +
                           '<div class="form-group">' +
                           '<label>Change</label>' +
                           '<div class="input-group">' +
                           '<span class="input-group-addon"><i class="fa fa-money"></i></span>' +
                           '<input type="text" class="form-control" id="newCashChange" name="newCashChange" placeholder="0.00" readonly required>' +
                           '</div>' +
                           '</div>' +
                           '</div>';
             
             $('.paymentMethodBoxes').html(cashHtml);
             
             // Initialize number formatting and change calculation
             $("#newCashValue, #newCashChange").number(true, 2);
             
             // Listen for cash value changes
             $("#newCashValue").change(function () {
                 var cashValue = parseFloat($(this).val()) || 0;
                 var totalPrice = parseFloat($('#saleTotal').val()) || 0;
                 var change = cashValue - totalPrice;
                 
                 if (change >= 0) {
                     $('#newCashChange').val(change.toFixed(2));
                 } else {
                     $('#newCashChange').val('0.00');
                 }
             });
             
             // Focus on cash input
             setTimeout(function() {
                 $("#newCashValue").focus();
             }, 100);
             
             console.log('Cash payment selected:', method, '- showing cash fields');
         } else {
             // For other payments, clear all additional options
             $('#listPaymentMethod').val(method);
             
             // Reset container classes for normal layout
             $('#newPaymentMethod').parent().parent().removeClass('col-xs-4');
             $('#newPaymentMethod').parent().parent().addClass('col-xs-6');
             
             // Clear payment method boxes
             $('.paymentMethodBoxes').empty();
             
             console.log('Other payment selected:', method, '- no additional options needed');
         }
    });
    
    // Add form submission debugging
    $('.saleForm').on('submit', function(e) {
        console.log('Form being submitted');
        console.log('installmentMonths value:', $('#installmentMonths').val());
        console.log('installmentInterest value:', $('#installmentInterest').val());
        console.log('listPaymentMethod value:', $('#listPaymentMethod').val());
        console.log('newPaymentMethod value:', $('#newPaymentMethod').val());
        
        // Don't prevent submission, just log
    });
});
</script>