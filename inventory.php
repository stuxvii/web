<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
ob_start();
?>
<div class="deadcenter">
<span><a href="/">Home</a> -- Your inventory</span>
<div class="itemborder">
    <?php
    
    if (!empty($inv)) {
    foreach (json_decode($inv) as $v) {
        $stmtcheckitem = $db->prepare("
        SELECT approved, name, asset, owner, value, public, type
        FROM items
        WHERE id = ?
        ");
        $stmtcheckitem->bind_param('i',$v);
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
    <div class='item' id="<?php echo $v;?>">
        <div class='iteminfo'>
            <?php echo $iteminfo['name'];?>
            <br> By 
            <?php echo getuser($iteminfo['owner'])['username'];?>
            </div>
            <div class='itemasset'>
                <?php
            if ($iteminfo['type'] == "Shr" || $iteminfo['type'] == "Dec") {
                ?>
                <img src="getfile?id=<?php echo $v;?>" height="128" >
                <?php
            } else if ($iteminfo['type'] == "Aud") {
                ?>
                <audio controls> 
                    <source src="<?php echo "/getfile?id=" . $v;?>" type="audio/mpeg">
                </audio>
                <?php
            }
            ?>
            </div>
        </div>

<?php }} else {
    echo "No items in your inventory.";
}
echo "</div>";
echo "</div>";
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>