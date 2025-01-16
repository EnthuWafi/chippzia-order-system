<?php

require("../../includes/functions.inc.php");

session_start();

if (!isAjaxRequest()) {
    header("Location: ".BASE_URL."index.php");
    die();
}

$products = retrieveAllProduct();


echo json_encode(["status"=>"success", "message" => $products]);