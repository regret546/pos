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

public function getBillPrinting(){

//WE BRING THE INFORMATION OF THE SALE

$itemSale = "code";
$valueSale = $this->code;

$answerSale = ControllerSales::ctrShowSales($itemSale, $valueSale);

$saledate = substr($answerSale["saledate"],0,-8);
$products = json_decode($answerSale["products"], true);
$paymentMethod = $answerSale["paymentMethod"];

$tax = number_format($answerSale["tax"],2);
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

// ---------------------------------------------------------

$block1 = <<<EOF

	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="width:15%; vertical-align:top;">
				<!-- Logo placeholder - you can add your logo here -->
				<img src="images/company-logo.png" style="width:80px; height:80px;">
			</td>
			<td style="width:35%; vertical-align:top; padding-left:10px;">
				<div style="font-size:16px; font-weight:bold; margin-bottom:10px;">
					DELIVERY RECEIPT
				</div>
				<br>
				<div style="font-size:12px; font-weight:bold;">
					Siargao Computer Trading
				</div>
				<div style="font-size:10px;">
					Pob. 12, Dapa, Siargao Island
				</div>
				<div style="font-size:10px;">
					9286127206
				</div>
			</td>
			<td style="width:25%; vertical-align:top; text-align:center;">
				<table style="border-collapse: collapse; width:100%; margin-bottom:10px;">
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; background-color:#E0E0E0; text-align:center;">DATE</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; text-align:center;">$saledate</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; background-color:#E0E0E0; text-align:center;">RECEIPT NO.</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; text-align:center;">$valueSale</td>
					</tr>
				</table>
			</td>
			<td style="width:25%; vertical-align:top;">
				<table style="border-collapse: collapse; width:100%;">
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; background-color:#E0E0E0; text-align:center;">RECIPIENT INFORMATION</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px;">Name</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px;">$answerCustomer[name]</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px;">Address</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px;">$answerCustomer[address]</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px;">Contact No.</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px;">$answerCustomer[phone]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<br><br>

EOF;

$pdf->writeHTML($block1, false, false, false, false, '');

// ---------------------------------------------------------

$block2 = <<<EOF

	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:12%;">CODE</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:28%;">DESCRIPTION</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:8%;">QTY</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:12%;">AMOUNT</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:15%;">PAYMENT</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:25%;">TOTAL</td>
		</tr>
	</table>

EOF;

$pdf->writeHTML($block2, false, false, false, false, '');

// ---------------------------------------------------------

foreach ($products as $key => $item) {

$itemProduct = "description";
$valueProduct = $item["description"];
$orden = null;

$answerProduct = ControllerProducts::ctrShowProducts($itemProduct, $valueProduct, $orden);

$valueUnit = number_format($answerProduct["sellingPrice"], 2);
$itemTotalPrice = number_format($item["totalPrice"], 2);

// Get product code if available
$productCode = isset($answerProduct["code"]) ? $answerProduct["code"] : 'N/A';

$block3 = <<<EOF

	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center; width:12%;">$productCode</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:left; width:28%;">$item[description]</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center; width:8%;">$item[quantity]</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center; width:12%;">$peso $valueUnit</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center; width:15%;">$paymentMethod</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:right; width:25%;">$peso $itemTotalPrice</td>
		</tr>
	</table>

EOF;

$pdf->writeHTML($block3, false, false, false, false, '');

}

// ---------------------------------------------------------

// Add empty rows to match the design
$emptyRows = '';
for($i = 0; $i < 5; $i++) {
    $emptyRows .= '
        <table style="width:100%; border-collapse: collapse;">
            <tr>
                <td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center; width:12%; height:25px;"></td>
                <td style="border:1px solid #000; padding:8px; font-size:10px; text-align:left; width:28%;"></td>
                <td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center; width:8%;"></td>
                <td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center; width:12%;"></td>
                <td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center; width:15%;"></td>
                <td style="border:1px solid #000; padding:8px; font-size:10px; text-align:right; width:25%;">0</td>
            </tr>
        </table>';
}

$block4 = <<<EOF

$emptyRows

<table style="width:100%; border-collapse: collapse;">
	<tr>
		<td style="width:55%; padding:10px; vertical-align:top;">
			<div style="font-size:10px;">
				<strong>Thank you for your purchase!</strong><br>
				This document is only used for after-sales certificate. Official Receipt will be issued separately.
			</div>
		</td>
		<td style="width:15%;"></td>
		<td style="width:12%; vertical-align:top;">
			<div style="font-size:11px; font-weight:bold; text-align:center; border:1px solid #000; padding:5px; background-color:#E0E0E0;">
				TOTAL
			</div>
		</td>
		<td style="width:18%; vertical-align:top;">
			<div style="font-size:11px; text-align:right; border:1px solid #000; padding:8px;">
				$peso $saleTotalPrice
			</div>
		</td>
	</tr>
</table>

<br><br>

<table style="width:100%;">
	<tr>
		<td style="width:100%; text-align:left;">
			<div style="font-size:10px;">
				<strong>Recipient Signature:</strong>
				<br><br><br>
				_________________________________________________
			</div>
		</td>
	</tr>
</table>

EOF;

$pdf->writeHTML($block4, false, false, false, false, '');



// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

$pdf->Output('bill.pdf', 'D');

}

}

$bill = new printBill();
$bill -> code = $_GET["code"];
$bill -> getBillPrinting();

?>