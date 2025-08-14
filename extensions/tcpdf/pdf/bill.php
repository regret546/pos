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

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage('P', 'A7');

$peso = 'PHP';  // Using PHP as text instead of the symbol for better compatibility

//---------------------------------------------------------

$block1 = <<<EOF

<table style="font-size:8px; text-align:center">

	<tr>
		
		<td style="width:160px;">
	
			<div>
			
				<strong>DELIVERY RECEIPT</strong>

				<br><br>
				Siargao Computer Trading

				<br>
				Pob. 12, Dapa, Siargao Island

				<br>
				9286127206

				<br><br>
				Date: $saledate
				<br>
				Receipt No: $valueSale

				<br><br>					
				Customer: $answerCustomer[name]

				<br>
				Seller: $answerSeller[name]

				<br>
				Payment: $paymentMethod

				<br><br>

			</div>

		</td>

	</tr>


</table>

EOF;

$pdf->writeHTML($block1, false, false, false, false, '');

// ---------------------------------------------------------


foreach ($products as $key => $item) {

$unitValue = number_format($item["price"], 2);

$itemTotalPrice = number_format($item["totalPrice"], 2);

$block2 = <<<EOF

<table style="font-size:7px;">

	<tr>
	
		<td style="width:160px; text-align:left">
		$item[description] 
		</td>

	</tr>

	<tr>
	
		<td style="width:160px; text-align:right">
		$peso $unitValue Units * $item[quantity]  = $peso $itemTotalPrice
		<br>
		</td>

	</tr>

</table>

EOF;

$pdf->writeHTML($block2, false, false, false, false, '');

}

// ---------------------------------------------------------

$block3 = <<<EOF

<table style="font-size:7px; text-align:right">

	<tr>
	
		<td style="width:80px;">
			 NET: 
		</td>

		<td style="width:80px;">
			$peso $saleTotalPrice
		</td>

	</tr>

	<tr>
	
		<td style="width:80px;">
			 TAX: 
		</td>

		<td style="width:80px;">
			$peso $tax
		</td>

	</tr>

	<tr>
	
		<td style="width:160px;">
			 --------------------------
		</td>

	</tr>

	<tr>
	
		<td style="width:80px;">
			 TOTAL: 
		</td>

		<td style="width:80px;">
			$peso $saleTotalPrice
		</td>

	</tr>

	<tr>
	
		<td style="width:160px;">
			<br>
			<br>
			Thank you for your purchase!
		</td>

	</tr>

</table>

EOF;

$pdf->writeHTML($block3, false, false, false, false, '');

$pdf->Output('bill.pdf');

}

}

$bill = new printBill();
$bill -> code = $_GET["code"];
$bill -> getBillPrinting();

?>