<?php

session_start();

require("../includes/functions.inc.php");

employee_forbidden();

displayToast();
?>

<!DOCTYPE html>
<html>
  <head>
    <title><?= WEBSITE_NAME ?></title>
    <?php head_tag_content(); ?>
  </head>
  <body data-bs-spy="scroll" data-bs-target="#navbar" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true" tabindex="0" bgcolor=#FF682D>

    <?php nav_menu() ?>

    <section id="billboard" class="position-relative overflow-hidden bg-body">
        <div class="swiper main-swiper">
            <div class="swiper-wrapper">
                <!-- First Slide -->
                <div class="swiper-slide">
                    <div class="container">
                        <div class="row d-flex align-items-center">
                            <div class="col-md-6">
                                <div class="banner-content">
                                    <h1 class="display-2 text-uppercase text-dark pb-5">Greatest Kerepek in History.</h1>
                                    <a href="account/shop.php" class="btn btn-medium btn-dark text-uppercase btn-rounded-none">Shop Product</a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <a class="image-holder" href="<?= BASE_URL ?>">
                                    <img src="<?= BASE_URL ?>assets/images/banner-image.png" alt="banner" width="600" height="600">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Second Slide -->
                <div class="swiper-slide">
                    <div class="container">
                        <div class="row d-flex align-items-center">
                            <div class="col-md-6">
                                <div class="banner-content">
                                    <h1 class="display-2 text-uppercase text-dark pb-5">The only Kerepek that valid!</h1>
                                    <a href="<?= BASE_URL ?>account/shop.php" class="btn btn-medium btn-dark text-uppercase btn-rounded-none">Shop Product</a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <a class="image-holder" href="<?= BASE_URL ?>">
                                    <img src="assets/images/banner-image.png" alt="banner" width="600" height="600">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-icon swiper-arrow swiper-arrow-prev">
            <svg class="chevron-left">
                <use xlink:href="#chevron-left" />
            </svg>
        </div>
        <div class="swiper-icon swiper-arrow swiper-arrow-next">
            <svg class="chevron-right">
                <use xlink:href="#chevron-right" />
            </svg>
        </div>
    </section>

    <section id="company-services" class="padding-large">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-6 pb-3">
            <div class="icon-box d-flex">
              <div class="icon-box-icon pe-3 pb-3">
                <svg class="cart-outline">
                  <use xlink:href="#cart-outline" />
                </svg>
              </div>
              <div class="icon-box-content">
                <h3 class="card-title text-uppercase text-dark">Cheap delivery</h3>
                <p>Where ever you live, we will reach you. Of course at an affordable price!.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 pb-3">
            <div class="icon-box d-flex">
              <div class="icon-box-icon pe-3 pb-3">
                <svg class="quality">
                  <use xlink:href="#quality" />
                </svg>
              </div>
              <div class="icon-box-content">
                <h3 class="card-title text-uppercase text-dark">Quality guarantee</h3>
                <p>All of our products using high quality ingredients.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 pb-3">
            <div class="icon-box d-flex">
              <div class="icon-box-icon pe-3 pb-3">
                <svg class="price-tag">
                  <use xlink:href="#price-tag" />
                </svg>
              </div>
              <div class="icon-box-content">
                <h3 class="card-title text-uppercase text-dark">Daily offers</h3>
                <p>We always giving best prices everyday.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 pb-3">
            <div class="icon-box d-flex">
              <div class="icon-box-icon pe-3 pb-3">
                <svg class="shield-plus">
                  <use xlink:href="#shield-plus" />
                </svg>
              </div>
              <div class="icon-box-content">
                <h3 class="card-title text-uppercase text-dark">100% secure payment</h3>
                <p>Easy to pay, only with us!</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section id="mobile-products" class="product-store position-relative padding-large no-padding-top">
        <div class="container">
            <div class="row">
                <div class="display-header d-flex justify-content-between pb-3">
                    <h2 class="display-7 text-dark text-uppercase">Our Special kerepek</h2>
                    <div class="btn-right">
                        <a href="<?= BASE_URL ?>account/shop.php" class="btn btn-medium btn-normal text-uppercase">Go to Shop</a>
                    </div>
                </div>
                <div class="swiper product-swiper">
                    <div class="swiper-wrapper">
                        <?php
                        $products = retrieveAllProduct();
                        if (isset($products)) {
                            foreach ($products as $product) {
                                echo '
                                <div class="swiper-slide">
                                    <div class="product-card position-relative">
                                        <div class="image-holder">
                                            <img src="'.$product["PRODUCT_IMAGE"].'" alt="product-item" class="img-fluid">
                                        </div>
                                        <div class="cart-concern position-absolute">
                                            <div class="cart-button d-flex">
                                                <a href="'. BASE_URL .'account/shop.php" class="btn btn-medium btn-black">Add to Cart<svg class="cart-outline"><use xlink:href="#cart-outline"></use></svg></a>
                                            </div>
                                        </div>
                                        <div class="card-detail d-flex justify-content-between align-items-baseline pt-3">
                                            <h3 class="card-title text-uppercase">
                                                <a href="#">'.$product["PRODUCT_NAME"].'</a>
                                            </h3>
                                            <span class="item-price text-primary">'.$product["PRODUCT_PRICE"].'</span></div>
                                    </div>
                                </div>';
                            }
                        }
                        else {
                            echo "No products yet.";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-pagination position-absolute text-center"></div>
    </section>
    <section id="yearly-sale" class="bg-light-blue overflow-hidden mt-5 padding-xlarge"
             style="background-image: url('assets/images/kerepek-bawang-sale.png');background-position: center; background-repeat: no-repeat;">
      <div class="row d-flex flex-wrap align-items-center">
        <div class="col-md-6 col-sm-12">
          <div class="text-content offset-4 padding-medium">
            <h3>Want Cheaper Chips?</h3>
            <h2 class="display-2 pb-5 text-uppercase text-dark">Become a Member!</h2>
            <a href="<?= BASE_URL ?>account/shop.php" class="btn btn-medium btn-dark text-uppercase btn-rounded-none">Shop Sale</a>
          </div>
        </div>
      </div>
    </section>


    <?php footer() ?>
    <?php body_script_tag_content(); ?>
    <script type="text/javascript" src="assets/js/plugins.js"></script>
    <script type="text/javascript" src="assets/js/script.js"></script>
  </body>
</html>