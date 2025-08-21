<?php

class ControllerCategories
{

	/*=============================================
	CREATE CATEGORY
	=============================================*/

	static public function ctrCreateCategory()
	{

		if (isset($_POST['newCategory'])) {

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newCategory"])) {

				$table = 'categories';

				$data = strtoupper($_POST['newCategory']);

				$answer = ModelCategories::mdlAddCategory($table, $data);

				if ($answer == 'ok') {

					echo '<script>
						
						swal({
							type: "success",
							title: "Category has been successfully saved ",
							showConfirmButton: true,
							confirmButtonText: "Close"

							}).then(function(result){
								if (result.value) {

									window.location = "index.php?route=categories";

								}
							});
						
					</script>';
				}
			} else {

				echo '<script>
						
						swal({
							type: "error",
							title: "No especial characters or blank fields",
							showConfirmButton: true,
							confirmButtonText: "Close"
				
							 }).then(function(result){

								if (result.value) {
									window.location = "index.php?route=categories";
								}
							});
						
				</script>';
			}
		}
	}

	/*=============================================
	SHOW CATEGORIES
	=============================================*/

	static public function ctrShowCategories($item, $value)
	{

		$table = "categories";

		$answer = ModelCategories::mdlShowCategories($table, $item, $value);

		return $answer;
	}

	/*=============================================
	EDIT CATEGORY
	=============================================*/

	static public function ctrEditCategory()
	{

		if (isset($_POST["editCategory"])) {

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editCategory"])) {

				$table = "categories";

				$data = array(
					"Category" => strtoupper($_POST["editCategory"]),
					"id" => $_POST["idCategory"]
				);

				$answer = ModelCategories::mdlEditCategory($table, $data);

				if ($answer == "ok") {

					echo '<script>

					swal({
						  type: "success",
						  title: "Category has been successfully saved ",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
									if (result.value) {

									window.location = "index.php?route=categories";

									}
								})

					</script>';
				}
			} else {

				echo '<script>

					swal({
						  type: "error",
						  title: "No especial characters or blank fields",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
							if (result.value) {

							window.location = "index.php?route=categories";

							}
						})

			  	</script>';
			}
		}
	}

	/*=============================================
	DELETE CATEGORY
	=============================================*/

	static public function ctrDeleteCategory()
	{

		if (isset($_GET["idCategory"])) {

			$table = "categories";
			$data = $_GET["idCategory"];

			$answer = ModelCategories::mdlDeleteCategory($table, $data);

			if ($answer == "ok") {

				echo '<script>

					swal({
						  type: "success",
						  title: "The category has been successfully deleted",
						  showConfirmButton: true,
						  confirmButtonText: "Close"
						  }).then(function(result){
									if (result.value) {

									window.location = "index.php?route=categories";

									}
								})

					</script>';
			}
		}
	}
}
