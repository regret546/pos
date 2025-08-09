<div class="content-wrapper">

  <section class="content-header">

    <h1>

      Edit Sale

    </h1>

    <ol class="breadcrumb">

      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>

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


                ?>

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
					<!-- Log on to codeastro.com for more projects! -->
                    <div class="row">

                      <!--=====================================
                        TOTAL INPUT
                      ======================================-->

                      <div class="col-xs-6 pull-right">

                        <div class="form-group">
                          <label>Total</label>
                          <div class="input-group">
                            
                            <span class="input-group-addon">₱</span>
                            
                            <input type="number" class="form-control" name="newSaleTotal" id="newSaleTotal" placeholder="00000" totalSale="<?php echo $sale["totalPrice"]; ?>" value="<?php echo $sale["totalPrice"]; ?>" readonly required>

                            <input type="hidden" name="saleTotal" id="saleTotal" value="<?php echo $sale["totalPrice"]; ?>" required>
                            <input type="hidden" name="newTaxPrice" id="newTaxPrice" value="<?php echo $sale["tax"]; ?>" required>

                          </div>
                        </div>
                        
                      </div>

                      <hr>
                      
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
                              <option value="CC" <?php if($sale["paymentMethod"] == "CC") echo "selected"; ?>>Credit Card</option>
                              <option value="DC" <?php if($sale["paymentMethod"] == "DC") echo "selected"; ?>>Debit Card</option>
                              <option value="installment" <?php if(strpos($sale["paymentMethod"], "installment") !== false) echo "selected"; ?>>Installment</option>

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
              
              <table class="table table-bordered table-hover table-striped dt-responsive salesTable">
                  
                <thead>

                   <tr>
                     
                     <th style="width:10px">#</th>
                     <th>Image</th>
                     <th style="width:30px">Code</th>
                     <th>Description</th>
                     <th>Stock</th>
                     <th>Actions</th>
					<!-- Log on to codeastro.com for more projects! -->
                   </tr> 

                </thead>

              </table>

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
$(document).ready(function() {
    
    // Set initial payment method value
    var currentPaymentMethod = '<?php echo $sale["paymentMethod"]; ?>';
    $('#listPaymentMethod').val(currentPaymentMethod);
    
    // If it's an installment, trigger the installment display on page load
    if (currentPaymentMethod.indexOf('installment') !== -1) {
        $('#newPaymentMethod').trigger('change');
    }
    
    $('#newPaymentMethod').change(function() {
        var method = $(this).val();
        
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
                if (months) {
                    $('#installmentInterestDiv').show();
                    $('#listPaymentMethod').val('installment_' + months);
                } else {
                    $('#installmentInterestDiv').hide();
                    $('#listPaymentMethod').val('');
                }
            });
            
            $(document).on('input', '#installmentInterest', function() {
                updateInstallmentSummary();
            });
            
            // Function to update installment summary
            function updateInstallmentSummary() {
                var interest = parseFloat($('#installmentInterest').val()) || 0;
                var months = parseInt($('#installmentMonths').val()) || 0;
                var total = parseFloat($('#saleTotal').val()) || 0;
                
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
                                        '<p><strong>Example Amount:</strong> ₱' + exampleTotal.toFixed(2) + '</p>' +
                                        '<p><strong>Interest Rate:</strong> ' + interest + '% (' + months + ' months)</p>' +
                                        '<p><strong>Interest Amount:</strong> ₱' + totalInterestAmount.toFixed(2) + '</p>' +
                                        '<p style="font-size: 16px; color: #00a65a;"><strong>Total Amount:</strong> ₱' + totalWithInterest.toFixed(2) + '</p>' +
                                        '<p style="font-size: 16px; color: #3c8dbc;"><strong>Monthly Payment:</strong> ₱' + monthlyPayment.toFixed(2) + '</p>';
                    }
                    
                    // Wait a moment for DOM to be ready, then update
                    setTimeout(function() {
                        var summaryDiv = $('#installmentSummaryDiv');
                        var summaryContent = $('#installmentSummary');
                        
                        if (summaryDiv.length > 0 && summaryContent.length > 0) {
                            summaryContent.html(summaryHTML);
                            summaryDiv.show();
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