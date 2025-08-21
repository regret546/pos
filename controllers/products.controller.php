<?php

class ControllerProducts{

	/*=============================================
	SHOW PRODUCTS
	=============================================*/
	
	static public function ctrShowProducts($item, $value, $order){

		$table = "products";

		$answer = ProductsModel::mdlShowProducts($table, $item, $value, $order);

		return $answer;

	}

	/*=============================================
	CREATE PRODUCTS
	=============================================*/

	static public function ctrCreateProducts(){

		if(isset($_POST["newDescription"])){

			// Check if category is selected
			if(empty($_POST["newCategory"]) || $_POST["newCategory"] == ""){
				
				echo'<script>

					swal({
						  type: "error",
						  title: "Category must be selected!",
						  text: "Please select a category for the product.",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
							if (result.value) {

							window.location = "index.php?route=products";

							}
						})

			  	</script>';

			  	return;
			}

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newDescription"]) &&
			   preg_match('/^[0-9]+$/', $_POST["newStock"]) &&	
			   preg_match('/^[0-9.]+$/', $_POST["newBuyingPrice"]) &&
			   preg_match('/^[0-9.]+$/', $_POST["newSellingPrice"])){

		   		/*=============================================
				VALIDATE IMAGE
				=============================================*/

			   	$route = "views/img/products/default/anonymous.png";

			   	if(isset($_FILES["newProdPhoto"]["tmp_name"]) && !empty($_FILES["newProdPhoto"]["tmp_name"])){

					list($width, $height) = getimagesize($_FILES["newProdPhoto"]["tmp_name"]);

					$newWidth = 500;
					$newHeight = 500;

					/*=============================================
					we create the directory to save the photo
					=============================================*/

					$directory = "views/img/products/".$_POST["newCode"];

					mkdir($directory, 0755);

					/*=============================================
					PHP functions depending on the image
					=============================================*/

					if($_FILES["newProdPhoto"]["type"] == "image/jpeg"){

						$randomNumber = mt_rand(100,999);
						
						$route = "views/img/products/".$_POST["newCode"]."/".$randomNumber.".jpg";

						$source = imagecreatefromjpeg($_FILES["newProdPhoto"]["tmp_name"]);						

						$destination = imagecreatetruecolor($newWidth, $newHeight);

						imagecopyresized($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

						imagejpeg($destination, $route);

					}

					if($_FILES["newProdPhoto"]["type"] == "image/png"){

						$randomNumber = mt_rand(100,999);
						
						$route = "views/img/products/".$_POST["newCode"]."/".$randomNumber.".png";

						$source = imagecreatefrompng($_FILES["newProdPhoto"]["tmp_name"]);						

						$destination = imagecreatetruecolor($newWidth, $newHeight);

						imagecopyresized($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

						imagepng($destination, $route);

					}

				}

				$table = "products";

				$data = array("idCategory" => $_POST["newCategory"],
							   "code" => $_POST["newCode"],
							   "description" => $_POST["newDescription"],
							   "stock" => $_POST["newStock"],
							   "buyingPrice" => $_POST["newBuyingPrice"],
							   "sellingPrice" => $_POST["newSellingPrice"],
							   "sales" => 0,
							   "image" => $route);

				$answer = ProductsModel::mdlAddProduct($table, $data);

				if($answer == "ok"){

					echo'<script>

						swal({
							  type: "success",
							  title: "Product has been successfully saved",
							  showConfirmButton: true,
							  confirmButtonText: "Close"
							  }).then(function(result){
										if (result.value) {

										window.location = "index.php?route=products";

										}
									})

						</script>';

				}


			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "¡Product cannot go with empty fields or carry special characters!",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
							if (result.value) {

							window.location = "index.php?route=products";

							}
						})

			  	</script>';
			}

		}

	}

	/*=============================================
	EDIT PRODUCT
	=============================================*/

	static public function ctrEditProduct(){

		if(isset($_POST["editDescription"])){

			// Check if category is selected
			if(empty($_POST["editCategory"]) || $_POST["editCategory"] == ""){
				
				echo'<script>

					swal({
						  type: "error",
						  title: "Category must be selected!",
						  text: "Please select a category for the product.",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
							if (result.value) {

							window.location = "index.php?route=products";

							}
						})

			  	</script>';

			  	return;
			}

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editDescription"]) &&
			   preg_match('/^[0-9]+$/', $_POST["editStock"]) &&	
			   preg_match('/^[0-9.]+$/', $_POST["editBuyingPrice"]) &&
			   preg_match('/^[0-9.]+$/', $_POST["editSellingPrice"])){

		   		/*=============================================
				VALIDATE IMAGE
				=============================================*/

			   	$route = $_POST["currentImage"];

			   	if(isset($_FILES["editImage"]["tmp_name"]) && !empty($_FILES["editImage"]["tmp_name"])){

					list($width, $height) = getimagesize($_FILES["editImage"]["tmp_name"]);

					$newWidth = 500;
					$newHeight = 500;

					/*=============================================
					WE CREATE THE FOLDER WHERE WE WILL SAVE THE PRODUCT IMAGE
					=============================================*/

					$folder = "views/img/products/".$_POST["editCode"];

					/*=============================================
					WE ASK IF WE HAVE ANOTHER PICTURE IN THE DB
					=============================================*/

					if(!empty($_POST["currentImage"]) && $_POST["currentImage"] != "views/img/products/default/anonymous.png"){

						unlink($_POST["currentImage"]);

					}else{

						mkdir($folder, 0755);	
					
					}
					
					/*=============================================
					WE APPLY DEFAULT PHP FUNCTIONS ACCORDING TO THE IMAGE FORMAT
					=============================================*/

					if($_FILES["editImage"]["type"] == "image/jpeg"){

						/*=============================================
						WE SAVE THE IMAGE IN THE FOLDER
						=============================================*/

						$random = mt_rand(100,999);

						$route = "views/img/products/".$_POST["editCode"]."/".$random.".jpg";

						$origin = imagecreatefromjpeg($_FILES["editImage"]["tmp_name"]);						

						$destiny = imagecreatetruecolor($newWidth, $newHeight);

						imagecopyresized($destiny, $origin, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

						imagejpeg($destiny, $route);

					}

					if($_FILES["editImage"]["type"] == "image/png"){

						/*=============================================
						WE SAVE THE IMAGE IN THE FOLDER
						=============================================*/

						$random = mt_rand(100,999);

						$route = "views/img/products/".$_POST["editCode"]."/".$random.".png";

						$origin = imagecreatefrompng($_FILES["editImage"]["tmp_name"]);

						$destiny = imagecreatetruecolor($newWidth, $newHeight);

						imagecopyresized($destiny, $origin, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

						imagepng($destiny, $route);

					}

				}

				$table = "products";

				$data = array("idCategory" => $_POST["editCategory"],
							   "code" => $_POST["editCode"],
							   "description" => $_POST["editDescription"],
							   "stock" => $_POST["editStock"],
							   "buyingPrice" => $_POST["editBuyingPrice"],
							   "sellingPrice" => $_POST["editSellingPrice"],
							   "image" => $route);

				$answer = ProductsModel::mdlEditProduct($table, $data);

				if($answer == "ok"){

					echo'<script>

						swal({
							  type: "success",
							  title: "The product has been updated",
							  showConfirmButton: true,
							  confirmButtonText: "Close"
							  }).then(function(result){
										if (result.value) {

										window.location = "index.php?route=products";

										}
									})

						</script>';

				}


			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "The Product cannot be empty or have special characters!",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
							if (result.value) {

							window.location = "index.php?route=products";

							}
						})

			  	</script>';
			}

		}

	}

	/*=============================================
	DELETE PRODUCT
	=============================================*/
	static public function ctrDeleteProduct(){

		if(isset($_GET["idProduct"])){

			$table ="products";
			$datum = $_GET["idProduct"];

			if($_GET["image"] != "" && $_GET["image"] != "views/img/products/default/anonymous.png"){

				unlink($_GET["image"]);
				rmdir('views/img/products/'.$_GET["code"]);

			}

			$answer = ProductsModel::mdlDeleteProduct($table, $datum);

			if($answer == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "The Product has been successfully deleted",
					  showConfirmButton: true,
					  confirmButtonText: "Close"
					  }).then(function(result){
								if (result.value) {

								window.location = "index.php?route=products";

								}
							})

				</script>';

			}		
		
		}

	}

	/*=============================================
	SHOW ADDING OF THE SALES
	=============================================*/

	static public function ctrShowAddingOfTheSales(){

		$table = "products";

		$answer = ProductsModel::mdlShowAddingOfTheSales($table);

		return $answer;

	}

	/*=============================================
	SHOW TOTAL INVENTORY ITEMS
	=============================================*/

	static public function ctrShowTotalInventoryItems(){

		$table = "products";

		$answer = ProductsModel::mdlShowTotalInventoryItems($table);

		return $answer;

	}

	/*=============================================
	SHOW TOTAL INVENTORY VALUE
	=============================================*/

	static public function ctrShowTotalInventoryValue(){

		$table = "products";

		$answer = ProductsModel::mdlShowTotalInventoryValue($table);

		return $answer;

	}

}