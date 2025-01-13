<?php
require('connection.inc.php');
require('boilerplate.inc.php');
require('users.inc.php');
require('orders.inc.php');
require('product.inc.php');

const BASE_URL = '/kerepek/';
const WEBSITE_NAME = 'Chipzzia';
//functions
function isAjaxRequest(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
function current_page(): void
{
    echo htmlspecialchars($_SERVER["PHP_SELF"]);
}

function makeToast($type, $message, $title) {
    $_SESSION["alert"] = ["type"=>$type, "message"=>$message, "title"=>$title];
}
// TOASTS
function displayToast() {
    if (isset($_SESSION["alert"])){
        showToastr($_SESSION["alert"]);
        unset($_SESSION["alert"]);
    }
}

//Prevent circular hierarchy in employee table
function isHierarchyValid($employeeId, $managerId) {
    $conn = OpenConn();

    $currentManagerId = $managerId;

    while ($currentManagerId) {
        if ($currentManagerId == $employeeId) {
            return false; // Circular reference found
        }

        $sql = "SELECT MANAGER_ID FROM EMPLOYEES WHERE EMPLOYEE_ID = :managerId";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":managerId", $currentManagerId);

        if (!oci_execute($stmt)) {
            oci_free_statement($stmt);
            CloseConn($conn);
            return false;
        }

        $result = oci_fetch_assoc($stmt);
        $currentManagerId = $result['MANAGER_ID'] ?? null;

        oci_free_statement($stmt);
    }

    CloseConn($conn);
    return true;
}

//TOASTS
function showToastr($alert): void
{
    echo ("<script>
window.onload = function() {
    toastr.options = {
      \"closeButton\": false,
      \"debug\": false,
      \"newestOnTop\": false,
      \"progressBar\": false,
      \"positionClass\": \"toast-top-right\",
      \"preventDuplicates\": false,
      \"onclick\": null,
      \"showDuration\": \"300\",
      \"hideDuration\": \"1000\",
      \"timeOut\": \"5000\",
      \"extendedTimeOut\": \"1000\",
      \"showEasing\": \"swing\",
      \"hideEasing\": \"linear\",
      \"showMethod\": \"fadeIn\",
      \"hideMethod\": \"fadeOut\"
    }
    toastr[\"{$alert["type"]}\"](\"{$alert["message"]}\", \"{$alert["title"]}\");
}
</script>");
}


//Requires login to access the site
function member_login_required(): void
{
    if (empty($_SESSION["user_data"])){
        header("Location: ". BASE_URL . "login.php");
        die();
    }

    if (returnUserType() != "member"){
        header("Location: ". BASE_URL . "index.php");
        die();
    }
}


//Requires user to not be logged in to access the site (For instance, like Login page or Register page)
function employee_login_required() {
    if (empty($_SESSION["user_data"])){
        header("Location: ". BASE_URL . "login.php");
        die();
    }

    if (returnUserType() != "employee"){
        header("Location: ". BASE_URL . "index.php");
        die();
    }
}

//special function to prevent admin from purchase products (for themselves) & customer from accessing admin side
function employee_forbidden(): void
{
    if (isset($_SESSION["user_data"])){
        if (returnUserType() === "employee"){
            header("Location: ". BASE_URL . "admin/dashboard.php");
            die();
        }
    }
}
function member_forbidden(): void
{
    if (isset($_SESSION["user_data"])){
        if (returnUserType() === "member"){
            makeToast("warning", "You are forbidden from going to that page", "Warning");
            header("Location: ". BASE_URL . "account/dashboard.php");
            die();
        }
    }
}

function getToken(){
    $token = sha1(mt_rand());
    $_SESSION['token'] = $token;

    return $token;
}

function isTokenValid($token){
    if(!empty($_SESSION['token'])){
        if ($_SESSION['token'] === $token){
            unset($_SESSION['token']);
            return true;
        }
    }
    return false;
}


//check array keys is set
function array_keys_isset_or_not($keys, $array): bool
{
    foreach ($keys as $key) {
        if (!isset($array[$key])) {
            return false;
        }
    }
    return true;
}

//check array keys is empty
function array_keys_isempty_or_not($keys, $array): bool
{
    foreach ($keys as $key) {
        if (empty($array[$key])) {
            return false;
        }
    }
    return true;
}



function createLog($data): void
{
    $file = $_SERVER['DOCUMENT_ROOT'].BASE_URL."logs/log_".date("j.n.Y").".txt";
    $fh = fopen($file, 'a');
    fwrite($fh,$data);
    fclose($fh);
}


function orders_memberOrders($orders){
    foreach ($orders as $order) {
        $count = 1;
        //date
        $date = date_create($order["CREATED_AT"]);
        $dateFormatted = date_format($date, "d M Y");

        $orderLines = retrieveAllOrderLines($order["ORDER_ID"]);
        $orderLineStr = "";
        foreach ($orderLines as $orderLine) {
            $price = number_format((float)$orderLine["PRICE"], 2, ".", ",");

            $orderLineStr .=
                "<tr class='align-middle'>
<th scope='row'>$count</th>
<td><img class='img-fluid w-100' src='{$orderLine["PRODUCT_IMAGE"]}' style='max-width: 200px;'></td>
<td>{$orderLine["PRODUCT_NAME"]}</td>
<td class='text-center'>{$orderLine["QUANTITY"]}</td>
<td>$dateFormatted</td>
<td>RM{$price}</td>
                                    </tr>";
            $count++;
        }


        //code
        $orderCode = sprintf('%08d', $order["ORDER_ID"]);
        $total = number_format((float)$order["TOTAL_PRICE"], 2, ".", ",");
        $statusSmall = strtolower($order["ORDER_STATUS"]);
        echo "
<div class='row mt-3'>
    <div class='col'><span class='h4'>Order #{$orderCode}</span></div>
    <div class='col text-end'>
        <span class='{$statusSmall}'>{$order["ORDER_STATUS"]}</span>
    </div>
</div>
<div class='row'>
    <table class='table'>
    <caption>Delivery Fee: RM5.00</caption>
    <thead>
        <tr>
            <th scope='col'>#</th>
            <th scope='col'>Product</th>
            <th scope='col'>Name</th>
            <th scope='col'>Quantity</th>
            <th scope='col'>Date Ordered</th>
            <th scope='col'>Price</th>
        </tr>
    </thead>
    <tbody>
        {$orderLineStr}
    </tbody>
    <tfoot>
    <td colspan='5'><span class='fw-bold'>Total:</span></td>
    <td>RM{$total}</td>
    </tfoot>
    </table>
</div>
";
    }
}
function orders_adminOrders($orders) {
    $base_url = BASE_URL;
    if ($orders != null){
        $statusOptions = "
        <option value='PENDING'>Pending</option>
        <option value='COMPLETED'>Completed</option>
        <option value='CANCELLED'>Cancelled</option>";
        foreach ($orders as $order) {
            $count = 1;
            //date
            $date = date_create($order["CREATED_AT"]);
            $dateFormatted = date_format($date, "d M Y");

            $orderLines = retrieveAllOrderLines($order["ORDER_ID"]);
            $orderLineStr = "";
            foreach ($orderLines as $orderLine) {
                $price = number_format((float)$orderLine["PRICE"], 2, ".", ",");

                $orderLineStr .=
                    "<tr class='align-middle'>
    <th scope='row'>$count</th>
    <td><img class='img-fluid w-100' src='{$orderLine["PRODUCT_IMAGE"]}' style='max-width: 200px;'></td>
    <td>{$orderLine["PRODUCT_NAME"]}</td>
    <td class='text-center'>{$orderLine["QUANTITY"]}</td>
    <td>$dateFormatted</td>
    <td>RM{$price}</td>
</tr>";
                $count++;
            }
            //code
            $orderCode = sprintf('%08d', $order["ORDER_ID"]);
            $total = number_format((float)$order["TOTAL_PRICE"], 2, ".", ",");
            $statusSmall = strtolower($order["ORDER_STATUS"]);
            echo "
<div>
<div class='row mt-3 mb-3'>
    <div class='col-5'>
        <span class='h4'>Order #{$orderCode}</span><span class='text-muted h4'> by {$order["FIRST_NAME"]} {$order["LAST_NAME"]}</span>
    </div>
    <div class='col'>
        <div class='row'>
            <div class='col-3 text-end mt-2'>
                <span class='{$statusSmall}'>{$order["ORDER_STATUS"]}</span>
            </div>
            <div class='col'>
               <form method='post' id='{$order["ORDER_ID"]}' action='{$base_url}admin/manage-orders.php'>
               <div class='row offset-1'>
                    <div class='col'>
                        <input type='hidden' name='order_id' value='{$order["ORDER_ID"]}'>
                        <select name='status' class='form-select'>$statusOptions</select>      
                    </div>
                    <div class='col'>
                        <input type='hidden' name='token' value='{$_SESSION["token"]}'>
                        <a type='button' data-bs-toggle='modal' data-bs-target='#updateStatic' onclick='updateModal({$order["ORDER_ID"]}, \"modal-btn-update\");' class='btn btn-outline-primary'>
                        Update</a>
                    </div>
                </div>
                </form>  
                          
            </div>
            <div class='col-1 mt-2'>
                <form action='/admin/manage-orders.php' id='{$order["ORDER_ID"]}' method='post'>
                    <input type='hidden' name='order_id' value='{$order["ORDER_ID"]}'>
                    <input type='hidden' name='token' value='{$_SESSION["token"]}'>
                    <a type='button' data-bs-toggle='modal' data-bs-target='#deleteStatic' onclick='updateModal({$order["ORDER_ID"]}, \"modal-btn-delete\");' class='h4'>
                    <i class='bi bi-trash'></i></a>
                </form>    
            </div> 
        </div>      
    </div>
</div>
<hr>
<div class='row mb-4'>
    <table class='table'>
    <caption>Delivery Fee: RM5.00</caption>
    <thead>
        <tr>
            <th scope='col'>#</th>
            <th scope='col'>Product</th>
            <th scope='col'>Name</th>
            <th scope='col'>Quantity</th>
            <th scope='col'>Date Ordered</th>
            <th scope='col'>Price</th>
        </tr>
    </thead>
    <tbody>
        {$orderLineStr}
    </tbody>
    <tfoot>
    <td colspan='5'><span class='fw-bold'>Total:</span></td>
    <td>RM{$total}</td>
    </tfoot>
    </table>
</div>
</div>

";
        }
    }
}

//for dashboard admin
function orders_adminOrdersLite($orders) {
    $base_url = BASE_URL;
    if ($orders != null){
        foreach ($orders as $order) {
            $count = 1;
            //date
            $date = date_create($order["CREATED_AT"]);
            $dateFormatted = date_format($date, "d M Y");

            //code
            $orderCode = sprintf('%08d', $order["ORDER_ID"]);
            $total = number_format((float)$order["TOTAL_PRICE"], 2, ".", ",");
            $statusSmall = strtolower($order["ORDER_STATUS"]);
            echo "
<div class='row mt-3 mb-1'>
    <div class='col-5'>
        <span class='h4'>Order #{$orderCode}</span><span class='text-muted h4'> by {$order["username"]}</span>
    </div>
    <div class='col'>
        <div class='row'>
            <div class='col-3 text-end mt-2'>
                <span class='{$statusSmall}'>{$order["ORDER_STATUS"]}</span>
            </div>
            <div class='col'>
            <a class='btn btn-outline-primary float-end' href='{$base_url}admin/manage-orders.php'>See More Options..</a>  
            </div>
        </div>      
    </div>
    
</div>
<div class='row'>
    <div class='col-2 text-end mt-2'>
        <span class='fw-bold'>Total:</span>
    </div>
    <div class='col'>
    <span class='fs-4'>RM{$total}</span>
    </div>
</div>

<hr>
";
        }
    }
    else {
        echo "<span class='align-items-center'>No orders yet</span>";
    }
}
function admin_displayEmployeeUsers($employeeUsers) {
    if ($employeeUsers != null) {
        $count = 1;
        $base_url = BASE_URL;
        // OKAY, FOR DELETING, I need to use a modal so the user can be sure to remove it
        foreach ($employeeUsers as $user) {
            $fullName = $user["FIRST_NAME"] . " " . $user["LAST_NAME"];
            $date = date_create($user["CREATED_AT"]);
            $dateFormatted = date_format($date, "d M Y");
            echo
            "<tr class='align-middle'>
                <th scope='row'>$count</th>
                <td>{$user["USERNAME"]}</td>
                <td>{$fullName}</td>
                <td>{$user["EMAIL"]}</td>
                <td>{$dateFormatted}</td>
                <td class='position-relative text-center align-middle'>
                    <div class='position-absolute top-50 start-0 translate-middle-y'>
                        <a type='button' class='h4' href='mailto:{$user["EMAIL"]}'>
                        <i class='bi bi-envelope'></i></a>
                    </div>
                    <div class='position-absolute top-50 end-0 translate-middle-y'>
                        <a type='button' class='h4 edit-employee' 
                        data-username='{$user["USERNAME"]}' 
                        data-employee-id='{$user["EMPLOYEE_ID"]}'
                        data-first-name='{$user["FIRST_NAME"]}'
                        data-last-name='{$user["LAST_NAME"]}'
                        data-email='{$user["EMAIL"]}'
                        data-phone='{$user["PHONE"]}'
                        data-authority-level='{$user["AUTHORITY_LEVEL"]}'
                        data-manager-id='{$user["MANAGER_ID"]}'>
                            <i class='bi bi-pencil-square'></i>
                        </a>
                        <a type='button' class='h4 delete-employee' data-employee-id='{$user["EMPLOYEE_ID"]}'>
                            <i class='bi bi-trash'></i>
                        </a>
                    </div>
                    
                </td>
            </tr>";
            $count++;
        }
    }
}

function admin_displayMemberUsers($memberUsers) {
    if ($memberUsers != null) {
        $base_url = BASE_URL;
        $count = 1;
        // OKAY, FOR DELETING, I need to use a modal so the user can be sure to remove it
        foreach ($memberUsers as $user) {
            $fullName = $user["FIRST_NAME"] . " " . $user["LAST_NAME"];
            $date = date_create($user["CREATED_AT"]);
            $dateFormatted = date_format($date, "d M Y");

            $address = ($user["ADDRESS"] ?? "") . ", " . ($user["CITY"] ?? "")
                        . ", " . ($user["STATE"] ?? "");
            $phone = $user["PHONE"] ?? "-";
            echo
            "<tr class='align-middle'>
                <th scope='row'>$count</th>
                <td>{$user["USERNAME"]}</td>
                <td>{$fullName}</td>
                <td>{$address}</td>
                <td>{$user["EMAIL"]}</td>
                <td>{$phone}</td>
                <td>{$dateFormatted}</td>
                <td class='position-relative text-center align-middle'>
                    <div class='position-absolute top-50 start-0 translate-middle-y'>
                        <a type='button' class='h4' href='mailto:{$user["EMAIL"]}'>
                        <i class='bi bi-envelope'></i></a>
                    </div>
                    <div class='position-absolute top-50 start-50 translate-middle'>
                        <a type='button' class='h5' href='https://wa.me/{$phone}'>
                        <i class='bi bi-whatsapp'></i></a>
                    </div>
                    <div class='position-absolute top-50 end-0 translate-middle-y'>
                        <a type='button' class='h4 delete-member' data-member-id='{$user["CUSTOMER_ID"]}'>
                            <i class='bi bi-trash'></i>
                        </a>
                    </div>
                       
                </td>
            </tr>";
            $count++;
        }
    }
}



