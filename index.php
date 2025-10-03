<?php
require_once 'auth.php';

?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../normalize.css">
        <link rel="stylesheet" href="../styles.css">
        <?php
            if ($authsuccessful) {
            if ($theme) {
                echo "<style>:root{--primary-color: #fff;--secondary-color: #000;--bgimg: url(\"cargonetlight.bmp\");}</style>";
            }
            if (!$movebg) {
                echo "<style>body{animation-name: none;}</style>";
            }}
        ?>
    </head>
    <body>
        <div class="sidebars">
        <?php 
        require "sidebars.php";
        ?>
        <div class="content">
        <div class="btmh">
            <?php
            if ($authsuccessful) {
                echo "<span>Hey there, " . $name . " (@" . $discordtag . ")" . " (UserID: " . $uid . ")" . "</span>";
                echo "<a href=\"character\">character customization</a>";
                if ($dispchar) {echo "<img height='240px' class='jump' id='render' src='renders/$uid" . ".png'>";}
            }
            ?>
        </div>

        <div class="midh">
        <?php 
        if ($authsuccessful) {
            echo "<img class='bounce' src='processing.png' id='speen'>";
        } else {
            echo "<a href=\"login\">Login</a>
            <a href=\"register\">Register</a>";
        }
        ?>
        </div>
        <div class="rite">
            <?php
            if (!empty($token)) {
                echo "<a href=\"https://discord.gg/7JwYGHAvJV\">Official Discord server</a>";
                if ($opperms) {
                    echo "<a href=\"admin/\">Admin panel</a>";
                }
                echo "<a href=\"config\">Settings</a>";
                echo "<a href=\"logout\">log out</a>";
            }
            ?>
        </div>
        </div>
        <?php 
        $rightside = true;
        require "sidebars.php";
        ?>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>