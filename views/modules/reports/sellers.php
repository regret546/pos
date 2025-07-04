<?php

$item = null;
$value = null;

$sales = ControllerSales::ctrShowSales($item, $value);
$users = ControllerUsers::ctrShowUsers($item, $value);

$arrayCashiers = array();
$arrayCashiersList = array();

foreach ($sales as $key => $valueSales) {

  foreach ($users as $key => $valueUsers) {

    if($valueUsers["id"] == $valueSales["idSeller"]){

        #We capture cashiers in an array
        array_push($arrayCashiers, $valueUsers["name"]);

        #We add all values for each cashier
        $arrayCashiersList = array($valueUsers["name"] => $valueSales["totalPrice"]);

        #We add the netprice of each cashier
        foreach ($arrayCashiersList as $key => $value) {

            $sumTotal[$valueUsers["name"]] += $value;

         }

    }
  
  }

}

#Avoiding repeating cashiers names
$dontrepeatnames = array_unique($arrayCashiers);

?>
<!-- Log on to codeastro.com for more projects! -->

<!--=====================================
CASHIERS
======================================-->

<div class="box box-success">
	
	<div class="box-header with-border">
    
    	<h3 class="box-title">Cashiers</h3>
  
  	</div>

  	<div class="box-body">
  		
		<div class="chart-responsive">
			
			<div class="chart" id="bar-chart2" style="height: 300px;"></div>

		</div>

  	</div>

</div>

<script>
	
//BAR CHART
var bar = new Morris.Bar({
  element: 'bar-chart2',
  resize: true,
  data: [

  <?php
    
    foreach($dontrepeatnames as $value){

      echo "{y: '".$value."', a: '".$sumTotal[$value]."'},";

    }

  ?>
  ],
  barColors: ['#0af'],
  xkey: 'y',
  ykeys: ['a'],
  labels: ['sales'],
  preUnits: '₱',
  hideHover: 'auto'
});


</script>