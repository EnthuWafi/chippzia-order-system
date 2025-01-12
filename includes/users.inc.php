<?php

require_once("functions.inc.php");
/* USER RELATED */
//create customer/admin

// Technically not a user but :shrug:
function createCustomer($fname, $lname, $phone, $address, $city, $state, $conn=null) {
    //createCustomer can be called from createMember
    $oci_mode = OCI_COMMIT_ON_SUCCESS;
    if (!isset($conn)) {
        $conn = OpenConn();
    }
    else {
        //Do not commit if conn exists (which means its called from another function)
        $oci_mode = OCI_NO_AUTO_COMMIT;
    }
    $sql = "INSERT INTO customers (CUSTOMER_ID, FIRST_NAME, LAST_NAME, PHONE, ADDRESS, CITY, STATE) 
            VALUES (CUSTOMER_SEQ.NEXTVAL, :firstname, :lastname, :phone, :address, :city, :state)";

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':firstname', $fname);
        oci_bind_by_name($stmt, ':lastname', $lname);
        oci_bind_by_name($stmt, ':phone', $phone);
        oci_bind_by_name($stmt, ':address', $address);
        oci_bind_by_name($stmt, ':city', $city);
        oci_bind_by_name($stmt, ':state', $state);

        if (!oci_execute($stmt, $oci_mode)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        if ($oci_mode == OCI_COMMIT_ON_SUCCESS) {
            oci_free_statement($stmt);
            CloseConn($conn);
        }
        return true;
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        makeToast("error", "Error", "Error creating customer! Please try again!");
        return false;
    }
}
function createMember($fname, $lname, $email, $phone, $address, $city, $state, $username, $password) {
    $conn = OpenConn();
    $sql = "INSERT INTO MEMBERS(CUSTOMER_ID, EMAIL, USERNAME, PASSWORD_HASH)
            VALUES (CUSTOMER_SEQ.CURRVAL, :email, :username,:passwordhash)";

    try {
        if (!createCustomer($fname, $lname, $phone, $address, $city, $state, $conn)) {
            return false;
        }
        $stmt = oci_parse($conn, $sql);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        oci_bind_by_name($stmt, ':username', $username);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':passwordhash', $password_hash);

        if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        oci_commit($conn);

        oci_free_statement($stmt);
        CloseConn($conn);

        return true;
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        oci_rollback($conn);
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);

        makeToast("error", "Error", "Error creating members account! Please try again!");
        return false;
    }
}
function createEmployee($fname, $lname, $email, $phone, $username, $password, $managerID, $authorityLevel=1) {
    $conn = OpenConn();
    $sql = "INSERT INTO EMPLOYEES(EMPLOYEE_ID, FIRST_NAME, LAST_NAME, USERNAME, PASSWORD_HASH, EMAIL, PHONE, MANAGER_ID, AUTHORITY_LEVEL)
            VALUES (EMPLOYEE_SEQ.NEXTVAL, :firstname, :lastname, :username,:passwordhash, :email, :phone, :managerid, :authoritylevel)";

    try {
        $stmt = oci_parse($conn, $sql);

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        oci_bind_by_name($stmt, ':username', $username);
        oci_bind_by_name($stmt, ':firstname', $fname);
        oci_bind_by_name($stmt, ':lastname', $lname);
        oci_bind_by_name($stmt, ':passwordhash', $password_hash);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':phone', $phone);
        oci_bind_by_name($stmt, ':managerid', $managerID);
        oci_bind_by_name($stmt, ':authoritylevel', $authorityLevel);

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
        makeToast("error", "Error", "Error creating employees account! Please try again!");
        return false;
    }
}

function checkMember($username, $email)
{
    $sql = "SELECT * FROM members WHERE USERNAME = :username OR EMAIL = :email";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':username', $username);
        oci_bind_by_name($stmt, ':email', $email);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return true;
        }
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        makeToast("error", "Error", "Error checking members! Please try again!");
    }

    return false;
}

function checkEmployee($username, $email)
{
    $sql = "SELECT * FROM EMPLOYEES WHERE USERNAME = :username OR EMAIL = :email";
    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':username', $username);
        oci_bind_by_name($stmt, ':email', $email);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            return true;
        }
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        makeToast("error", "Error", "Error checking employees! Please try again!");
    }

    return false;
}

function returnUserType(){
    return $_SESSION["user_data"]["user_type"];
}

function verifyMember($username_input, $password) {
    //username input since it can either be email or username :)
    $sql = "SELECT c.*, m.*
            FROM CUSTOMERS c
            INNER JOIN MEMBERS m ON c.CUSTOMER_ID = m.CUSTOMER_ID
            AND (m.USERNAME = :usernameinput OR m.EMAIL = :usernameinput)";

    $conn = OpenConn();

    try{
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':usernameinput', $username_input);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            if (password_verify($password, $result["PASSWORD_HASH"])){
                return $result;
            }
        }
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to verify member!");
    }

    return null;
}

//Since employee and member has been separated, we will need to separate function to verify too
function verifyEmployee($username_input, $password) {
    $sql = "SELECT *
            FROM EMPLOYEES
            WHERE (USERNAME = :usernameinput OR EMAIL = :usernameinput)";

    $conn = OpenConn();

    try{
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':usernameinput', $username_input);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        $result = oci_fetch_assoc($stmt);

        oci_free_statement($stmt);
        CloseConn($conn);

        if ($result) {
            if (password_verify($password, $result["PASSWORD_HASH"])){
                return $result;
            }
        }
    }
    catch (Exception $e) {
        createLog($e->getMessage());
        if (isset($stmt)) {
            oci_free_statement($stmt);
        }
        CloseConn($conn);
        die("Error: unable to verify employee!");
    }

    return null;
}

//Retrieve states will be moved to functions.inc.php (it will refer to an API instead), since data
// no longer exist in database

function retrieveMember($customerID) {
    $sql = "SELECT c.*, m.*
            FROM CUSTOMERS c
            INNER JOIN MEMBERS m ON c.CUSTOMER_ID = m.CUSTOMER_ID
            WHERE c.CUSTOMER_ID = :customerID";

    $conn = OpenConn();

    try{
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':customerID', $customerID);

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

        die("Error: unable to retrieve member!");
    }
    makeToast("error", "Member doesn't exist or was removed!", "Error");
    header("Location: /logout.php");
    die();
}
function retrieveEmployee($employeeID) {
    $sql = "SELECT *
            FROM EMPLOYEES
            WHERE EMPLOYEE_ID = :employeeID";

    $conn = OpenConn();

    try{
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':employeeID', $employeeID);

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

        die("Error: unable to retrieve employee!");
    }
    makeToast("error", "Employee doesn't exist or was removed!", "Error");
    header("Location: /logout.php");
    die();
}

function updateCustomerContact($customerID, $contact){
    $sql = "UPDATE CUSTOMERS SET address = :address, CITY = :city, state = :state, PHONE = :phone, POSTCODE = :postcode
            WHERE customer_id = ?";

    $conn = OpenConn();
    try{
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':customerID', $customerID);
        oci_bind_by_name($stmt, ':address', $contact["address"]);
        oci_bind_by_name($stmt, ':city', $contact["city"]);
        oci_bind_by_name($stmt, ':state', $contact["state"]);
        oci_bind_by_name($stmt, ':phone', $contact["phone"]);
        oci_bind_by_name($stmt, ':postcode', $contact["postcode"]);

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

        makeToast("error", "Unable to update customer contact details!", "Error");
        return false;
    }
}

//admin functions
function retrieveCountCustomers() {
    $sql = "SELECT COUNT(customer_id) as \"COUNT\" FROM CUSTOMERS";

    $conn = OpenConn();

    try{
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
        die("Error: cannot get the customers count!");
    }
    return null;
}

//Specifically count only members
function retrieveCountMembers() {
    $sql = "SELECT COUNT(customer_id) as \"COUNT\" FROM MEMBERS";

    $conn = OpenConn();

    try{
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
        die("Error: cannot get the members count!");
    }
    return null;
}

function retrieveCountEmployees() {
    $sql = "SELECT COUNT(employee_id) as \"COUNT\" FROM EMPLOYEES";

    $conn = OpenConn();

    try{
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
        die("Error: cannot get the employees count!");
    }
    return null;
}

function retrieveAllEmployees() {
    $sql = "SELECT
                *
            FROM EMPLOYEES";
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
        die("Error: unable to retrieve employees!");
    }

    return null;
}

function retrieveAllMembers() {
    $sql = "SELECT
                c.*, m.*
            FROM CUSTOMERS c
            INNER JOIN MEMBERS M on c.CUSTOMER_ID = M.CUSTOMER_ID";
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
        die("Error: unable to retrieve members!");
    }

    return null;
}

function retrieveAllCustomers() {
    $sql = "SELECT
                customer_id AS customer_id,
                first_name AS first_name,
                last_name AS last_name,
                phone AS phone,
                address AS address,
                city AS city,
                state AS state,
                created_at AS created_at,
                deleted_at AS deleted_at
            FROM CUSTOMERS";
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
        die("Error: unable to retrieve customers!");
    }

    return null;
}

//delete functions
// delete member is not required, since member is set to on delete cascade, which mean this function can delete members too
function deleteCustomer($customerID) {
    $sql = "DELETE FROM CUSTOMERS WHERE customer_id = :customerID";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':customer_id', $customerID);

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
        die("Error: unable to delete customer!");
    }
}

function deleteEmployees($employeeID) {
    $sql = "DELETE FROM EMPLOYEES WHERE EMPLOYEE_ID = :employee_id";

    $conn = OpenConn();

    try {
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':employee_id', $employeeID);

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
        die("Error: unable to delete employee!");
    }
}

//Not sure what to really do with LIKE function below, I'll ignore it for now
function retrieveCustomerNameLike($query) {
    $sql = "SELECT 
                customer_id AS customer_id,
                first_name AS first_name,
                last_name AS last_name,
                phone AS phone,
                address AS address,
                city AS city,
                state AS state,
                created_at AS created_at,
                deleted_at AS deleted_at
            FROM CUSTOMERS WHERE (FIRST_NAME LIKE :query OR LAST_NAME LIKE :query)";
    $conn = OpenConn();
    $query = "%".$query."%";

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
        die("Error: unable to retrieve employees!");
    }

    return null;
}

// NEW FUNCTION WAW


/* Outdated functions below */

// TODO: Warning. Highly outdated function, separate user function into two (employees and members)
function createUser($fname, $lname, $username, $password, $email, $user_type) {
    if (!($user_type == "customer" || $user_type == "admin")) {
        die("Invalid user type");
    }

    $conn = OpenConn();
    $sql = "INSERT INTO users(username, password, user_fname, user_lname, user_email, user_type) 
            VALUES (?, ?, ?, ?, ?, ?)";

    try {
        if ($conn->execute_query($sql, [$username, password_hash($password, PASSWORD_DEFAULT), $fname, $lname,
                                        $email, $user_type])){
            CloseConn($conn);
            return true;
        }
    }
    catch (mysqli_sql_exception){
        createLog($conn->error);
        header("Location: /index.php");
        die();
    }

    return false;
}

//verify user (return customer/admin)
function verifyUser($username, $password) {
    $sql = "SELECT us.* FROM users us WHERE us.username = ?";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql, [$username]);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            //check password
            if (password_verify($password, $row["password"])){
                return $row;
            }
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user!");
    }

    return null;
}

function retrieveStates() {
    $sql = "SELECT * FROM states";
    $conn = OpenConn();

    $result = $conn->execute_query($sql);
    CloseConn($conn);

    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return null;
}

function retrieveUser($userID) {
    $sql = "SELECT us.*, s.* FROM users us 
            LEFT OUTER JOIN states s on us.state_code = s.state_code
            WHERE us.user_id = ?";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql, [$userID]);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user!");
    }

    makeToast("error", "User doesn't exist or was removed!", "Error");
    header("Location: /logout.php");
    die();
}

function retrieveUserSimple($userID) {
    $sql = "SELECT us.* FROM users us 
            WHERE us.user_id = ?";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql, [$userID]);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user!");
    }

    makeToast("error", "User doesn't exist or was removed!", "Error");
    header("Location: /logout.php");
    die();
}

function updateContact($userID, $contact){
    $sql = "UPDATE users SET user_address = ?, user_city = ?, user_postcode = ?, state_code = ?, user_phone = ?
            WHERE user_id = ?";

    $conn = OpenConn();
    try{
        $result = $conn->execute_query($sql, [$contact["address"], $contact["city"], $contact["postcode"],
                                                $contact["state_code"], $contact["phone"], $userID]);
        CloseConn($conn);

        if ($result) {
            return true;
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot update user contact!");
    }

    return false;
}

//admin
function retrieveCountUsers() {
    $sql = "SELECT COUNT(user_id) as 'count' FROM users";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user count!");
    }
    return null;
}
function retrieveCountAdminUsers() {
    $sql = "SELECT COUNT(user_id) as 'count' FROM users  WHERE user_type = 'admin'";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user admin count!");
    }
    return null;
}
function retrieveCountCustomerUsers() {
    $sql = "SELECT COUNT(user_id) as 'count' FROM users WHERE user_type = 'customer'";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user customer count!");
    }
    return null;
}
function retrieveAllAdminUsers() {
    $sql = "SELECT * FROM users WHERE user_type = 'admin'";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user admins!");
    }
    return null;
}

function retrieveAllCustomerUsers() {
    $sql = "SELECT u.*, s.state_name FROM users u 
         LEFT OUTER JOIN states s on u.state_code = s.state_code
         WHERE user_type = 'customer'";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user customers!");
    }
    return null;
}

function deleteUser($userID) {
    $sql = "DELETE FROM users WHERE user_id = ?";

    $conn = OpenConn();

    try{
        $result = $conn->execute_query($sql, [$userID]);
        CloseConn($conn);

        if ($result) {
            return true;
        }
    }
    catch (mysqli_sql_exception) {
        createLog($conn->error);
        die("Error: cannot get the user customers!");
    }
    return false;
}

function retrieveAllUserLike($query) {
    $sql = "SELECT * FROM users u WHERE u.user_fname LIKE ? OR u.user_lname LIKE ? OR u.username LIKE ?";
    $query = "%{$query}%";

    $conn = OpenConn();

    try {
        $result = $conn->execute_query($sql, [$query, $query, $query]);
        CloseConn($conn);

        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }
    catch (mysqli_sql_exception){
        createLog($conn->error);
        die("Error: unable to retrieve users like!");
    }

    return null;
}
