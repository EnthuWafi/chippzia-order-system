<?php
session_start();
require("../../includes/functions.inc.php");


member_login_required();

//check if all info are okay
$user = $_SESSION["user_data"];
if (!array_keys_isempty_or_not(["user_address", "user_postcode", "user_city",
    "user_phone", "state_code"], $user)){
    makeToast("info", "You must fill in all the contact details before you are allowed to order!", "Info");
    header("Location: ".BASE_URL."account/profile.php");
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postedToken = $_POST["token"];
    try{
        if(!empty($postedToken)){
            if(isTokenValid($postedToken)){
                //TODO: Change the calculation later (include the option to use loyalty points)
                $user = retrieveMember($_SESSION["user_data"]["CUSTOMER_ID"]);
                $customerID = $user["CUSTOMER_ID"];
                $cart = $_SESSION["cart"];
                $cost = 0;
                foreach ($cart as $item){
                    $quantity = $item["quantity"];
                    $cost += $item["product"]["PRODUCT_PRICE"] * $quantity;
                }
                $totalCost = $cost + 5;
                //process
                $order = createOrderCustomer($totalCost, $customerID, $cart);

                if (isset($order)) {
                    makeToast("success", "Your order has been placed!", "Success");
                    $_SESSION["ORDER_ID"] = $order["ORDER_ID"];
                }
                else {
                    throw new Exception("Something went terribly wrong during the ordering process!<br>Please contact the administrator!");
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
    catch (exception $e){
        makeToast("error", $e->getMessage(), "Error");
    }

    header("Location: ".BASE_URL."order/confirm.php");
    die();
}


displayToast();

try{
    $cart = $_SESSION["cart"] or throw new Exception();
} catch (Exception) {
    makeToast("warning", "You cannot checkout with an empty cart!", "Warning");
    header("Location: ".BASE_URL."index.php");
    die();
}


$token = getToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php head_tag_content(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/progress.css">
    <title><?= WEBSITE_NAME ?> | Shopping Cart</title>
</head>
<style>
	.icon-container {
		margin-bottom: 20px;
		padding: 7px 0;
		font-size: 24px;
	}
</style>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php header_bar("Check-out") ?>

            <!-- todo DASHBOARD here  -->
            <div class="container mt-3">
                <div class="row justify-content-center">
                    <div class="col-lg-8 me-3 mt-3 mb-5">
                        <h2><strong>Complete your order</strong></h2>
                        <p>Fill all form field to go to next step</p>
                        <div class="row">
                            <div class="col-md-12 mx-0">
                                <form id="msform" method="post">
                                    <!-- progressbar -->
                                    <ul id="progressbar">
                                        <li class="active"><strong>Cart</strong></li>
                                        <li class="active"><strong>Checkout</strong></li>
                                        <li><strong>Finish</strong></li>
                                    </ul>
                                    <fieldset>
                                        <div class="row">
                                          <div class="col-75">
                                            <div class="container">
                                              <form>

                                                <div class="row">
                                                  <div class="col-50">
                                                    <h3>Payment Information</h3>
                                                    <label for="fname">Accepted Cards Only!</label>
                                                      <label for="fname">Debit/Credit card required</label>
                                                      .
                                                      <div class="icon-container">
                                                      <i class="fa fa-cc-visa" style="color:navy;"></i>
                                                      <i class="fa fa-cc-amex" style="color:blue;"></i>
                                                      <i class="fa fa-cc-mastercard" style="color:red;"></i>
                                                      <i class="fa fa-cc-discover" style="color:orange;"></i>
                                                    </div>
                                                    <label for="cname">Name on Card</label>
                                                    <input type="text" id="cname" name="cardname" placeholder="John More Doe" required>
                                                    <label for="ccnum">Credit card number</label>
                                                    <input type="text" id="ccnum" name="cardnumber" placeholder="1111-2222-3333-4444" required>
                                                    <label for="expmonth">Exp Month</label>
                                                    <input type="text" id="expmonth" name="expmonth" placeholder="September" required>

                                                    <div class="row">
                                                      <div class="col-50">
                                                        <label for="expyear">Exp Year</label>
                                                        <input type="text" id="expyear" name="expyear" placeholder="2018" required>
                                                      </div>
                                                      <div class="col-50">
                                                        <label for="cvv">CVV</label>
                                                        <input type="text" id="cvv" name="cvv" placeholder="352" required>
                                                      </div>
                                                    </div>
                                                    </div>
                                                </div>
                                              </form>
                                            </div>
                                          </div>
                                        </div>
                                        <input type="hidden" name="token" value="<?= $token ?>">
                                        <input type="submit" class="action-button float-end" value="Proceed"/>
                                    </fieldset>
                                </form>
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