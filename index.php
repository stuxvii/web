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
                echo "<style>body{animation-name: none;}</style>";
            }
        ?>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Orbitron:wght@400..900&family=Rajdhani:wght@300;400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap'); 
            .mwrtng {
                font-family: 'Great Vibes', cursive;
            }
        </style>
    </head>
    <body>
        <?php 
        require_once "sidebars.php";
        ?>
    <div>
        <div class="diva">
        <?php 
        if ($authsuccessful) {
            echo "<a href=\"https://windows93.net/c/programs/acidBox93/\">acidBox93 (LOUD AUDIO)</a>
            <a href=\"character\">character customization</a>
            <a href=\"mwrtng/\" class=\"mwrtng\">mewity rating</a>";
            if ($dispchar) {
                require 'brickcolor.php';

                $stmt = $db->prepare("SELECT head, torso, leftarm, rightarm, leftleg, rightleg FROM avatars WHERE id = :uid");
                $stmt->bindValue(':uid', $uid, SQLITE3_INTEGER);
                $result = $stmt->execute();
                $colorrow = $result ? $result->fetchArray(SQLITE3_ASSOC) : false; // source:http://genius.com/Ayesha-erotica-emo-boy-lyrics

                if ($result) {
                    $bpdata = [];
                    $bodyparts = [
                        "head" => $colorrow['head'],
                        "trso" => $colorrow['torso'],
                        "larm" => $colorrow['leftarm'],
                        "rarm" => $colorrow['rightarm'],
                        "lleg" => $colorrow['leftleg'],
                        "rleg" => $colorrow['rightleg']
                    ];
                    foreach ($bodyparts as $part => $sqlvalue) {
                        $color = $sqlvalue;
                        $hex = array_search($color, $brickcolor);
                        $bpdata[] = [
                            'id' => $part,
                            'hex' => $hex
                        ];
                    }
                    $newjson = json_encode($bpdata);

                    echo "<script>document.addEventListener(\"DOMContentLoaded\",e=>{let t=$newjson;t.forEach(e=>{let t=e.id,d=\"#\"+e.hex,n=document.getElementById(t);n&&(n.style.backgroundColor=d)})});</script>";
                }
                
                if (isset($stmt)) $stmt->close();
            }
        } else {
            echo "<a href=\"login\">Login</a>
            <a href=\"register\">Register</a>";
        }
        ?>
        </div>
        <div class="btmleft">
            <?php
                echo "<span class='username'><br>Hey there, " . $name . " (@" . $discordtag . ")" . " (UserID: " . $uid . ")" . "</span>";
            ?>
        </div>
        <?php
        if ($authsuccessful) {
            $banner = "Website is currently in development. <br>Expect weird errors or things to suddenly change.";
            if ($dispchar) {

            }
            
            echo "<div class=\"warn\" id=\"warn\">
                <span style='background-color:red;cursor: url(\"cursors/chicken.cur\"), auto;' id=\"dumjokeclosebtn\">&nbsp;X </span>
                <em style='text-align: center;'>$banner</em>
            </div>
            <script>
                const a = document.getElementById(\"dumjokeclosebtn\");
                const b = document.getElementById(\"warn\");
                a.addEventListener(\"click\", function(event) {
                    b.remove()
                })
            </script>";
        }
        ?>
        <div class="btmrite">
            <?php
            if (isset($_COOKIE['auth'])) {
                echo "<a href=\"config\">Settings</a>";
                echo "<a href=\"logout\">Log out</a>";
                echo "<a href=\"https://discord.gg/7JwYGHAvJV\">Official Discord server</a>";
            }
            ?>
        </div>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>