<?php

require_once "../controllers/sales.controller.php";
require_once "../models/sales.model.php";
require_once "../controllers/customers.controller.php";
require_once "../models/customers.model.php";
require_once "../controllers/users.controller.php";
require_once "../models/users.model.php";
require_once "../controllers/products.controller.php";
require_once "../models/products.model.php";

class AjaxSales {
    public function processSale() {
        if(isset($_POST["newSale"])) {
            // Validate cash payment
            if($_POST["newPaymentMethod"] === "cash") {
                $change = floatval(str_replace(',', '', $_POST["newCashChange"] ?? "0"));
                if($change < 0) {
                    echo "error_negative_change";
                    return;
                }
            }

            $saveSale = new ControllerSales();
            $result = $saveSale->ctrCreateSale();
            
            if($result === "ok") {
                echo "ok";
            } else {
                echo "error";
            }
        }
    }
}

// Create instance and process sale
$processSale = new AjaxSales();
$processSale->processSale(); 