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
				"totalPrice"=>$_POST["saleTotal"],
				"paymentMethod"=>$_POST["listPaymentMethod"]
			);

			$answer = ModelSales::mdlAddSale($table, $data);

			if($answer == "ok"){
				// Check if this is an installment payment
				if(strpos($_POST["listPaymentMethod"], "installment") === 0) {
					
					// Get number of payments from form data
					$numberOfPayments = isset($_POST["installmentMonths"]) ? intval($_POST["installmentMonths"]) : 3;
					$interestRate = isset($_POST["installmentInterest"]) ? floatval($_POST["installmentInterest"]) : 0;
					
					// Get the last inserted sale ID
					$lastSaleId = ModelSales::mdlGetLastInsertId();
					
					// Calculate total amount with interest
					$baseAmount = floatval($_POST["saleTotal"]);
					$monthlyInterest = $interestRate / 100;
					$totalAmount = $baseAmount * (1 + ($monthlyInterest * $numberOfPayments));
					$paymentAmount = $totalAmount / $numberOfPayments;
					$startDate = date('Y-m-d');
					
					try {
						// Create installment plan directly
						$stmt = Connection::connect()->prepare("INSERT INTO installment_plans(sale_id, customer_id, total_amount, base_amount, number_of_payments, payment_amount, interest_rate, start_date, status) VALUES (:sale_id, :customer_id, :total_amount, :base_amount, :number_of_payments, :payment_amount, :interest_rate, :start_date, :status)");
						
						$stmt->bindParam(":sale_id", $lastSaleId, PDO::PARAM_INT);
						$stmt->bindParam(":customer_id", $_POST["selectCustomer"], PDO::PARAM_INT);
						$stmt->bindParam(":total_amount", $totalAmount, PDO::PARAM_STR);
						$stmt->bindParam(":base_amount", $baseAmount, PDO::PARAM_STR);
						$stmt->bindParam(":number_of_payments", $numberOfPayments, PDO::PARAM_INT);
						$stmt->bindParam(":payment_amount", $paymentAmount, PDO::PARAM_STR);
						$stmt->bindParam(":interest_rate", $interestRate, PDO::PARAM_STR);
						$stmt->bindParam(":start_date", $startDate, PDO::PARAM_STR);
						$status = "active";
						$stmt->bindParam(":status", $status, PDO::PARAM_STR);
						
						if($stmt->execute()) {
							$planId = Connection::connect()->lastInsertId();
							
							// Create individual payment records
							for($i = 1; $i <= $numberOfPayments; $i++) {
								$dueDate = date('Y-m-d', strtotime($startDate . " + " . $i . " months"));
								
								$paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:installment_plan_id, :payment_number, :amount, :due_date, :status)");
								
								$paymentStmt->bindParam(":installment_plan_id", $planId, PDO::PARAM_INT);
								$paymentStmt->bindParam(":payment_number", $i, PDO::PARAM_INT);
								$paymentStmt->bindParam(":amount", $paymentAmount, PDO::PARAM_STR);
								$paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
								$pendingStatus = "pending";
								$paymentStmt->bindParam(":status", $pendingStatus, PDO::PARAM_STR);
								
								$paymentStmt->execute();
							}
						}
						
					} catch(Exception $e) {
						// Installment plan creation failed, but sale was successful
						// Could log error or show warning
					}
				}

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
					$value = $value["id"];
					$order = "id";

					$getProduct = ProductsModel::mdlShowProducts($tableProducts, $item, $value, $order);

					$item1a = "sales";
					$value1a = $getProduct["sales"] - $value["quantity"];

					$newSales = ProductsModel::mdlUpdateProduct($tableProducts, $item1a, $value1a, $value);

					$item1b = "stock";
					$value1b = $value["quantity"] + $getProduct["stock"];

					$stockNew = ProductsModel::mdlUpdateProduct($tableProducts, $item1b, $value1b, $value);

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
					
					// Calculate new totals
					$baseAmount = floatval($_POST["saleTotal"]);
					$monthlyInterest = $interestRate / 100;
					$totalAmount = $baseAmount * (1 + ($monthlyInterest * $numberOfPayments));
					$paymentAmount = $totalAmount / $numberOfPayments;
					
					try {
						// Get the sale ID
						$saleId = $getSale["id"];
						
						// Update existing installment plan
						$stmt = Connection::connect()->prepare("UPDATE installment_plans SET 
							total_amount = :total_amount, 
							base_amount = :base_amount, 
							number_of_payments = :number_of_payments, 
							payment_amount = :payment_amount, 
							interest_rate = :interest_rate 
							WHERE sale_id = :sale_id");
						
						$stmt->bindParam(":total_amount", $totalAmount, PDO::PARAM_STR);
						$stmt->bindParam(":base_amount", $baseAmount, PDO::PARAM_STR);
						$stmt->bindParam(":number_of_payments", $numberOfPayments, PDO::PARAM_INT);
						$stmt->bindParam(":payment_amount", $paymentAmount, PDO::PARAM_STR);
						$stmt->bindParam(":interest_rate", $interestRate, PDO::PARAM_STR);
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
								
								// Create new payment records with updated amounts
								$startDate = date('Y-m-d');
								for($i = 1; $i <= $numberOfPayments; $i++) {
									$dueDate = date('Y-m-d', strtotime($startDate . " + " . $i . " months"));
									
									$paymentStmt = Connection::connect()->prepare("INSERT INTO installment_payments(installment_plan_id, payment_number, amount, due_date, status) VALUES (:installment_plan_id, :payment_number, :amount, :due_date, :status)");
									
									$paymentStmt->bindParam(":installment_plan_id", $planId, PDO::PARAM_INT);
									$paymentStmt->bindParam(":payment_number", $i, PDO::PARAM_INT);
									$paymentStmt->bindParam(":amount", $paymentAmount, PDO::PARAM_STR);
									$paymentStmt->bindParam(":due_date", $dueDate, PDO::PARAM_STR);
									$pendingStatus = "pending";
									$paymentStmt->bindParam(":status", $pendingStatus, PDO::PARAM_STR);
									
									$paymentStmt->execute();
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
				$value = "0000-00-00 00:00:00";
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

}