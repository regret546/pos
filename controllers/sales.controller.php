<?php

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ControllerSales{
	/* --LOG ON TO codeastro.com FOR MORE PROJECTS-- */
	/*=============================================
	SHOW SALES
	=============================================*/

	static public function ctrShowSales($item, $value){

		$table = "sales";

		$answer = ModelSales::mdlShowSales($table, $item, $value);

		return $answer;

	}

	/*=============================================
	CREATE SALE
	=============================================*/

	static public function ctrCreateSale(){

		if(isset($_POST["newSale"])){

			/*=============================================
			UPDATE CUSTOMER'S PURCHASES AND REDUCE THE STOCK AND INCREMENT THE SALES OF THE PRODUCT
			=============================================*/

			$productsList = json_decode($_POST["productsList"], true);

			$totalPurchasedProducts = array();

			foreach ($productsList as $key => $value) {

			   array_push($totalPurchasedProducts, $value["quantity"]);
				
			   $tableProducts = "products";

			    $item = "id";
			    $valueProduct = $value["id"];
			    $order = "id";

			    $getProduct = ProductsModel::mdlShowProducts($tableProducts, $item, $valueProduct, $order);

				$item1a = "sales";
				$value1a = $value["quantity"] + $getProduct["sales"];

			    $newSales = ProductsModel::mdlUpdateProduct($tableProducts, $item1a, $value1a, $valueProduct);

				$item1b = "stock";
				$value1b = $getProduct["stock"] - $value["quantity"];

				$newStock = ProductsModel::mdlUpdateProduct($tableProducts, $item1b, $value1b, $valueProduct);

			}

			$tableCustomers = "customers";

			$item = "id";
			$valueCustomer = $_POST["selectCustomer"];

			$getCustomer = ModelCustomers::mdlShowCustomers($tableCustomers, $item, $valueCustomer);

			$item1a = "purchases";
			$value1a = array_sum($totalPurchasedProducts) + $getCustomer["purchases"];

			$customerPurchases = ModelCustomers::mdlUpdateCustomer($tableCustomers, $item1a, $value1a, $valueCustomer);

			/*=============================================
			UPDATE CUSTOMER'S LAST PURCHASE
			=============================================*/

			require_once "models/date.helper.php";
			DateHelper::init();

			$item1b = "lastPurchase";
			$value1b = DateHelper::getDateTime();

			$dateCustomer = ModelCustomers::mdlUpdateCustomer($tableCustomers, $item1b, $value1b, $valueCustomer);

			/*=============================================
			SAVE THE SALE
			=============================================*/	

			$table = "sales";

			$data = array(
				"code"=>$_POST["newSale"],
				"idSeller"=>$_POST["idSeller"],
				"idCustomer"=>$_POST["selectCustomer"],
				"products"=>$_POST["productsList"],
				"tax"=>$_POST["newTaxPrice"],
				"discount"=>isset($_POST["saleDiscount"]) ? $_POST["saleDiscount"] : 0,
				"totalPrice"=>$_POST["saleTotal"],
				"paymentMethod"=>$_POST["listPaymentMethod"]
			);

			$answer = ModelSales::mdlAddSale($table, $data);

			if((is_array($answer) && $answer["status"] == "ok") || $answer == "ok"){
				// Debug: Log the payment method
				error_log("Sale created successfully. Payment method: " . $_POST["listPaymentMethod"]);
				
				// Get the sale ID first
				if(is_array($answer) && isset($answer["id"])) {
					$saleId = $answer["id"];
					error_log("Using sale ID from array: " . $saleId);
				} else {
					// Fallback to old method for backward compatibility
					$saleId = ModelSales::mdlGetLastInsertId();
					error_log("Using sale ID from lastInsertId: " . $saleId);
				}
				
				// Check if this is an installment payment
				if(strpos($_POST["listPaymentMethod"], "installment") === 0) {
					error_log("Installment payment detected. Starting installment plan creation...");
					error_log("POST data available: installmentMonths=" . (isset($_POST["installmentMonths"]) ? $_POST["installmentMonths"] : "NOT SET"));
					error_log("POST data available: installmentInterest=" . (isset($_POST["installmentInterest"]) ? $_POST["installmentInterest"] : "NOT SET"));
					error_log("POST data available: installmentFrequency=" . (isset($_POST["installmentFrequency"]) ? $_POST["installmentFrequency"] : "NOT SET"));
					
					// Get number of payments from form data
					$numberOfPayments = isset($_POST["installmentMonths"]) ? intval($_POST["installmentMonths"]) : 3;
					$interestRate = isset($_POST["installmentInterest"]) ? floatval($_POST["installmentInterest"]) : 0;
					$paymentFrequency = isset($_POST["installmentFrequency"]) ? $_POST["installmentFrequency"] : "30th";
					
					// Get downpayment data
					$hasDownpayment = isset($_POST["hasDownpayment"]) && $_POST["hasDownpayment"] === "on";
					$downpaymentAmount = $hasDownpayment && isset($_POST["downpaymentAmount"]) ? floatval($_POST["downpaymentAmount"]) : 0;
					
					error_log("Parsed values: numberOfPayments=$numberOfPayments, interestRate=$interestRate, paymentFrequency=$paymentFrequency, hasDownpayment=" . ($hasDownpayment ? "true" : "false") . ", downpaymentAmount=$downpaymentAmount");
					
					// Get the sale ID - handle both new array format and old string format
					if(is_array($answer) && isset($answer["id"])) {
						$saleId = $answer["id"];
						error_log("Using sale ID from array: " . $saleId);
					} else {
						// Fallback to old method for backward compatibility
						$saleId = ModelSales::mdlGetLastInsertId();
						error_log("Using sale ID from lastInsertId: " . $saleId);
					}
					
					error_log("Sale ID: " . $saleId . ", Months: " . $numberOfPayments . ", Interest: " . $interestRate);
					
					// Ensure we have a valid sale ID
					if(!$saleId || $saleId <= 0) {
						error_log("Warning: Invalid sale ID returned: " . $saleId);
					} else {
						error_log("Valid sale ID found, creating installment plan...");
					
					// Calculate total amount with interest, accounting for downpayment
					$baseAmount = floatval($_POST["saleTotal"]);
					$remainingAmount = $baseAmount - $downpaymentAmount;
					$monthlyInterest = $interestRate / 100;
					$totalAmountWithInterest = $remainingAmount * (1 + ($monthlyInterest * $numberOfPayments));
					$finalTotalAmount = $downpaymentAmount + $totalAmountWithInterest; // Total including downpayment and installments
					
					// Calculate actual number of payments based on frequency
					$actualNumberOfPayments = $paymentFrequency === "both" ? $numberOfPayments * 2 : $numberOfPayments;
					$paymentAmount = round($totalAmountWithInterest / $actualNumberOfPayments, 2);
					$startDate = date('Y-m-d');
					
					error_log("Payment calculation: baseAmount=$baseAmount, downpaymentAmount=$downpaymentAmount, remainingAmount=$remainingAmount, totalAmountWithInterest=$totalAmountWithInterest, finalTotalAmount=$finalTotalAmount, actualNumberOfPayments=$actualNumberOfPayments, paymentAmount=$paymentAmount");
					
					try {
						// Check if installment_plans table exists
						$checkTable = Connection::connect()->prepare("SHOW TABLES LIKE 'installment_plans'");
						$checkTable->execute();
						$tableExists = $checkTable->fetch();
						
						if(!$tableExists) {
							error_log("ERROR: installment_plans table does not exist!");
							throw new Exception("Installment plans table not found");
						}
						
						// Note: actualNumberOfPayments already calculated above
						
						// Check if downpayment_amount field exists
						$checkColumn = Connection::connect()->prepare("SHOW COLUMNS FROM installment_plans LIKE 'downpayment_amount'");
						$checkColumn->execute();
						$columnExists = $checkColumn->fetch();
						
						// Create installment plan directly - with backward compatibility
						if($columnExists) {
							// New version with downpayment support
							$stmt = Connection::connect()->prepare("INSERT INTO installment_plans(sale_id, bill_number, customer_id, total_amount, base_amount, number_of_payments, payment_amount, interest_rate, payment_frequency, downpayment_amount, start_date, status) VALUES (:sale_id, :bill_number, :customer_id, :total_amount, :base_amount, :number_of_payments, :payment_amount, :interest_rate, :payment_frequency, :downpayment_amount, :start_date, :status)");
						} else {
							// Old version without downpayment field - use base amount for total_amount for now
							$stmt = Connection::connect()->prepare("INSERT INTO installment_plans(sale_id, bill_number, customer_id, total_amount, base_amount, number_of_payments, payment_amount, interest_rate, payment_frequency, start_date, status) VALUES (:sale_id, :bill_number, :customer_id, :total_amount, :base_amount, :number_of_payments, :payment_amount, :interest_rate, :payment_frequency, :start_date, :status)");
							error_log("WARNING: Using legacy installment_plans table without downpayment support");
						}
						
						$stmt->bindParam(":sale_id", $saleId, PDO::PARAM_INT);
						$stmt->bindParam(":bill_number", $_POST["newSale"], PDO::PARAM_STR);
						$stmt->bindParam(":customer_id", $_POST["selectCustomer"], PDO::PARAM_INT);
						
						// Conditional binding based on table structure
						if($columnExists) {
							// New version with downpayment support
							$stmt->bindParam(":total_amount", $finalTotalAmount, PDO::PARAM_STR);
							$stmt->bindParam(":downpayment_amount", $downpaymentAmount, PDO::PARAM_STR);
						} else {
							// Old version - use base amount as total amount
							$stmt->bindParam(":total_amount", $baseAmount, PDO::PARAM_STR);
						}
						
						$stmt->bindParam(":base_amount", $baseAmount, PDO::PARAM_STR);
						$stmt->bindParam(":number_of_payments", $actualNumberOfPayments, PDO::PARAM_INT);
						$stmt->bindParam(":payment_amount", $paymentAmount, PDO::PARAM_STR);
						$stmt->bindParam(":interest_rate", $interestRate, PDO::PARAM_STR);
						$stmt->bindParam(":payment_frequency", $paymentFrequency, PDO::PARAM_STR);
						$stmt->bindParam(":start_date", $startDate, PDO::PARAM_STR);
						$status = "active";
						$stmt->bindParam(":status", $status, PDO::PARAM_STR);
						
						if($stmt->execute()) {
							$planId = Connection::connect()->lastInsertId();
							error_log("Installment plan created successfully with ID: " . $planId);
							
							// Create individual payment records based on frequency
							$paymentCount = 0;
							$individualPaymentAmount = $paymentAmount; // Already calculated correctly above
							
							for($i = 1; $i <= $numberOfPayments; $i++) {
								if($paymentFrequency === "15th" || $paymentFrequency === "both") {
									// Payment on 15th
									$paymentCount++;
									$currentMonth = date('m');
									$currentYear = date('Y');
									$targetMonth = $currentMonth + $i;
									$targetYear = $currentYear;
									if($targetMonth > 12) {
										$targetYear += floor(($targetMonth - 1) / 12);
										$targetMonth = (($targetMonth - 1) % 12) + 1;
									}
									$dueDate = date('Y-m-d', mktime(0, 0, 0, $targetMonth, 15, $targetYear));
									
									$paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:installment_plan_id, :payment_number, :amount, :due_date, :status)");
									
									$paymentStmt->bindParam(":installment_plan_id", $planId, PDO::PARAM_INT);
									$paymentStmt->bindParam(":payment_number", $paymentCount, PDO::PARAM_INT);
									$paymentStmt->bindParam(":amount", $individualPaymentAmount, PDO::PARAM_STR);
									$paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
									$pendingStatus = "pending";
									$paymentStmt->bindParam(":status", $pendingStatus, PDO::PARAM_STR);
									
									if($paymentStmt->execute()) {
										error_log("Created payment " . $paymentCount . " (15th) for plan ID: " . $planId . " - Due: " . $dueDate . " - Amount: " . $individualPaymentAmount);
									} else {
										error_log("Failed to create payment " . $paymentCount . " (15th) for plan ID: " . $planId . " - Error: " . print_r($paymentStmt->errorInfo(), true));
									}
								}
								
								if($paymentFrequency === "30th" || $paymentFrequency === "both") {
									// Payment on 30th (or last day of month)
									$paymentCount++;
									$currentMonth = date('m');
									$currentYear = date('Y');
									$targetMonth = $currentMonth + $i;
									$targetYear = $currentYear;
									if($targetMonth > 12) {
										$targetYear += floor(($targetMonth - 1) / 12);
										$targetMonth = (($targetMonth - 1) % 12) + 1;
									}
									$lastDayOfMonth = date('t', mktime(0, 0, 0, $targetMonth, 1, $targetYear));
									$dueDate = date('Y-m-d', mktime(0, 0, 0, $targetMonth, min(30, $lastDayOfMonth), $targetYear));
									
									$paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:installment_plan_id, :payment_number, :amount, :due_date, :status)");
									
									$paymentStmt->bindParam(":installment_plan_id", $planId, PDO::PARAM_INT);
									$paymentStmt->bindParam(":payment_number", $paymentCount, PDO::PARAM_INT);
									$paymentStmt->bindParam(":amount", $individualPaymentAmount, PDO::PARAM_STR);
									$paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
									$pendingStatus = "pending";
									$paymentStmt->bindParam(":status", $pendingStatus, PDO::PARAM_STR);
									
									if($paymentStmt->execute()) {
										error_log("Created payment " . $paymentCount . " (30th) for plan ID: " . $planId . " - Due: " . $dueDate . " - Amount: " . $individualPaymentAmount);
									} else {
										error_log("Failed to create payment " . $paymentCount . " (30th) for plan ID: " . $planId . " - Error: " . print_r($paymentStmt->errorInfo(), true));
									}
								}
							}
							
							// Verify payment creation
							$verifyStmt = Connection::connect()->prepare("SELECT COUNT(*) as payment_count FROM installment_payments WHERE installment_plan_id = :plan_id");
							$verifyStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
							$verifyStmt->execute();
							$verifyResult = $verifyStmt->fetch();
							error_log("Verification: Created " . $verifyResult['payment_count'] . " payment records for plan ID: " . $planId);
							
							// Fallback: If no payment records were created, try again with a simpler approach
							if($verifyResult['payment_count'] == 0) {
								error_log("FALLBACK: No payment records created, attempting fallback creation...");
								self::createMissingPaymentRecords($planId);
							}
							
						} else {
							error_log("Failed to create installment plan for sale ID: " . $saleId);
						}
						
					} catch(Exception $e) {
						// Installment plan creation failed, but sale was successful
						// Could log error or show warning
						error_log("Installment plan creation failed: " . $e->getMessage());
					}
					} // End of valid sale ID check
				}

				// FINAL CHECK: Auto-fix any installment plans without payment records
				self::autoFixMissingPaymentRecords($saleId);

				echo'<script>

				localStorage.removeItem("range");

				swal({
					  type: "success",
					  title: "Sale added successfully",
					  showConfirmButton: true,
					  confirmButtonText: "Close"
					  }).then((result) => {
								if (result.value) {

								window.location = "index.php?route=create-sale";

								}
							})

				</script>';

			}

		}

	}
	/* --LOG ON TO codeastro.com FOR MORE PROJECTS-- */
	/*=============================================
	EDIT SALE
	=============================================*/

	static public function ctrEditSale(){

		if(isset($_POST["editSale"])){

			/*=============================================
			FORMAT PRODUCTS AND CUSTOMERS TABLES
			=============================================*/
			$table = "sales";

			$item = "code";
			$value = $_POST["editSale"];

			$getSale = ModelSales::mdlShowSales($table, $item, $value);

			/*=============================================
			CHECK IF THERE'S ANY EDITED SALE
			=============================================*/

			if($_POST["productsList"] == ""){

				$productsList = $getSale["products"];
				$productChange = false;


			}else{

				$productsList = $_POST["productsList"];
				$productChange = true;
			}

			if($productChange){

				$products =  json_decode($getSale["products"], true);

				$totalPurchasedProducts = array();

				foreach ($products as $key => $value) {

					array_push($totalPurchasedProducts, $value["quantity"]);
					
					$tableProducts = "products";

					$item = "id";
					$valueProductId = $value["id"];
					$order = "id";

					$getProduct = ProductsModel::mdlShowProducts($tableProducts, $item, $valueProductId, $order);

					$item1a = "sales";
					$value1a = $getProduct["sales"] - $value["quantity"];

					$newSales = ProductsModel::mdlUpdateProduct($tableProducts, $item1a, $value1a, $valueProductId);

					$item1b = "stock";
					$value1b = $value["quantity"] + $getProduct["stock"];

					$stockNew = ProductsModel::mdlUpdateProduct($tableProducts, $item1b, $value1b, $valueProductId);

				}

				$tableCustomers = "customers";

				$itemCustomer = "id";
				$valueCustomer = $_POST["selectCustomer"];

				$getCustomer = ModelCustomers::mdlShowCustomers($tableCustomers, $itemCustomer, $valueCustomer);

				$item1a = "purchases";
				$value1a = $getCustomer["purchases"] - array_sum($totalPurchasedProducts);

				$customerPurchases = ModelCustomers::mdlUpdateCustomer($tableCustomers, $item1a, $value1a, $valueCustomer);

				/*=============================================
				UPDATE THE CUSTOMER'S PURCHASES AND REDUCE THE STOCK AND INCREMENT PRODUCT SALES
				=============================================*/

				$productsList_2 = json_decode($productsList, true);

				$totalPurchasedProducts_2 = array();

				foreach ($productsList_2 as $key => $value) {

					array_push($totalPurchasedProducts_2, $value["quantity"]);
					
					$tableProducts_2 = "products";

					$item_2 = "id";
					$value_2 = $value["id"];
					$order = "id";

					$getProduct_2 = ProductsModel::mdlShowProducts($tableProducts_2, $item_2, $value_2, $order);

					$item1a_2 = "sales";
					$value1a_2 = $value["quantity"] + $getProduct_2["sales"];

					$newSales_2 = ProductsModel::mdlUpdateProduct($tableProducts_2, $item1a_2, $value1a_2, $value_2);

					$item1b_2 = "stock";
					$value1b_2 = $getProduct_2["stock"] - $value["quantity"];

					$newStock_2 = ProductsModel::mdlUpdateProduct($tableProducts_2, $item1b_2, $value1b_2, $value_2);

				}

				$tableCustomers_2 = "customers";

				$item_2 = "id";
				$value_2 = $_POST["selectCustomer"];

				$getCustomer_2 = ModelCustomers::mdlShowCustomers($tableCustomers_2, $item_2, $value_2);

				$item1a_2 = "purchases";
				$value1a_2 = array_sum($totalPurchasedProducts_2) + $getCustomer_2["purchases"];

				$customerPurchases_2 = ModelCustomers::mdlUpdateCustomer($tableCustomers_2, $item1a_2, $value1a_2, $value_2);

				$item1b_2 = "lastPurchase";
				$value1b_2 = DateHelper::getDateTime();

				$dateCustomer_2 = ModelCustomers::mdlUpdateCustomer($tableCustomers_2, $item1b_2, $value1b_2, $value_2);

			}

			/*=============================================
			SAVE PURCHASE CHANGES
			=============================================*/	

			$data = array(
				"code"=>$_POST["editSale"],
				"idCustomer"=>$_POST["selectCustomer"],
				"idSeller"=>$_POST["idSeller"],
				"products"=>$productsList,
				"tax"=>$_POST["newTaxPrice"],
				"discount"=>isset($_POST["saleDiscount"]) ? $_POST["saleDiscount"] : 0,
				"totalPrice"=>$_POST["saleTotal"],
				"paymentMethod"=>$_POST["listPaymentMethod"]
			);

			$answer = ModelSales::mdlEditSale($table, $data);

			if($answer == "ok"){

				// Check if this is an installment payment and update installment plan
				if(strpos($_POST["listPaymentMethod"], "installment") === 0) {
					
					// Get installment data from form
					$numberOfPayments = isset($_POST["installmentMonths"]) ? intval($_POST["installmentMonths"]) : 3;
					$interestRate = isset($_POST["installmentInterest"]) ? floatval($_POST["installmentInterest"]) : 0;
					$paymentFrequency = isset($_POST["installmentFrequency"]) ? $_POST["installmentFrequency"] : "30th";
					
					// Get downpayment data
					$hasDownpayment = isset($_POST["hasDownpayment"]) && $_POST["hasDownpayment"] === "on";
					$downpaymentAmount = $hasDownpayment && isset($_POST["downpaymentAmount"]) ? floatval($_POST["downpaymentAmount"]) : 0;
					
					// Calculate new totals with downpayment
					$baseAmount = floatval($_POST["saleTotal"]);
					$remainingAmount = $baseAmount - $downpaymentAmount;
					$monthlyInterest = $interestRate / 100;
					$totalAmountWithInterest = $remainingAmount * (1 + ($monthlyInterest * $numberOfPayments));
					$finalTotalAmount = $downpaymentAmount + $totalAmountWithInterest;
					
					// Calculate actual number of payments based on frequency
					$actualNumberOfPayments = $paymentFrequency === "both" ? $numberOfPayments * 2 : $numberOfPayments;
					$paymentAmount = round($totalAmountWithInterest / $actualNumberOfPayments, 2);
					
					try {
						// Get the sale ID
						$saleId = $getSale["id"];
						
						// Check if downpayment_amount field exists for backward compatibility
						$checkColumn = Connection::connect()->prepare("SHOW COLUMNS FROM installment_plans LIKE 'downpayment_amount'");
						$checkColumn->execute();
						$columnExists = $checkColumn->fetch();
						
						// Update existing installment plan
						if($columnExists) {
							// New version with downpayment support
							$stmt = Connection::connect()->prepare("UPDATE installment_plans SET 
								total_amount = :total_amount, 
								base_amount = :base_amount, 
								number_of_payments = :number_of_payments, 
								payment_amount = :payment_amount, 
								interest_rate = :interest_rate,
								payment_frequency = :payment_frequency,
								downpayment_amount = :downpayment_amount
								WHERE sale_id = :sale_id");
						} else {
							// Old version without downpayment field
							$stmt = Connection::connect()->prepare("UPDATE installment_plans SET 
								total_amount = :total_amount, 
								base_amount = :base_amount, 
								number_of_payments = :number_of_payments, 
								payment_amount = :payment_amount, 
								interest_rate = :interest_rate
								WHERE sale_id = :sale_id");
						}
						
						$stmt->bindParam(":total_amount", $finalTotalAmount, PDO::PARAM_STR);
						$stmt->bindParam(":base_amount", $baseAmount, PDO::PARAM_STR);
						$stmt->bindParam(":number_of_payments", $actualNumberOfPayments, PDO::PARAM_INT);
						$stmt->bindParam(":payment_amount", $paymentAmount, PDO::PARAM_STR);
						$stmt->bindParam(":interest_rate", $interestRate, PDO::PARAM_STR);
						
						if($columnExists) {
							$stmt->bindParam(":payment_frequency", $paymentFrequency, PDO::PARAM_STR);
							$stmt->bindParam(":downpayment_amount", $downpaymentAmount, PDO::PARAM_STR);
						}
						
						$stmt->bindParam(":sale_id", $saleId, PDO::PARAM_INT);
						
						if($stmt->execute()) {
							// Get the installment plan ID
							$planStmt = Connection::connect()->prepare("SELECT id FROM installment_plans WHERE sale_id = :sale_id");
							$planStmt->bindParam(":sale_id", $saleId, PDO::PARAM_INT);
							$planStmt->execute();
							$plan = $planStmt->fetch();
							
							if($plan) {
								$planId = $plan["id"];
								
								// Delete existing payment records
								$deleteStmt = Connection::connect()->prepare("DELETE FROM installment_payments WHERE installment_plan_id = :plan_id");
								$deleteStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
								$deleteStmt->execute();
								
								// Create new payment records based on frequency
								$paymentCount = 0;
								$individualPaymentAmount = $paymentAmount; // Already calculated correctly above
								
								for($i = 1; $i <= $numberOfPayments; $i++) {
									if($paymentFrequency === "15th" || $paymentFrequency === "both") {
										// Payment on 15th
										$paymentCount++;
										$currentMonth = date('m');
										$currentYear = date('Y');
										$targetMonth = $currentMonth + $i;
										$targetYear = $currentYear;
										if($targetMonth > 12) {
											$targetYear += floor(($targetMonth - 1) / 12);
											$targetMonth = (($targetMonth - 1) % 12) + 1;
										}
										$dueDate = date('Y-m-d', mktime(0, 0, 0, $targetMonth, 15, $targetYear));
										
										$paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:installment_plan_id, :payment_number, :amount, :due_date, :status)");
										
										$paymentStmt->bindParam(":installment_plan_id", $planId, PDO::PARAM_INT);
										$paymentStmt->bindParam(":payment_number", $paymentCount, PDO::PARAM_INT);
										$paymentStmt->bindParam(":amount", $individualPaymentAmount, PDO::PARAM_STR);
										$paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
										$pendingStatus = "pending";
										$paymentStmt->bindParam(":status", $pendingStatus, PDO::PARAM_STR);
										
										$paymentStmt->execute();
									}
									
									if($paymentFrequency === "30th" || $paymentFrequency === "both") {
										// Payment on 30th
										$paymentCount++;
										$currentMonth = date('m');
										$currentYear = date('Y');
										$targetMonth = $currentMonth + $i;
										$targetYear = $currentYear;
										if($targetMonth > 12) {
											$targetYear += floor(($targetMonth - 1) / 12);
											$targetMonth = (($targetMonth - 1) % 12) + 1;
										}
										$dueDate = date('Y-m-d', mktime(0, 0, 0, $targetMonth, 30, $targetYear));
										
										$paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:installment_plan_id, :payment_number, :amount, :due_date, :status)");
										
										$paymentStmt->bindParam(":installment_plan_id", $planId, PDO::PARAM_INT);
										$paymentStmt->bindParam(":payment_number", $paymentCount, PDO::PARAM_INT);
										$paymentStmt->bindParam(":amount", $individualPaymentAmount, PDO::PARAM_STR);
										$paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
										$pendingStatus = "pending";
										$paymentStmt->bindParam(":status", $pendingStatus, PDO::PARAM_STR);
										
										$paymentStmt->execute();
									}
								}
							}
						}
						
					} catch(Exception $e) {
						// Installment plan update failed, but sale was successful
						// Could log error or show warning
					}
				}

				echo'<script>

				localStorage.removeItem("range");

				swal({
					  type: "success",
					  title: "Sale edited successfully",
					  showConfirmButton: true,
					  confirmButtonText: "Close"
					  }).then((result) => {
								if (result.value) {

								window.location = "index.php?route=sales";

								}
							})

				</script>';

			}

		}

	}
	/* --LOG ON TO codeastro.com FOR MORE PROJECTS-- */
	/*=============================================
	Delete Sale
	=============================================*/

	static public function ctrDeleteSale(){

		if(isset($_GET["idSale"])){

			$table = "sales";

			$item = "id";
			$value = $_GET["idSale"];

			$getSale = ModelSales::mdlShowSales($table, $item, $value);

			/*=============================================
			Update last Purchase date
			=============================================*/

			$tableCustomers = "customers";

			$itemsales = null;
			$valuesales = null;

			$getSales = ModelSales::mdlShowSales($table, $itemsales, $valuesales);

			$saveDates = array();

			foreach ($getSales as $key => $value) {
				
				if($value["idCustomer"] == $getSale["idCustomer"]){

					array_push($saveDates, $value["saledate"]);

				}

			}

			if(count($saveDates) > 1){

				if($getSale["saledate"] > $saveDates[count($saveDates)-2]){

					$item = "lastPurchase";
					$value = $saveDates[count($saveDates)-2];
					$valueIdCustomer = $getSale["idCustomer"];

					$customerPurchases = ModelCustomers::mdlUpdateCustomer($tableCustomers, $item, $value, $valueIdCustomer);

				}else{

					$item = "lastPurchase";
					$value = $saveDates[count($saveDates)-1];
					$valueIdCustomer = $getSale["idCustomer"];

					$customerPurchases = ModelCustomers::mdlUpdateCustomer($tableCustomers, $item, $value, $valueIdCustomer);

				}


			}else{

				$item = "lastPurchase";
				$value = "1970-01-01 00:00:00";
				$valueIdCustomer = $getSale["idCustomer"];

				$customerPurchases = ModelCustomers::mdlUpdateCustomer($tableCustomers, $item, $value, $valueIdCustomer);

			}
			/* --LOG ON TO codeastro.com FOR MORE PROJECTS-- */
			/*=============================================
			FORMAT PRODUCTS AND CUSTOMERS TABLE
			=============================================*/

			$products =  json_decode($getSale["products"], true);

			$totalPurchasedProducts = array();

			foreach ($products as $key => $value) {

				array_push($totalPurchasedProducts, $value["quantity"]);
				
				$tableProducts = "products";

				$item = "id";
				$valueProductId = $value["id"];
				$order = "id";

				$getProduct = ProductsModel::mdlShowProducts($tableProducts, $item, $valueProductId, $order);

				$item1a = "sales";
				$value1a = $getProduct["sales"] - $value["quantity"];

				$newSales = ProductsModel::mdlUpdateProduct($tableProducts, $item1a, $value1a, $valueProductId);

				$item1b = "stock";
				$value1b = $value["quantity"] + $getProduct["stock"];

				$stockNew = ProductsModel::mdlUpdateProduct($tableProducts, $item1b, $value1b, $valueProductId);

			}

			$tableCustomers = "customers";

			$itemCustomer = "id";
			$valueCustomer = $getSale["idCustomer"];

			$getCustomer = ModelCustomers::mdlShowCustomers($tableCustomers, $itemCustomer, $valueCustomer);

			$item1a = "purchases";
			$value1a = $getCustomer["purchases"] - array_sum($totalPurchasedProducts);

			$customerPurchases = ModelCustomers::mdlUpdateCustomer($tableCustomers, $item1a, $value1a, $valueCustomer);

			/*=============================================
			Delete Installment Plan if Payment Method is Installment
			=============================================*/

			// Check if this sale has an installment payment method
			if(strpos($getSale["paymentMethod"], "installment") === 0) {
				
				try {
					// First, delete all payment records associated with this installment plan
					$deletePaymentsStmt = Connection::connect()->prepare("DELETE installment_payments FROM installment_payments 
						INNER JOIN installment_plans ON installment_payments.installment_plan_id = installment_plans.id 
						WHERE installment_plans.sale_id = :sale_id");
					$deletePaymentsStmt->bindParam(":sale_id", $_GET["idSale"], PDO::PARAM_INT);
					$deletePaymentsStmt->execute();
					
					// Then, delete the installment plan itself
					$deletePlanStmt = Connection::connect()->prepare("DELETE FROM installment_plans WHERE sale_id = :sale_id");
					$deletePlanStmt->bindParam(":sale_id", $_GET["idSale"], PDO::PARAM_INT);
					$deletePlanStmt->execute();
					
				} catch(Exception $e) {
					// Log error but don't stop the sale deletion process
					error_log("Error deleting installment plan for sale ID " . $_GET["idSale"] . ": " . $e->getMessage());
				}
			}

			/*=============================================
			Delete Sale
			=============================================*/

			$answer = ModelSales::mdlDeleteSale($table, $_GET["idSale"]);

			if($answer == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "Sale deleted successfully",
					  showConfirmButton: true,
					  confirmButtonText: "Close"
					  }).then((result) => {
								if (result.value) {

								window.location = "index.php?route=sales";

								}
							})

				</script>';

			}		
		}

	}
	/* --LOG ON TO codeastro.com FOR MORE PROJECTS-- */
	/*=============================================
	DATES RANGE
	=============================================*/	

	static public function ctrSalesDatesRange($initialDate, $finalDate){

		$table = "sales";

		$answer = ModelSales::mdlSalesDatesRange($table, $initialDate, $finalDate);

		return $answer;
		
	}

	/*=============================================
	COMPLETED SALES DATES RANGE (for accurate sales graph)
	=============================================*/	

	static public function ctrCompletedSalesDatesRange($initialDate, $finalDate){

		$table = "sales";

		$answer = ModelSales::mdlCompletedSalesDateRange($table, $initialDate, $finalDate);

		return $answer;
		
	}

	/*=============================================
	DOWNLOAD EXCEL
	=============================================*/

	public function ctrDownloadReport(){

		if(isset($_GET["report"])){

			$table = "sales";

			if(isset($_GET["initialDate"]) && isset($_GET["finalDate"])){

				$sales = ModelSales::mdlSalesDatesRange($table, $_GET["initialDate"], $_GET["finalDate"]);

			}else{

				$item = null;
				$value = null;

				$sales = ModelSales::mdlShowSales($table, $item, $value);

			}

			/*=============================================
			WE CREATE EXCEL FILE
			=============================================*/

			$name = $_GET["report"].'.xls';

			header('Expires: 0');
			header('Cache-control: private');
			header("Content-type: application/vnd.ms-excel; charset=UTF-8"); // Added charset
			header("Cache-Control: cache, must-revalidate"); 
			header('Content-Description: File Transfer');
			header('Last-Modified: '.DateHelper::getDateTime());
			header("Pragma: public"); 
			header('Content-Disposition:; filename="'.$name.'"');
			header("Content-Transfer-Encoding: binary");
            
            // Define peso sign - using PHP chr() function
            $pesoSign = chr(0x20B1); // Unicode for peso sign

			echo utf8_decode("<table border='0'> 

					<tr> 
					<td style='font-weight:bold; border:1px solid #eee;'>CODE</td> 
					<td style='font-weight:bold; border:1px solid #eee;'>CUSTOMER</td>
					<td style='font-weight:bold; border:1px solid #eee;'>SELLER</td>
					<td style='font-weight:bold; border:1px solid #eee;'>QUANTITY</td>
					<td style='font-weight:bold; border:1px solid #eee;'>PRODUCTS</td>
					<td style='font-weight:bold; border:1px solid #eee;'>TAX</td>
					<td style='font-weight:bold; border:1px solid #eee;'>NET PRICE</td>		
					<td style='font-weight:bold; border:1px solid #eee;'>TOTAL</td>		
					<td style='font-weight:bold; border:1px solid #eee;'>METHOD OF PAYMENT</td>	
					<td style='font-weight:bold; border:1px solid #eee;'>DATE</td>		
					</tr>");

			foreach ($sales as $row => $item){

				$customer = ControllerCustomers::ctrShowCustomers("id", $item["idCustomer"]);
				$Seller = ControllerUsers::ctrShowUsers("id", $item["idSeller"]);

			 echo utf8_decode("<tr>
			 			<td style='border:1px solid #eee;'>".$item["code"]."</td> 
			 			<td style='border:1px solid #eee;'>".$customer["name"]."</td>
			 			<td style='border:1px solid #eee;'>".$Seller["name"]."</td>
			 			<td style='border:1px solid #eee;'>");

			 	$products =  json_decode($item["products"], true);

			 	foreach ($products as $key => $valueproducts) {
			 			
			 			echo utf8_decode($valueproducts["quantity"]."<br>");
			 		}

			 	echo utf8_decode("</td><td style='border:1px solid #eee;'>");	

		 		foreach ($products as $key => $valueproducts) {
			 			
		 			echo utf8_decode($valueproducts["description"]."<br>");
		 		
		 		}

		 		echo utf8_decode("</td>
					<td style='border:1px solid #eee;'>PHP ".number_format($item["tax"],2)."</td>
					<td style='border:1px solid #eee;'>PHP ".number_format($item["netPrice"],2)."</td>	
					<td style='border:1px solid #eee;'>PHP ".number_format($item["totalPrice"],2)."</td>
					<td style='border:1px solid #eee;'>".$item["paymentMethod"]."</td>
					<td style='border:1px solid #eee;'>".substr($item["saledate"],0,10)."</td>		
		 			</tr>");

			}


			echo "</table>";

		}

	}

	/* --LOG ON TO codeastro.com FOR MORE PROJECTS-- */
	/*=============================================
	Adding TOTAL sales
	=============================================*/

	static public function ctrAddingTotalSales(){

		$table = "sales";

		$answer = ModelSales::mdlAddingTotalSales($table);

		return $answer;

	}

	/*=============================================
	SALES BY PAYMENT METHOD
	=============================================*/

	static public function ctrSalesByPaymentMethod($paymentMethod){

		$table = "sales";

		$answer = ModelSales::mdlSalesByPaymentMethod($table, $paymentMethod);

		return $answer;

	}

	/*=============================================
	INSTALLMENT STATISTICS
	=============================================*/

	static public function ctrInstallmentStats(){

		$answer = ModelSales::mdlInstallmentStats();

		return $answer;

	}

	/*=============================================
	COMPLETED SALES TOTAL (Cash + QRPH + Card + Paid Installments)
	=============================================*/

	static public function ctrCompletedSalesTotal(){

		$answer = ModelSales::mdlCompletedSalesTotal();

		return $answer;

	}

	/*=============================================
	AUTO-FIX MISSING PAYMENT RECORDS FOR A SALE
	=============================================*/
	
	static private function autoFixMissingPaymentRecords($saleId) {
		try {
			error_log("Auto-fixing missing payment records for sale ID: " . $saleId);
			
			// Find any installment plan for this sale
			$planStmt = Connection::connect()->prepare("SELECT * FROM installment_plans WHERE sale_id = :sale_id");
			$planStmt->bindParam(":sale_id", $saleId, PDO::PARAM_INT);
			$planStmt->execute();
			$plan = $planStmt->fetch();
			
			if($plan) {
				error_log("Found installment plan ID: " . $plan['id'] . " for sale ID: " . $saleId);
				
				// Check if payment records exist
				$paymentStmt = Connection::connect()->prepare("SELECT COUNT(*) as payment_count FROM installment_payments WHERE installment_plan_id = :plan_id");
				$paymentStmt->bindParam(":plan_id", $plan['id'], PDO::PARAM_INT);
				$paymentStmt->execute();
				$paymentResult = $paymentStmt->fetch();
				
				if($paymentResult['payment_count'] == 0) {
					error_log("AUTO-FIX: No payment records found for plan " . $plan['id'] . ", creating them now...");
					self::createMissingPaymentRecords($plan['id']);
				} else {
					error_log("AUTO-FIX: Payment records already exist (" . $paymentResult['payment_count'] . " records)");
				}
			} else {
				error_log("AUTO-FIX: No installment plan found for sale ID: " . $saleId);
			}
			
		} catch(Exception $e) {
			error_log("AUTO-FIX: Error in autoFixMissingPaymentRecords: " . $e->getMessage());
		}
	}

	/*=============================================
	CREATE MISSING PAYMENT RECORDS FOR INSTALLMENT PLAN
	=============================================*/
	
	static private function createMissingPaymentRecords($planId) {
		try {
			error_log("Creating missing payment records for plan ID: " . $planId);
			
			// Get the installment plan details
			$planStmt = Connection::connect()->prepare("SELECT * FROM installment_plans WHERE id = :plan_id");
			$planStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
			$planStmt->execute();
			$plan = $planStmt->fetch();
			
			if(!$plan) {
				error_log("Plan not found for ID: " . $planId);
				return false;
			}
			
			$paymentFrequency = $plan['payment_frequency'];
			$actualNumberOfPayments = intval($plan['number_of_payments']);
			$totalAmount = floatval($plan['total_amount']);
			$paymentAmount = round($totalAmount / $actualNumberOfPayments, 2);
			$startDate = $plan['start_date'];
			
			// Calculate original months
			$originalMonths = $paymentFrequency === 'both' ? $actualNumberOfPayments / 2 : $actualNumberOfPayments;
			
			error_log("Plan details - Frequency: $paymentFrequency, Payments: $actualNumberOfPayments, Amount: $paymentAmount, Months: $originalMonths");
			
			$paymentCount = 0;
			for($i = 1; $i <= $originalMonths; $i++) {
				if($paymentFrequency === "15th" || $paymentFrequency === "both") {
					$paymentCount++;
					$currentMonth = date('m', strtotime($startDate));
					$currentYear = date('Y', strtotime($startDate));
					$targetMonth = $currentMonth + $i;
					$targetYear = $currentYear;
					if($targetMonth > 12) {
						$targetYear += floor(($targetMonth - 1) / 12);
						$targetMonth = (($targetMonth - 1) % 12) + 1;
					}
					$dueDate = date('Y-m-d', mktime(0, 0, 0, $targetMonth, 15, $targetYear));
					
					$paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:plan_id, :payment_number, :amount, :due_date, 'pending')");
					$paymentStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
					$paymentStmt->bindParam(":payment_number", $paymentCount, PDO::PARAM_INT);
					$paymentStmt->bindParam(":amount", $paymentAmount, PDO::PARAM_STR);
					$paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
					
					if($paymentStmt->execute()) {
						error_log("FALLBACK: Created payment {$paymentCount} (15th): {$dueDate} - Amount: {$paymentAmount}");
					} else {
						error_log("FALLBACK: Failed to create payment {$paymentCount} (15th)");
					}
				}
				
				if($paymentFrequency === "30th" || $paymentFrequency === "both") {
					$paymentCount++;
					$currentMonth = date('m', strtotime($startDate));
					$currentYear = date('Y', strtotime($startDate));
					$targetMonth = $currentMonth + $i;
					$targetYear = $currentYear;
					if($targetMonth > 12) {
						$targetYear += floor(($targetMonth - 1) / 12);
						$targetMonth = (($targetMonth - 1) % 12) + 1;
					}
					$lastDayOfMonth = date('t', mktime(0, 0, 0, $targetMonth, 1, $targetYear));
					$dueDate = date('Y-m-d', mktime(0, 0, 0, $targetMonth, min(30, $lastDayOfMonth), $targetYear));
					
					$paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:plan_id, :payment_number, :amount, :due_date, 'pending')");
					$paymentStmt->bindParam(":plan_id", $planId, PDO::PARAM_INT);
					$paymentStmt->bindParam(":payment_number", $paymentCount, PDO::PARAM_INT);
					$paymentStmt->bindParam(":amount", $paymentAmount, PDO::PARAM_STR);
					$paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
					
					if($paymentStmt->execute()) {
						error_log("FALLBACK: Created payment {$paymentCount} (30th): {$dueDate} - Amount: {$paymentAmount}");
					} else {
						error_log("FALLBACK: Failed to create payment {$paymentCount} (30th)");
					}
				}
			}
			
			error_log("FALLBACK: Completed creating {$paymentCount} payment records for plan {$planId}");
			return true;
			
		} catch(Exception $e) {
			error_log("FALLBACK: Error creating missing payment records: " . $e->getMessage());
			return false;
		}
	}

}