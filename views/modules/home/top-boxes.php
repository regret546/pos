<?php

$item = null;
$value = null;
$order = "id";

// Sales statistics by payment method
$cashSales = ControllerSales::ctrSalesByPaymentMethod("cash");
$qrphSales = ControllerSales::ctrSalesByPaymentMethod("QRPH");
$cardSales = ControllerSales::ctrSalesByPaymentMethod("Card");

// Installment statistics
$installmentStats = ControllerSales::ctrInstallmentStats();

// Completed sales total (excluding pending installments)
$completedSales = ControllerSales::ctrCompletedSalesTotal();

$categories = ControllerCategories::ctrShowCategories($item, $value);
$totalCategories = count($categories);

$customers = ControllerCustomers::ctrShowCustomers($item, $value);
$totalCustomers = count($customers);

// Get products and filter same as DataTable (only products with valid categories)
$allProducts = ControllerProducts::ctrShowProducts($item, $value, $order);
$validProducts = array();

// Filter products same way as DataTable does
foreach($allProducts as $product) {
  // Check if product has valid category (same logic as datatable-products.ajax.php)
  if(isset($product["idCategory"])) {
    $categoryItem = "id";
    $categoryValue = $product["idCategory"];
    $categories = ControllerCategories::ctrShowCategories($categoryItem, $categoryValue);
    
    // Only include products with valid categories
    if(is_array($categories) && isset($categories["Category"])) {
      $validProducts[] = $product;
    }
  }
}

$totalProducts = count($validProducts);

// Calculate inventory statistics from valid products only
$totalInventoryItems = 0;
$totalInventoryValue = 0;

foreach($validProducts as $product) {
  $stock = floatval($product["stock"]);
  $sellingPrice = floatval($product["sellingPrice"]);
  
  $totalInventoryItems += $stock;
  $totalInventoryValue += ($stock * $sellingPrice);
}

?>

<!-- Payment Method Statistics Row -->
<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-green">
    <div class="inner">
      <h3>₱<?php echo number_format($completedSales["total"] ? $completedSales["total"] : 0, 2); ?></h3>
      <p>Total Completed Sales</p>
    </div>
    <div class="icon">
      <i class="fa fa-money"></i>
    </div>
    <a href="sales" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>

<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-blue">
    <div class="inner">
      <h3>₱<?php echo number_format($cashSales["total"] ? $cashSales["total"] : 0, 2); ?></h3>
      <p>Cash Sales</p>
    </div>
    <div class="icon">
      <i class="fa fa-money"></i>
    </div>
    <a href="sales" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>

<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-aqua">
    <div class="inner">
      <h3>₱<?php echo number_format($qrphSales["total"] ? $qrphSales["total"] : 0, 2); ?></h3>
      <p>QRPH Sales</p>
    </div>
    <div class="icon">
      <i class="fa fa-qrcode"></i>
    </div>
    <a href="sales" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>

<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-purple">
    <div class="inner">
      <h3>₱<?php echo number_format($cardSales["total"] ? $cardSales["total"] : 0, 2); ?></h3>
      <p>Card Sales</p>
    </div>
    <div class="icon">
      <i class="fa fa-credit-card"></i>
    </div>
    <a href="sales" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>

<!-- Installment Statistics Row -->
<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-yellow">
    <div class="inner">
      <h3>₱<?php echo number_format($installmentStats["paid_total"] ? $installmentStats["paid_total"] : 0, 2); ?></h3>
      <p>Paid Installments</p>
    </div>
    <div class="icon">
      <i class="fa fa-check-circle"></i>
    </div>
    <a href="installments" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>

<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-red">
    <div class="inner">
      <h3>₱<?php echo number_format($installmentStats["pending_total"] ? $installmentStats["pending_total"] : 0, 2); ?></h3>
      <p>Pending Installments</p>
    </div>
    <div class="icon">
      <i class="fa fa-clock-o"></i>
    </div>
    <a href="installments" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>

<!-- General Statistics Row -->
<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-maroon">
    <div class="inner">
      <h3><?php echo number_format($totalInventoryItems); ?></h3>
      <p>Items in Inventory</p>
    </div>
    <div class="icon">
      <i class="fa fa-cubes"></i>
    </div>
    <a href="products" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>

<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-orange">
    <div class="inner">
      <h3>₱<?php echo number_format($totalInventoryValue, 2); ?></h3>
      <p>Total Inventory Value</p>
    </div>
    <div class="icon">
      <i class="fa fa-calculator"></i>
    </div>
    <a href="products" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>

<div class="col-lg-3 col-xs-6">
  <div class="small-box bg-navy">
    <div class="inner">
      <h3><?php echo number_format($totalCustomers); ?></h3>
      <p>Total Customers</p>
    </div>
    <div class="icon">
      <i class="ion ion-person-add"></i>
    </div>
    <a href="customers" class="small-box-footer">
      More info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>
</div>