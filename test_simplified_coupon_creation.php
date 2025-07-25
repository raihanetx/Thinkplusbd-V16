<?php
$_POST = json_encode(array(
    "code" => "SIMPLIFIED",
    "discount_type" => "fixed",
    "discount_value" => 100,
    "category" => "Software",
    "product_ids" => array("1")
));

include 'create_coupon.php';
?>
