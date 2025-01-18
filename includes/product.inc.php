<?php

require_once("functions.inc.php");
/* PRODUCT RELATED */


//todo create, delete select products


function retrieveAllProduct() {
    $sql = "SELECT * FROM products p";
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
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve products!");
    }

    return null;
}

function retrieveProductCount() {
    $sql = "SELECT COUNT(p.product_id) as \"COUNT\" FROM products p";
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
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve product count!");
    }

    return null;
}

function retrieveProduct($productID) {
    $sql = "SELECT p.* FROM products p WHERE p.product_id = :product_id";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':product_id', $productID);

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
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        makeToast("error", "Error while looking for product", "Error");
    }

    return null;
}

function deleteProduct($productID) {
    $sql = "DELETE FROM products WHERE product_id = :product_id";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':product_id', $productID);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        return true;
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to delete product!");
    }

}


//TODO: New schema, this code is no longer applicable. Will need changing soon! Product code does not exist
// Inventory Quantity needed too
function createProduct($productName, $productImage, $productPrice, $inventoryQuantity, $productDescription) {
    $sql = "INSERT INTO products(PRODUCT_ID, PRODUCT_NAME, product_image, product_price, INVENTORY_QUANTITY, PRODUCT_DESCRIPTION) 
            VALUES (PRODUCT_SEQ.nextval, :product_name, :product_image, :product_price, :inventory_quantity, :product_description)";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':product_name', $productName);
        oci_bind_by_name($stmt, ':product_image', $productImage);
        oci_bind_by_name($stmt, ':product_price', $productPrice);
        oci_bind_by_name($stmt, ':inventory_quantity', $inventoryQuantity);
        oci_bind_by_name($stmt, ':product_description', $productDescription);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        return true;
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        return false;
    }
}

// TODO: This code is also no longer applicable, since product code is taken out. Use Product Name next time
// NVM I just did a quick change lmao
function retrieveAllProductLike($query) {
    $sql = "SELECT * FROM products p WHERE p.product_name LIKE :query";
    $query = "%{$query}%";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':query', $query);

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
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to retrieve products like".$query);
    }

    return null;
}

//TODO: Update product
function updateProduct($productId, $productName, $productImage, $productPrice, $inventoryQuantity, $productDescription) {
    $sql = "UPDATE products
        SET PRODUCT_NAME = :product_name,
            product_image = :product_image,
            product_price = :product_price,
            INVENTORY_QUANTITY = :inventory_quantity,
            PRODUCT_DESCRIPTION = :product_description
        WHERE PRODUCT_ID = :product_id";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':product_name', $productName);
        oci_bind_by_name($stmt, ':product_image', $productImage);
        oci_bind_by_name($stmt, ':product_price', $productPrice);
        oci_bind_by_name($stmt, ':inventory_quantity', $inventoryQuantity);
        oci_bind_by_name($stmt, ':product_description', $productDescription);
        oci_bind_by_name($stmt, ':product_id', $productId);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        oci_free_statement($stmt);
        CloseConn($conn);

        return true;
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        return false;
    }
}
