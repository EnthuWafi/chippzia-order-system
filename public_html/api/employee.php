<?php

require("../../includes/functions.inc.php");

session_start();

if (!isAjaxRequest()) {
    header("Location: ".BASE_URL."index.php");
    die();
}

//get all employee except current one
$employeeID = $_GET["employeeID"];

$employees = retrieveAllEmployees();

if (isset($employeeID)) {
    foreach ($employees as $key => $employee) {
        if ($employee["EMPLOYEE_ID"] == $employeeID) {
            unset($employees[$key]);
            break;
        }
    }
}

echo json_encode(["status"=>"success", "message" => $employees]);