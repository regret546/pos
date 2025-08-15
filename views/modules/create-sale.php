<?php

if($_SESSION["profile"] == "Special"){

  echo '<script>

    window.location = "index.php?route=home";

  </script>';

  return;

}

?>
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

      Sales Management

    </h1>

    <ol class="breadcrumb">

      <li><a href="index.php?route=home"><i class="fa fa-dashboard"></i> Home</a></li>

      <li class="active">Create Sale</li>

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

                    <!--=====================================
                    =            SELLER INPUT           =
                    ======================================-->
                  
                    
                    <div class="form-group">

                      <div class="input-group">
                        
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>

                        <input type="text" class="form-control" name="newSeller" id="newSeller" value="<?php echo $_SESSION["name"]; ?>" readonly>

                        <input type="hidden" name="idSeller" value="<?php echo $_SESSION["id"]; ?>">

                      </div>

                    </div>


                    <!--=====================================
                    CODE INPUT
                    ======================================-->
                  
                    
                    <div class="form-group">

                      <div class="input-group">
                        
                        <span class="input-group-addon"><i class="fa fa-key"></i></span>
                        

                        <?php 
                          $item = null;
                          $value = null;

                          $sales = ControllerSales::ctrShowSales($item, $value);

                          if(!$sales){

                            echo '<input type="text" class="form-control" name="newSale" id="newSale" value="10001" readonly>';
                          }
                          else{

                            foreach ($sales as $key => $value) {
                              
                            }

                            $code = $value["code"] +1;

                            echo '<input type="text" class="form-control" name="newSale" id="newSale" value="'.$code.'" readonly>';

                          }

                        ?>

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
                          
                            <option value="">Select Customer</option>

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

                    </div>
					<!-- Log on to codeastro.com for more projects! -->
                    <!--=====================================
                    =            PRODUCT INPUT           =
                    ======================================-->
                  
                    
                    <div class="form-group row newProduct">


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
                                    <input type="number" class="form-control" name="saleDiscount" id="saleDiscount" placeholder="0.00" min="0" step="0.01" value="0">
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
                                    <input type="text" class="form-control" id="totalDisplay" placeholder="0.00" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden fields for calculations -->
                        <input type="hidden" name="saleTotal" id="saleTotal" value="0">
                        <input type="hidden" name="newTaxPrice" id="newTaxPrice" value="0">
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
                              <option value="cash">Cash</option>
                              <option value="QRPH">QRPH</option>
                              <option value="Card">Debit/Credit Card</option>
                              <option value="installment">Installment</option>

                          </select>

                        </div>

                      </div>

                      <div class="paymentMethodBoxes"></div>

                      <input type="hidden" name="listPaymentMethod" id="listPaymentMethod" required>

                    </div>

                    <br>
                    
                </div>

            </div>

            <div class="box-footer">
              <button type="submit" class="btn btn-success pull-right">Save Sale</button>
            </div>
          </form>

          <?php

            $saveSale = new ControllerSales();
            $saveSale -> ctrCreateSale();
            
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

<!-- Log on to codeastro.com for more projects! -->
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
                <input class="form-control input-lg" type="text" name="newPhone" placeholder="phone" required>
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
      </form>

      <?php

        $createCustomer = new ControllerCustomers();
        $createCustomer -> ctrCreateCustomer();

      ?>
    </div>

  </div>
</div>
<!-- Log on to codeastro.com for more projects! -->
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
    console.log('PaymentMethodBoxes content:', $('.paymentMethodBoxes').html());
    console.log('PaymentMethodBoxes visible:', $('.paymentMethodBoxes').is(':visible'));
    console.log('PaymentMethodBoxes display style:', $('.paymentMethodBoxes').css('display'));
    console.log('TransactionCode field visible:', $('#transactionCode').is(':visible'));
    console.log('TransactionCode field display style:', $('#transactionCode').css('display'));
};

$(document).ready(function() {
    
    // Initialize total display and calculations
    setTimeout(function() {
        addingTotalPrices();
        addTax();
    }, 100);
    
    $('#newPaymentMethod').change(function() {
        var method = $(this).val();
        
        console.log('Create-sale payment method changed to:', method);
        
        // Clear payment method boxes
        $('.paymentMethodBoxes').empty();
        
        if (method === 'installment') {
            // Reset container classes for installment layout
            $('#newPaymentMethod').parent().parent().removeClass('col-xs-4');
            $('#newPaymentMethod').parent().parent().addClass('col-xs-6');
            
            // Add installment options to payment method boxes
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
            
            $('.paymentMethodBoxes').html(html);
            $('#listPaymentMethod').val('');
            
            // Add event handlers for the new elements
            $(document).on('change', '#installmentMonths', function() {
                var months = $(this).val();
                if (months) {
                    $('#downpaymentDiv').show();
                    $('#installmentInterestDiv').show();
                    $('#installmentFrequencyDiv').show();
                    $('#listPaymentMethod').val('installment_' + months);
                } else {
                    $('#downpaymentDiv').hide();
                    $('#installmentInterestDiv').hide();
                    $('#installmentFrequencyDiv').hide();
                    $('#listPaymentMethod').val('');
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
            
            // Monitor changes to sale total for installment summary updates
            var originalListProducts = window.listProducts;
            if (typeof originalListProducts === 'function') {
                window.listProducts = function() {
                    originalListProducts.apply(this, arguments);
                    // Update installment summary after total is recalculated
                    if ($('#newPaymentMethod').val() === 'installment' && $('#installmentSummaryDiv').is(':visible')) {
                        setTimeout(updateInstallmentSummary, 100);
                    }
                };
            }
            
            // Also monitor direct changes to saleTotal input
            $(document).on('change', '#saleTotal', function() {
                if ($('#newPaymentMethod').val() === 'installment' && $('#installmentSummaryDiv').is(':visible')) {
                    updateInstallmentSummary();
                }
            });
        } else if (method === 'QRPH' || method === 'Card') {
            // For QRPH and Card payments, show transaction field
            $('#listPaymentMethod').val(method);
            
            // Reset container classes for non-cash layout
            $('#newPaymentMethod').parent().parent().removeClass('col-xs-4');
            $('#newPaymentMethod').parent().parent().addClass('col-xs-6');
            
            console.log('Electronic payment selected:', method, '- showing transaction field');
            console.log('About to call showTransactionField for:', method);
            
            // Add a small delay to ensure DOM is ready
            setTimeout(function() {
                showTransactionField(method);
                console.log('showTransactionField call completed');
            }, 50);
        } else if (method === 'cash') {
            console.log('Cash payment detected - adding cash fields');
            $('#listPaymentMethod').val(method);
            
            // Reset container classes for cash layout
            $('#newPaymentMethod').parent().parent().removeClass('col-xs-4');
            $('#newPaymentMethod').parent().parent().addClass('col-xs-6');
            
            // Add cash payment fields
            var html = '<div class="col-xs-6">' +
                       '<label for="newCashValue">Customer Cash:</label>' +
                       '<div class="input-group">' +
                       '<span class="input-group-addon">₱</span>' +
                       '<input type="number" class="form-control" name="newCashValue" id="newCashValue" ' +
                       'placeholder="0.00" min="0" step="0.01" required>' +
                       '</div>' +
                       '</div>' +
                       '<div class="col-xs-6">' +
                       '<label for="newCashChange">Change:</label>' +
                       '<div class="input-group">' +
                       '<span class="input-group-addon">₱</span>' +
                       '<input type="text" class="form-control" name="newCashChange" id="newCashChange" ' +
                       'placeholder="0.00" readonly>' +
                       '</div>' +
                       '</div>';
            
            $('.paymentMethodBoxes').html(html);
            
            // Add event handler for cash change calculation
            $(document).on('input change', '#newCashValue', function() {
                var cashValue = parseFloat($(this).val()) || 0;
                var totalPrice = parseFloat($('#saleTotal').val()) || 0;
                var change = cashValue - totalPrice;
                
                if (change >= 0) {
                    $('#newCashChange').val(change.toFixed(2));
                } else {
                    $('#newCashChange').val('0.00');
                }
            });
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
});
</script>