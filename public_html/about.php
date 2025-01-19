<?php
session_start();
require("../includes/functions.inc.php");

displayToast();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php head_tag_content(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/about-us.css">
    <title><?= WEBSITE_NAME ?>| About Us</title>
</head>
<body>
<?php nav_menu(); ?>
<section id="billboard" class="position-relative overflow-hidden bg-body">
</section>

<div class="container mt-5 py-5">
    <div class="row pt-5">
        <h1>About <?= WEBSITE_NAME ?></h1>
        <p class="text-body-secondary">Chipzzia is a premier Malaysian producer of traditional Malay snacks, specializing in ‘kerepek’ (crispy fried chips).
            Founded with a vision to bring the unique flavors of traditional Malay snacks to households and businesses around the world, Chipzzia is dedicated to supporting and uplifting the local food industry.
            In addition to production, Chipzzia actively partners with SMEs and local entrepreneurs, offering resources and guidance to help them thrive in a competitive market. The company’s mission is to maximize profitability while consistently delivering high-quality, authentic products and strengthening its support for local enterprises.
            The goal is to establish Chipzzia as a widely recognized brand, beloved across diverse communities and continuously expanding its reach at both domestic and international levels.
        </p>
    </div>
</div>
<div class=" bg-white">
    <div class="container mt-5 py-5">
        <div class="">
            <h1>Group Members</h1>
            <div class="row">
                <div class="column">
                    <div class="our-team">
                        <div class="picture">
                            <img class="img-fluid" src="<?= BASE_URL ?>assets/images/me.jpg">
                        </div>
                        <div style="width:100%">
                            <h3>ABDUL WAFI <br>BIN CHE<br> AB. RAHIM</h3>
                            <h4 class="title">Project Manager</h4>
                            <span>2024699546</span><br/>
                            <span>wafithird@gmail.com</span>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="our-team">
                        <div class="picture">
                            <img class="img-fluid" src="<?= BASE_URL ?>assets/images/anis.png">
                        </div>
                        <div class="team-content">
                            <h3 class="name">NUR ANIS <br>SYUHADA BINTI <br>MOHD NAIM</h3>
                            <h4 class="title">Marketing Director</h4>
                            <span>2024663216</span><br/>
                            <span>email</span>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="our-team">
                        <div class="picture">
                            <img class="img-fluid" src="<?= BASE_URL ?>assets/images/elisa.png">
                        </div>
                        <div class="team-content">
                            <h3 class="name">NUR ELISA <br>AISYA <br>BT MUSTAPAH</h3>
                            <h4 class="title">Developer</h4>
                            <span>2024860064</span><br/>
                            <span>email</span>

                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="our-team">
                        <div class="picture">
                            <img class="img-fluid" src="<?= BASE_URL ?>assets/images/elliya.png">
                        </div>
                        <div class="team-content">
                            <h3 class="name">NUR ELLIYA <br>MASLINA <br>BINTI MAZLI </h3>
                            <h4 class="title">Designer</h4>
                            <span>2024260312</span><br/>
                            <span>nurelliya25@gmail.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php footer(); ?>


<?php body_script_tag_content(); ?>
</body>
</html>

