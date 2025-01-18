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
                    <!-- Maybe traffic and bookings here? todo -->
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
                    <!-- Maybe traffic here? todo -->
                </div>
            </div>


            <?php footer(); ?>
        </main>

    </div>
</div>
<?php body_script_tag_content();?>
</body>

</html>