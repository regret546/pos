<?php

class ControllerCategories {
	/*=============================================
	CREATE CATEGORY
	=============================================*/
	public function ctrCreateCategory() {
		if(isset($_POST["newCategory"])) {
			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newCategory"])) {
				$table = "categories";
				$data = $_POST["newCategory"];
				$answer = CategoriesModel::mdlAddCategory($table, $data);

				if($answer == "ok") {
					echo '<script>
						swal({
							type: "success",
							title: "Category has been successfully saved",
							showConfirmButton: true,
							confirmButtonText: "Close"
						}).then(function(result) {
							if (result.value) {
								window.location = "categories";
							}
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "No special characters or numbers allowed",
						showConfirmButton: true,
						confirmButtonText: "Close"
					}).then(function(result) {
						if (result.value) {
							window.location = "categories";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	SHOW CATEGORIES
	=============================================*/
	static public function ctrShowCategories($item, $value) {
		$table = "categories";
		$answer = ModelCategories::mdlShowCategories($table, $item, $value);
		return $answer;
	}

	/*=============================================
	EDIT CATEGORY
	=============================================*/
	public function ctrEditCategory() {
		if(isset($_POST["editCategory"])) {
			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editCategory"])) {
				$table = "categories";
				$data = array(
					"category" => $_POST["editCategory"],
					"id" => $_POST["idCategory"]
				);

				$answer = CategoriesModel::mdlEditCategory($table, $data);

				if($answer == "ok") {
					echo '<script>
						swal({
							type: "success",
							title: "Category has been successfully edited",
							showConfirmButton: true,
							confirmButtonText: "Close"
						}).then(function(result) {
							if (result.value) {
								window.location = "categories";
							}
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "No special characters or numbers allowed",
						showConfirmButton: true,
						confirmButtonText: "Close"
					}).then(function(result) {
						if (result.value) {
							window.location = "categories";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	DELETE CATEGORY
	=============================================*/
	static public function ctrDeleteCategory() {
		if(isset($_GET["idCategory"])) {
			$table = "categories";
			$data = $_GET["idCategory"];
			$answer = ModelCategories::mdlDeleteCategory($table, $data);

			if($answer == "ok") {
				echo '<script>
					swal({
						type: "success",
						title: "The category has been successfully deleted",
						showConfirmButton: true,
						confirmButtonText: "Close"
					}).then(function(result) {
						if (result.value) {
							window.location = "categories";
						}
					});
				</script>';
			}
		}
	}
}