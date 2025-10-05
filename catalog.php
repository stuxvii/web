<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
$stmtcheckitem = $db->prepare("
SELECT id, name, asset, owner, value, public, type
FROM items
WHERE approved = 1
");
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();
ob_start();
?>
<div class="deadcenter">
<div class="itemborder">
    <?php
if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
    $id     = htmlspecialchars($row['id']);
    $name   = htmlspecialchars($row['name']);
    $owner  = htmlspecialchars($row['owner']);
    $value  = htmlspecialchars($row['value']);
    $public = htmlspecialchars($row['public']);
    $type   = htmlspecialchars($row['type']);
    ?>
    <div class='item' id="<?php echo $id;?>">
        <div class='iteminfo'>
            <?php echo $name; ?>
            </div>
            <div class='itemasset'>
                <?php
            if ($type == "Shr" || $type == "Dec") {
                ?>
                <img src="getfile?id=<?php echo $id;?>" height="128" >
                <?php
            } else if ($type == "Aud") {
                ?>
                <audio controls> 
                    <source src="<?php echo "/getfile?id=" . $id;?>" type="audio/mpeg">
                </audio>
                <?php
            }
            ?>
            </div>
        </div>

<?php }} else {
    echo "No items up for moderation.";
}
echo "</div>";
echo "</div>";
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>