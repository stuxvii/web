<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';

$emptyinv = true;
ob_start();
$oldinv = json_decode($inv);
if (!empty($oldinv)) {
    $inv = array_reverse($oldinv);
    $emptyinv = false;
}
?>
<div class="deadcenter">
<div class="catalogitemborder">
<?php

if (!$emptyinv) {
    foreach ($inv as $v) {
        $stmtcheckitem = $db->prepare('
    SELECT approved, name, asset, owner, value, public, type
    FROM items
    WHERE id = ?
    ');
        $stmtcheckitem->bind_param('i', $v);
        $stmtcheckitem->execute();
        $result = $stmtcheckitem->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $iteminfo = [];
            foreach ($row as $key => $value) {
                $iteminfo[$key] = htmlspecialchars($value);
            }
        }
        ?>
    <div class='catalogitem' id="<?php echo $v; ?>">
        <div class='catalogiteminfo'>

            <?php switch ($iteminfo['type']) {
                    case "Shr":
                        echo "A shirt";
                        break;
                    case "Aud":
                        echo "A sound";
                        break;
                    case "Dec":
                        echo "A decal";
                        break;
                    }?>
        </div>
        <div class="catalogitemasset">
            <?php
            if ($iteminfo['approved'] == 1) {
            switch ($iteminfo['type']) {
                case "Shr":
                    echo "<img class='catalogitemimg' src='getfile?id=$v'>";
                    break;
                case "Dec":
                    echo "<img class='catalogitemimg' src='getfile?id=$v'>";
                    break;
                case "Aud":?>
                    <audio controls id="player">
                        <source src="getfile?id=<?php echo $v; ?>" type="audio/mp3"/>
                    </audio>
                    
                    <?php break;
                }
            } else if ($iteminfo['approved'] == 0) {
                echo "Item is pending moderation.";
            } else {
                echo "Asset rejected.";
            }
            ?>
<a href="item.php?id=<?php echo $v;?>">
    <?php echo $iteminfo['name'];?>
</a>
        </div>
    </div>
<?php
    }
} else {
    echo 'No items in your inventory.';
}
echo '</div>';
echo '</div>';
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>