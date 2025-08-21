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

  /* Payment Summary Styling */
  .payment-summary-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 0;
  }

  .payment-summary-header {
    background: rgba(255,255,255,0.15);
    color: white;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
  }

  .payment-summary-header i {
    margin-right: 8px;
    opacity: 0.9;
  }

  .payment-summary-content {
    padding: 16px;
    color: white;
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    font-size: 13px;
  }

  .summary-row:last-child {
    border-bottom: none;
  }

  .summary-label {
    font-weight: 500;
    color: rgba(255,255,255,0.9);
    flex: 1;
  }

  .summary-value {
    font-weight: 600;
    color: white;
    text-align: right;
    margin-left: 10px;
  }

  .total-row {
    background: rgba(255,255,255,0.1);
    margin: 8px -16px 8px -16px;
    padding: 12px 16px;
    font-size: 14px;
  }

  .total-row .summary-label,
  .total-row .summary-value {
    font-weight: 700;
    color: #fff;
  }

  .monthly-row {
    background: rgba(76, 175, 80, 0.2);
    margin: 8px -16px -16px -16px;
    padding: 12px 16px;
    font-size: 14px;
  }

  .monthly-row .summary-label,
  .monthly-row .summary-value {
    font-weight: 700;
    color: #fff;
  }

  .example-header {
    background: rgba(255,193,7,0.2);
    color: #fff;
    padding: 8px 12px;
    margin: -16px -16px 12px -16px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
  }

  .preview-notice {
    background-color: #fff3cd !important;
    border-color: #ffeaa7 !important;
    color: #856404 !important;
  }

  .calculating-placeholder {
    text-align: center;
    color: rgba(255,255,255,0.8);
    font-style: italic;
    padding: 20px;
  }

  .calculating-placeholder i {
    margin-right: 8px;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .summary-row {
      flex-direction: column;
      align-items: flex-start;
    }
    
    .summary-value {
      text-align: left;
      margin-left: 0;
      margin-top: 4px;
      font-size: 14px;
    }
  }

  /* Customer Search Dropdown Styles */
  .customer-selection-container {
    position: relative;
  }

  .customer-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    max-height: 200px;
    overflow-y: auto;
  }

  .customer-dropdown-content {
    padding: 0;
  }

  .customer-item {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
  }

  .customer-item:hover {
    background-color: #f5f5f5;
  }

  .customer-item:last-child {
    border-bottom: none;
  }

  .customer-item.selected {
    background-color: #337ab7;
    color: white;
  }

  .customer-name {
    font-weight: 600;
    color: #333;
  }

  .customer-details {
    font-size: 12px;
    color: #666;
    margin-top: 2px;
  }

  .customer-item.selected .customer-name,
  .customer-item.selected .customer-details {
    color: white;
  }

  .no-results {
    padding: 15px;
    text-align: center;
    color: #999;
    font-style: italic;
  }
</style>

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
                  
                    
                    <div class="form-group">

                      <div class="customer-selection-container">
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-users"></i></span>
                          <input type="text" class="form-control" id="customerSearchInput" placeholder="Search customers by name..." autocomplete="off">
                          <span class="input-group-addon">
                              <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modalAddCustomer" data-dismiss="modal">
                                  <i class="fa fa-plus"></i> Add
                              </button>
                          </span>
                        </div>
                        
                        <!-- Hidden dropdown for customer selection -->
                        <div class="customer-dropdown" id="customerDropdown" style="display: none;">
                          <div class="customer-dropdown-content">
                            <div class="no-results" style="display: none;">No customers found</div>
                          </div>
                        </div>
                        
                        <!-- Hidden select for form submission -->
                        <select name="selectCustomer" id="selectCustomer" style="display: none;" required>
                          <option value="">Select Customer</option>
                          <?php 
                          $item = null;
                          $value = null;
                          $customers = ControllerCustomers::ctrShowCustomers($item, $value);
                          foreach ($customers as $key => $value) {
                            echo '<option value="'.$value["id"].'" data-name="'.$value["name"].'" data-email="'.$value["email"].'" data-phone="'.$value["phone"].'">'.$value["name"].'</option>';
                          }
                          ?>
                        </select>
                      </div>

                    </div>
					
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

                    <div class="form-group">
                      <label>Payment Method</label>
                      <div class="row">
                        <div class="col-xs-12">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                            <select class="form-control" name="newPaymentMethod" id="newPaymentMethod" required>
                              <option value="">-Select Payment Method-</option>
                              <option value="cash">💵 Cash Payment</option>
                              <option value="QRPH">📱 QRPH Payment</option>
                              <option value="Card">💳 Debit/Credit Card</option>
                              <option value="installment">📅 Installment Plan</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      
                      <div class="row paymentMethodBoxes" style="margin-top: 15px;">
                        <!-- Payment method specific options will be inserted here -->
                      </div>

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

<!--====  End of module add Customer  ====-->

<script>
// Function to show transaction field for electronic payments - define globally
window.showTransactionField = function(paymentMethod) {
    console.log('showTransactionField called for:', paymentMethod);
    
    // Clear any existing content first
    $('.paymentMethodBoxes').empty();
    
    var fieldLabel = paymentMethod === 'QRPH' ? 'QRPH Transaction Code' : 'Card Transaction Code';
    var placeholder = paymentMethod === 'QRPH' ? 'Enter QRPH transaction reference' : 'Enter card transaction reference';
    
    var html = '<div class="col-xs-6">' +
               '<label for="transactionCode">' + fieldLabel + ':</label>' +
               '<div class="input-group">' +
               '<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>' +
               '<input type="text" class="form-control" name="transactionCode" id="transactionCode" ' +
               'placeholder="' + placeholder + '" required>' +
               '</div>' +
               '</div>' +
               '<div class="col-xs-6" id="electronicPaymentSummaryDiv" style="margin-top: 10px;">' +
               '<div class="payment-summary-container">' +
               '<div class="payment-summary-header">' +
               '<i class="fa fa-calculator"></i> Payment Summary' +
               '</div>' +
               '<div id="electronicPaymentSummary">' +
               '<div class="calculating-placeholder"><i class="fa fa-spinner fa-spin"></i> Calculating...</div>' +
               '</div>' +
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

// Function to update electronic payment summary
window.updateElectronicPaymentSummary = function(paymentMethod) {
    var total = parseFloat($('#saleTotal').val()) || 0;
    var discount = parseFloat($('#saleDiscount').val()) || 0;
    var subtotal = total + discount; // Calculate original subtotal before discount
    var paymentTypeLabel = paymentMethod === 'QRPH' ? 'QRPH Payment' : 'Card Payment';
    var paymentIcon = paymentMethod === 'QRPH' ? 'fa-qrcode' : 'fa-credit-card';
    
    if (total > 0 || discount > 0) {
        var summaryHTML = '<div class="payment-summary-content">' +
            '<div class="summary-row">' +
                '<span class="summary-label"><i class="fa ' + paymentIcon + '"></i> Payment Method:</span>' +
                '<span class="summary-value">' + paymentTypeLabel + '</span>' +
            '</div>' +
            '<div class="summary-row">' +
                '<span class="summary-label">Subtotal:</span>' +
                '<span class="summary-value">₱' + subtotal.toFixed(2) + '</span>' +
            '</div>';
            
        if (discount > 0) {
            summaryHTML += '<div class="summary-row">' +
                '<span class="summary-label">Discount:</span>' +
                '<span class="summary-value">-₱' + discount.toFixed(2) + '</span>' +
            '</div>';
        }
        
        summaryHTML += '<div class="summary-row">' +
                '<span class="summary-label">Processing Fee:</span>' +
                '<span class="summary-value">₱0.00</span>' +
            '</div>' +
            '<div class="summary-row total-row">' +
                '<span class="summary-label">Total Amount:</span>' +
                '<span class="summary-value">₱' + total.toFixed(2) + '</span>' +
            '</div>' +
        '</div>';
    } else {
        var exampleDiscount = 100;
        var exampleSubtotal = 1000;
        var exampleTotal = exampleSubtotal - exampleDiscount;
        
        var summaryHTML = '<div class="alert alert-warning preview-notice" style="margin-bottom: 15px; padding: 8px 12px; font-size: 12px; border-radius: 4px;">' +
                        '<i class="fa fa-info-circle"></i> <strong>Preview:</strong> Add products to see actual calculation' +
                    '</div>' +
                    '<div class="payment-summary-content">' +
                        '<div class="example-header">Example Calculation (₱1,000 - ₱100 discount)</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label"><i class="fa ' + paymentIcon + '"></i> Payment Method:</span>' +
                            '<span class="summary-value">' + paymentTypeLabel + '</span>' +
                        '</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label">Subtotal:</span>' +
                            '<span class="summary-value">₱' + exampleSubtotal.toFixed(2) + '</span>' +
                        '</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label">Discount:</span>' +
                            '<span class="summary-value">-₱' + exampleDiscount.toFixed(2) + '</span>' +
                        '</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label">Processing Fee:</span>' +
                            '<span class="summary-value">₱0.00</span>' +
                        '</div>' +
                        '<div class="summary-row total-row">' +
                            '<span class="summary-label">Total Amount:</span>' +
                            '<span class="summary-value">₱' + exampleTotal.toFixed(2) + '</span>' +
                        '</div>' +
                    '</div>';
    }
    
    setTimeout(function() {
        var summaryDiv = $('#electronicPaymentSummaryDiv');
        var summaryContent = $('#electronicPaymentSummary');
        
        if (summaryDiv.length > 0 && summaryContent.length > 0) {
            summaryContent.html(summaryHTML);
            summaryDiv.show();
        }
    }, 100);
};

// Function to update cash payment summary
window.updateCashPaymentSummary = function() {
    var total = parseFloat($('#saleTotal').val()) || 0;
    var discount = parseFloat($('#saleDiscount').val()) || 0;
    var subtotal = total + discount; // Calculate original subtotal before discount
    var cashValue = parseFloat($('#newCashValue').val()) || 0;
    var change = Math.max(0, cashValue - total);
    
    if (total > 0 || discount > 0) {
        var summaryHTML = '<div class="payment-summary-content">' +
            '<div class="summary-row">' +
                '<span class="summary-label"><i class="fa fa-money"></i> Payment Method:</span>' +
                '<span class="summary-value">Cash Payment</span>' +
            '</div>' +
            '<div class="summary-row">' +
                '<span class="summary-label">Subtotal:</span>' +
                '<span class="summary-value">₱' + subtotal.toFixed(2) + '</span>' +
            '</div>';
            
        if (discount > 0) {
            summaryHTML += '<div class="summary-row">' +
                '<span class="summary-label">Discount:</span>' +
                '<span class="summary-value">-₱' + discount.toFixed(2) + '</span>' +
            '</div>';
        }
        
        summaryHTML += '<div class="summary-row">' +
                '<span class="summary-label">Total Amount:</span>' +
                '<span class="summary-value">₱' + total.toFixed(2) + '</span>' +
            '</div>' +
            '<div class="summary-row">' +
                '<span class="summary-label">Cash Received:</span>' +
                '<span class="summary-value">₱' + cashValue.toFixed(2) + '</span>' +
            '</div>' +
            '<div class="summary-row total-row">' +
                '<span class="summary-label">Change Due:</span>' +
                '<span class="summary-value">₱' + change.toFixed(2) + '</span>' +
            '</div>' +
        '</div>';
    } else {
        var exampleDiscount = 100;
        var exampleSubtotal = 1000;
        var exampleTotal = exampleSubtotal - exampleDiscount;
        
        var summaryHTML = '<div class="alert alert-warning preview-notice" style="margin-bottom: 15px; padding: 8px 12px; font-size: 12px; border-radius: 4px;">' +
                        '<i class="fa fa-info-circle"></i> <strong>Preview:</strong> Add products to see actual calculation' +
                    '</div>' +
                    '<div class="payment-summary-content">' +
                        '<div class="example-header">Example Calculation (₱1,000 - ₱100 discount)</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label"><i class="fa fa-money"></i> Payment Method:</span>' +
                            '<span class="summary-value">Cash Payment</span>' +
                        '</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label">Subtotal:</span>' +
                            '<span class="summary-value">₱' + exampleSubtotal.toFixed(2) + '</span>' +
                        '</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label">Discount:</span>' +
                            '<span class="summary-value">-₱' + exampleDiscount.toFixed(2) + '</span>' +
                        '</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label">Total Amount:</span>' +
                            '<span class="summary-value">₱' + exampleTotal.toFixed(2) + '</span>' +
                        '</div>' +
                        '<div class="summary-row">' +
                            '<span class="summary-label">Cash Received:</span>' +
                            '<span class="summary-value">₱' + cashValue.toFixed(2) + '</span>' +
                        '</div>' +
                        '<div class="summary-row total-row">' +
                            '<span class="summary-label">Change Due:</span>' +
                            '<span class="summary-value">₱' + Math.max(0, cashValue - exampleTotal).toFixed(2) + '</span>' +
                        '</div>' +
                    '</div>';
    }
    
    setTimeout(function() {
        var summaryDiv = $('#cashPaymentSummaryDiv');
        var summaryContent = $('#cashPaymentSummary');
        
        if (summaryDiv.length > 0 && summaryContent.length > 0) {
            summaryContent.html(summaryHTML);
            summaryDiv.show();
        }
    }, 100);
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
                       '<div class="col-xs-12" id="installmentSummaryDiv" style="display: none; margin-top: 15px;">' +
                       '<div class="payment-summary-container">' +
                       '<div class="payment-summary-header">' +
                       '<i class="fa fa-calculator"></i> Payment Summary' +
                       '</div>' +
                       '<div id="installmentSummary">' +
                       '<div class="calculating-placeholder"><i class="fa fa-spinner fa-spin"></i> Calculating...</div>' +
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
                        
                        var summaryHTML = '<div class="payment-summary-content">' +
                            '<div class="summary-row">' +
                                '<span class="summary-label">Original Amount:</span>' +
                                '<span class="summary-value">₱' + total.toFixed(2) + '</span>' +
                            '</div>';
                        
                        if (hasDownpayment && downpayment > 0) {
                            summaryHTML += '<div class="summary-row">' +
                                '<span class="summary-label">Downpayment:</span>' +
                                '<span class="summary-value">₱' + downpayment.toFixed(2) + '</span>' +
                            '</div>' +
                            '<div class="summary-row">' +
                                '<span class="summary-label">Remaining for Installment:</span>' +
                                '<span class="summary-value">₱' + remainingAmount.toFixed(2) + '</span>' +
                            '</div>';
                        }
                        
                        summaryHTML += '<div class="summary-row">' +
                                '<span class="summary-label">Interest Rate:</span>' +
                                '<span class="summary-value">' + interest + '% (' + months + ' months)</span>' +
                            '</div>' +
                            '<div class="summary-row">' +
                                '<span class="summary-label">Interest Amount:</span>' +
                                '<span class="summary-value">₱' + totalInterestAmount.toFixed(2) + '</span>' +
                            '</div>' +
                            '<div class="summary-row total-row">' +
                                '<span class="summary-label">Total to Pay:</span>' +
                                '<span class="summary-value">₱' + (total + totalInterestAmount).toFixed(2) + '</span>' +
                            '</div>' +
                            '<div class="summary-row monthly-row">' +
                                '<span class="summary-label">Monthly Payment:</span>' +
                                '<span class="summary-value">₱' + monthlyPayment.toFixed(2) + '</span>' +
                            '</div>' +
                        '</div>';
                    } else {
                        // Show example with 1000 pesos when no products added yet
                        var exampleTotal = 1000;
                        var exampleDownpayment = hasDownpayment ? 200 : 0;
                        var remainingAmount = exampleTotal - exampleDownpayment;
                        var totalWithInterest = remainingAmount * (1 + (monthlyInterest * months));
                        var monthlyPayment = totalWithInterest / months;
                        var totalInterestAmount = totalWithInterest - remainingAmount;
                        var finalTotalToPay = exampleDownpayment + totalWithInterest;
                        
                        var summaryHTML = '<div class="alert alert-warning preview-notice" style="margin-bottom: 15px; padding: 8px 12px; font-size: 12px; border-radius: 4px;">' +
                                        '<i class="fa fa-info-circle"></i> <strong>Preview:</strong> Add products to see actual calculation' +
                                    '</div>' +
                                    '<div class="payment-summary-content">' +
                                        '<div class="example-header">Example Calculation (₱1,000)</div>';
                        
                        if (hasDownpayment) {
                            summaryHTML += '<div class="summary-row">' +
                                '<span class="summary-label">Example Downpayment:</span>' +
                                '<span class="summary-value">₱' + exampleDownpayment.toFixed(2) + '</span>' +
                            '</div>' +
                            '<div class="summary-row">' +
                                '<span class="summary-label">Remaining for Installment:</span>' +
                                '<span class="summary-value">₱' + remainingAmount.toFixed(2) + '</span>' +
                            '</div>';
                        }
                        
                        summaryHTML += '<div class="summary-row">' +
                                '<span class="summary-label">Interest Rate:</span>' +
                                '<span class="summary-value">' + interest + '% (' + months + ' months)</span>' +
                            '</div>' +
                            '<div class="summary-row">' +
                                '<span class="summary-label">Interest Amount:</span>' +
                                '<span class="summary-value">₱' + totalInterestAmount.toFixed(2) + '</span>' +
                            '</div>' +
                            '<div class="summary-row total-row">' +
                                '<span class="summary-label">Total to Pay:</span>' +
                                '<span class="summary-value">₱' + (exampleTotal + totalInterestAmount).toFixed(2) + '</span>' +
                            '</div>' +
                            '<div class="summary-row monthly-row">' +
                                '<span class="summary-label">Monthly Payment:</span>' +
                                '<span class="summary-value">₱' + monthlyPayment.toFixed(2) + '</span>' +
                            '</div>' +
                        '</div>';
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
                            var manualSummaryHtml = '<div class="col-xs-12" id="installmentSummaryDiv" style="display: block !important; margin-top: 15px;">' +
                                                  '<div class="payment-summary-container">' +
                                                  '<div class="payment-summary-header">' +
                                                  '<i class="fa fa-calculator"></i> Payment Summary' +
                                                  '</div>' +
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
                var currentMethod = $('#newPaymentMethod').val();
                if (currentMethod === 'installment' && $('#installmentSummaryDiv').is(':visible')) {
                    updateInstallmentSummary();
                } else if ((currentMethod === 'QRPH' || currentMethod === 'Card') && $('#electronicPaymentSummaryDiv').is(':visible')) {
                    updateElectronicPaymentSummary(currentMethod);
                } else if (currentMethod === 'cash' && $('#cashPaymentSummaryDiv').is(':visible')) {
                    updateCashPaymentSummary();
                }
            });
            
            // Monitor discount changes
            $(document).on('input change', '#saleDiscount', function() {
                var currentMethod = $('#newPaymentMethod').val();
                if ((currentMethod === 'QRPH' || currentMethod === 'Card') && $('#electronicPaymentSummaryDiv').is(':visible')) {
                    updateElectronicPaymentSummary(currentMethod);
                } else if (currentMethod === 'cash' && $('#cashPaymentSummaryDiv').is(':visible')) {
                    updateCashPaymentSummary();
                }
            });
        } else if (method === 'QRPH' || method === 'Card') {
            // For QRPH and Card payments, show transaction field
            $('#listPaymentMethod').val(method);
            
            console.log('Electronic payment selected:', method, '- showing transaction field');
            console.log('About to call showTransactionField for:', method);
            
            // Add a small delay to ensure DOM is ready
            setTimeout(function() {
                showTransactionField(method);
                console.log('showTransactionField call completed');
                // Update electronic payment summary after field is shown
                updateElectronicPaymentSummary(method);
            }, 50);
        } else if (method === 'cash') {
            console.log('Cash payment detected - adding cash fields');
            $('#listPaymentMethod').val(method);
            
            // Add cash payment fields
            var html = '<div class="col-xs-6">' +
                       '<label for="newCashValue">Customer Cash:</label>' +
                       '<div class="input-group">' +
                       '<span class="input-group-addon">₱</span>' +
                       '<input type="number" class="form-control" name="newCashValue" id="newCashValue" ' +
                       'placeholder="0.00" min="0" step="0.01" required>' +
                       '</div>' +
                       '<br>' +
                       '<label for="newCashChange">Change:</label>' +
                       '<div class="input-group">' +
                       '<span class="input-group-addon">₱</span>' +
                       '<input type="text" class="form-control" name="newCashChange" id="newCashChange" ' +
                       'placeholder="0.00" readonly>' +
                       '</div>' +
                       '</div>' +
                       '<div class="col-xs-6" id="cashPaymentSummaryDiv" style="margin-top: 10px;">' +
                       '<div class="payment-summary-container">' +
                       '<div class="payment-summary-header">' +
                       '<i class="fa fa-calculator"></i> Payment Summary' +
                       '</div>' +
                       '<div id="cashPaymentSummary">' +
                       '<div class="calculating-placeholder"><i class="fa fa-spinner fa-spin"></i> Calculating...</div>' +
                       '</div>' +
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
                
                // Update cash payment summary
                updateCashPaymentSummary();
            });
            
            // Initial summary update
            updateCashPaymentSummary();
        } else {
            // For other payments, clear all additional options
            $('#listPaymentMethod').val(method);
            
            // Clear payment method boxes
            $('.paymentMethodBoxes').empty();
            
            console.log('Other payment selected:', method, '- no additional options needed');
        }
    });
    
    // Customer search dropdown functionality
    var customerData = [];
    
    // Build customer data array from select options
    $('#selectCustomer option').each(function() {
        if ($(this).val() !== '') {
            customerData.push({
                id: $(this).val(),
                name: $(this).data('name') || $(this).text(),
                email: $(this).data('email') || '',
                phone: $(this).data('phone') || ''
            });
        }
    });
    
    // Search input event handler
    $('#customerSearchInput').on('input focus', function() {
        var searchTerm = $(this).val().toLowerCase().trim();
        showCustomerDropdown(searchTerm);
    });
    
    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.customer-selection-container').length) {
            $('#customerDropdown').hide();
        }
    });
    
    // Handle keyboard navigation
    $('#customerSearchInput').on('keydown', function(e) {
        var dropdown = $('#customerDropdown');
        if (dropdown.is(':visible')) {
            var items = dropdown.find('.customer-item');
            var selected = items.filter('.selected');
            
            if (e.keyCode === 38) { // Up arrow
                e.preventDefault();
                if (selected.length) {
                    var prev = selected.prev('.customer-item');
                    if (prev.length) {
                        selected.removeClass('selected');
                        prev.addClass('selected');
                    }
                } else {
                    items.last().addClass('selected');
                }
            } else if (e.keyCode === 40) { // Down arrow
                e.preventDefault();
                if (selected.length) {
                    var next = selected.next('.customer-item');
                    if (next.length) {
                        selected.removeClass('selected');
                        next.addClass('selected');
                    }
                } else {
                    items.first().addClass('selected');
                }
            } else if (e.keyCode === 13) { // Enter
                e.preventDefault();
                if (selected.length) {
                    selectCustomer(selected.data('customer-id'), selected.find('.customer-name').text());
                }
            } else if (e.keyCode === 27) { // Escape
                dropdown.hide();
            }
        }
    });
    
    function showCustomerDropdown(searchTerm) {
        var dropdown = $('#customerDropdown');
        var content = dropdown.find('.customer-dropdown-content');
        var noResults = content.find('.no-results');
        
        // Clear existing items
        content.find('.customer-item').remove();
        
        var matchedCustomers = [];
        
        if (searchTerm === '') {
            // Show all customers if no search term
            matchedCustomers = customerData;
        } else {
            // Filter customers based on search term
            matchedCustomers = customerData.filter(function(customer) {
                return customer.name.toLowerCase().includes(searchTerm) ||
                       customer.email.toLowerCase().includes(searchTerm) ||
                       customer.phone.toLowerCase().includes(searchTerm);
            });
        }
        
        if (matchedCustomers.length > 0) {
            noResults.hide();
            
            // Add customer items to dropdown
            matchedCustomers.forEach(function(customer) {
                var customerItem = $('<div class="customer-item" data-customer-id="' + customer.id + '">' +
                    '<div class="customer-name">' + customer.name + '</div>' +
                    '<div class="customer-details">' + 
                    (customer.email ? 'Email: ' + customer.email : '') +
                    (customer.email && customer.phone ? ' | ' : '') +
                    (customer.phone ? 'Phone: ' + customer.phone : '') +
                    '</div>' +
                    '</div>');
                
                customerItem.on('click', function() {
                    selectCustomer(customer.id, customer.name);
                });
                
                content.append(customerItem);
            });
            
            dropdown.show();
        } else if (searchTerm !== '') {
            // Show no results message
            content.find('.customer-item').remove();
            noResults.show();
            dropdown.show();
        } else {
            dropdown.hide();
        }
    }
    
    function selectCustomer(customerId, customerName) {
        $('#selectCustomer').val(customerId);
        $('#customerSearchInput').val(customerName);
        $('#customerDropdown').hide();
        
        // Trigger change event for any other handlers
        $('#selectCustomer').trigger('change');
    }
});
</script>