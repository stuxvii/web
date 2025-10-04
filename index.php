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
                #echo "<style>body{animation-name: none;}</style>";
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
                echo "<span>Hey there, " . $name . ".</span>";
                echo "<span>₱" . $money . "</span>";
                if ($dispchar === 2) {echo "<img height='240px' class='jump' id='render' src='renders/$uid" . ".png'>";
                } else { if ($dispchar === 1) {echo "
                 <div class=\"border\" id=\"char\">
                <span class=\"bodypart\" id=\"head\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\">
                   <img src=\"images/epicface.png\" width='56' height='56'>
                </span>
                 <div class=\"horiz\">
                <span class=\"bodypart limb\" id=\"larm\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\"></span>
                <span class=\"bodypart\" id=\"trso\" color=\"23\" style=\"background-color: rgb(13, 105, 172);\"></span>
                <span class=\"bodypart limb\" id=\"rarm\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\"></span>
                </div>
                 <div class=\"horiz\">
                <span class=\"bodypart limb\" id=\"lleg\" color=\"301\" style=\"background-color: rgb(80, 109, 84);\"></span>
                <span class=\"bodypart limb\" id=\"rleg\" color=\"301\" style=\"background-color: rgb(80, 109, 84);\"></span>
                </div>
            </div>";}}
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
                echo "<a href=\"character\">Character customization</a>";
                echo "<a href=\"https://discord.gg/7JwYGHAvJV\">Official Discord server</a>";
                echo "<a href=\"uploadui\">Upload asset</a>";
                if ($opperms) {
                    echo "<a href=\"admin/\">Admin panel</a>";
                }
                echo "<a href=\"inventory\">Inventory</a>";
                echo "<a href=\"config\">Settings</a>";
                echo "<a href=\"logout\">log out</a>";
            }
            ?>
        </div>
        </div>
        <?php 
        $rightside = true;
        require "sidebars.php";
        if ($dispchar === 1) {
            require_once 'brickcolor.php';
            $bpdata = [];
            $bodyparts_map = [
                "head" => $head,
                "trso" => $trso,
                "larm" => $larm,
                "rarm" => $rarm,
                "lleg" => $lleg,
                "rleg" => $rleg
            ];
            foreach ($bodyparts_map as $part_id => $sql_color_id) {
                $color_id = $sql_color_id;
                $hex = array_search((int)$color_id, $brickcolor);
                if ($hex) {
                    $bpdata[] = [
                        'id' => $part_id,
                        'color_id' => $color_id,
                        'hex' => $hex
                    ];
                }
            }
            $newjson = json_encode($bpdata);
            echo "<script>
document.addEventListener(\"DOMContentLoaded\", e => {
    let t = $newjson;
    t.forEach(e => {
        let n = document.getElementById(e.id);
        if (n) {
            // Set the visual color style
            n.style.backgroundColor = \"#\" + e.hex; 
            // Set the 'color' attribute for the save logic
            n.setAttribute('color', e.color_id); 
        }
    });
});
</script>";}
        ?>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>