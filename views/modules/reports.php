<?php

if($_SESSION["profile"] == "Special" || $_SESSION["profile"] == "Seller"){

  echo '<script>

    window.location = "index.php?route=home";

  </script>';

  return;

}

?>

<style>
/* Basic daterangepicker styling */
.daterangepicker {
  width: 650px !important;
  z-index: 9999 !important;
  border: 1px solid #ccc !important;
  border-radius: 4px !important;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
}

/* Show the ranges sidebar with preset options */
.daterangepicker .ranges {
  width: 160px !important;
  float: left !important;
  background-color: #f5f5f5 !important;
  border-right: 1px solid #ddd !important;
  border-radius: 4px 0 0 4px !important;
}

.daterangepicker .ranges ul {
  list-style: none !important;
  margin: 0 !important;
  padding: 0 !important;
}

.daterangepicker .ranges li {
  color: #08c !important;
  padding: 8px 12px !important;
  cursor: pointer !important;
  font-size: 12px !important;
  border-bottom: 1px solid #e0e0e0 !important;
}

.daterangepicker .ranges li:hover {
  background-color: #08c !important;
  color: #fff !important;
}

.daterangepicker .ranges li.active {
  background-color: #357ca5 !important;
  color: #fff !important;
}

/* Calendar container */
.daterangepicker .drp-calendar {
  width: calc(100% - 160px) !important;
  float: right !important;
  border-right: none !important;
}

/* Calendar styling */
.daterangepicker .calendar-table {
  border: none !important;
  padding: 10px !important;
}

/* Date cell styling */
.daterangepicker td.available:hover {
  background-color: #eee !important;
  border-color: transparent !important;
  color: inherit !important;
}

.daterangepicker td.start-date {
  background-color: #357ca5 !important;
  color: #fff !important;
  border-radius: 4px 0 0 4px !important;
}

.daterangepicker td.end-date {
  background-color: #357ca5 !important;
  color: #fff !important;
  border-radius: 0 4px 4px 0 !important;
}

.daterangepicker td.start-date.end-date {
  border-radius: 4px !important;
}

.daterangepicker td.in-range {
  background-color: #ebf4f8 !important;
  border-color: transparent !important;
  color: #000 !important;
}

/* Buttons container - fixed at bottom */
.daterangepicker .drp-buttons {
  clear: both !important;
  text-align: right !important;
  padding: 15px !important;
  border-top: 1px solid #ddd !important;
  background-color: #fff !important;
  position: relative !important;
  bottom: 0 !important;
  width: 100% !important;
  box-sizing: border-box !important;
  display: block !important;
  border-radius: 0 0 4px 4px !important;
}

/* Button styling */
.daterangepicker .drp-buttons .btn {
  margin-left: 8px !important;
  padding: 6px 12px !important;
  font-size: 12px !important;
  border-radius: 4px !important;
  min-width: 70px !important;
}

.daterangepicker .btn-success {
  background-color: #5cb85c !important;
  border-color: #4cae4c !important;
  color: #fff !important;
}

.daterangepicker .btn-default {
  background-color: #fff !important;
  border-color: #ccc !important;
  color: #333 !important;
}

/* Navigation arrows */
.daterangepicker .drp-calendar .calendar-table .next,
.daterangepicker .drp-calendar .calendar-table .prev {
  color: #999 !important;
  text-decoration: none !important;
  padding: 5px !important;
}

.daterangepicker .drp-calendar .calendar-table .next:hover,
.daterangepicker .drp-calendar .calendar-table .prev:hover {
  color: #000 !important;
}

/* Month/Year dropdowns */
.daterangepicker select.monthselect,
.daterangepicker select.yearselect {
  font-size: 12px !important;
  padding: 1px !important;
  height: auto !important;
  margin: 0 !important;
  cursor: pointer !important;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .daterangepicker {
    width: 95% !important;
    max-width: 350px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
  }
  
  .daterangepicker .ranges {
    width: 100% !important;
    float: none !important;
    border-right: none !important;
    border-bottom: 1px solid #ddd !important;
  }
  
  .daterangepicker .drp-calendar {
    width: 100% !important;
    float: none !important;
  }
  
  .daterangepicker .drp-buttons {
    padding: 10px !important;
  }
  
  .daterangepicker .drp-buttons .btn {
    width: 45% !important;
    margin: 2px 1% !important;
  }
}

/* Positioning adjustments */
.daterangepicker.opensleft {
  left: auto !important;
  right: 0 !important;
}

.daterangepicker.opensright {
  left: 0 !important;
  right: auto !important;
}
</style>
<div class="content-wrapper">

  <section class="content-header">
    
    <h1><!-- Log on to codeastro.com for more projects! -->
      
      Sales report
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="index.php?route=home"><i class="fa fa-dashboard"></i> Home</a></li>
      
      <li class="active">Sales report</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <div class="input-group">

          <button type="button" class="btn btn-default" id="daterange-btn2">
           
            <span>
              <i class="fa fa-calendar"></i> Date range
            </span>

            <i class="fa fa-caret-down"></i>

          </button>

        </div>

        <div class="box-tools pull-right">

        <!-- Regular Sales Export Button -->
        <?php

        if(isset($_GET["inicialDate"])){

          echo '<a href="views/modules/download-report.php?report=report&inicialDate='.$_GET["inicialDate"].'&finalDate='.$_GET["finalDate"].'&type=regular">';

        }else{

           echo '<a href="views/modules/download-report.php?report=report&type=regular">';

        }         

        ?>
           
           <button class="btn btn-success" style="margin-top:5px; margin-right:5px">Export Regular Sales</button>

          </a>

        <!-- Installment Sales Export Button -->
        <?php

        if(isset($_GET["inicialDate"])){

          echo '<a href="views/modules/download-report.php?report=report&inicialDate='.$_GET["inicialDate"].'&finalDate='.$_GET["finalDate"].'&type=installments">';

        }else{

           echo '<a href="views/modules/download-report.php?report=report&type=installments">';

        }         

        ?>
           
           <button class="btn btn-info" style="margin-top:5px">Export Installment Sales</button>

          </a>

        </div>
         
      </div>

      <div class="box-body">
        
        <div class="row">

          <div class="col-xs-12">
            
            <?php

            include "reports/sales-graph.php";

            ?>

          </div>

           <div class="col-md-6 col-xs-12">
             
            <?php

            include "reports/bestseller-products.php";

            ?>

          </div>

          <div class="col-md-6 col-xs-12">
           
            <?php

            include "reports/sellers.php";

            ?>

         </div>

         <div class="col-md-6 col-xs-12">
           
            <?php

            include "reports/buyers.php";

            ?>

         </div>
          
        </div>

      </div>
      
    </div>

  </section>
	<!-- Log on to codeastro.com for more projects! -->
 </div>
