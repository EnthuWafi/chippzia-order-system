<?php

require_once("functions.inc.php");
/* PRODUCT RELATED */


//todo create, delete select orders

//orders (customer**)
// Changed the code to fit better with the new database
function retrieveAllCustomerOrders($customerID, $limit=null) {
    $sql = "SELECT o.*, c.*
            FROM orders o
            INNER JOIN customers c on c.customer_id = o.customer_id 
            WHERE c.customer_id = :customer_id";

    if (isset($limit)) {
        $sql .= "  AND rownum <= " . $limit;
    }

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':customer_id', $customerID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $result[] = $row;
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }

    } catch (Exception $e) {
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: Unable to retrieve orders!");
    }

    return null;
}

//orders (admin)
function retrieveAllOrders() {
    $sql = "SELECT o.*, c.*
            FROM ORDERS o
            INNER JOIN customers c on c.customer_id = o.customer_id";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $result[] = $row;
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve orders!");
    }

    return null;
}

function retrieveAllOrders5LIMIT() {
    $sql = "SELECT o.*, c.*
            FROM ORDERS o
            INNER JOIN customers c on c.customer_id = o.customer_id
            WHERE rownum <= 5
            ORDER BY o.CREATED_AT DESC";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $result[] = $row;
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve orders!");
    }

    return null;
}

// order count (admin)
function retrieveOrderCount() {
    $sql = "SELECT COUNT(o.order_id) AS \"COUNT\" FROM orders o";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve orders count!");
    }

    return null;
}

//order count (customer)
function retrieveCustomerOrderCount($customerID) {
    $sql = "SELECT COUNT(o.order_id) AS \"COUNT\" 
            FROM orders o 
            WHERE o.customer_id = :customer_id";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':customer_id', $customerID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve orders count!");
    }

    return null;
}

function retrieveCustomerOrderLineSumQuantity($customerID) {
    $sql = "SELECT SUM(ol.quantity) as \"SUM\" 
            FROM order_lines ol
            INNER JOIN orders o on ol.order_id = o.order_id AND o.customer_id = :customer_id";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':customer_id', $customerID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }
        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve order lines count!");
    }

    return null;
}

function retrieveCustomerTotalSpend($customerID) {
    $sql = "SELECT sum(TOTAL_PRICE) as \"SUM\" FROM orders
            WHERE order_status = 'COMPLETED' and customer_id = :customer_id";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':customer_id', $customerID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve customer total spent!");
    }

    return null;
}
function retrieveAllOrderLines($orderID) {
    $sql = "SELECT o.*, p.* FROM order_lines o
         INNER JOIN products p on o.product_id = p.product_id
         WHERE o.order_id = :order_id";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':order_id', $orderID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $result[] = $row;
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve order lines!");
    }

    return null;
}

//TODO : Create Order Function Oracle (this one is MariaDB/MySQL)

function createOrderCustomer($total_price, $customer_id, $cart, $loyalty_points=0, $conn=null) {
    $sqlQueryFirst = "INSERT INTO orders(ORDER_ID, TOTAL_PRICE, CUSTOMER_ID, LOYALTY_POINTS_REDEEMED, ORDER_STATUS)
                    VALUES (:order_id, :total_price, :customer_id, :loyalty_points, :order_status)";
    $sqlQuerySecond = "INSERT INTO order_lines(order_line_id, order_id, product_id, quantity, price) 
                    VALUES (:order_line_id, :order_id, :product_id, :quantity, :price)";
    $updateInventoryQuery = "UPDATE products SET INVENTORY_QUANTITY = INVENTORY_QUANTITY - :quantity 
                             WHERE PRODUCT_ID = :product_id";

    $order_status = 'COMPLETED'; //means that this is generated via employee
    if (!isset($conn)) {
        $conn = OpenConn();
        $order_status = 'PENDING'; //generated by members
    }

    try {

        // Generate new order ID using ORDER_SEQ
        $order_id_stmt = oci_parse($conn, "SELECT ORDER_SEQ.NEXTVAL AS order_id FROM dual");
        oci_execute($order_id_stmt);
        $row = oci_fetch_assoc($order_id_stmt);
        $order_id = $row['ORDER_ID'];

        // Insert into orders table
        $stmt = oci_parse($conn, $sqlQueryFirst);
        oci_bind_by_name($stmt, ':order_id', $order_id);
        oci_bind_by_name($stmt, ':total_price', $total_price);
        oci_bind_by_name($stmt, ':customer_id', $customer_id);
        oci_bind_by_name($stmt, ':loyalty_points', $loyalty_points);
        oci_bind_by_name($stmt, ':order_status', $order_status);


        oci_execute($stmt, OCI_NO_AUTO_COMMIT);

        if ($order_status == 'COMPLETED') {
            if (!updateOrderEmployeeID($order_id, $_SESSION['user_data']['EMPLOYEE_ID'], $conn)) {
                throw new Exception("Unable to update order employee ID!");
            };
        }


        // Insert each item in the cart into order_lines table
        foreach ($cart as $item) {
            $quantity = $item["quantity"];
            $product = $item["product"];

            // Lock the product row using FOR UPDATE
            $lockProductStmt = oci_parse($conn, "SELECT INVENTORY_QUANTITY FROM products WHERE PRODUCT_ID = :product_id FOR UPDATE");
            oci_bind_by_name($lockProductStmt, ':product_id', $product['PRODUCT_ID']);
            oci_execute($lockProductStmt);
            $productRow = oci_fetch_assoc($lockProductStmt);

            if ($productRow['INVENTORY_QUANTITY'] < $quantity) {
                throw new Exception("Sorry. Insufficient inventory quantity for product ID {$product['PRODUCT_ID']}");
            }

            // Generate new order line ID using ORDER_LINE_SEQ
            $order_line_id_stmt = oci_parse($conn, "SELECT ORDER_LINE_SEQ.NEXTVAL AS order_line_id FROM dual");
            oci_execute($order_line_id_stmt);
            $row = oci_fetch_assoc($order_line_id_stmt);
            $order_line_id = $row['ORDER_LINE_ID'];

            $stmt = oci_parse($conn, $sqlQuerySecond);
            oci_bind_by_name($stmt, ':order_line_id', $order_line_id);
            oci_bind_by_name($stmt, ':order_id', $order_id);
            oci_bind_by_name($stmt, ':product_id', $product['PRODUCT_ID']);
            oci_bind_by_name($stmt, ':quantity', $quantity);
            oci_bind_by_name($stmt, ':price', $product['PRODUCT_PRICE']);
            oci_execute($stmt,OCI_NO_AUTO_COMMIT);

            // Update product inventory
            $updateStmt = oci_parse($conn, $updateInventoryQuery);
            oci_bind_by_name($updateStmt, ':quantity', $quantity);
            oci_bind_by_name($updateStmt, ':product_id', $product['PRODUCT_ID']);
            oci_execute($updateStmt, OCI_NO_AUTO_COMMIT);
        }

        // Commit the transaction
        oci_commit($conn);

        return ["ORDER_ID" => $order_id];
    } catch (Exception $e) {
        // Rollback in case of error
        oci_rollback($conn);
        createLog($e->getMessage());
        makeToast("error", $e->getMessage(), "Error");
        return null;
    } finally {
        CloseConn($conn);
    }

}

function deleteOrder($orderID){
    $sql = "DELETE FROM orders WHERE order_id = :order_id";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':order_id', $orderID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        return true;
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to delete order!");
    }
}

// TODO: this function has to be updated to include the employee who updated the order
// In accordance with the new database and all that jazz :shrug:
function updateOrderStatusAndEmployeeID($orderID, $orderStatus, $employeeID){
    $sql= "UPDATE orders SET order_status = :order_status, EMPLOYEE_ID = :employee_id
            WHERE order_id = :order_id";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':order_id', $orderID);
        oci_bind_by_name($stmt, ':order_status', $orderStatus);
        oci_bind_by_name($stmt, ':employee_id', $employeeID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        return true;
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        return false;
    }
}

function retrieveTotalIncome() {
    $sql = "SELECT SUM(TOTAL_PRICE) as \"SUM\" FROM ORDERS 
            WHERE ORDER_STATUS = 'COMPLETED'";

    $conn = OpenConn();

    try{
        $stmt = oci_parse($conn, $sql);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result =  oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: cannot get the income!");
    }
    return null;
}

//retrieve product bought total
function retrieveAllProductBought() {
    $sql = "SELECT SUM(ol.quantity) as \"SUM\" FROM order_lines ol
            INNER JOIN orders o on ol.order_id = o.order_id AND o.order_status = 'COMPLETED'";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve product bought count!");
    }

    return null;
}

//will retrieve only one order so one is enough
function retrieveOrderSpecific($orderID) {
    $sql = "SELECT 
             o.created_at AS order_created_at, 
            c.created_at AS customer_created_at, 
            o.*, c.* FROM orders o
            INNER JOIN customers c on o.customer_id = c.customer_id
            WHERE o.order_id = :order_id";

    $conn = OpenConn();

    try{
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':order_id', $orderID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        die("Error: cannot get the order!");
    }
    return null;
}

function retrieveOrderSpecificMemberOnly($orderID) {
    $sql = "SELECT 
            o.created_at AS order_created_at, 
            c.created_at AS customer_created_at, 
            o.*, c.*, m.* FROM orders o
            INNER JOIN customers c on o.customer_id = c.customer_id
            INNER JOIN members m on c.CUSTOMER_ID = m.customer_id
            WHERE o.order_id = :order_id";

    $conn = OpenConn();

    try{
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':order_id', $orderID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return $result;
        }
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
    }
    return null;
}

function updateOrderEmployeeID($orderID, $employeeID, $conn){
    $sql= "UPDATE orders SET employee_id = :employee_id
            WHERE order_id = :order_id";

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':order_id', $orderID);
        oci_bind_by_name($stmt, ':employee_id', $employeeID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        oci_free_statement($stmt);

        return true;
    }
    catch (Exception $e){
        createLog($e->getMessage());
        if ($stmt) {
            oci_free_statement($stmt);
        }
        return false;
    }
}