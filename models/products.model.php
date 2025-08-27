<?php

require_once 'connection.php';

class ProductsModel{

	/*=============================================
	SHOWING PRODUCTS
	=============================================*/

	static public function mdlShowProducts($table, $item, $value, $order){
		$stmt = null;
		try {
			$conn = Connection::connect();

			if($item != null){
				$stmt = $conn->prepare("SELECT * FROM $table WHERE $item = :$item ORDER BY id DESC");
				$stmt->bindParam(":".$item, $value, PDO::PARAM_STR);
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				$stmt = $conn->prepare("SELECT * FROM $table ORDER BY $order DESC");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}

			// Add backward compatibility for model field
			if($result) {
				if(is_array($result) && isset($result[0])) {
					// Multiple results
					foreach($result as &$row) {
						if(!isset($row['model']) && isset($row['code'])) {
							$row['model'] = $row['code'];
						}
					}
				} else if(is_array($result)) {
					// Single result
					if(!isset($result['model']) && isset($result['code'])) {
						$result['model'] = $result['code'];
					}
				}
			}

			return $result ?: array();

		} catch(Exception $e) {
			return array();
		} finally {
			if($stmt) {
				$stmt->closeCursor();
				$stmt = null;
			}
		}
	}

	/*=============================================
	ADDING PRODUCT
	=============================================*/
	static public function mdlAddProduct($table, $data){
		$stmt = null;
		try {
			$conn = Connection::connect();
			$stmt = $conn->prepare("INSERT INTO $table(idCategory, model, description, image, stock, buyingPrice, sellingPrice, sales) VALUES (:idCategory, :model, :description, :image, :stock, :buyingPrice, :sellingPrice, :sales)");

			$stmt->bindParam(":idCategory", $data["idCategory"], PDO::PARAM_INT);
			$stmt->bindParam(":model", $data["model"], PDO::PARAM_STR);
			$stmt->bindParam(":description", $data["description"], PDO::PARAM_STR);
			$stmt->bindParam(":image", $data["image"], PDO::PARAM_STR);
			$stmt->bindParam(":stock", $data["stock"], PDO::PARAM_STR);
			$stmt->bindParam(":buyingPrice", $data["buyingPrice"], PDO::PARAM_STR);
			$stmt->bindParam(":sellingPrice", $data["sellingPrice"], PDO::PARAM_STR);
			$stmt->bindParam(":sales", $data["sales"], PDO::PARAM_INT);

			if($stmt->execute()){
				return "ok";
			}

			return "error";

		} catch(Exception $e) {
			return "error";
		} finally {
			if($stmt) {
				$stmt->closeCursor();
				$stmt = null;
			}
		}
	}

	/*=============================================
	EDITING PRODUCT
	=============================================*/
	static public function mdlEditProduct($table, $data){
		$stmt = null;
		try {
			$conn = Connection::connect();
			
			// Check if model column exists, if not use code column for backwards compatibility
			$checkColumn = $conn->prepare("SHOW COLUMNS FROM $table LIKE 'model'");
			$checkColumn->execute();
			$hasModelColumn = $checkColumn->rowCount() > 0;
			
			if($hasModelColumn) {
				$stmt = $conn->prepare("UPDATE $table SET idCategory = :idCategory, model = :model, description = :description, image = :image, stock = :stock, buyingPrice = :buyingPrice, sellingPrice = :sellingPrice WHERE id = :id");
			} else {
				// Fallback to code column if model doesn't exist yet
				$stmt = $conn->prepare("UPDATE $table SET idCategory = :idCategory, code = :model, description = :description, image = :image, stock = :stock, buyingPrice = :buyingPrice, sellingPrice = :sellingPrice WHERE id = :id");
			}

			$stmt->bindParam(":idCategory", $data["idCategory"], PDO::PARAM_INT);
			$stmt->bindParam(":id", $data["id"], PDO::PARAM_INT);
			$stmt->bindParam(":model", $data["model"], PDO::PARAM_STR);
			$stmt->bindParam(":description", $data["description"], PDO::PARAM_STR);
			$stmt->bindParam(":image", $data["image"], PDO::PARAM_STR);
			$stmt->bindParam(":stock", $data["stock"], PDO::PARAM_STR);
			$stmt->bindParam(":buyingPrice", $data["buyingPrice"], PDO::PARAM_STR);
			$stmt->bindParam(":sellingPrice", $data["sellingPrice"], PDO::PARAM_STR);

			if($stmt->execute()){
				return "ok";
			} else {
				// Log the actual error for debugging
				$errorInfo = $stmt->errorInfo();
				error_log("Product edit error: " . print_r($errorInfo, true));
				return "error";
			}

		} catch(Exception $e) {
			error_log("Product edit exception: " . $e->getMessage());
			return "error";
		} finally {
			if($stmt) {
				$stmt->closeCursor();
				$stmt = null;
			}
		}
	}

	/*=============================================
	DELETING PRODUCT
	=============================================*/

	static public function mdlDeleteProduct($table, $data){

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
	UPDATE PRODUCT
	=============================================*/

	static public function mdlUpdateProduct($table, $item1, $value1, $value){

		$stmt = Connection::connect()->prepare("UPDATE $table SET $item1 = :$item1 WHERE id = :id");

		$stmt -> bindParam(":".$item1, $value1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $value, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	SHOW ADDING OF THE SALES
	=============================================*/	

	static public function mdlShowAddingOfTheSales($table){

		$stmt = Connection::connect()->prepare("SELECT SUM(sales) as total FROM $table");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	SHOW TOTAL INVENTORY ITEMS
	=============================================*/	

	static public function mdlShowTotalInventoryItems($table){

		$stmt = Connection::connect()->prepare("SELECT SUM(stock) as total FROM $table");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	SHOW TOTAL INVENTORY VALUE
	=============================================*/	

	static public function mdlShowTotalInventoryValue($table){

		$stmt = Connection::connect()->prepare("SELECT SUM(stock * sellingPrice) as total FROM $table");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}


	
}