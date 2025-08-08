<?php

if($_SESSION["profile"] == "Special"){

  echo '<script>

    window.location = "home";

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

      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>

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
                        <!-- Hidden field for total calculation -->
                        <input type="hidden" name="saleTotal" id="saleTotal" value="0">
                        <!-- Hidden field for tax calculation -->
                        <input type="hidden" name="newTaxPrice" id="newTaxPrice" value="0">
                        <!-- Hidden field for tax percentage -->
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
                              <option value="CC">Credit Card</option>
                              <option value="DC">Debit Card</option>
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
$(document).ready(function() {
    console.log('Create sale page loaded - Payment method handler ready');
    
    $('#newPaymentMethod').change(function() {
        var method = $(this).val();
        console.log('Payment method changed to:', method);
        
        // Clear payment method boxes
        $('.paymentMethodBoxes').empty();
        
        if (method === 'installment') {
            console.log('Installment selected - adding month options');
            
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
                       '<div class="col-xs-12" id="installmentInterestDiv" style="display: none; margin-top: 10px;">' +
                       '<label for="installmentInterest">Interest Rate (%):</label>' +
                       '<div class="input-group">' +
                       '<span class="input-group-addon"><i class="fa fa-percent"></i></span>' +
                       '<input type="number" class="form-control" name="installmentInterest" id="installmentInterest" ' +
                       'min="0" max="100" step="0.01" placeholder="Enter interest rate" required>' +
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
                console.log('Months selected:', months);
                if (months) {
                    $('#installmentInterestDiv').show();
                    $('#listPaymentMethod').val('installment_' + months);
                } else {
                    $('#installmentInterestDiv').hide();
                    $('#listPaymentMethod').val('');
                }
            });
            
            $(document).on('input', '#installmentInterest', function() {
                console.log('DEBUG - Interest input detected, calling updateInstallmentSummary');
                updateInstallmentSummary();
            });
            
            // Function to update installment summary
            function updateInstallmentSummary() {
                var interest = parseFloat($('#installmentInterest').val()) || 0;
                var months = parseInt($('#installmentMonths').val()) || 0;
                var total = parseFloat($('#saleTotal').val()) || 0;
                
                console.log('DEBUG - Interest:', interest, 'Months:', months, 'Total:', total);
                console.log('DEBUG - saleTotal element exists:', $('#saleTotal').length > 0);
                console.log('DEBUG - saleTotal value:', $('#saleTotal').val());
                
                // Show summary even if total is 0 for demonstration purposes
                if (interest >= 0 && months > 0) {
                    var monthlyInterest = interest / 100;
                    
                    if (total > 0) {
                        // Calculate with actual total
                        var totalWithInterest = total * (1 + (monthlyInterest * months));
                        var monthlyPayment = totalWithInterest / months;
                        var totalInterestAmount = totalWithInterest - total;
                        
                        var summaryHTML = '<p><strong>Original Amount:</strong> ₱' + total.toFixed(2) + '</p>' +
                                        '<p><strong>Interest Rate:</strong> ' + interest + '% (' + months + ' months)</p>' +
                                        '<p><strong>Interest Amount:</strong> ₱' + totalInterestAmount.toFixed(2) + '</p>' +
                                        '<p style="font-size: 16px; color: #00a65a;"><strong>Total Amount:</strong> ₱' + totalWithInterest.toFixed(2) + '</p>' +
                                        '<p style="font-size: 16px; color: #3c8dbc;"><strong>Monthly Payment:</strong> ₱' + monthlyPayment.toFixed(2) + '</p>';
                    } else {
                        // Show example with 1000 pesos when no products added yet
                        var exampleTotal = 1000;
                        var totalWithInterest = exampleTotal * (1 + (monthlyInterest * months));
                        var monthlyPayment = totalWithInterest / months;
                        var totalInterestAmount = totalWithInterest - exampleTotal;
                        
                        var summaryHTML = '<div class="alert alert-warning" style="margin-bottom: 10px;"><small><strong>Preview:</strong> Add products to see actual calculation</small></div>' +
                                        '<p><strong>Example (₱1,000):</strong></p>' +
                                        '<p><strong>Interest Rate:</strong> ' + interest + '% (' + months + ' months)</p>' +
                                        '<p><strong>Interest Amount:</strong> ₱' + totalInterestAmount.toFixed(2) + '</p>' +
                                        '<p style="font-size: 16px; color: #00a65a;"><strong>Total Amount:</strong> ₱' + totalWithInterest.toFixed(2) + '</p>' +
                                        '<p style="font-size: 16px; color: #3c8dbc;"><strong>Monthly Payment:</strong> ₱' + monthlyPayment.toFixed(2) + '</p>';
                    }
                    
                    // Wait a moment for DOM to be ready, then update
                    setTimeout(function() {
                        var summaryDiv = $('#installmentSummaryDiv');
                        var summaryContent = $('#installmentSummary');
                        
                        console.log('DEBUG - Element exists check:', summaryDiv.length, summaryContent.length);
                        
                        if (summaryDiv.length > 0 && summaryContent.length > 0) {
                            summaryContent.html(summaryHTML);
                            summaryDiv.show();
                            summaryDiv.css({
                                'display': 'block !important',
                                'visibility': 'visible',
                                'opacity': '1'
                            });
                            
                            console.log('DEBUG - Summary displayed successfully');
                            console.log('DEBUG - installmentSummaryDiv visible:', summaryDiv.is(':visible'));
                            console.log('DEBUG - installmentSummaryDiv height:', summaryDiv.height());
                            console.log('DEBUG - installmentSummary content length:', summaryContent.html().length);
                        } else {
                            console.log('DEBUG - Elements not found in DOM, creating manually');
                            // Create the summary div manually and append it
                            var manualSummaryHtml = '<div class="col-xs-12" id="installmentSummaryDiv" style="display: block !important; margin-top: 10px;">' +
                                                  '<div class="alert alert-info" style="margin-bottom: 0; background-color: #f9f9f9; border: 1px solid #ddd; color: #333;">' +
                                                  '<strong>Payment Summary:</strong>' +
                                                  '<div id="installmentSummary">' + summaryHTML + '</div>' +
                                                  '</div>' +
                                                  '</div>';
                            $('.paymentMethodBoxes').append(manualSummaryHtml);
                            console.log('DEBUG - Manual summary created and appended');
                        }
                    }, 200);
                } else {
                    console.log('DEBUG - Hiding summary - Invalid input');
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
        } else {
            // For non-installment payments
            $('#listPaymentMethod').val(method);
        }
    });
});
</script>