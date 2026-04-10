<?php
require 'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

$xlsx = SimpleXLSX::parse('d:\CFARM_HR\รายชื่อ-1.xlsx');
if ($xlsx) {
    print_r(array_slice($xlsx->rows(), 0, 5));
} else {
    echo SimpleXLSX::parseError();
}
