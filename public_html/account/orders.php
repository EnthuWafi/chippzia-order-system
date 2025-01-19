<?php

require("../../includes/functions.inc.php");

session_start();

member_login_required();

$orders = retrieveAllCustomerOrders($_SESSION["user_data"]["CUSTOMER_ID"]);
$orderCount = retrieveCustomerOrderCount($_SESSION["user_data"]["CUSTOMER_ID"])["COUNT"];

displayToast();
?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?> | Orders</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php header_bar("Orders") ?>

            <!-- todo DASHBOARD here  -->
            <div class="container">
                <div class="row mt-4 gx-4 ms-3">
                    <div class="shadow p-3 mb-5 bg-body rounded row gx-3">
                        <div class="row">
                            <span class="h3"><?= $orderCount ?> order(s)</span>
                        </div>

                        <?php
                        if ($orders != null){
                            orders_memberOrders($orders);
                        }
                        else {
                            echo "<span class='fs-4'>No orders yet.</span>";
                        }
                        ?>

                        ?>
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