/*=============================================
LOAD DYNAMIC PRODUCTS TABLE
=============================================*/

// $.ajax({

// 	url: "ajax/datatable-products.ajax.php",
// 	success:function(answer){

// 		console.log("answer", answer);

// 	}

// })

$(".salesTable").DataTable({
  ajax: "ajax/datatable-sales.ajax.php",
  deferRender: true,
  retrieve: true,
  processing: true,
});

/*=============================================
ADDING PRODUCTS TO THE SALE FROM THE TABLE
=============================================*/

$(".salesTable tbody").on("click", "button.addProductSale", function () {
  var idProduct = $(this).attr("idProduct");

  $(this).removeClass("btn-primary addProductSale");

  $(this).addClass("btn-default");

  var datum = new FormData();
  datum.append("idProduct", idProduct);

  $.ajax({
    url: "ajax/products.ajax.php",
    method: "POST",
    data: datum,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (answer) {
      var description = answer["description"];
      var stock = answer["stock"];
      var price = answer["sellingPrice"];

      /*=============================================
          	AVOID ADDING THE PRODUCT WHEN ITS STOCK IS ZERO
          	=============================================*/

      if (stock == 0) {
        swal({
          title: "There's no stock available",
          type: "error",
          confirmButtonText: "¡Close!",
        });

        $("button[idProduct='" + idProduct + "']").addClass(
          "btn-primary addProductSale"
        );

        return;
      }

      $(".newProduct").append(
        '<div class="row" style="padding:5px 15px">' +
          "<!-- Product description -->" +
          '<div class="col-xs-6" style="padding-right:0px">' +
          '<div class="input-group">' +
          '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct="' +
          idProduct +
          '"><i class="fa fa-times"></i></button></span>' +
          '<input type="text" class="form-control newProductDescription" idProduct="' +
          idProduct +
          '" name="addProductSale" value="' +
          description +
          '" readonly required>' +
          "</div>" +
          "</div>" +
          "<!-- Product quantity -->" +
          '<div class="col-xs-3">' +
          '<input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="1" stock="' +
          stock +
          '" newStock="' +
          Number(stock - 1) +
          '" required>' +
          "</div>" +
          "<!-- product price -->" +
          '<div class="col-xs-3 enterPrice" style="padding-left:0px">' +
          '<div class="input-group">' +
          '<span class="input-group-addon">₱</span>' +
          '<input type="text" class="form-control newProductPrice" realPrice="' +
          price +
          '" name="newProductPrice" value="' +
          price +
          '" readonly required>' +
          "</div>" +
          "</div>" +
          "</div>"
      );

      // ADDING TOTAL PRICES

      addingTotalPrices();

      // ADD TAX

      addTax();

      // GROUP PRODUCTS IN JSON FORMAT

      listProducts();

      // FORMAT PRODUCT PRICE

      $(".newProductPrice").number(true, 2);
    },
  });
});

/*=============================================
WHEN TABLE LOADS EVERYTIME THAT NAVIGATE IN IT
=============================================*/

$(".salesTable").on("draw.dt", function () {
  if (localStorage.getItem("removeProduct") != null) {
    var listIdProducts = JSON.parse(localStorage.getItem("removeProduct"));

    for (var i = 0; i < listIdProducts.length; i++) {
      $(
        "button.recoverButton[idProduct='" +
          listIdProducts[i]["idProduct"] +
          "']"
      ).removeClass("btn-default");
      $(
        "button.recoverButton[idProduct='" +
          listIdProducts[i]["idProduct"] +
          "']"
      ).addClass("btn-primary addProductSale");
    }
  }
});

/*=============================================
REMOVE PRODUCTS FROM THE SALE AND RECOVER BUTTON
=============================================*/

var idRemoveProduct = [];

localStorage.removeItem("removeProduct");

$(".saleForm").on("click", "button.removeProduct", function () {
  console.log("$(this)", $(this));
  $(this).parent().parent().parent().parent().remove();

  console.log("idProduct", idProduct);
  var idProduct = $(this).attr("idProduct");

  /*=============================================
	STORE IN LOCALSTORAGE THE ID OF THE PRODUCT WE WANT TO DELETE
	=============================================*/

  if (localStorage.getItem("removeProduct") == null) {
    idRemoveProduct = [];
  } else {
    idRemoveProduct.concat(localStorage.getItem("removeProduct"));
  }

  idRemoveProduct.push({ idProduct: idProduct });

  localStorage.setItem("removeProduct", JSON.stringify(idRemoveProduct));

  $("button.recoverButton[idProduct='" + idProduct + "']").removeClass(
    "btn-default"
  );

  $("button.recoverButton[idProduct='" + idProduct + "']").addClass(
    "btn-primary addProductSale"
  );

  if ($(".newProducto").children().length == 0) {
    $("#newTaxSale").val(0);
    $("#newTotalSale").val(0);
    $("#totalSale").val(0);
    $("#newTotalSale").attr("totalSale", 0);
  } else {
    // ADDING TOTAL PRICES

    addingTotalPrices();

    // ADD TAX

    addTax();

    // GROUP PRODUCTS IN JSON FORMAT

    listProducts();
  }
});

/*=============================================
ADDING PRODUCT FROM A DEVICE
=============================================*/

var numProduct = 0;

$(".btnAddProduct").click(function () {
  numProduct++;

  var datum = new FormData();
  datum.append("getProducts", "ok");

  $.ajax({
    url: "ajax/products.ajax.php",
    method: "POST",
    data: datum,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (answer) {
      $(".newProduct").append(
        '<div class="row" style="padding:5px 15px">' +
          "<!-- Product description -->" +
          '<div class="col-xs-6" style="padding-right:0px">' +
          '<div class="input-group">' +
          '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct><i class="fa fa-times"></i></button></span>' +
          '<select class="form-control newProductDescription" id="product' +
          numProduct +
          '" idProduct name="newProductDescription" required>' +
          "<option>Select product</option>" +
          "</select>" +
          "</div>" +
          "</div>" +
          "<!-- Product quantity -->" +
          '<div class="col-xs-3 enterQuantity">' +
          '<input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="1" stock newStock required>' +
          "</div>" +
          "<!-- Product price -->" +
          '<div class="col-xs-3 enterPrice" style="padding-left:0px">' +
          '<div class="input-group">' +
          '<span class="input-group-addon">₱</span>' +
          '<input type="text" class="form-control newProductPrice" realPrice="" name="newProductPrice" readonly required>' +
          "</div>" +
          "</div>" +
          "</div>"
      );

      // ADDING PRODUCTS TO THE SELECT

      answer.forEach(functionForEach);

      function functionForEach(item, index) {
        if (item.stock != 0) {
          $("#product" + numProduct).append(
            '<option idProduct="' +
              item.id +
              '" value="' +
              item.description +
              '">' +
              item.description +
              "</option>"
          );
        }
      }

      // ADDING TOTAL PRICES

      addingTotalPrices();

      // ADD TAX

      addTax();

      // SET FORMAT TO THE PRODUCT PRICE

      $(".newProductPrice").number(true, 2);
    },
  });
});

/*=============================================
SELECT PRODUCT
=============================================*/

$(".saleForm").on("change", "select.newProductDescription", function () {
  var productName = $(this).val();

  var newProductDescription = $(this)
    .parent()
    .parent()
    .parent()
    .children()
    .children()
    .children(".newProductDescription");

  var newProductPrice = $(this)
    .parent()
    .parent()
    .parent()
    .children(".enterPrice")
    .children()
    .children(".newProductPrice");

  var newProductQuantity = $(this)
    .parent()
    .parent()
    .parent()
    .children(".enterQuantity")
    .children(".newProductQuantity");

  var datum = new FormData();
  datum.append("productName", productName);

  $.ajax({
    url: "ajax/products.ajax.php",
    method: "POST",
    data: datum,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (answer) {
      $(newProductDescription).attr("idProduct", answer["id"]);
      $(newProductQuantity).attr("stock", answer["stock"]);
      $(newProductQuantity).attr("newStock", Number(answer["stock"]) - 1);
      $(newProductPrice).val(answer["sellingPrice"]);
      $(newProductPrice).attr("realPrice", answer["sellingPrice"]);

      // GROUP PRODUCTS IN JSON FORMAT

      listProducts();
    },
  });
});

/*=============================================
MODIFY QUANTITY
=============================================*/

$(".saleForm").on("change", "input.newProductQuantity", function () {
  var price = $(this)
    .parent()
    .parent()
    .children(".enterPrice")
    .children()
    .children(".newProductPrice");

  var finalPrice = $(this).val() * price.attr("realPrice");

  price.val(finalPrice);

  var newStock = Number($(this).attr("stock")) - $(this).val();

  $(this).attr("newStock", newStock);

  console.log('$(this).attr("stock")', $(this).attr("stock"));
  if (Number($(this).val()) > Number($(this).attr("stock"))) {
    /*=============================================
		IF QUANTITY IS MORE THAN THE STOCK VALUE SET INITIAL VALUES
		=============================================*/

    $(this).val(1);

    var finalPrice = $(this).val() * price.attr("realPrice");

    price.val(finalPrice);

    addingTotalPrices();

    swal({
      title: "The quantity is more than your stock",
      text: "¡There's only" + $(this).attr("stock") + " units!",
      type: "error",
      confirmButtonText: "Close!",
    });

    return;
  }

  // ADDING TOTAL PRICES

  addingTotalPrices();

  // ADD TAX

  addTax();

  // GROUP PRODUCTS IN JSON FORMAT

  listProducts();
});
/*============================================
PRICES ADDITION
=============================================*/

function addingTotalPrices() {
  var priceItem = $(".newProductPrice");
  var arrayAdditionPrice = [];

  for (var i = 0; i < priceItem.length; i++) {
    arrayAdditionPrice.push(Number($(priceItem[i]).val()));
  }

  function additionArray(total, number) {
    return total + number;
  }

  var addingTotalPrice = arrayAdditionPrice.reduce(additionArray);

  $("#newSaleTotal").val(addingTotalPrice);
  $("#saleTotal").val(addingTotalPrice);
  $("#newSaleTotal").attr("totalSale", addingTotalPrice);
}

/*=============================================
ADD TAX
=============================================*/

function addTax() {
  var tax = Number($("#newTaxSale").val());
  var totalPrice = Number($("#saleTotal").val());

  var totalTax = (totalPrice * tax) / 100;
  totalPrice = totalPrice + totalTax;

  $("#newTaxPrice").val(totalTax);
  $("#newTaxPrice").attr("taxValue", totalTax);
  $("#newSaleTotal").val(totalPrice);
  $("#saleTotal").val(totalPrice);

  // If cash payment is active, update change
  if (
    $("#newPaymentMethod").val() === "cash" &&
    $("#newCashValue").length > 0
  ) {
    updateCashChange();
  }
}

/*=============================================
WHEN TAX CHANGES
=============================================*/

$("#newTaxSale").change(function () {
  listProducts();
});

/*=============================================
FINAL PRICE FORMAT
=============================================*/

$("#newSaleTotal").number(true, 2);

/*=============================================
SELECT PAYMENT METHOD
=============================================*/

$("#newPaymentMethod").change(function () {
  var method = $(this).val();

  if (method == "cash") {
    $(this).parent().parent().removeClass("col-xs-6");
    $(this).parent().parent().addClass("col-xs-4");

    $(".paymentMethodBoxes").html(
      '<div class="col-xs-4">' +
        '<div class="form-group">' +
        "<label>Customer Cash</label>" +
        '<div class="input-group">' +
        '<span class="input-group-addon"><i class="fa fa-money"></i></span>' +
        '<input type="text" class="form-control" id="newCashValue" placeholder="0.00" required>' +
        "</div>" +
        "</div>" +
        "</div>" +
        '<div class="col-xs-4" id="getCashChange" style="padding-left:0px">' +
        '<div class="form-group">' +
        "<label>Change</label>" +
        '<div class="input-group">' +
        '<span class="input-group-addon"><i class="fa fa-money"></i></span>' +
        '<input type="text" class="form-control" id="newCashChange" name="newCashChange" placeholder="0.00" readonly required>' +
        "</div>" +
        "</div>" +
        "</div>"
    );

    // Initialize number formatting
    $("#newCashValue").number(true, 2);
    $("#newCashChange").number(true, 2);

    // Listen for cash value changes
    $("#newCashValue").change(function () {
      var cash = $(this).val();
      var change = Number(cash) - Number($("#saleTotal").val());
      $("#newCashChange").val(change);
    });
  } else {
    $(this).parent().parent().removeClass("col-xs-4");
    $(this).parent().parent().addClass("col-xs-6");

    $(".paymentMethodBoxes").html(
      '<div class="col-xs-6" style="padding-left:0px">' +
        '<div class="input-group">' +
        '<input type="text" class="form-control" id="newTransactionCode" placeholder="Transaction code" required>' +
        '<span class="input-group-addon"><i class="fa fa-lock"></i></span>' +
        "</div>" +
        "</div>"
    );
  }

  listMethods();
});

/*=============================================
LIST PAYMENT METHOD
=============================================*/
function listMethods() {
  var paymentMethod = $("#newPaymentMethod").val();

  if (paymentMethod == "cash") {
    $("#listPaymentMethod").val("cash");
  } else {
    var transactionCode = $("#newTransactionCode").val();
    $("#listPaymentMethod").val(paymentMethod + "-" + transactionCode);
  }
}

/*=============================================
CASH CHANGE
=============================================*/
function updateCashChange() {
  // Get the input values and clean them
  var cashInput = $("#newCashValue").val() || "0";
  var totalInput = $("#saleTotal").val() || "0";

  // Remove commas and convert to numbers with 2 decimal precision
  var cash = parseFloat(cashInput.replace(/,/g, ""));
  var total = parseFloat(totalInput.replace(/,/g, ""));

  // Ensure we have valid numbers
  cash = isNaN(cash) ? 0 : cash;
  total = isNaN(total) ? 0 : total;

  // Calculate change with 2 decimal precision
  var change = (Math.round((cash - total) * 100) / 100).toFixed(2);

  if (parseFloat(change) < 0) {
    swal({
      type: "error",
      title: "Invalid Cash Amount",
      text: "Cash amount must be greater than or equal to total amount. Change cannot be negative.",
      showConfirmButton: true,
      confirmButtonText: "Close",
    });
    return false;
  }

  // Update the change field
  $("#newCashChange").val(change);

  return true;
}

/*=============================================
EDIT SALE BUTTON
=============================================*/
$(".tables").on("click", ".btnEditSale", function () {
  var idSale = $(this).attr("idSale");

  window.location = "index.php?route=edit-sale&idSale=" + idSale;
});

/*=============================================
FUNCTION TO DEACTIVATE "ADD" BUTTONS WHEN THE PRODUCT HAS BEEN SELECTED IN THE FOLDER
=============================================*/

function removeAddProductSale() {
  //We capture all the products' id that were selected in the sale
  var idProducts = $(".removeProduct");

  //We capture all the buttons to add that appear in the table
  var tableButtons = $(".salesTable tbody button.addProductSale");

  //We navigate the cycle to get the different idProducts that were added to the sale
  for (var i = 0; i < idProducts.length; i++) {
    //We capture the IDs of the products added to the sale
    var button = $(idProducts[i]).attr("idProduct");

    //We go over the table that appears to deactivate the "add" buttons
    for (var j = 0; j < tableButtons.length; j++) {
      if ($(tableButtons[j]).attr("idProduct") == button) {
        $(tableButtons[j]).removeClass("btn-primary addProductSale");
        $(tableButtons[j]).addClass("btn-default");
      }
    }
  }
}

/*=============================================
EVERY TIME THAT THE TABLE IS LOADED WHEN WE NAVIGATE THROUGH IT EXECUTES A FUNCTION
=============================================*/

$(".salesTable").on("draw.dt", function () {
  removeAddProductSale();
});

/*=============================================
DELETE SALE
=============================================*/
$(".tables").on("click", ".btnDeleteSale", function () {
  var idSale = $(this).attr("idSale");

  swal({
    title: "Are you sure you want to delete this?",
    text: "If you're not, you can cancel!",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    cancelButtonText: "Cancel",
    confirmButtonText: "Yes, delete it!",
  }).then(function (result) {
    if (result.value) {
      window.location = "index.php?route=sales&idSale=" + idSale;
    }
  });
});

/*=============================================
PRINT BILL
=============================================*/

$(".tables").on("click", ".btnPrintBill", function () {
  var saleCode = $(this).attr("saleCode");

  window.open("extensions/tcpdf/pdf/bill.php?code=" + saleCode, "_blank");
});

/*=============================================
DATES RANGE
=============================================*/

$("#daterange-btn").daterangepicker(
  {
    ranges: {
      Today: [moment(), moment()],
      Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
      "Last 7 days": [moment().subtract(6, "days"), moment()],
      "Last 30 days": [moment().subtract(29, "days"), moment()],
      "this month": [moment().startOf("month"), moment().endOf("month")],
      "Last month": [
        moment().subtract(1, "month").startOf("month"),
        moment().subtract(1, "month").endOf("month"),
      ],
    },
    startDate: moment(),
    endDate: moment(),
  },
  function (start, end) {
    $("#daterange-btn span").html(
      start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY")
    );

    var initialDate = start.format("YYYY-MM-DD");

    var finalDate = end.format("YYYY-MM-DD");

    var captureRange = $("#daterange-btn span").html();

    localStorage.setItem("captureRange", captureRange);
    console.log("localStorage", localStorage);

    window.location =
      "index.php?route=sales&initialDate=" +
      initialDate +
      "&finalDate=" +
      finalDate;
  }
);

/*=============================================
CANCEL DATES RANGE
=============================================*/

$(".daterangepicker.opensleft .range_inputs .cancelBtn").on(
  "click",
  function () {
    localStorage.removeItem("captureRange");
    localStorage.clear();
    window.location = "sales";
  }
);

/*=============================================
CAPTURE TODAY'S BUTTON
=============================================*/

$(".daterangepicker.opensleft .ranges li").on("click", function () {
  var todayButton = $(this).attr("data-range-key");

  if (todayButton == "Today") {
    var d = new Date();

    var day = d.getDate();
    var month = d.getMonth() + 1;
    var year = d.getFullYear();

    if (month < 10) {
      var initialDate = year + "-0" + month + "-" + day;
      var finalDate = year + "-0" + month + "-" + day;
    } else if (day < 10) {
      var initialDate = year + "-" + month + "-0" + day;
      var finalDate = year + "-" + month + "-0" + day;
    } else if (month < 10 && day < 10) {
      var initialDate = year + "-0" + month + "-0" + day;
      var finalDate = year + "-0" + month + "-0" + day;
    } else {
      var initialDate = year + "-" + month + "-" + day;
      var finalDate = year + "-" + month + "-" + day;
    }

    localStorage.setItem("captureRange", "Today");

    window.location =
      "index.php?route=sales&initialDate=" +
      initialDate +
      "&finalDate=" +
      finalDate;
  }
});

/*=============================================
OPEN XML FILE IN A NEW TAB
=============================================*/

$(".openXML").click(function () {
  var file = $(this).attr("file");
  window.open(file, "_blank");
});

/*=============================================
LIST ALL THE PRODUCTS
=============================================*/

function listProducts() {
  var productsList = [];
  var description = $(".newProductDescription");
  var quantity = $(".newProductQuantity");
  var price = $(".newProductPrice");
  var totalSale = 0;

  for (var i = 0; i < description.length; i++) {
    var totalPrice =
      Number($(quantity[i]).val()) * Number($(price[i]).attr("realPrice"));
    totalSale += totalPrice;

    productsList.push({
      id: $(description[i]).attr("idProduct"),
      description: $(description[i]).val(),
      quantity: $(quantity[i]).val(),
      stock: $(quantity[i]).attr("newStock"),
      price: $(price[i]).attr("realPrice"),
      totalPrice: totalPrice,
    });
  }

  $("#productsList").val(JSON.stringify(productsList));
  $("#saleTotal").val(totalSale);
}

// Add event listeners for quantity changes
$(".saleForm").on("change", "input.newProductQuantity", function () {
  listProducts();
});

// Add event listeners for product removal
$(".saleForm").on("click", "button.removeProduct", function () {
  $(this).closest(".row").remove();
  listProducts();
});

/*=============================================
RESTORE PRODUCT TO SALE
=============================================*/
function addProductToSale(product) {
  var description = product.description;
  var stock = product.stock;
  var price = product.price;
  var idProduct = product.id;

  $(".newProduct").append(
    '<div class="row" style="padding:5px 15px">' +
      "<!-- Product description -->" +
      '<div class="col-xs-6" style="padding-right:0px">' +
      '<div class="input-group">' +
      '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct="' +
      idProduct +
      '"><i class="fa fa-times"></i></button></span>' +
      '<input type="text" class="form-control newProductDescription" idProduct="' +
      idProduct +
      '" name="addProductSale" value="' +
      description +
      '" readonly required>' +
      "</div>" +
      "</div>" +
      "<!-- Product quantity -->" +
      '<div class="col-xs-3">' +
      '<input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="' +
      product.quantity +
      '" stock="' +
      stock +
      '" newStock="' +
      Number(stock - product.quantity) +
      '" required>' +
      "</div>" +
      "<!-- product price -->" +
      '<div class="col-xs-3 enterPrice" style="padding-left:0px">' +
      '<div class="input-group">' +
      '<span class="input-group-addon">₱</span>' +
      '<input type="text" class="form-control newProductPrice" realPrice="' +
      price +
      '" name="newProductPrice" value="' +
      price +
      '" readonly required>' +
      "</div>" +
      "</div>" +
      "</div>"
  );

  // ADDING TOTAL PRICES
  addingTotalPrices();

  // ADD TAX
  addTax();

  // GROUP PRODUCTS IN JSON FORMAT
  listProducts();

  // FORMAT PRODUCT PRICE
  $(".newProductPrice").number(true, 2);
}

// Add form submission handler
$(".saleForm").on("submit", function (e) {
  e.preventDefault();

  // Validate if products have been added
  if ($(".newProduct").children().length === 0) {
    swal({
      type: "error",
      title: "The sale must contain at least one product",
      showConfirmButton: true,
      confirmButtonText: "Close",
    });
    return false;
  }

  // Validate if customer is selected
  if ($("#selectCustomer").val() === "") {
    swal({
      type: "error",
      title: "You must select a customer",
      showConfirmButton: true,
      confirmButtonText: "Close",
    });
    return false;
  }

  // Validate if payment method is selected
  if ($("#newPaymentMethod").val() === "") {
    swal({
      type: "error",
      title: "You must select a payment method",
      showConfirmButton: true,
      confirmButtonText: "Close",
    });
    return false;
  }

  // For cash payments, validate that change is not negative
  if ($("#newPaymentMethod").val() === "cash") {
    var cash = Number($("#newCashValue").val());
    var total = Number($("#saleTotal").val());

    if (cash < total) {
      swal({
        type: "error",
        title: "Invalid Cash Amount",
        text: "Cash amount must be greater than or equal to total amount",
        showConfirmButton: true,
        confirmButtonText: "Close",
      });
      return false;
    }
  }

  // Set payment method
  $("#listPaymentMethod").val($("#newPaymentMethod").val());

  // Submit form
  this.submit();
});

/*=============================================
CALCULATE CHANGE
=============================================*/

$("#customerCash").change(function () {
  var cash = $(this).val();
  var total = $("#saleTotal").val();

  if (cash >= total) {
    var change = Number(cash) - Number(total);
    $("#cashChange").val(change.toFixed(2));
  } else {
    $("#cashChange").val("");
  }
});

/*=============================================
SHOW/HIDE PAYMENT METHOD BASED ON CUSTOMER SELECTION
=============================================*/

$(document).ready(function () {
  // Hide payment method section initially
  $(".payment-method-section").hide();

  // Show/hide payment method when customer is selected
  $("#selectCustomer").change(function () {
    if ($(this).val() !== "") {
      $(".payment-method-section").show();
    } else {
      $(".payment-method-section").hide();
      $("#newPaymentMethod").val(""); // Reset payment method when hidden
      $(".paymentMethodBoxes").empty(); // Clear any payment method specific fields
    }
  });
});
