<?php

require("../../includes/functions.inc.php");

session_start();

member_login_required();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postedToken = $_POST["token"];
    try{
        if(!empty($postedToken)){
            if(isTokenValid($postedToken)){
                if (!array_keys_isset_or_not(["address", "postcode", "city", "state", "phone"], $_POST)){
                    throw new Exception("Values not found!");
                }

                $contact = ["address"=>htmlspecialchars($_POST["address"]), "postcode"=>htmlspecialchars($_POST["postcode"]),
                    "city"=>htmlspecialchars($_POST["city"]), "state"=>htmlspecialchars($_POST["state"]),
                    "phone"=>htmlspecialchars($_POST["phone"])];
                $customerID = $_SESSION["user_data"]["CUSTOMER_ID"];

                //TODO: Change this later
                if (updateCustomerContact($customerID, $contact)){
                    $usertype = $_SESSION["user_data"]["user_type"];

                    $_SESSION["user_data"] = retrieveMember($customerID);
                    $_SESSION["user_data"]["user_type"] = $usertype;
                    makeToast('success', "Contact info is successfully updated!", "Success");
                }
                else{
                    throw new Exception("Contact info wasn't able to be updated!");
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

    header("Location: ".BASE_URL."account/profile.php");
    die();
}

displayToast();
$user = retrieveMember($_SESSION["user_data"]["CUSTOMER_ID"]);

//i will use api later
$states = [
        [
            "state_name" => "Perak"
        ]
];
$optionStates = "";

foreach ($states as $state){
    $optionStates .= "<option value='{$state["state_name"]}'>{$state["state_name"]}</option>";
}
$token = getToken();
?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?> | Profile</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php header_bar("Profile") ?>

            <!-- todo DASHBOARD here  -->
            <div class="container">
                <div class="row mt-4 gx-4 ms-3">
                    <div class="col-5">
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3">
                            <span class="fs-2">Contact Update</span>
                            <div class="mt-2">
                                <form method="post" action="<?php current_page(); ?>">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col">
                                                <input type="text" class="form-control" name="address" placeholder="Address" required>
                                            </div>
                                            </div>
                                        <div class="row my-2">
                                            <div class="col">
                                                <input type="text" class="form-control" name="postcode" placeholder="Postcode" required>
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control" name="city" placeholder="City" required>
                                            </div>
                                            <div class="col">
                                                <select name="state" class="form-select" required>
                                                    <?= $optionStates; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col">
                                                <input type="tel" class="form-control" name="phone" placeholder="Phone Number" required>
                                            </div>
                                        </div>
                                        <input type="hidden" name="token" value="<?= $token ?>">
                                        <button type="submit" class="btn btn-secondary mt-3 float-end text-center">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="shadow p-3 bg-body rounded row gx-3">
                            <span class="fs-2">Account Details</span>
                            <div class="col mt-2">
                                <div class="row">
                                    <span class="fw-bold">Username</span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold">First Name</span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold">Last Name</span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold">Email</span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold text-primary">Loyalty Points</span>
                                </div>
                            </div>
                            <div class="col mt-2">
                                <div class="row">
                                    <span class="fw-bold"><?= $user["USERNAME"]  ?? "-" ?></span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold"><?= $user["FIRST_NAME"]  ?? "-" ?></span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold"><?= $user["LAST_NAME"]  ?? "-" ?></span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold"><?= $user["EMAIL"]  ?? "-" ?></span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold text-primary"><?= $user["LOYALTY_POINTS"]  ?? "0" ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="shadow p-3 mt-4 mb-4 bg-body rounded row gx-3">
                            <span class="fs-2">Contact Details</span>
                            <div class="col mt-2">
                                <div class="row">
                                    <span class="fw-bold">Address</span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold">Postcode</span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold">City</span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold">State</span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold">Phone Number</span>
                                </div>

                            </div>
                            <div class="col mt-2">
                                <div class="row">
                                    <span class="fw-bold"><?= $user["ADDRESS"] ?? "-" ?></span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold"><?= $user["POSTCODE"]  ?? "-"  ?></span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold"><?= $user["CITY"]  ?? "-" ?></span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold"><?= $user["STATE"] ?? "-"  ?></span>
                                </div>
                                <div class="row">
                                    <span class="fw-bold"><?= $user["PHONE"]  ?? "-" ?></span>
                                </div>

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