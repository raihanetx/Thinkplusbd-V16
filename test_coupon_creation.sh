#!/bin/bash

curl -X POST -H "Content-Type: application/json" -d '{
    "discount_type": "percentage",
    "discount_value": 10
}' http://localhost:8000/create_coupon.php
