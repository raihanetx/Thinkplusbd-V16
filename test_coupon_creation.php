<?php
$_POST = json_encode(array(
    "discount_type" => "percentage",
    "discount_value" => 10
));

include 'create_coupon.php';
?>
