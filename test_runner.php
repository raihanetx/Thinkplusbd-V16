<?php
// Simulate the input that would come from the web server
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array(
    'code' => 'TESTCOUPON',
    'discount_type' => 'percentage',
    'discount_value' => '15',
    'product_ids' => array('1', '2'),
    'category' => array('Course')
);

// Capture the output of the script
ob_start();
include 'create_coupon.php';
$output = ob_get_clean();

// You can then assert the output or the state of the coupons.json file
// For example, let's just print the output for now
echo $output;
?>
