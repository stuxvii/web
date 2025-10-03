<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../normalize.css">
        <link rel="stylesheet" href="../styles.css">
        <?php
            if ($theme) {
                echo "<style>:root{--primary-color: #fff;--secondary-color: #000;--bgimg: url(\"cargonetlight.bmp\");}</style>";
            }
            if (!$movebg) {
                echo "<style>body{animation: none;}</style>";
            }
        ?>
    </head>
    <body>
    <div class="content">
        <?php 
        global $sidebarid;
        global $sidebars;
        require "sidebars.php";
        ?>
        <div class="diva">
        <a href="/">Home</a>
        <br>
            <div class="border">
                You have no items.
            </div>
        </div>
        <?php
        global $sidebarid;
        global $sidebars;
        $rightside = true;
        require "sidebars.php";
        ?>
    </div>
    <script src="../titleanim.min.js"></script>
    </body>
</html>