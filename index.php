<?php
$token = $_COOKIE['auth'] ? '' : false;
if (!preg_match('/^[0-9a-f]{64}$/', $token) && !$token == false) {
    header("Location: logout.php");
    exit;
}

$db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
$stmt = $db->prepare("
    SELECT id
    FROM users 
    WHERE authuuid = :cookie
");

$stmt->bindValue(':cookie', $_COOKIE['auth'], SQLITE3_TEXT);
$name = $stmt->execute();

if ($name) {
    $row = $name->fetchArray(SQLITE3_ASSOC);
    $currentuid = $row['id'];
}
$fetchsettings = $db->prepare("
SELECT appearance,movingbg,dispchar,sidebarid,sidebars
FROM config
WHERE id = :uid");

$fetchsettings->bindValue(":uid",$currentuid,SQLITE3_INTEGER);
$settings = $fetchsettings->execute();
$colorrow = $settings ? $settings->fetchArray(SQLITE3_ASSOC) : false;

$theme = (bool)$colorrow['appearance'];
$movebg = (bool)$colorrow['movingbg'];
$dispchar = (bool)$colorrow['dispchar'];

?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../normalize.css">
        <link rel="stylesheet" href="../pagecharacter.css">
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
        <div class="diva">
        <?php
        $loggedin = false;
        if (!isset($_COOKIE['auth'])) {
            echo "<a href=\"login\">Login</a>
            <a href=\"register\">Register</a>";
            $loggedin = false;
        }
        if (isset($_COOKIE['auth'])) {
            echo "<a href=\"https://windows93.net/c/programs/acidBox93/\">acidBox93 (LOUD AUDIO)</a>
            <a href=\"character\">character customization</a>
            <a href=\"mwrtng/\" class=\"mwrtng\">mewity rating</a>";
            $loggedin = true;
        }
        $brickcolor = ["111111" => 1003, "CDCDCD" => 1002, "ECECEC" => 40, "F8F8F8" => 1001, "EDEAEA" => 348, "E9DADA" => 349, "FFC9C9" => 1025, "FF9494" => 337, "965555" => 344, 
        "A34B4B" => 1007, "883E3E" => 350, "562424" => 339, "FF5959" => 331, "750000" => 332, "970000" => 327, "FF0000" => 1004, "966766" => 360, "BE6862" => 338, "957977" => 153, 
        "CD544B" => 41, "C4281C" => 21, "958988" => 357, "BBB3B2" => 354, "DA867A" => 101, "D9856C" => 47, "97695B" => 176, "EEC4B6" => 100, "D36F4C" => 123, "904C2A" => 216, 
        "8F4C2A" => 345, "CF6024" => 193, "D5733D" => 133, "694028" => 192, "CC8E69" => 18, "564236" => 361, "AF9483" => 359, "AE7A59" => 128, "A05F35" => 38, "6C584B" => 355, 
        "7C5C46" => 217, "5A4C42" => 364, "E09864" => 137, "BFB7B1" => 111, "EAB892" => 125, "624732" => 25, "DA8541" => 106, "CB8442" => 12, "B48455" => 178, "6A3909" => 365, 
        "AA5500" => 1014, "FFCC99" => 1030, "756C62" => 168, "EBB87F" => 225, "E29B40" => 105, "E7AC58" => 121, "F3CF9B" => 36, "C7C1B7" => 103, "DCBC81" => 127, "7E683F" => 362, 
        "BC9B5D" => 351, "A0844F" => 356, "D3BE96" => 346, "C7AC78" => 352, "F0D5A0" => 224, "D7A94B" => 180, "E8AB2D" => 191, "685C43" => 108, "958A73" => 138, "B08E44" => 209, 
        "FFAF00" => 1017, "FFB000" => 1005, "EFB838" => 333, "D7C59A" => 5, "ECE8DE" => 50, "CABFA3" => 353, "938767" => 147, "F1E7C7" => 340, "ABA89E" => 358, "69665C" => 363, 
        "F8D96D" => 334, "F5CD30" => 24, "F9D62E" => 190, "FDEA8D" => 226, "F9E999" => 3, "E5E4DF" => 208, "FEF3BB" => 341, "E2DCBC" => 347, "FFF67B" => 157, "F8F184" => 49, 
        "F7F18D" => 44, "C1BE42" => 1008, "DFDFDE" => 325, "FFFFCC" => 1029, "FFFF00" => 1009, "D8DD56" => 134, "C7D23C" => 115, "828A5D" => 200, "D9E4A7" => 120, "A4BD47" => 119, 
        "7F8E64" => 1022, "6D6E6C" => 27, "B9C4B1" => 319, "A8BD99" => 324, "A1C48C" => 29, "3A7D15" => 1021, "7C9C6B" => 317, "94BE81" => 323, "C2DAB8" => 6, "2C651D" => 304, 
        "5B9A4C" => 310, "B1E5A6" => 328, "8AAB85" => 318, "1F801D" => 313, "575857" => 148, "CCFFCC" => 1028, "4B974B" => 37, "00FF00" => 1020, "348E40" => 309, "506D54" => 301, 
        "84B68D" => 48, "27462D" => 141, "709578" => 210, "A1A5A2" => 2, "287F47" => 28, "789082" => 151, "ABADAC" => 150, "9FF3E9" => 1027, "12EED4" => 1018, "B7D7D5" => 118, 
        "F2F3F3" => 1, "79B5B5" => 211, "00FFFF" => 1019, "008F9C" => 107, "55A5AF" => 116, "04AFEC" => 1013, "0989CF" => 315, "7DBBDD" => 232, "80BBDC" => 11, "C1DFF0" => 42, 
        "98C2DB" => 329, "B4D2E4" => 45, "9CA3A8" => 131, "0D69AC" => 23, "1B2A35" => 26, "AFDDFF" => 1024, "7BB6E8" => 43, "9FC3E9" => 212, "203A56" => 140, "CFE2F7" => 143, 
        "335882" => 306, "6E99CA" => 102, "527CAE" => 305, "C7D4E4" => 336, "74869D" => 135, "9FADC0" => 314, "7988A1" => 145, "4667A4" => 195, "23478B" => 196, "2154B9" => 1012, 
        "002060" => 1011, "C1CADE" => 39, "6C81B7" => 213, "161D32" => 149, "435493" => 110, "6874AC" => 112, "9FA1AC" => 311, "CACBD1" => 320, "5B5D69" => 302, "102ADC" => 307, 
        "0010B0" => 303, "A7A9CE" => 220, "E7E7EC" => 335, "A5A5CB" => 126, "0000FF" => 1010, "B1A7FF" => 1026, "342B75" => 268, "6B629B" => 219, "A3A2A5" => 194, "958EA3" => 146, 
        "6225D1" => 1031, "3D1585" => 308, "B480FF" => 1006, "877C90" => 136, "8C5B9F" => 1023, "6B327C" => 104, "96709F" => 218, "7B2F7B" => 322, "592259" => 312, "7B007B" => 316, 
        "AA00AA" => 1015, "8E4285" => 198, "A75E9B" => 321, "635F62" => 199, "FF00BF" => 1032, "923978" => 124, "FF66CC" => 1016, "D490BD" => 343, "FF98DC" => 330, "E0B2D0" => 342, 
        "C470A0" => 22, "CD6298" => 221, "898788" => 179, "E1A4C2" => 158, "E4ADC8" => 222, "E5ADC8" => 113, "E8BAC8" => 9, "DC9095" => 223, "7B2E2F" => 154];
        $currentuid = null;
        $db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        
        $stmt = $db->prepare("
            SELECT id
            FROM users 
            WHERE authuuid = :cookie
        ");

        $stmt->bindValue(':cookie', $_COOKIE['auth'], SQLITE3_TEXT);
        $name = $stmt->execute();

        if ($name) {
            $row = $name->fetchArray(SQLITE3_ASSOC);
            $currentuid = $row['id'];
        }
        $stmt = $db->prepare("SELECT head, torso, leftarm, rightarm, leftleg, rightleg FROM avatars WHERE id = :uid");
        $stmt->bindValue(':uid', $currentuid, SQLITE3_INTEGER);
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
        if (isset($insertAvatarStmt)) $insertAvatarStmt->close();
        $db->close();
        ?>
        </div>
        <div class="btmleft">
            <?php
            const logout = 'logout.php';

            $db = null;
            try {
                $db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            } catch (Exception $e) {
                error_log("Database connection failed: " . $e->getMessage());
                exit("Database connection error.");
            }
            if ($loggedin) {
                $authcookie = $_COOKIE['auth'];

                $stmt = $db->prepare("
                    SELECT username,discordtag,id
                    FROM users 
                    WHERE authuuid = :cookie
                    LIMIT 1
                ");
                
                $stmt->bindValue(':cookie', $authcookie, SQLITE3_TEXT);
                
                $result = $stmt->execute();

                if ($result) {
                    $row = $result->fetchArray(SQLITE3_ASSOC);
                    if ($row) {
                        echo "<span class='username'><br>Hey there, " . $row['username'] . " (@" . $row['discordtag'] . ")" . " (UserID: " . $row['id'] . ")" . "</span>";
                    } else {
                        header("Location: " . logout);
                        exit();
                    }
                    $result->finalize();
                } else {
                    error_log("SQL execution failed: " . $db->lastErrorMsg());
                    header("Location: " . logout);
                    exit();
                }
            }

            if ($db) {
                $db->close();
            }
            ?>
        </div>
        </div>
        <?php
        $aaaaaaaaaaaaamessage = null;
        $dev = true;
        if ($loggedin) {
            if ($dispchar) {
                echo "<div class=\"char\"><span class=\"bodypart\" id=\"head\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\"><img src=\"images/epicface.png\" width='56' height='56'></span>
                <span class=\"bodypart limb\" id=\"lleg\" color=\"301\" style=\"background-color: rgb(80, 109, 84);\"></span>
                <span class=\"bodypart limb\" id=\"rleg\" color=\"301\" style=\"background-color: rgb(80, 109, 84);\"></span>
                <span class=\"bodypart limb\" id=\"larm\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\"></span>
                <span class=\"bodypart\" id=\"trso\" color=\"23\" style=\"background-color: rgb(13, 105, 172);\"></span>
                <span class=\"bodypart limb\" id=\"rarm\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\"></span></div>";
            }
            $aaaaaaaaaaaaamessage = "Website is currently in development. <br>Expect weird errors or things to suddenly change.";
        }
        if ($aaaaaaaaaaaaamessage == null) {return;}
        echo "<div class=\"warn\" id=\"warn\">
            <span style='background-color:red;cursor: url(\"cursors/chicken.cur\"), auto;' id=\"dumjokeclosebtn\">&nbsp;X </span>
            <em style='text-align: center;'>$aaaaaaaaaaaaamessage</em>
        </div>
        <script>
            const a = document.getElementById(\"dumjokeclosebtn\");
            const b = document.getElementById(\"warn\");
            a.addEventListener(\"click\", function(event) {
                b.remove()
            })
        </script>";
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
        <script src="../titleanim.min.js"></script>
    </body>
</html>