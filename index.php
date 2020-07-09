<?php
include("Controller.php");

$data = json_decode(file_get_contents('php://input'), true);
$controller = new Controller();

$result = $controller->execute($data);
echo $result;
