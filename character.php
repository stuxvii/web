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

if ($uid === null || $db === null) {
    http_response_code(500);
    die("Authentication Error...");
}

$insertAvatarStmt = $db->prepare("
    INSERT IGNORE INTO avatars (id) VALUES (?)
");

$insertAvatarStmt->bind_param('i', $uid);
$insertAvatarStmt->execute();
$insertAvatarStmt->close();

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
                $bind_types = '';
                $bind_values = [];

                foreach ($update_data as $db_column => $value) {
                    $set_clauses[] = "$db_column = ?";
                    $bind_types .= 'i';
                    $bind_values[] = $value;
                }
            $bind_types .= 'i';
            $bind_values[] = $uid;

            $sql_set = implode(', ', $set_clauses);
            $sql = "UPDATE avatars SET $sql_set WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            
            $stmt->bind_param($bind_types, ...$bind_values);

            $stmt->execute();
            echo "Saved!";
            $stmt->close();
            exit;
        } else {
            http_response_code(400);
            echo "Invalid color data received.";
            exit;
        }
    }
}

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
                echo "<style>body{animation: none;}</style>";
            }
        ?>
    </head>
    <body>
        <div class="content">
        <?php
        require "sidebars.php";
        ?>
        <div class="diva" style="flex-direction:row;">
            <div class="border" id="char">
                <span class="bodypart" id="head" color="1009" style="background-color: rgb(255, 255, 0);">
                    <img src="images/epicface.png" width='56' height='56'>
                </span>
                <div class="horiz">
                    <span class="bodypart limb" id="larm" color="1009" style="background-color: rgb(255, 255, 0);"></span>
                    <span class="bodypart" id="trso" color="23" style="background-color: rgb(13, 105, 172);"></span>
                    <span class="bodypart limb" id="rarm" color="1009" style="background-color: rgb(255, 255, 0);"></span>
                </div>
                <div class="horiz">
                    <span class="bodypart limb" id="lleg" color="301" style="background-color: rgb(80, 109, 84);"></span>
                    <span class="bodypart limb" id="rleg" color="301" style="background-color: rgb(80, 109, 84);"></span>
                </div>
            </div>
            <div>
                <a href="/">Home page</a>
                <div class="border">
                    <div class="colorpicker" id="colorpicker">
                        <?php
                        foreach ($brickcolor as $k => $v) {
                            echo "<span class='color' colorbrick='$v' style='background-color:#$k;'></span>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="border">
                <div class="vert">
                    <button onclick="render();" id="renderstat" class="left">Save</button>
                    <?php echo "<img height='240px' id='render' src='renders/$uid" . ".png'>"; ?>
                </div>
            </div>
        </div>
        <script src="../character.js"></script>
        <script src="../titleanim.min.js"></script>
        <?php
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
</script>";
        $rightside = true;
        require "sidebars.php";
        ?>
        </div>
    </body>
</html>