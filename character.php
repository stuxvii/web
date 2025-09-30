<?php
require_once 'auth.php';
require_once 'brickcolor.php';

$color_map = [
    'head_color' => 'head',
    'trso_color' => 'torso',
    'lleg_color' => 'leftleg',
    'rleg_color' => 'rightleg',
    'larm_color' => 'leftarm',
    'rarm_color' => 'rightarm'
];
$db->exec("
CREATE TABLE IF NOT EXISTS avatars (
    id INTEGER PRIMARY KEY,
    head INTEGER,
    torso INTEGER,
    leftarm INTEGER,
    rightarm INTEGER,
    leftleg INTEGER,
    rightleg INTEGER
)"); 

$insertAvatarStmt = $db->prepare("
    INSERT OR IGNORE INTO avatars (id) VALUES (:uid)
");

$insertAvatarStmt->bindValue(':uid', $uid, SQLITE3_INTEGER);
$insertAvatarStmt->execute();
$insertAvatarStmt->reset();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['is_ajax_save']) && $_POST['is_ajax_save'] == '1') {
        $update_data = [];
        $valid_request = true;

        foreach ($color_map as $post_key => $db_column) {
            if (!isset($_POST[$post_key])) {
                $valid_request = false;
                break;
            }
            $color_value = $_POST[$post_key]; 
            
            if (!ctype_digit($color_value) || !in_array((int)$color_value, $brickcolor)) {
                $valid_request = false;
                break;
            }

            $update_data[$db_column] = (int)$color_value;
        }

        if ($valid_request) {
            $set_clauses = [];
            foreach ($update_data as $db_column => $value) {
                $set_clauses[] = "$db_column = :$db_column";
            }
            $sql_set = implode(', ', $set_clauses);
            $sql = "UPDATE avatars SET $sql_set WHERE id = :id";
            $stmt = $db->prepare($sql);
            
            foreach ($update_data as $db_column => $value) {
                $stmt->bindValue(":$db_column", $value, SQLITE3_INTEGER);
            }
            $stmt->bindValue(':id', $uid, SQLITE3_INTEGER);
            $stmt->execute();
            echo "Saved!";
            $stmt->close();
            $db->close();
            exit;
        } else {
            http_response_code(400);
            echo "Invalid color data received.";
            $db->close();
            exit;
        }
    }
}

$stmt_fetch = $db->prepare("SELECT head, torso, leftarm, rightarm, leftleg, rightleg FROM avatars WHERE id = :uid");
$stmt_fetch->bindValue(':uid', $uid, SQLITE3_INTEGER);
$result_fetch = $stmt_fetch->execute();
$colorrow = $result_fetch ? $result_fetch->fetchArray(SQLITE3_ASSOC) : false; 
$stmt_fetch->close();


?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../animate.min.css">
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
        <?php
        require "sidebars.php";
        ?>
        <div class="diva" style="flex-direction:row;">
            <div class="charborder">
                <div id="char">
                    <span class="bodypart" id="head" color="1009" style="background-color: rgb(255, 255, 0);"><img src="images/epicface.png" width='56' height='56'></span>
                    <span class="bodypart limb" id="lleg" color="301" style="background-color: rgb(80, 109, 84);"></span>
                    <span class="bodypart limb" id="rleg" color="301" style="background-color: rgb(80, 109, 84);"></span>
                    <span class="bodypart limb" id="larm" color="1009" style="background-color: rgb(255, 255, 0);"></span>
                    <span class="bodypart" id="trso" color="23" style="background-color: rgb(13, 105, 172);"></span>
                    <span class="bodypart limb" id="rarm" color="1009" style="background-color: rgb(255, 255, 0);"></span>
                    <div class="btmleft">
                        <span id="whatdiduselect">click<br>guy</span>
                        <div id="plrform"> 
                            <button id="saveButton" type="button">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border">
                <div class="colorpicker" id="colorpicker">
                    <?php
                    foreach ($brickcolor as $k => $v) {
                        echo "<span class='color' colorbrick='$v' style='background-color:#$k;'></span>";
                    }
                    ?>
                </div>
            </div>
            <div class="border">
                <button onclick="render();" id="renderstat">Render</button>
                <br>
                <?php echo "<img height='240px' id='render' src='renders/$uid" . ".png'>"; ?>
            </div>
        </div>
        <script src="../character.js"></script>
        <script src="../titleanim.min.js"></script>
        <div class="rite">
            <a href="/">Home page</a>
        </div>
        <?php
        if ($colorrow) {
            $bpdata = [];
            $bodyparts_map = [
                "head" => $colorrow['head'],
                "trso" => $colorrow['torso'],
                "larm" => $colorrow['leftarm'],
                "rarm" => $colorrow['rightarm'],
                "lleg" => $colorrow['leftleg'],
                "rleg" => $colorrow['rightleg']
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
</script>";
        }
        
        if (isset($insertAvatarStmt)) $insertAvatarStmt->close();
        $db->close();
        $rightside = true;
        require "sidebars.php";
        ?>
        </div>
    </body>
</html>