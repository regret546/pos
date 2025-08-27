<?php

class ControllerCustomers{

	/*=============================================
	CREATE CUSTOMERS
	=============================================*/

	static public function ctrCreateCustomer(){

		if(isset($_POST["newCustomer"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newCustomer"]) &&
			   preg_match('/^[0-9]+$/', $_POST["newIdDocument"]) &&
			   preg_match('/^[()\-0-9 ]+$/', $_POST["newPhone"]) && 
			   preg_match('/^[#\.\-a-zA-Z0-9 ]+$/', $_POST["newAddress"])){

			   	$table = "customers";

			   	$data = array("name"=>$_POST["newCustomer"],
					           "idDocument"=>$_POST["newIdDocument"],
					           "phone"=>$_POST["newPhone"],
					           "address"=>$_POST["newAddress"],
					           "purchases"=>0,
					           "lastPurchase"=>"0000-00-00 00:00:00");

			   	$answer = ModelCustomers::mdlAddCustomer($table, $data);

			   				   	if($answer == "ok"){

					// Check if we're on a sales page (create-sale or edit-sale)
					$currentRoute = isset($_GET['route']) ? $_GET['route'] : '';
					$redirectUrl = "index.php?route=customers"; // default redirect
					
					if($currentRoute == 'create-sale') {
						$redirectUrl = "index.php?route=create-sale";
					} elseif($currentRoute == 'edit-sale' && isset($_GET['idSale'])) {
						$redirectUrl = "index.php?route=edit-sale&idSale=" . $_GET['idSale'];
					}

					echo'<script>

					swal({
						  type: "success",
						  title: "The customer has been saved successfully!",
						  text: "The new customer has been added and will be available in the customer list.",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
								if (result.value) {

								window.location = "' . $redirectUrl . '";

								}
							})

					</script>';

				}

			}else{

				// Check if we're on a sales page (create-sale or edit-sale)
				$currentRoute = isset($_GET['route']) ? $_GET['route'] : '';
				$redirectUrl = "index.php?route=customers"; // default redirect
				
				if($currentRoute == 'create-sale') {
					$redirectUrl = "index.php?route=create-sale";
				} elseif($currentRoute == 'edit-sale' && isset($_GET['idSale'])) {
					$redirectUrl = "index.php?route=edit-sale&idSale=" . $_GET['idSale'];
				}

				echo'<script>

					swal({
						  type: "error",
						  title: "Customer cannot be blank or contain special characters!",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
							if (result.value) {

							window.location = "' . $redirectUrl . '";

							}
						})

			  	</script>';

			}

		}

	}

	/*=============================================
	SHOW CUSTOMERS
	=============================================*/

	static public function ctrShowCustomers($item, $value){

		$table = "customers";

		$answer = ModelCustomers::mdlShowCustomers($table, $item, $value);

		return $answer;

	}

	/*=============================================
	EDIT CUSTOMER
	=============================================*/

	static public function ctrEditCustomer(){

		if(isset($_POST["editCustomer"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editCustomer"]) &&
			   preg_match('/^[0-9]+$/', $_POST["editIdDocument"]) &&
			   preg_match('/^[()\-0-9 ]+$/', $_POST["editPhone"]) && 
			   preg_match('/^[#\.\-a-zA-Z0-9 ]+$/', $_POST["editAddress"])){

			   	$table = "customers";

			   	$data = array("id"=>$_POST["idCustomer"],
			   				   "name"=>$_POST["editCustomer"],
					           "idDocument"=>$_POST["editIdDocument"],
					           "phone"=>$_POST["editPhone"],
					           "address"=>$_POST["editAddress"]);

			   	$answer = ModelCustomers::mdlEditCustomer($table, $data);

			   	if($answer == "ok"){

					echo'<script>

					swal({
						  type: "success",
						  title: "The customer has been updated",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
									if (result.value) {

									window.location = "index.php?route=customers";

									}
								})

					</script>';

				}

			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "Customer cannot be blank or special characters!",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
							if (result.value) {

							window.location = "index.php?route=customers";

							}
						})

			  	</script>';



			}

		}

	}

	/*=============================================
	DELETE CUSTOMER
	=============================================*/

	static public function ctrDeleteCustomer(){

		if(isset($_GET["idCustomer"])){

			$table ="customers";
			$data = $_GET["idCustomer"];

			$answer = ModelCustomers::mdlDeleteCustomer($table, $data);

			if($answer == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "The customer has been deleted",
					  showConfirmButton: true,
					  confirmButtonText: "Close"
					  }).then(function(result){
								if (result.value) {

								window.location = "index.php?route=customers";

								}
							})

				</script>';

			}		

		}

	}

}

