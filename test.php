<?php
require_once 'src/GCFoundation.php';
use Gravitycar\src\GCFoundation;
$app = GCFoundation::getInstance();

$tb = new \Gravitycar\src\TableBuilder\TableBuilder($app, $app->getDB());



print("Done\n");