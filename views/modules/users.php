<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      User management
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      
      <li class="active">User management</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#addUser">
          
          Add user

        </button>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tables" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th>Name</th>
           <th>Username</th>
           <th>Photo</th>
           <th>Profile</th>
           <th>Status</th>
           <th>Last login</th>
           <th>Actions</th>

         </tr> 

        </thead>

        <tbody>

        <?php

        $item = null; 
        $value = null;

        $users = ControllerUsers::ctrShowUsers($item, $value);

        foreach ($users as $key => $value) {
          
          echo '<tr>
                  <td>'.($key+1).'</td>
                  <td>'.$value["name"].'</td>
                  <td>'.$value["user"].'</td>';

                  if($value["photo"] != ""){

                    echo '<td><img src="'.$value["photo"].'" class="img-thumbnail" width="40px"></td>';

                  }else{

                    echo '<td><img src="views/img/users/default/anonymous.png" class="img-thumbnail" width="40px"></td>';

                  }

                  echo '<td>'.$value["profile"].'</td>';

                  if($value["status"] != 0){

                    echo '<td><button class="btn btn-success btn-xs btnActivate" userId="'.$value["id"].'" userStatus="0">Activated</button></td>';

                  }else{

                    echo '<td><button class="btn btn-danger btn-xs btnActivate" userId="'.$value["id"].'" userStatus="1">Deactivated</button></td>';

                  }

                  echo '<td>'.$value["lastLogin"].'</td>
                  <td>

                    <div class="btn-group">
                        
                      <button class="btn btn-warning btnEditUser" idUser="'.$value["id"].'" data-toggle="modal" data-target="#editUser"><i class="fa fa-pencil"></i></button>

                      <button class="btn btn-danger btnDeleteUser" userId="'.$value["id"].'" username="'.$value["user"].'" userPhoto="'.$value["photo"].'"><i class="fa fa-times"></i></button>

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

<!--=====================================
=            module add user            =
======================================-->

<div id="addUser" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        HEADER
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Add user</h4>

        </div>

        <!--=====================================
        BODY
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- input name -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                <input type="text" class="form-control input-lg" name="newName" placeholder="Add name" required>

              </div>

            </div>

            <!-- input username -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 

                <input type="text" class="form-control input-lg" name="newUser" placeholder="Add username" id="newUser" required>

              </div>

            </div>

            <!-- input password -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-lock"></i></span> 

                <input type="password" class="form-control input-lg" name="newPasswd" placeholder="Add password" required>

              </div>

            </div>

            <!-- input profile -->

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-users"></i></span> 

                <select class="form-control input-lg" name="newProfile">
                  
                  <option value="">Select profile</option>

                  <option value="Administrator">Administrator</option>

                  <option value="Special">Special</option>

                </select>

              </div>

            </div>

            <!-- Uploading image -->

             <div class="form-group">
              
              <div class="panel">Upload image</div>

              <input type="file" class="newPicture" name="newPicture">

              <p class="help-block">Maximum size 2MB</p>

              <img src="views/img/users/default/anonymous.png" class="img-thumbnail preview" width="100px">

            </div>

          </div>

        </div>

        <!--=====================================
        FOOTER
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>

          <button type="submit" class="btn btn-primary">Save user</button>

        </div>

        <?php

          $createUser = new ControllerUsers();
          $createUser -> ctrCreateUser();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
=            module edit user            =
======================================-->

<div id="editUser" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        HEADER
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Edit user</h4>

        </div>

        <!--=====================================
        BODY
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- input name -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                <input type="text" class="form-control input-lg" id="editName" name="editName" value="" required>

              </div>

            </div>

            <!-- input username -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 

                <input type="text" class="form-control input-lg" id="editUser" name="editUser" value="" readonly>

              </div>

            </div>

            <!-- input password -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-lock"></i></span> 

                <input type="password" class="form-control input-lg" name="editPasswd" placeholder="Write new password">

                <input type="hidden" id="currentPasswd" name="currentPasswd">

              </div>

            </div>

            <!-- input profile -->

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-users"></i></span> 

                <select class="form-control input-lg" name="editProfile">
                  
                  <option value="" id="editProfile"></option>

                  <option value="Administrator">Administrator</option>

                  <option value="Special">Special</option>

                </select>

              </div>

            </div>

            <!-- Uploading image -->

             <div class="form-group">
              
              <div class="panel">Upload image</div>

              <input type="file" class="newPicture" name="editPicture">

              <p class="help-block">Maximum size 2MB</p>

              <img src="views/img/users/default/anonymous.png" class="img-thumbnail preview" width="100px">

              <input type="hidden" name="currentPicture" id="currentPicture">

            </div>

          </div>

        </div>

        <!--=====================================
        FOOTER
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>

          <button type="submit" class="btn btn-primary">Save changes</button>

        </div>

     <?php

          $editUser = new ControllerUsers();
          $editUser -> ctrEditUser();

        ?> 

      </form>

    </div>

  </div>

</div>

<?php

  $deleteUser = new ControllerUsers();
  $deleteUser -> ctrDeleteUser();

?> 