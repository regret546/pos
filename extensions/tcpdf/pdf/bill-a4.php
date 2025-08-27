<?php
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

require_once "../../../controllers/sales.controller.php";
require_once "../../../models/sales.model.php";

require_once "../../../controllers/customers.controller.php";
require_once "../../../models/customers.model.php";

require_once "../../../controllers/users.controller.php";
require_once "../../../models/users.model.php";

require_once "../../../controllers/products.controller.php";
require_once "../../../models/products.model.php";

// Use Composer's autoloader for TCPDF
require_once "../../vendor/autoload.php";

class printBill{

public $code;

// Generate the receipt content as HTML
private function generateReceiptHTML($saledate, $valueSale, $answerCustomer, $answerSeller, $products, $subtotal, $discount, $saleTotalPrice, $peso, $copyNumber = ""){

$copyLabel = $copyNumber ? " - $copyNumber" : "";

$block1 = <<<EOF

	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="width:15%; vertical-align:top; border:1px solid #000; padding:8px;">
				<img src="images/company-logo.png" style="width:60px; height:60px;">
			</td>
			<td style="width:35%; vertical-align:top; border:1px solid #000; padding:8px;">
				<div style="font-size:14px; font-weight:bold; margin-bottom:8px;">
					DELIVERY RECEIPT$copyLabel
				</div>
				<br>
				<div style="font-size:10px; font-weight:bold;">
					Siargao Computer Trading
				</div>
				<div style="font-size:8px;">
					Pob. 12, Dapa, Siargao Island
				</div>
				<div style="font-size:8px;">
					9286127206
				</div>
			</td>
			<td style="width:25%; vertical-align:top; border:1px solid #000; padding:4px;">
				<table style="width:100%; border-collapse: collapse;">
					<tr>
						<td style="border:1px solid #000; padding:4px; font-size:8px; background-color:#B3D9FF; text-align:center; font-weight:bold;">DATE</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:4px; font-size:8px; text-align:center;">$saledate</td>
					</tr>
				</table>
				<br>
				<table style="width:100%; border-collapse: collapse;">
					<tr>
						<td style="border:1px solid #000; padding:4px; font-size:8px; background-color:#B3D9FF; text-align:center; font-weight:bold;">RECEIPT NO.</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:4px; font-size:8px; text-align:center;">$valueSale</td>
					</tr>
				</table>
			</td>
			<td style="width:25%; vertical-align:top; border:1px solid #000; padding:4px;">
				<table style="width:100%; border-collapse: collapse;">
					<tr>
						<td style="border:1px solid #000; padding:4px; font-size:8px; background-color:#B3D9FF; text-align:center; font-weight:bold;">RECIPIENT INFORMATION</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:2px; font-size:7px; font-weight:bold;">Name</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:2px; font-size:7px;">$answerCustomer[name]</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:2px; font-size:7px; font-weight:bold;">Address</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:2px; font-size:7px;">$answerCustomer[address]</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:2px; font-size:7px; font-weight:bold;">Contact No.</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:2px; font-size:7px;">$answerCustomer[phone]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<br>

EOF;

$block2 = <<<EOF

	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="border:1px solid #000; padding:6px; font-size:9px; font-weight:bold; text-align:center; width:15%; background-color:#B3D9FF;">MODEL</td>
			<td style="border:1px solid #000; padding:6px; font-size:9px; font-weight:bold; text-align:center; width:35%; background-color:#B3D9FF;">DESCRIPTION</td>
			<td style="border:1px solid #000; padding:6px; font-size:9px; font-weight:bold; text-align:center; width:10%; background-color:#B3D9FF;">QTY</td>
			<td style="border:1px solid #000; padding:6px; font-size:9px; font-weight:bold; text-align:center; width:20%; background-color:#B3D9FF;">AMOUNT</td>
			<td style="border:1px solid #000; padding:6px; font-size:9px; font-weight:bold; text-align:center; width:20%; background-color:#B3D9FF;">TOTAL</td>
		</tr>

EOF;

$block3 = '';
foreach ($products as $key => $item) {
	$valueProduct = $item["id"];
	$orderProduct = "id";

	$answerProduct = ControllerProducts::ctrShowProducts($orderProduct, $valueProduct, null);

	// Format the prices properly
	$itemPrice = number_format($item["price"], 2);
	$itemTotalPrice = number_format($item["totalPrice"], 2);

	// Get product model if available
	$productCode = isset($answerProduct["model"]) ? $answerProduct["model"] : 'N/A';

	$block3 .= <<<EOF
		<tr>
			<td style="border:1px solid #000; padding:6px; font-size:8px; text-align:center;">$productCode</td>
			<td style="border:1px solid #000; padding:6px; font-size:8px;">$answerProduct[description]</td>
			<td style="border:1px solid #000; padding:6px; font-size:8px; text-align:center;">$item[quantity]</td>
			<td style="border:1px solid #000; padding:6px; font-size:8px; text-align:right;">$peso $itemPrice</td>
			<td style="border:1px solid #000; padding:6px; font-size:8px; text-align:right;">$peso $itemTotalPrice</td>
		</tr>

EOF;
}

$totalsBlock = <<<EOF
		<tr>
			<td style="border:1px solid #000; padding:6px; font-size:8px;">&nbsp;</td>
			<td style="border:1px solid #000; padding:6px; font-size:8px;">&nbsp;</td>
			<td style="border:1px solid #000; padding:6px; font-size:8px;">&nbsp;</td>
			<td style="border:1px solid #000; padding:6px; font-size:8px;">&nbsp;</td>
			<td style="border:1px solid #000; padding:6px; font-size:8px; text-align:right;">0</td>
		</tr>
		<tr>
			<td style="border:1px solid #000; padding:4px; font-size:8px; font-weight:bold; text-align:center;" colspan="4">SUBTOTAL</td>
			<td style="border:1px solid #000; padding:4px; font-size:8px; font-weight:bold; text-align:right;">$peso $subtotal</td>
		</tr>
		<tr>
			<td style="border:1px solid #000; padding:4px; font-size:8px; font-weight:bold; text-align:center;" colspan="4">DISCOUNT</td>
			<td style="border:1px solid #000; padding:4px; font-size:8px; font-weight:bold; text-align:right;">$peso $discount</td>
		</tr>
		<tr>
			<td style="border:1px solid #000; padding:6px; font-size:9px; font-weight:bold; text-align:center; background-color:#B3D9FF;" colspan="4">TOTAL</td>
			<td style="border:1px solid #000; padding:6px; font-size:9px; font-weight:bold; text-align:right; background-color:#B3D9FF;">$peso $saleTotalPrice</td>
		</tr>
	</table>

EOF;

$block4 = <<<EOF

<br>

<table style="width:100%;">
	<tr>
		<td style="width:50%; text-align:left; vertical-align:bottom;">
			<div style="font-size:8px;">
				<strong>Thank you for your purchase!</strong>
				<br><br>
				<div style="font-size:7px; font-style:italic;">
					This document is only used for after-sales certificate. Official Receipt will be issued separately.
				</div>
				<br><br>
				<strong>Recipient Signature:</strong>
				<br><br>
				_________________________________________________
			</div>
		</td>
		<td style="width:50%; text-align:right; vertical-align:bottom;">
			<div style="font-size:8px;">
				<strong>Sales Representative:</strong>
				<br><br>
				<div style="border-top:1px solid #000; padding-top:3px; width:80%; margin-left:auto; text-align:center;">
					$answerSeller[name]
				</div>
			</div>
		</td>
	</tr>
</table>

EOF;

return $block1 . $block2 . $block3 . $totalsBlock . $block4;
}

public function getBillPrinting(){

//WE BRING THE INFORMATION OF THE SALE

$itemSale = "code";
$valueSale = $this->code;

$answerSale = ControllerSales::ctrShowSales($itemSale, $valueSale);

$saledate = substr($answerSale["saledate"],0,-8);
$products = json_decode($answerSale["products"], true);
$paymentMethod = $answerSale["paymentMethod"];

$tax = number_format($answerSale["tax"],2);
$discount = number_format(isset($answerSale["discount"]) ? $answerSale["discount"] : 0, 2);
$subtotal = number_format($answerSale["totalPrice"] + (isset($answerSale["discount"]) ? $answerSale["discount"] : 0), 2);
$saleTotalPrice = number_format($answerSale["totalPrice"],2);

//TRAEMOS LA INFORMACIÓN DEL Customer

$itemCustomer = "id";
$valueCustomer = $answerSale["idCustomer"];

$answerCustomer = ControllerCustomers::ctrShowCustomers($itemCustomer, $valueCustomer);

//TRAEMOS LA INFORMACIÓN DEL Seller

$itemSeller = "id";
$valueSeller = $answerSale["idSeller"];

$answerSeller = ControllerUsers::ctrShowUsers($itemSeller, $valueSeller);

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage();

$peso = 'PHP';  // Using PHP as text instead of the symbol for better compatibility

// Generate the first copy (Customer Copy)
$firstCopy = $this->generateReceiptHTML($saledate, $valueSale, $answerCustomer, $answerSeller, $products, $subtotal, $discount, $saleTotalPrice, $peso, "CUSTOMER COPY");

$pdf->writeHTML($firstCopy, false, false, false, false, '');

// Add separator line and spacing between copies
$separator = <<<EOF

<br><br>

<hr style="border: 1px dashed #000; margin: 10px 0;">

<div style="text-align:center; font-size:10px; margin:10px 0;">
	✂️ -------------------------------- CUT HERE -------------------------------- ✂️
</div>

<hr style="border: 1px dashed #000; margin: 10px 0;">

<br><br>

EOF;

$pdf->writeHTML($separator, false, false, false, false, '');

// Generate the second copy (Store Copy)
$secondCopy = $this->generateReceiptHTML($saledate, $valueSale, $answerCustomer, $answerSeller, $products, $subtotal, $discount, $saleTotalPrice, $peso, "STORE COPY");

$pdf->writeHTML($secondCopy, false, false, false, false, '');

// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

$pdf->Output('delivery-receipt-'.$valueSale.'.pdf', 'I');

}

}

$bill = new printBill();
$bill -> code = $_GET["code"];
$bill -> getBillPrinting();

?>