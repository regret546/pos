<?php

if($_SESSION["profile"] == "Cashier"){

  echo '<script>

    window.location = "home";

  </script>';

  return;

}

?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Category Management</h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Categories</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addCategories">
          <i class="fa fa-plus"></i> Add category
        </button>
      </div>

      <div class="box-body">
        <table class="table table-bordered table-hover table-striped dt-responsive tables" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Category</th>
              <th>Actions</th>
            </tr> 
          </thead>

          <tbody>
            <?php
              $item = null; 
              $value = null;
              $categories = ControllerCategories::ctrShowCategories($item, $value);

              foreach ($categories as $key => $value) {
                echo '<tr>
                  <td>'.($key+1).'</td>
                  <td class="text-uppercase">'.$value['Category'].'</td>
                  <td>
                    <div class="btn-group">
                      <button class="btn btn-primary btnEditCategory" idCategory="'.$value["id"].'" data-toggle="modal" data-target="#editCategories">
                        <i class="fa fa-pencil"></i>
                      </button>
                      <button class="btn btn-danger btnDeleteCategory" idCategory="'.$value["id"].'">
                        <i class="fa fa-trash"></i>
                      </button>
                    </div>  
                  </td>
                </tr>';
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<!-- Add Category Modal -->
<div id="addCategories" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="POST">
        <div class="modal-header" style="background: #3c8dbc; color: #fff">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add category</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span>
                <input class="form-control input-lg" type="text" name="newCategory" placeholder="Enter category name" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
  $createCategory = new ControllerCategories();
  $createCategory -> ctrCreateCategory();
?>

<!-- Edit Category Modal -->
<div id="editCategories" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="POST">
        <div class="modal-header" style="background: #3c8dbc; color: #fff">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit category</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span>
                <input class="form-control input-lg" type="text" id="editCategory" name="editCategory" required>
                <input type="hidden" name="idCategory" id="idCategory" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>

        <?php
          $editCategory = new ControllerCategories();
          $editCategory -> ctrEditCategory();
        ?>
      </form>
    </div>
  </div>
</div>

<?php
  $deleteCategory = new ControllerCategories();
  $deleteCategory -> ctrDeleteCategory();
?>