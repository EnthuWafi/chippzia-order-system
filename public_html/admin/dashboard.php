<?php

require("../../includes/functions.inc.php");

session_start();

employee_login_required();

$employeeCount = retrieveCountEmployees()["COUNT"] ?? 0;
$memberCount = retrieveCountMembers()["COUNT"] ?? 0;

$userCount = $employeeCount + $memberCount;
$productCount = retrieveProductCount()["COUNT"] ?? 0;
$ordersCount = retrieveOrderCount()["COUNT"] ?? 0;
$income = retrieveTotalIncome()["SUM"] ?? 0;

$incomeDecimal =  number_format((float)$income, 2, '.', '');

$productBought = retrieveAllProductBought()["SUM"] ?? 0;
$orders = retrieveAllOrders5LIMIT();

$subordinates = getImmediateSubordinates($_SESSION["user_data"]["EMPLOYEE_ID"]);

$name = ($_SESSION["user_data"]["FIRST_NAME"] ?? "") . " " . ($_SESSION["user_data"]["LAST_NAME"] ?? "");
$today = date_create();
$date = date_format($today, "D, d M Y");

$manager = retrieveEmployee($_SESSION["user_data"]["MANAGER_ID"]);
$authorityBadge = getAuthorityBadge($_SESSION["user_data"]["AUTHORITY_LEVEL"]);

displayToast();
?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?> | Admin Dashboard</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php admin_side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php admin_header_bar("Dashboard") ?>

            <!-- todo DASHBOARD here  -->
            <div class="container">
                <div class="row mt-4 gx-4 ms-3">
                    <div class="col-auto">
                        <span class="h3">Hello there, <?= $name ?? "-" ?></span><br>
                        <span class="lead">Today is <?= $date ?></span><br>
                        <?php if (isset($manager)): ?>
                        <span class="lead">Your manager is <?php echo ($manager["FIRST_NAME"] ?? "") . " " . ($manager["LAST_NAME"] ?? "") ?> </span>
                        <?php else: ?>
                        <span class="lead">You do not have a manager </span>
                        <?php endif; ?>
                    </div>
                    <div class="col text-end">
                        <?= $authorityBadge ?>
                    </div>
                </div>
                <div class="row mt-4 gx-4 ms-3">
                    <!-- USER COUNT -->
                    <div class="col">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3 h-75">
                            <div class="col">
                                <div class="row">
                                    <span class="fs-2"><?= $userCount; ?></span>
                                </div>
                                <div class="row">
                                    <span class="text-muted">Users</span>
                                </div>
                            </div>
                            <div class="col text-end">
                                <i class="bi bi-people-fill icon-yellow-dark h2"></i>
                            </div>
                        </div>
                    </div>

                    <!-- ORDERS COUNT -->
                    <div class="col">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3 h-75">
                            <div class="col">
                                <div class="row">
                                    <span class="fs-2"><?= $ordersCount; ?></span>
                                </div>
                                <div class="row">
                                    <span class="text-muted">Orders</span>
                                </div>
                            </div>
                            <div class="col text-end">
                                <i class="bi bi-cart-fill icon-yellow-dark h2"></i>
                            </div>
                        </div>
                    </div>

                    <!-- PRODUCT COUNT -->
                    <div class="col">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3 h-75">
                            <div class="col">
                                <div class="row">
                                    <span class="fs-2"><?= $productCount; ?></span>
                                </div>
                                <div class="row">
                                    <span class="text-muted">Products</span>
                                </div>
                            </div>
                            <div class="col text-end">
                                <i class="bi bi-box-seam-fill icon-yellow-dark h2"></i>
                            </div>
                        </div>
                    </div>

                    <!-- PRODUCT COUNT COUNT -->
                    <div class="col">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3 h-75">
                            <div class="col">
                                <div class="row">
                                    <span class="fs-2"><?= $productBought; ?></span>
                                </div>
                                <div class="row">
                                    <span class="text-muted">Items Sold</span>
                                </div>
                            </div>
                            <div class="col text-end">
                                <i class="bi bi-basket-fill icon-yellow-dark h2"></i>
                            </div>
                        </div>
                    </div>

                    <!-- INCOME -->
                    <div class="col-sm-auto col-md-3">
                        <div class="shadow p-3 mb-5 gradient-primary rounded row gx-3 h-75">
                            <div class="col">
                                <div class="row">
                                    <span class="fs-2 text-white">RM<?= $incomeDecimal; ?></span>
                                </div>
                                <div class="row">
                                    <span class="text-white">Income</span>
                                </div>
                            </div>
                            <div class="col text-end">
                                <i class="bi bi-cash-coin icon-white h2"></i>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row mt-1 gx-4 ms-3">
                    <div class="col">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3">
                            <div class="row mb-3">
                                <span class="h3">Recent Orders</span>
                            </div>
                            <div class="row align-items-center">
                                <?php
                                orders_adminOrdersLite($orders);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-1 gx-4 ms-3">

                    <div class="shadow p-3 mb-5 bg-body rounded">
                        <h4 class="mb-3">Subordinates</h4>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($subordinates)): ?>
                                <?php foreach ($subordinates as $subordinate): ?>
                                    <tr>
                                        <td><?= $subordinate["FIRST_NAME"] . " " . $subordinate["LAST_NAME"] ?></td>
                                        <td>
                                            <a href="mailto:<?= $subordinate["EMAIL"] ?>" class="text-decoration-none">
                                                <i class="bi bi-envelope me-2"></i><?= $subordinate["EMAIL"] ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $subordinate["PHONE"]) ?>"
                                               class="text-decoration-none"
                                               target="_blank">
                                                <i class="bi bi-whatsapp me-2 text-success"></i>Chat on WhatsApp
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No subordinates assigned to this employee.</td>
                                </tr>
                            <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <?php footer(); ?>
        </main>

    </div>
</div>
<?php body_script_tag_content();?>
</body>

</html>