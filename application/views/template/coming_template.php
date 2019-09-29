<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
} ?>
<!DOCTYPE HTML>
<html lang="en">

<head>
    <title><?= $this->title ?></title>
    <meta name="description" content="<?= $this->description ?>">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link rel="icon" href="<?= FILESSTATIC ?>/img/milaciky_fav.ico" type="image/png">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700%7CPoppins:400,500" rel="stylesheet">

    <link href="<?= FILESSTATIC ?>/coming/common-css/bootstrap.css" rel="stylesheet">


    <link href="<?= FILESSTATIC ?>/coming/common-css/ionicons.css" rel="stylesheet">


    <link rel="stylesheet" href="<?= FILESSTATIC ?>/coming/common-css/jquery.classycountdown.css" />

    <link href="<?= FILESSTATIC ?>/coming/css/styles.css" rel="stylesheet">

    <link href="<?= FILESSTATIC ?>/coming/css/responsive.css" rel="stylesheet">

</head>

<body>

    <div class="main-area">
        <div class="container full-height position-static">

            <section class="left-section full-height">

                <a class="logo" href="/"><img src="<?= FILESSTATIC ?>/img/logo.png" alt="<?= SITE_NAME ?>" title="<?= SITE_NAME ?>"></a>

                <div class="display-table">
                    <div class="display-table-cell">
                        <div class="main-content">
                            <h1 class="title"><b>Už čoskoro</b></h1>
                            <p>Pre Vás pripravujeme zaujímavé články o vašich domácich miláčikoch.</p><br />
                            <p>Všetko, čo ste nemohli vedieť: anatómia, plemená, ako si vybrať zvieraťa, ako sa starať a vzdelávať ho, aké choroby sú, ako kŕmiť a oveľa viac - to všetko sa zhromažďuje na portáli <?= SITE_NAME ?></p>

                            <div class="email-input-area">
                                <form>
                                    <input class="email-input" name="email" type="text" placeholder="Zadajte svoj e-mail" />
                                    <button class="submit-btn" name="submit" type="button"><b>OZNÁMTE MI</b></button>
                                </form>
                            </div><!-- email-input-area -->
                            <p class="info_smg"></p>
                            <p class="post-desc">Zaregistrujte sa teraz a získajte včas informácie o našom dátume spustenia!</p>
                            <p class="post-desc">A nebojte sa, nenávidíme aj spam! Odber môžete kedykoľvek zrušiť.</p>
                        </div><!-- main-content -->


                    </div><!-- display-table-cell -->
                </div><!-- display-table -->

                <ul class="footer-icons">
                    <li>Zostaňte v kontakte : </li>
                    <li><a href="https://www.facebook.com/Miláčiky-930457157163114/"><i class="ion-social-facebook"></i></a></li>
                    <!--<li><a href="#"><i class="ion-social-twitter"></i></a></li>-->
                    <!--<li><a href="#"><i class="ion-social-googleplus"></i></a></li>-->
                    <li><a href="https://www.instagram.com/milaciky/"><i class="ion-social-instagram-outline"></i></a></li>
                    <!--<li><a href="#"><i class="ion-social-pinterest"></i></a></li>-->
                </ul>
            </section><!-- left-section -->

            <section class="right-section" style="background-image: url(<?= FILESSTATIC ?>/coming/images/bg_cat.jpg)">

                <div class="display-table center-text">
                    <div class="display-table-cell">


                        <div id="rounded-countdown">
                            <div class="countdown" data-remaining-sec="<?= abs(time() - strtotime(COMING_END)) ?>"></div>
                        </div>

                    </div><!-- display-table-cell -->
                </div><!-- display-table -->

            </section><!-- right-section -->

        </div><!-- container -->
    </div><!-- main-area -->

    <!-- SCIPTS -->

    <script src="<?= FILESSTATIC ?>/coming/common-js/jquery-3.1.1.min.js"></script>
    <script src="<?= FILESSTATIC ?>/coming/common-js/tether.min.js"></script>
    <script src="<?= FILESSTATIC ?>/coming/common-js/bootstrap.js"></script>
    <script src="<?= FILESSTATIC ?>/coming/common-js/jquery.classycountdown.js"></script>
    <script src="<?= FILESSTATIC ?>/coming/common-js/jquery.knob.js"></script>
    <script src="<?= FILESSTATIC ?>/coming/common-js/jquery.throttle.js"></script>
    <script src="<?= FILESSTATIC ?>/coming/common-js/scripts.js"></script>
</body>

</html>