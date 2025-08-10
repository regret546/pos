<aside class="main-sidebar">

	<section class="sidebar">
		
		<ul class="sidebar-menu">

			<?php

			if ($_SESSION["profile"] == "Administrator") {
				
				echo '

					<li class="active">

						<a href="index.php?route=home">

							<i class="fa fa-home"></i>

							<span>Home</span>

						</a>

					</li>

					

				';
			}

			if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Special"){

				echo '

					<li>

						<a href="index.php?route=categories">

							<i class="fa fa-th"></i>

							<span>Categories</span>

						</a>

					</li>

					<li>

						<a href="index.php?route=products">

							<i class="fa fa-product-hunt"></i>

							<span>Products</span>

						</a>

					</li>
				';

			}

			if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Seller"){
				echo '
					
					<li>

						<a href="index.php?route=customers">

							<i class="fa fa-users"></i>

							<span>Customers</span>

						</a>

					</li>

				';
			}

			if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Seller"){

			echo'


				<li class="treeview">

				<a href="#">

					<i class="fa fa-usd"></i>

					<span>Sales</span>

					<span class="pull-right-container">

						<i class="fa fa-angle-left pull-right"></i>

					</span>

				</a>

				<ul class="treeview-menu">

					<li>

						<a href="index.php?route=sales">

							<i class="fa fa-circle"></i>

							<span>Manage Sales</span>

						</a>

					</li>

					<li>

						<a href="index.php?route=create-sale">

							<i class="fa fa-circle"></i>

							<span>Create Sale</span>

						</a>

					</li> </ul>';

				}

				if($_SESSION["profile"] == "Administrator"){

					echo '<li>

						<a href="index.php?route=reports">

							<i class="fa fa-file"></i>

							<span>Sales Report</span>

						</a>

					</li>

					<li>

						<a href="index.php?route=installments">

							<i class="fa fa-calendar"></i>

							<span>Installment Plans</span>

						</a>

					</li>
					
					
					<li>

						<a href="index.php?route=users">

							<i class="fa fa-user"></i>

							<span>User Management</span>

						</a>

					</li>';

				}

				

		?>
			
		</ul>

	</section>
	<!-- Log on to codeastro.com for more projects! -->
</aside>