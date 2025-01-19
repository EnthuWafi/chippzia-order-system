<?php

require("../../includes/functions.inc.php");

session_start();

member_login_required();

displayToast();

$user = retrieveMember($_SESSION["user_data"]["CUSTOMER_ID"]);

$name = ($user["FIRST_NAME"] ?? "") . " " . ($user["LAST_NAME"] ?? "");
$today = date_create();
$date = date_format($today, "D, d M Y");

$ordersCount = retrieveCustomerOrderCount($user["CUSTOMER_ID"])["COUNT"];
$totalSpend = retrieveCustomerTotalSpend($user["CUSTOMER_ID"])["SUM"];
$ordersLineSum = retrieveCustomerOrderLineSumQuantity($user["CUSTOMER_ID"])["SUM"] ?? 0;

$orders = retrieveAllCustomerOrders($user["CUSTOMER_ID"], 5);
$totalSpendDecimal = number_format((float)$totalSpend, 2, ".", ",");


?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?> | User Dashboard</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php header_bar("Dashboard") ?>

            <!-- todo DASHBOARD here  -->
            <div class="container">
                <div class="row mt-4 gx-4 ms-3">
                    <div class="row">
                        <span class="h3">Hello there, <?= $name ?? "-" ?></span>
                        <span class="lead">Today is <?= $date ?></span>
                    </div>

                </div>
                <div class="row mt-3 ms-3 mb-3">
                    <!-- ORDERS-->
                    <div class="col">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3 h-100">
                            <div class="col">
                                <div class="row">
                                    <span class="fs-2"><?= $ordersCount; ?></span>
                                </div>
                                <div class="row">
                                    <span class="text-muted">Orders</span>
                                </div>
                            </div>
                            <div class="col text-end">
                                <i class="bi bi-people-fill icon-yellow-dark h2"></i>
                            </div>
                        </div>
                    </div>
                    <!-- ORDERS LINE-->
                    <div class="col">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3 h-100">
                            <div class="col">
                                <div class="row">
                                    <span class="fs-2"><?= $ordersLineSum; ?></span>
                                </div>
                                <div class="row">
                                    <span class="text-muted">Products Ordered</span>
                                </div>
                            </div>
                            <div class="col text-end">
                                <i class="bi bi-people-fill icon-yellow-dark h2"></i>
                            </div>
                        </div>
                    </div>
                    <!-- TOTAL SPENT-->
                    <div class="col align-items-end">
                        <div class="shadow p-3 gradient-primary rounded row gx-3 h-100">
                            <div class="col">
                                <div class="row">
                                    <span class="fs-2 text-white">RM<?= $totalSpendDecimal ?></span>
                                </div>
                                <div class="row">
                                    <span class="text-white">Total Spent</span>
                                </div>
                            </div>
                            <div class="col text-end">
                                <i class="bi bi-people-fill h2 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row mt-1 gx-4 ms-3">
                    <div class="col">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3">
                            <div class="row mb-3">
                                <div class="col">
                                    <span class="h3">Recent Orders</span>
                                </div>
                                <div class="col text-end">
                                    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>account/orders.php">See more..</a>
                                </div>
                            </div>
                            <div class="row">
                                <?php
                                if ($orders != null){
                                    orders_memberOrders($orders);
                                }
                                else {
                                    echo "<span class='fs-4'>No orders yet.</span>";
                                }
                                ?>
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
</body>

</html>