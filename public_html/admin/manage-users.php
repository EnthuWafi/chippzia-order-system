<?php

require("../../includes/functions.inc.php");

session_start();

employee_login_required();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postedToken = $_POST["token"];
    try{
        if(!empty($postedToken)){
            if(isTokenValid($postedToken)){

                if (!checkAuthority(1)) {
                    throw new Exception("Requires Super Admin authority!");
                }

                if (isset($_POST["delete_member"])) {
                    $customerID = htmlspecialchars($_POST["customer_id"]);
                    deleteCustomer($customerID) or throw new Exception("Customer wasn't able to be deleted!");
                    makeToast("success", "Account successfully deleted!", "Success");
                }
                else if (isset($_POST["delete_employee"])) {
                    $employeeID = htmlspecialchars($_POST["employee_id"]);
                    $currentUserID = $_SESSION["user_data"]["EMPLOYEE_ID"]; // Assuming user_data contains the current user info

                    // Check if current user == the employee being deleted
                    if ($employeeID == $currentUserID) {
                        throw new Exception("You cannot delete your own account!");
                    }

                    // Check if employee is the only super admin
                    $employee = retrieveEmployee($employeeID);
                    $countSuperAdmin = retrieveCountAuthorityLevel(1)["COUNT"];

                    if ($employee["AUTHORITY_LEVEL"] == 1
                        && $countSuperAdmin == 1) {
                        throw new Exception("Cannot delete the only super admin!");
                    }

                    deleteEmployees($employeeID) or throw new Exception("Employee wasn't able to be deleted!");
                    makeToast("success", "Employee successfully deleted!", "Success");
                }
                //create employee todo
                else if (isset($_POST["create_employee"])) {
                    $fname = htmlspecialchars($_POST["fname"]);
                    $lname = htmlspecialchars($_POST["lname"]);
                    $username = htmlspecialchars($_POST["username"]);
                    $phone = htmlspecialchars($_POST["phone"]);
                    $email = htmlspecialchars($_POST["email"]);
                    $password = htmlspecialchars($_POST["password"]);
                    $authorityLevel = htmlspecialchars($_POST["authorityLevel"]);
                    $managerID = htmlspecialchars($_POST["managerId"]);

                    if (empty($managerID)) {
                        $managerID = null;
                    }

                    createEmployee($fname, $lname, $email, $phone, $username, $password, $managerID, $authorityLevel) or throw new Exception("Employee account wasn't able to be created!");
                    makeToast("success", "Employee account successfully created!", "Success");
                }
                //edit employee
                else if (isset($_POST["update_employee"])) {
                    $fname = htmlspecialchars($_POST["fname"]);
                    $employee_id = htmlspecialchars($_POST["employee_id"]);
                    $lname = htmlspecialchars($_POST["lname"]);
                    $username = htmlspecialchars($_POST["username"]);
                    $password = htmlspecialchars($_POST["password"]);
                    $phone = htmlspecialchars($_POST["phone"]);
                    $email = htmlspecialchars($_POST["email"]);
                    $authorityLevel = htmlspecialchars($_POST["authorityLevel"]);
                    $managerID = htmlspecialchars($_POST["managerId"]);

                    //do stuff here
                    if (!empty($managerID)) {
                        if (!isHierarchyValid($employee_id, $managerID)) {
                            throw new Exception("Circular hierarchy detected!");
                        }
                    }
                    else {
                        $managerID = null;
                    }

                    updateEmployee($employee_id, $fname, $lname, $email, $phone, $username, $password, $managerID, $authorityLevel)
                    or throw new Exception("Employee account wasn't able to be updated!");

                    makeToast("success", "Employee account successfully updated!", "Success");
                }
            }
            else{
                makeToast("warning", "Please refrain from attempting to resubmit previous form", "Warning");
            }
        }
        else {
            throw new Exception("Token not found");
        }
    }
    catch (Exception $e){
        makeToast("error", $e->getMessage(), "Error");
    }

    header("Location: ".BASE_URL."admin/manage-users.php");
    die();
}

$membersCount = retrieveCountMembers()['COUNT'] ?? 0;
$employeeCount = retrieveCountEmployees()['COUNT'] ?? 0;

$userCount = $membersCount + $employeeCount;

$employees = retrieveAllEmployees();
$members = retrieveAllMembers();

displayToast();
$token = getToken();
?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?> | Manage Users</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php admin_side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php admin_header_bar("Manage User") ?>

            <!-- todo users here  -->
            <div class="container">
                <div class="row mt-4 gx-4 ms-3">
                    <div class="p-3 mb-5 bg-body rounded row gx-3">
                        <div class="row">
                            <span class="h3"><?= $userCount ?> users found</span>
                        </div>
                        <div class="shadow p-3 mb-5 mt-3 bg-body rounded row gx-3 mx-1">
                            <!-- ADMIN-->
                            <div class="row mb-1">
                                <div class="col">
                                    <span class="h3"><?= $employeeCount ?> employees found</span>
                                </div>
                                <div class="col text-end ">
                                    <?php if (checkAuthority(1)): ?>
                                    <button type="button" class="btn btn-danger add-employee">
                                        <span class="h5"><i class="bi bi-plus-circle"> </i>Add</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mt-3 px-3 table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col" style="width: 5%;">#</th>
                                        <th scope="col" style="width: 15%;">Username</th>
                                        <th scope="col" style="width: 20%;">Full Name</th>
                                        <th scope="col" style="width: 10%;">Email</th>
                                        <th scope="col" style="width: 15%;">Phone</th>
                                        <th scope="col" style="width: 20%;">Created</th>
                                        <th scope="col" style="width: 10%;">Authority</th>
                                        <th scope="col" class="text-center col-1">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        admin_displayEmployeeUsers($employees);
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3 mx-1">
                            <!-- CUSTOMER -->
                            <div class="row">
                                <span class="h3"><?= $membersCount ?> members found</span>
                            </div>
                            <div class="row mt-3 px-3 table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Username</th>
                                        <th scope="col" class="col-2">Full Name</th>
                                        <th scope="col" class="col-2">Address</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Registration</th>
                                        <th scope="col" class="text-center" style="width: 10%">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    admin_displayMemberUsers($members);
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                </div>
            </div>




            <?php footer(); ?>
        </main>

    </div>
</div>
<?php body_script_tag_content();?>
<script>
    $(document).ready(function () {
        const token = '<?= $_SESSION["token"]; ?>';

        //add employee
        $('.add-employee').on('click', function () {
            const currentEmployeeID = <?= $_SESSION['user_data']['EMPLOYEE_ID']; ?>;
            let managerOptions = null;

            $.ajax({
                url: `<?= BASE_URL ?>api/employee.php`,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response && response.status === "success") {
                        let messageData = [];

                        // Normalize response.message to always be an array
                        if (Array.isArray(response.message)) {
                            messageData = response.message;
                        } else if (typeof response.message === "object") {
                            messageData = Object.values(response.message);
                        }

                        managerOptions = messageData.map(manager => ({
                            value: manager.EMPLOYEE_ID,
                            label: `${manager.FIRST_NAME} ${manager.LAST_NAME}`
                        }));

                        // Add "No Manager" option at the start of the options array
                        managerOptions.unshift({
                            value: "",
                            label: "No Manager"
                        });


                        const formInfo = {
                            form: {
                                fname: {
                                    label: "First Name",
                                    type: "text",
                                    value: "",
                                    placeholder: "Enter first name"
                                },
                                lname: {
                                    label: "Last Name",
                                    type: "text",
                                    value: "",
                                    placeholder: "Enter last name"
                                },
                                username: {
                                    label: "Username",
                                    type: "text",
                                    value: "",
                                    placeholder: "Enter username"
                                },
                                password: {
                                    label: "Password",
                                    type: "password",
                                    value: "",
                                    placeholder: "Enter password"
                                },
                                email: {
                                    label: "Email",
                                    type: "email",
                                    value: "",
                                    placeholder: "Enter email"
                                },
                                phone: {
                                    label: "Phone",
                                    type: "text",
                                    value: "",
                                    placeholder: "Enter phone number"
                                },
                                authorityLevel: {
                                    label: "Authority",
                                    type: "select",
                                    value: 3,
                                    options: [
                                        {value: 1, label: "Super Admin"},
                                        {value: 2, label: "Admin"},
                                        {value: 3, label: "Employee"}
                                    ]
                                },
                                managerId: {
                                    label: "Manager",
                                    type: "select",
                                    value: currentEmployeeID,
                                    options: managerOptions
                                }
                            }
                        };

                        const formHTML = assembleForm(formInfo, '<?= BASE_URL ?>admin/manage-users.php');

                        bootbox.dialog({
                            title: "Create Employee",
                            message: formHTML,
                            size: "large",
                            buttons: {
                                cancel: {
                                    label: "Cancel",
                                    className: "btn-secondary"
                                },
                                save: {
                                    label: "Save Changes",
                                    className: "btn-primary",
                                    callback: function () {
                                        const form = $('#form');

                                        form.append($("<input>", {
                                            type: "hidden",
                                            name: "create_employee",
                                            value: true
                                        }));

                                        form.append($("<input>", {
                                            type: "hidden",
                                            name: "token",
                                            value: token
                                        }));

                                        form.submit();
                                    }
                                }
                            }
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.log("Error fetching managers:", error);
                    bootbox.alert("Error fetching manager data. Please try again later.");
                }
            });
        });

        $('.edit-employee').on('click', function () {
            const employeeId = $(this).data('employee-id');
            const username = $(this).data('username');
            const firstName = $(this).data('first-name');
            const lastName = $(this).data('last-name');
            const email = $(this).data('email');
            const phone = $(this).data('phone');
            const authorityLevel = $(this).data('authority-level');
            const managerId = $(this).data('manager-id');

            let managerOptions = null;

            $.ajax({
                url: `<?= BASE_URL ?>api/employee.php?employeeID=${employeeId}`,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response && response.status === "success") {
                        let messageData = [];

                        // Normalize response.message to always be an array
                        if (Array.isArray(response.message)) {
                            messageData = response.message;
                        } else if (typeof response.message === "object") {
                            messageData = Object.values(response.message);
                        }

                        managerOptions = messageData.map(manager => ({
                            value: manager.EMPLOYEE_ID,
                            label: `${manager.FIRST_NAME} ${manager.LAST_NAME}`
                        }));

                        // Add "No Manager" option at the start of the options array
                        managerOptions.unshift({
                            value: "",
                            label: "No Manager"
                        });


                        const formInfo = {
                            form: {
                                fname: {
                                    label: "First Name",
                                    type: "text",
                                    value: firstName,
                                    placeholder: "Enter first name"
                                },
                                lname: {
                                    label: "Last Name",
                                    type: "text",
                                    value: lastName,
                                    placeholder: "Enter last name"
                                },
                                username: {
                                    label: "Username",
                                    type: "text",
                                    value: username,
                                    placeholder: "Enter username"
                                },
                                password: {
                                    label: "Password",
                                    type: "password",
                                    value: "",
                                    placeholder: "Enter password"
                                },
                                email: {
                                    label: "Email",
                                    type: "email",
                                    value: email,
                                    placeholder: "Enter email"
                                },
                                phone: {
                                    label: "Phone",
                                    type: "text",
                                    value: phone,
                                    placeholder: "Enter phone number"
                                },
                                authorityLevel: {
                                    label: "Authority",
                                    type: "select",
                                    value: authorityLevel,
                                    options: [
                                        {value: 1, label: "Super Admin"},
                                        {value: 2, label: "Admin"},
                                        {value: 3, label: "Employee"}
                                    ]
                                },
                                managerId: {
                                    label: "Manager",
                                    type: "select",
                                    value: managerId,
                                    options: managerOptions
                                }
                            }
                        };

                        const formHTML = assembleForm(formInfo, '<?= BASE_URL ?>admin/manage-users.php');

                        bootbox.dialog({
                            title: "Edit Employee",
                            message: formHTML,
                            size: "large",
                            buttons: {
                                cancel: {
                                    label: "Cancel",
                                    className: "btn-secondary"
                                },
                                save: {
                                    label: "Save Changes",
                                    className: "btn-primary",
                                    callback: function () {
                                        const form = $('#form');

                                        form.append($("<input>", {
                                            type: "hidden",
                                            name: "employee_id",
                                            value: employeeId
                                        }));

                                        form.append($("<input>", {
                                            type: "hidden",
                                            name: "update_employee",
                                            value: true
                                        }));

                                        form.append($("<input>", {
                                            type: "hidden",
                                            name: "token",
                                            value: token
                                        }));

                                        form.submit();
                                    }
                                }
                            }
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.log("Error fetching managers:", error);
                    bootbox.alert("Error fetching manager data. Please try again later.");
                }
            });
        });

        $('.delete-employee').on('click', function () {
            const employeeId = $(this).data('employee-id'); // Get user ID from data attribute

            // Show Bootbox confirmation modal
            bootbox.confirm({
                title: "Confirm Deletion",
                message: "Are you sure you want to delete this employee? This action cannot be undone.",
                buttons: {
                    confirm: {
                        label: "Yes, Delete",
                        className: "btn-danger"
                    },
                    cancel: {
                        label: "Cancel",
                        className: "btn-secondary"
                    }
                },
                callback: function (result) {
                    if (result) {
                        const form = $('<form>', {
                            action: '<?= BASE_URL ?>admin/manage-users.php',
                            method: 'POST'
                        });

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'employee_id',
                            value: employeeId
                        }));

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'token',
                            value: token
                        }));

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'delete_employee',
                            value: true
                        }));

                        $('body').append(form);
                        form.submit();
                    }
                }
            });
        });

        $('.delete-member').on('click', function () {
            const memberId = $(this).data('member-id'); // Get the member ID

            bootbox.confirm({
                title: "Confirm Deletion",
                message: "Are you sure you want to delete this member? This action cannot be undone.",
                buttons: {
                    confirm: {
                        label: "Yes, Delete",
                        className: "btn-danger"
                    },
                    cancel: {
                        label: "Cancel",
                        className: "btn-secondary"
                    }
                },
                callback: function (result) {
                    if (result) {
                        const form = $('<form>', {
                            action: '<?= BASE_URL ?>admin/manage-users.php',
                            method: 'POST'
                        });

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'member_id',
                            value: memberId
                        }));

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'token',
                            value: token
                        }));

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'delete_member',
                            value: true
                        }));

                        $('body').append(form);
                        form.submit();
                    }
                }
            });
        });

    });
</script>
</body>

</html>