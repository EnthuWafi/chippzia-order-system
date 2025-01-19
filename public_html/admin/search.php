<?php

require("../../includes/functions.inc.php");

session_start();

employee_login_required();

if (isset($_GET["q"])){
    $query = htmlspecialchars($_GET["q"]);

    $products = retrieveAllProductLike($query);
    $members = retrieveAllMembersLike($query);
    $employees = retrieveAllEmployeeLike($query);
}
else {
    makeToast("Warning", "Query was not found!", "Warning");
    header("Location: ".BASE_URL."admin/dashboard.php");
    die();
}

$count = 0;
$base_url = BASE_URL;

displayToast();
?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?> | Search Result</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php admin_side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php admin_header_bar("Search Result") ?>

            <!-- todo users here  -->
            <div class="container">
                <div class="row mt-4 gx-4 ms-3">
                    <div class="p-3 mb-5 bg-body rounded row gx-3">
                        <div class="row">
                            <span class="h3"><span id="product-count">0</span> products found</span>
                        </div>
                        <div class="shadow p-3 mb-3 mt-3 bg-body rounded row gx-3 mx-1">
                            <div class="col">
                                <span class="fs-1 mb-3">Products</span>
                            </div>
                            <table class="table table-responsive table-hover">
                                <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Price</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($products != null){
                                    $productCount = 0;
                                    foreach ($products as $product){
                                        $price = number_format((float)$product["PRODUCT_PRICE"], 2, ".", ",");
                                        echo "
                                            <tr class='align-middle'>
                                                <th scope='row'>{$product["PRODUCT_ID"]}</th>
                                                <td><img class='img-fluid w-100' src='{$product["PRODUCT_IMAGE"]}' style='max-width: 200px;'></td>
                                                <td>{$product["PRODUCT_NAME"]}</td>
                                                <td>RM{$price}</td>
                                                <td class='text-center'>
                                                    <a type='button' class='btn btn-outline-primary' href='{$base_url}admin/manage-products.php'>
                                                        See More
                                                    </a>                                       
                                                </td>
                                            </tr>";
                                        $productCount++;
                                    }
                                    echo "<script>$('#product-count').text(\"{$productCount}\");</script>";
                                }
                                else {
                                    echo "<tr><td colspan='5' class='text-center'>No products found</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-5">
                            <span class="h3"><span id="user-count">0</span> users found</span>
                        </div>
                        <div class="shadow p-3 mb-3 mt-3 bg-body rounded row gx-3 mx-1">
                            <div class="row">
                                <span class="fs-1 mb-3">Employees</span>
                            </div>
                            <div class="row">
                                <span class="h4"><span id="employee-count">0</span> employee found</span>
                            </div>

                            <table class="table table-responsive table-hover">
                                <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone
                                    <th scope="col">Authority</th>
                                    <th scope="col">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php

                                if ($employees != null){
                                    $employeeCount = 0;
                                    foreach ($employees as $employee){
                                        $fullName = $employee["FIRST_NAME"] . " " . $employee["LAST_NAME"];

                                        $email = $employee["EMAIL"] ?? "-";
                                        $phone = $employee["PHONE"] ?? "-";

                                        $authority = $employee["AUTHORITY_LEVEL"] ?? 0;
                                        $authority_badge = getAuthorityBadge($authority);

                                        $count++;
                                        $employeeCount++;
                                        echo
                                        "<tr class='align-middle'>
                                            <th scope='row'>$employeeCount</th>
                                            <td>{$fullName}</td>
                                            <td>{$employee["USERNAME"]}</td>
                                            <td>{$employee["EMAIL"]}</td>
                                            <td>{$phone}</td>
                                            <td>{$authority_badge}</td>
                                            <td class='text-center'>
                                                <a type='button' class='btn btn-outline-primary' href='{$base_url}admin/manage-users.php'>
                                                    See More
                                                </a>                                       
                                            </td>
                                        </tr>";

                                    }
                                    echo "<script>$('#employee-count').text(\"{$employeeCount}\");</script>";
                                }
                                else {
                                    echo "<tr><td colspan='8' class='text-center'>No employee found</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="shadow p-3 mb-3 mt-3 bg-body rounded row gx-3 mx-1">
                            <div class="row">
                                <span class="fs-1 mb-3">Members</span>
                            </div>
                            <div class="row">
                                <span class="h4"><span id="member-count">0</span> member found</span>
                            </div>

                            <table class="table table-responsive table-hover">
                                <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($members != null){
                                    $memberCount = 0;
                                    foreach ($members as $member){
                                        $fullName = $member["FIRST_NAME"] . " " . $member["LAST_NAME"];

                                        $address = ($member["ADDRESS"] ?? "") . ", " . ($member["POSTCODE"] ?? "")
                                            . ", " . ($member["CITY"] ?? "") . ", " . ($member["STATE"] ?? "");
                                        $phone = $member["PHONE"] ?? "-";

                                        $count++;
                                        $memberCount++;
                                        echo
                                        "<tr class='align-middle'>
                                            <th scope='row'>$memberCount</th>
                                            <td>{$fullName}</td>
                                            <td>{$address}</td>
                                            <td>{$member["EMAIL"]}</td>
                                            <td>{$phone}</td>
                                            <td class='text-center'>
                                                <a type='button' class='btn btn-outline-primary' href='{$base_url}admin/manage-users.php'>
                                                    See More
                                                </a>                                       
                                            </td>
                                        </tr>";

                                    }
                                    echo "<script>$('#member-count').text(\"{$memberCount}\");</script>";
                                }
                                else {
                                    echo "<tr><td colspan='8' class='text-center'>No member found</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <?php footer(); ?>
        </main>

    </div>
</div>
<?php body_script_tag_content();?>
<?= "<script>$('#user-count').text(\"{$count}\");</script>" ?>
</body>

</html>