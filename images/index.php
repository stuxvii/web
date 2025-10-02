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
                echo "<style>body{animation-name: none;}</style>";
            }
        ?>
    </head>
    <body>
    <div class="content">
        <div class="diva">
            snooPINGAS usual, i see????
            <a href="/">Go back</a> to the homepage now, you worm.
        </div>
        <div class="rite">
        </div>
    </div>
    <script src="../titleanim.min.js"></script>
    </body>
</html>