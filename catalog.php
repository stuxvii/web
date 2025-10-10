<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
$category = isset($_GET['meow'])?$_GET['meow']:"shr";

// the joke is that cat can be short for category, but cats (animal) also meow.
$links = [
    ['href' => "catalog?meow=shr", "text" => "Shirts"],
    ['href' => "catalog?meow=aud", "text" => "Sounds"],
    ['href' => "catalog?meow=dec", "text" => "Decals"],
];

function getitemswtype($type) {
    global $db;
    $stmt = $db->prepare("
        SELECT id, name, asset, owner, value, type
        FROM items
        WHERE approved = 1 AND public =1 AND type = ?
        ORDER BY id DESC
    ");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    return $stmt->get_result();
}
$fetch = getitemswtype($category);
ob_start();
?>
<div class="deadcenter">
    <span id="purchase-status-message"></span>
    <div>
<?php
$curquery = ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
if ($category == "") { 
    $curquery = "shr"; 
}
foreach ($links as $link) {
    $href = $link['href'];
    $text = $link['text'];

    if ($href === $curquery) {
        echo "<span>$text</span> ";
    } else {
        echo "<a href=$href>$text</a> ";
    }
}
?>
</div>
<div class="catalogitemborder">
    <?php
if ($fetch->num_rows > 0) {
    while ($row = $fetch->fetch_assoc()) {
        $id     = htmlspecialchars($row['id']);
        $itemname   = htmlspecialchars($row['name']);
        $owner  = htmlspecialchars($row['owner']);
        $value  = htmlspecialchars($row['value']);
        $public = htmlspecialchars($row['public']);
        $type = htmlspecialchars($row['type']);
    ?>
    <div class='catalogitem' data-item-id="<?php echo $id;?>"> 
        <a href="item.php?id=<?php echo $id;?>">
        <div class='catalogitemasset'>
            <?php
            if ($type == "Shr" || $type == "Dec") {
            ?>
            <img class="catalogitemimg" src="getfile?id=<?php echo $id;?>">
            <?php } else {?>
            <audio controls src="getfile?id=<?php echo $id;?>"></audio>
            <?php }?>
        </div>
        <div class='catalogiteminfo'>
            <?php echo $itemname;?>
            <span><?php if ($value > 0) {echo "¥" . $value;} else {echo "Free";}?></span>
        </div>
    </a>
    </div>
<?php }} else {
    echo "No items up for sale.";
}?>
</div>
</div>
<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>