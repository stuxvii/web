<?php
require_once 'auth.php';
require_once 'brickcolor.php';

$color_map = [
    'head_color' => 'head',
    'trso_color' => 'trso',
    'lleg_color' => 'lleg',
    'rleg_color' => 'rleg',
    'larm_color' => 'larm',
    'rarm_color' => 'rarm'
];

if ($uid === null || $db === null) {
    http_response_code(400);
    header("Location: index.php");
}

$invarray = json_decode($inv, true);
if ($invarray) {
$placeholders = implode(',', array_fill(0, count($invarray), '?'));
$sql = "SELECT * FROM items WHERE id IN ($placeholders) AND type = 'Shr'";
try {
    $stmt = $db->prepare($sql);
    $stmt->execute($invarray);

    $result = $stmt->get_result();
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
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

        $stmt = $db->prepare("UPDATE avatars SET colors = ? WHERE id = ?");
        
        $stmt->bind_param('si', json_encode($update_data), $uid);

        $stmt->execute();
        echo "Saved!";
        $stmt->close();
        exit;
    } else if (isset($_POST['shirtting']) && $_POST['shirtting'] == '1') {
        if (in_array((int)$_POST['tshirt'],$invarray)) {
            $tshirt = (int)$_POST['tshirt'];
            $stmt = $db->prepare("UPDATE avatars SET tshirt = ? WHERE id = ?");
            $stmt->bind_param('ii', $tshirt, $uid);
            if ($stmt->execute()) {
                http_response_code(200);
                echo "Equipped";
            } else {
                http_response_code(500);
                echo "yikes server error!";
            }

        } else {
            http_response_code(400);
            echo "You dont own this item, and are probably fucking around in devtools";
        }
        exit;
    } else if (isset($_POST['nomoreshirt']) && $_POST['nomoreshirt'] == '1') {
        $tshirt = NULL;
        $stmt = $db->prepare("UPDATE avatars SET tshirt = ? WHERE id = ?");
        $stmt->bind_param('ii', $tshirt, $uid);
        if ($stmt->execute()) {
            http_response_code(200);
            echo "Equipped";
        } else {
            http_response_code(500);
            echo "yikes server error!";
        }
        exit;
    }
}


ob_start();
if ($authsuccessful) :
?>
<div class="diva" style="flex-direction:row;">
    <div class="planecharacter">
    <div class="border" id="char">
        <span class="bodypart" id="head" color="1009" style="background-color: rgb(255, 255, 0);"></span>
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
    </div>
    <div style="display:flex;flex-direction:column;align-items:baseline;">
    <button style="width: 10em;" id="unequipbutton" onclick="unequipshirt();">Unequip current shirt</button>
    <div class="catalogitemborder" style="flex-direction:column;max-height:40vh;">
    <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id     = htmlspecialchars($row['id']);
        $itname = htmlspecialchars($row['name']);
        $owner  = htmlspecialchars($row['owner']);
        $value  = htmlspecialchars($row['value']);
        $public = htmlspecialchars($row['public']);
    ?>
    
    <div class='catalogitem' data-item-id="<?php echo $id;?>"><div class='catalogitemasset'>
        <?php echo $itname; ?>
        <img class="catalogitemimg" src="getfile?id=<?php echo $id;?>" height="128" >
        <button style="width: 4em;" id="<?php echo $id;?>" onclick="settshirt(<?php echo $id;?>);">Equip</button>
    </div>
    
        </div>
<?php }} else {
    echo "You don't own any clothes.";
}?>
    </div></div>
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
            foreach ($avatarcolors as $part_id => $sql_color_id) {
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
        ?>
<script>
document.addEventListener("DOMContentLoaded", e => {
    let t = <?php echo $newjson; ?>;
    t.forEach(e => {
        let n = document.getElementById(e.id);
        if (n) {
            n.style.backgroundColor = "#" + e.hex; 
            n.setAttribute('color', e.color_id); 
        }
    });
});
</script>
<?php
endif;
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>