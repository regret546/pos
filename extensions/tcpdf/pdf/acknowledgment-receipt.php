<?php
// Start output buffering to prevent any output before PDF generation
ob_start();

// Suppress all error output that might interfere with PDF
error_reporting(0);
ini_set('display_errors', 0);

require_once "../../../controllers/sales.controller.php";
require_once "../../../models/sales.model.php";

require_once "../../../controllers/customers.controller.php";
require_once "../../../models/customers.model.php";

require_once "../../../controllers/users.controller.php";
require_once "../../../models/users.model.php";

require_once "../../../controllers/products.controller.php";
require_once "../../../models/products.model.php";

require_once "../../../models/connection.php";

// Use Composer's autoloader for TCPDF
require_once "../../vendor/autoload.php";

class printAcknowledgmentReceipt{

public $code;

public function getAcknowledgmentReceiptPrinting(){

//GET SALE INFORMATION
$itemSale = "code";
$valueSale = $this->code;

$answerSale = ControllerSales::ctrShowSales($itemSale, $valueSale);

if(!$answerSale || strpos($answerSale["paymentMethod"], "installment") === false) {
    ob_end_clean();
    echo '<script>alert("This receipt is only for installment payments."); window.close();</script>';
    return;
}

$saledate = date('F j, Y', strtotime($answerSale["saledate"]));
$products = json_decode($answerSale["products"], true);
$paymentMethod = $answerSale["paymentMethod"];

$tax = number_format($answerSale["tax"],2);
$saleTotalPrice = number_format($answerSale["totalPrice"],2);

//GET CUSTOMER INFORMATION
$itemCustomer = "id";
$valueCustomer = $answerSale["idCustomer"];

$answerCustomer = ControllerCustomers::ctrShowCustomers($itemCustomer, $valueCustomer);

//GET SELLER INFORMATION
$itemSeller = "id";
$valueSeller = $answerSale["idSeller"];

$answerSeller = ControllerUsers::ctrShowUsers($itemSeller, $valueSeller);

//GET INSTALLMENT PLAN INFORMATION
try {
    $stmt = Connection::connect()->prepare("SELECT * FROM installment_plans WHERE sale_id = :sale_id");
    $stmt->bindParam(":sale_id", $answerSale["id"], PDO::PARAM_INT);
    $stmt->execute();
    $installmentPlan = $stmt->fetch();
    
    if(!$installmentPlan) {
        ob_end_clean();
        echo '<script>alert("No installment plan found for this sale."); window.close();</script>';
        return;
    }
    
    // Get payment schedule
    $paymentStmt = Connection::connect()->prepare("SELECT * FROM installment_payments WHERE installment_plan_id = :plan_id ORDER BY due_date ASC");
    $paymentStmt->bindParam(":plan_id", $installmentPlan["id"], PDO::PARAM_INT);
    $paymentStmt->execute();
    $paymentSchedule = $paymentStmt->fetchAll();
    
} catch(Exception $e) {
    ob_end_clean();
    echo '<script>alert("Error retrieving installment information."); window.close();</script>';
    return;
}

$downpaymentAmount = isset($installmentPlan["downpayment_amount"]) ? floatval($installmentPlan["downpayment_amount"]) : 0;

// Clean output buffer before PDF generation
ob_end_clean();

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage();

$peso = 'PHP';

// ---------------------------------------------------------

$block1 = <<<EOF

	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="width:15%; vertical-align:top;">
				<img src="images/company-logo.png" style="width:80px; height:80px;">
			</td>
			<td style="width:45%; vertical-align:top; padding-left:10px;">
				<div style="font-size:18px; font-weight:bold; margin-bottom:15px;">
					ACKNOWLEDGMENT RECEIPT
				</div>
				<br><br>
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
			<td style="width:40%; vertical-align:top;">
				<table style="border-collapse: collapse; width:100%; margin-bottom:10px;">
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; background-color:#D3D3D3; text-align:center; font-weight:bold;">DATE</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; text-align:center;">$saledate</td>
					</tr>
				</table>
				
				<table style="border-collapse: collapse; width:100%; margin-bottom:20px;">
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; background-color:#D3D3D3; text-align:center; font-weight:bold;">RECEIPT NO.</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; text-align:center;">$valueSale</td>
					</tr>
				</table>
				
				<table style="border-collapse: collapse; width:100%;">
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; background-color:#D3D3D3; text-align:center; font-weight:bold;" colspan="2">RECIPIENT INFORMATION</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; font-weight:bold; width:30%;">Name</td>
						<td style="border:1px solid #000; padding:5px; font-size:10px; width:70%;">$answerCustomer[name]</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; font-weight:bold;">Address</td>
						<td style="border:1px solid #000; padding:5px; font-size:10px;">$answerCustomer[address]</td>
					</tr>
					<tr>
						<td style="border:1px solid #000; padding:5px; font-size:10px; font-weight:bold;">Contact No.</td>
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
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:15%;">CODE</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:35%;">DESCRIPTION</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:10%;">QTY</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:20%;">AMOUNT</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; width:20%;">TOTAL</td>
		</tr>

EOF;

foreach ($products as $key => $item) {

	$valueProduct = $item["id"];
	$orderProduct = "id";

	$answerProduct = ControllerProducts::ctrShowProducts($orderProduct, $valueProduct, null);

	// Format the prices properly
	$itemPrice = number_format($item["price"], 2);
	$itemTotalPrice = number_format($item["totalPrice"], 2);

	$block2 .= <<<EOF
		
		<tr>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center;">$answerProduct[code]</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px;">$answerProduct[description]</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center;">$item[quantity]</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:right;">$itemPrice</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:right;">$itemTotalPrice</td>
		</tr>

EOF;

}

// Add empty row
$block2 .= <<<EOF
		<tr>
			<td style="border:1px solid #000; padding:8px; font-size:10px;">&nbsp;</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px;">&nbsp;</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px;">&nbsp;</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px;">&nbsp;</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:right;">0</td>
		</tr>
		<tr>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center;" colspan="4">TOTAL</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:right;">$saleTotalPrice</td>
		</tr>
	</table>

	<br><br>

EOF;

$pdf->writeHTML($block2, false, false, false, false, '');

// ---------------------------------------------------------

$downpaymentFormatted = number_format($downpaymentAmount, 2);

// Generate the appropriate agreement text based on whether there's a downpayment
if($downpaymentAmount > 0) {
    $agreementText = "The amount of <span style=\"background-color:#D3D3D3; padding:2px 8px; font-weight:bold;\">$downpaymentFormatted</span> is received from <span style=\"background-color:#D3D3D3; padding:2px 8px; font-weight:bold;\">$answerCustomer[name]</span><br>as a <strong>downpayment</strong> for the indicated item above. As per agreement between both parties, the remaining balance will be settled on the following payment schedule:";
} else {
    $agreementText = "This acknowledges the installment payment agreement between <span style=\"background-color:#D3D3D3; padding:2px 8px; font-weight:bold;\">$answerCustomer[name]</span> and <strong>Siargao Computer Trading</strong><br>for the indicated item(s) above. As per agreement between both parties, the total amount will be settled according to the following payment schedule:";
}

$block3 = <<<EOF

	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="font-size:11px; padding:5px;">
				$agreementText
			</td>
		</tr>
	</table>

	<br>

	<table style="width:50%; border-collapse: collapse; margin:0 auto;">
		<tr>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; background-color:#D3D3D3; width:50%;">DATE</td>
			<td style="border:1px solid #000; padding:8px; font-size:11px; font-weight:bold; text-align:center; background-color:#D3D3D3; width:50%;">AMOUNT DUE</td>
		</tr>

EOF;

// Add payment schedule
foreach ($paymentSchedule as $payment) {
    $dueDate = date('j-M-y', strtotime($payment["due_date"]));
    $paymentAmount = number_format($payment["amount"], 2);
    
    $block3 .= <<<EOF
		<tr>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center;">$dueDate</td>
			<td style="border:1px solid #000; padding:8px; font-size:10px; text-align:center;">$paymentAmount</td>
		</tr>
EOF;
}

$block3 .= <<<EOF
	</table>

	<br><br><br>

	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="width:50%; text-align:left; vertical-align:bottom;">
				<div style="font-size:11px;">Received by:</div>
				<br><br><br>
				<div style="font-size:10px; font-weight:bold; border-top:1px solid #000; padding-top:5px; width:80%;">
					$answerSeller[name], Sales Representative
				</div>
			</td>
			<td style="width:50%; text-align:right; vertical-align:bottom;">
				<div style="font-size:11px;">Acknowledged by:</div>
				<br><br><br>
				<div style="font-size:10px; border-top:1px solid #000; padding-top:5px; width:80%; margin-left:auto;">
					Name of Customer and Signature
				</div>
			</td>
		</tr>
	</table>

	<br>

	<div style="font-size:9px; font-style:italic; text-align:center;">
		This document is only used for after-sales certificate. Official Receipt will be issued separately.
	</div>

EOF;

$pdf->writeHTML($block3, false, false, false, false, '');

// ---------------------------------------------------------

$pdf->Output('acknowledgment-receipt-'.$valueSale.'.pdf', 'I');

}

}

$acknowledgment = new printAcknowledgmentReceipt();

$acknowledgment->code = $_GET["code"];

$acknowledgment->getAcknowledgmentReceiptPrinting();

?>
