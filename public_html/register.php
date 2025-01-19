<?php
session_start();
require("../includes/functions.inc.php");

employee_forbidden();
member_forbidden();

// check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //This function will specifically create members
    $postedToken = $_POST["token"];
    try{
        if(!empty($postedToken)){
            if(isTokenValid($postedToken)){
                $firstname = htmlspecialchars($_POST["fname"]);
                $lastname = htmlspecialchars($_POST["lname"]);
                $email = filter_var($_POST["email"], FILTER_SANITIZE_SPECIAL_CHARS);
                $username = filter_var($_POST["username"], FILTER_SANITIZE_SPECIAL_CHARS);
                $password = filter_var($_POST["password"], FILTER_SANITIZE_SPECIAL_CHARS);

//                //check if exists
//                $user = checkUser($username, $email);
//                if (!$user) {
//                    if (createUser($firstname, $lastname, $username, $password, $email, "customer")){
//                        makeToast("info", "Success", "Account successfully created!");
//                    }
//                }
                $member = checkMember($username, $email);
                if(!$member){
                    if (createMember($firstname, $lastname, $email, null, null, null, null, $username, $password)){
                        makeToast("info", "Success", "Account successfully created!");
                    }
                }
                else {
                    throw new exception("Another account with the same username or email exists!");
                }
            }
            else{
                makeToast("warning", "Please refrain from attempting to resubmit previous form", "Warning");
            }
        }
        else {
            throw new exception("Token not found");
        }
    }
    catch (exception $e){
        makeToast("error", $e->getMessage(), "Error");
    }

    header("Location: ". BASE_URL . "register.php");
    die();

}

displayToast();

$token = getToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php head_tag_content(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/register.css">
    <title><?= WEBSITE_NAME ?> - Member Registration </title>
</head>
<body>
<?php nav_menu(); ?>
<section id="billboard" class="position-relative overflow-hidden bg-body">
</section>
<div class="container-fluid my-5 py-5">

    <form action="<?php current_page(); ?>" id="form" method="post">
        <div class="container">
            <h1>Member Registration</h1>
            <p>Register to our store and become a member today!</p>
            <hr>

            <!-- Input untuk First Name dan Last Name Sebelah-Sebelah -->
            <div class="input-row">
                <div>
                    <label for="firstName"><b>First Name</b></label>
                    <input type="text" placeholder="Enter Your First Name" name="fname" required>
                </div>
                <div>
                    <label for="lastName"><b>Last Name</b></label>
                    <input type="text" placeholder="Enter Your Last Name" name="lname" required>
                </div>
            </div>

            <label for="username"><b>Username</b></label>
            <input type="text" placeholder="Enter Your Username" name="username" required>

            <label for="email"><b>Email</b></label>
            <input type="text" placeholder="Enter Your Email" name="email" required>

            <label for="psw"><b>Password</b></label>
            <div style="position: relative; width: 100%;">
                <input type="password" placeholder name="psw" id="password" required>
                <span class="toggle-password" onclick="togglePassword('password')">Show</span>
            </div>

            <label for="psw-repeat"><b>Repeat Password</b></label>
            <div style="position: relative; width: 100%;">
                <input type="password" placeholder name="psw-repeat" id="password-repeat" required>
                <span class="toggle-password" onclick="togglePassword('password-repeat')">Show</span>
            </div>

            <label>
                <input type="checkbox" checked="checked" name="remember"> Remember me
            </label>

            <div class="clearfix">
                <button type="submit" class="signupbtn">Create Account</button>
                <button type="button" class="cancelbtn" onclick="document.getElementById('form').reset();">Reset</button>
            </div>
        </div>
    </form>
    <?php footer(); ?>
</div>
<?php body_script_tag_content(); ?>
<script type="text/javascript" src="assets/js/register.js"></script>
</body>
</html>

