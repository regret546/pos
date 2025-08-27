<?php

// Start output buffering
ob_start();

require_once "../controllers/products.controller.php";
require_once "../models/products.model.php";
require_once "../controllers/categories.controller.php";
require_once "../models/categories.model.php";

// Clean any output that might have occurred during includes
ob_clean();

// Set proper JSON content type
header('Content-Type: application/json');

class productsTable{

	/*=============================================
 	 SHOW PRODUCTS TABLE
  	=============================================*/ 
	public function showProductsTable(){
		try {
			$item = null;
			$value = null;
			$order = "id";

			$products = controllerProducts::ctrShowProducts($item, $value, $order);
			
			// If products is false or not an array, return empty data
			if(!is_array($products)) {
				echo json_encode(array("data" => array()));
				return;
			}

			$data = array();

			foreach($products as $i => $product){
				// Skip any invalid product entries - check for both model and code fields for backward compatibility
				$modelField = isset($product["model"]) ? $product["model"] : (isset($product["code"]) ? $product["code"] : null);
				if(!isset($product["image"], $product["idCategory"], $product["stock"], 
					     $product["id"], $product["description"], 
					     $product["buyingPrice"], $product["sellingPrice"], $product["date"]) || 
				   $modelField === null) {
					continue;
				}

				// Get category info
				$item = "id";
				$value = $product["idCategory"];
				$categories = ControllerCategories::ctrShowCategories($item, $value);

				// Skip if category is invalid
				if(!is_array($categories) || !isset($categories["Category"])) {
					continue;
				}

				// Format stock button
				$stockValue = intval($product["stock"]);
				if($stockValue <= 10){
					$stock = "<button class='btn btn-danger'>".$stockValue."</button>";
				} else if($stockValue > 11 && $stockValue <= 15){
					$stock = "<button class='btn btn-warning'>".$stockValue."</button>";
				} else {
					$stock = "<button class='btn btn-success'>".$stockValue."</button>";
				}

				// Format action buttons based on user profile
				$productId = intval($product["id"]);
				if (isset($_GET["hiddenProfile"]) && $_GET["hiddenProfile"] == "Special") {
					$buttons = "<div class='btn-group'><button class='btn btn-primary btnEditProduct' idProduct='".$productId."' data-toggle='modal' data-target='#modalEditProduct'><i class='fa fa-pencil'></i></button></div>";
				} else {
					$buttons = "<div class='btn-group'><button class='btn btn-primary btnEditProduct' idProduct='".$productId."' data-toggle='modal' data-target='#modalEditProduct'><i class='fa fa-pencil'></i></button><button class='btn btn-danger btnDeleteProduct' idProduct='".$productId."' model='".htmlspecialchars($modelField)."' image='".htmlspecialchars($product["image"])."'><i class='fa fa-trash'></i></button></div>";
				}

				// Build the row data
				$row = array(
					($i + 1),
					"<img src='".htmlspecialchars($product["image"])."' width='40px'>",
					htmlspecialchars($modelField),
					htmlspecialchars($product["description"]),
					strtoupper(htmlspecialchars($categories["Category"])),
					$stock,
					"₱ ".number_format((float)$product["buyingPrice"], 2),
					"₱ ".number_format((float)$product["sellingPrice"], 2),
					$product["date"],
					$buttons
				);

				$data[] = $row;
			}

			echo json_encode(array("data" => $data));

		} catch (Exception $e) {
			echo json_encode(array("data" => array()));
		}
	}
}

/*=============================================
ACTIVATE PRODUCTS TABLE
=============================================*/ 
$activateProducts = new productsTable();
$activateProducts -> showProductsTable();
