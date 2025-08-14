<?php

require_once "connection.php";
require_once "date.helper.php";

class ModelSales{

	/*=============================================
	SHOW SALES
	=============================================*/

	static public function mdlShowSales($table, $item, $value){

		if($item != null){

			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE $item = :$item ORDER BY id ASC");

			$stmt -> bindParam(":".$item, $value, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Connection::connect()->prepare("SELECT * FROM $table ORDER BY id ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}
		
		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	CREATE SALE
	=============================================*/

	static public function mdlAddSale($table, $data){

		DateHelper::init();
		$saledate = DateHelper::getDateTime();

		// Use the same connection object for both insert and lastInsertId
		$pdo = Connection::connect();
		$stmt = $pdo->prepare("INSERT INTO $table(code, idSeller, idCustomer, products, tax, discount, netPrice, totalPrice, paymentMethod, saledate) VALUES (:code, :idSeller, :idCustomer, :products, :tax, :discount, :netPrice, :totalPrice, :paymentMethod, :saledate)");

		$stmt->bindParam(":code", $data["code"], PDO::PARAM_INT);
		$stmt->bindParam(":idSeller", $data["idSeller"], PDO::PARAM_INT);
		$stmt->bindParam(":idCustomer", $data["idCustomer"], PDO::PARAM_INT);
		$stmt->bindParam(":products", $data["products"], PDO::PARAM_STR);
		$stmt->bindParam(":tax", $data["tax"], PDO::PARAM_STR);
		$stmt->bindParam(":discount", $data["discount"], PDO::PARAM_STR);
		$stmt->bindParam(":netPrice", $data["totalPrice"], PDO::PARAM_STR); // Use totalPrice before tax
		$stmt->bindParam(":totalPrice", $data["totalPrice"], PDO::PARAM_STR);
		$stmt->bindParam(":paymentMethod", $data["paymentMethod"], PDO::PARAM_STR);
		$stmt->bindParam(":saledate", $saledate, PDO::PARAM_STR);

		if($stmt->execute()){

			// Get the inserted ID from the same connection object
			$insertId = $pdo->lastInsertId();
			
			// Log for debugging
			error_log("Sale inserted with ID: " . $insertId);
			
			return array("status" => "ok", "id" => intval($insertId));

		}else{

			error_log("Sale insertion failed");
			return array("status" => "error");
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDIT SALE
	=============================================*/

	static public function mdlEditSale($table, $data){

		DateHelper::init();
		$saledate = DateHelper::getDateTime();
		
		$stmt = Connection::connect()->prepare("UPDATE $table SET idCustomer = :idCustomer, idSeller = :idSeller, products = :products, tax = :tax, discount = :discount, netPrice = :netPrice, totalPrice = :totalPrice, paymentMethod = :paymentMethod, saledate = :saledate WHERE code = :code");

		$stmt->bindParam(":code", $data["code"], PDO::PARAM_INT);
		$stmt->bindParam(":idSeller", $data["idSeller"], PDO::PARAM_INT);
		$stmt->bindParam(":idCustomer", $data["idCustomer"], PDO::PARAM_INT);
		$stmt->bindParam(":products", $data["products"], PDO::PARAM_STR);
		$stmt->bindParam(":tax", $data["tax"], PDO::PARAM_STR);
		$stmt->bindParam(":discount", $data["discount"], PDO::PARAM_STR);
		$stmt->bindParam(":netPrice", $data["totalPrice"], PDO::PARAM_STR); // Use totalPrice before tax
		$stmt->bindParam(":totalPrice", $data["totalPrice"], PDO::PARAM_STR);
		$stmt->bindParam(":paymentMethod", $data["paymentMethod"], PDO::PARAM_STR);
		$stmt->bindParam(":saledate", $saledate, PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	DELETE SALE
	=============================================*/

	static public function mdlDeleteSale($table, $data){

		$stmt = Connection::connect()->prepare("DELETE FROM $table WHERE id = :id");

		$stmt -> bindParam(":id", $data, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	DATES RANGE
	=============================================*/	

	static public function mdlSalesDatesRange($table, $initialDate, $finalDate){

		if($initialDate == null){

			$stmt = Connection::connect()->prepare("SELECT * FROM $table ORDER BY id ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();	


		}else if($initialDate == $finalDate){

			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE DATE(saledate) = :saledate ORDER BY id ASC");

			$stmt -> bindParam(":saledate", $finalDate, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$actualDate = new DateTime();
			$actualDate ->add(new DateInterval("P1D"));
			$actualDatePlusOne = $actualDate->format("Y-m-d");

			$finalDate2 = new DateTime($finalDate);
			$finalDate2 ->add(new DateInterval("P1D"));
			$finalDatePlusOne = $finalDate2->format("Y-m-d");

			if($finalDatePlusOne == $actualDatePlusOne){

				$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE saledate BETWEEN '$initialDate' AND '$finalDatePlusOne'");

			}else{


				$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE saledate BETWEEN '$initialDate' AND '$finalDate'");

			}
		
			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}

	static public function mdlGetLastInsertId(){
		return Connection::connect()->lastInsertId();
	}

	/*=============================================
	ADDING TOTAL SALES
	=============================================*/

	static public function mdlAddingTotalSales($table){	
		
		$stmt = Connection::connect()->prepare("SELECT SUM(totalPrice) as total FROM $table");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	Add TO CART
	=============================================*/

	static public function mdlAddToCart($table, $data){

		DateHelper::init();
		$saledate = DateHelper::getDateTime();

		$stmt = Connection::connect()->prepare("INSERT INTO $table(code, idSeller, idCustomer, products, tax, discount, totalPrice, paymentMethod, saledate) VALUES (:code, :idSeller, :idCustomer, :products, :tax, :discount, :totalPrice, :paymentMethod, :saledate)");

		$stmt->bindParam(":code", $data["code"], PDO::PARAM_INT);
		$stmt->bindParam(":idSeller", $data["idSeller"], PDO::PARAM_INT);
		$stmt->bindParam(":idCustomer", $data["idCustomer"], PDO::PARAM_INT);
		$stmt->bindParam(":products", $data["products"], PDO::PARAM_STR);
		$stmt->bindParam(":tax", $data["tax"], PDO::PARAM_STR);
		$stmt->bindParam(":discount", $data["discount"], PDO::PARAM_STR);
		$stmt->bindParam(":totalPrice", $data["totalPrice"], PDO::PARAM_STR);
		$stmt->bindParam(":paymentMethod", $data["paymentMethod"], PDO::PARAM_STR);
		$stmt->bindParam(":saledate", $saledate, PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDIT CART
	=============================================*/

	static public function mdlEditCart($table, $data){

		DateHelper::init();
		$saledate = DateHelper::getDateTime();
		
		$stmt = Connection::connect()->prepare("UPDATE $table SET  idCustomer = :idCustomer, idSeller = :idSeller, products = :products, tax = :tax, discount = :discount, totalPrice= :totalPrice, paymentMethod = :paymentMethod, saledate = :saledate WHERE code = :code");

		$stmt->bindParam(":code", $data["code"], PDO::PARAM_INT);
		$stmt->bindParam(":idSeller", $data["idSeller"], PDO::PARAM_INT);
		$stmt->bindParam(":idCustomer", $data["idCustomer"], PDO::PARAM_INT);
		$stmt->bindParam(":products", $data["products"], PDO::PARAM_STR);
		$stmt->bindParam(":tax", $data["tax"], PDO::PARAM_STR);
		$stmt->bindParam(":discount", $data["discount"], PDO::PARAM_STR);
		$stmt->bindParam(":totalPrice", $data["totalPrice"], PDO::PARAM_STR);
		$stmt->bindParam(":paymentMethod", $data["paymentMethod"], PDO::PARAM_STR);
		$stmt->bindParam(":saledate", $saledate, PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	SALES BY PAYMENT METHOD
	=============================================*/

	static public function mdlSalesByPaymentMethod($table, $paymentMethod){

		$stmt = Connection::connect()->prepare("SELECT SUM(totalPrice) as total FROM $table WHERE paymentMethod = :paymentMethod");

		$stmt -> bindParam(":paymentMethod", $paymentMethod, PDO::PARAM_STR);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	INSTALLMENT STATISTICS
	=============================================*/

	static public function mdlInstallmentStats(){

		// Get total installment amounts (paid and pending)
		$stmt = Connection::connect()->prepare("
			SELECT 
				SUM(CASE WHEN ip.status = 'paid' THEN ip.amount ELSE 0 END) as paid_total,
				SUM(CASE WHEN ip.status = 'pending' THEN ip.amount ELSE 0 END) as pending_total,
				COUNT(CASE WHEN ip.status = 'paid' THEN 1 END) as paid_count,
				COUNT(CASE WHEN ip.status = 'pending' THEN 1 END) as pending_count
			FROM installment_payments ip
			INNER JOIN installment_plans ipl ON ip.installment_plan_id = ipl.id
		");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	COMPLETED SALES TOTAL (Cash + QRPH + Card + Paid Installments)
	=============================================*/

	static public function mdlCompletedSalesTotal(){

		$stmt = Connection::connect()->prepare("
			SELECT 
				(
					COALESCE((SELECT SUM(totalPrice) FROM sales WHERE paymentMethod = 'cash'), 0) +
					COALESCE((SELECT SUM(totalPrice) FROM sales WHERE paymentMethod = 'QRPH'), 0) +
					COALESCE((SELECT SUM(totalPrice) FROM sales WHERE paymentMethod = 'Card'), 0) +
					COALESCE((SELECT SUM(ip.amount) FROM installment_payments ip INNER JOIN installment_plans ipl ON ip.installment_plan_id = ipl.id WHERE ip.status = 'paid'), 0)
				) as total
		");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

}