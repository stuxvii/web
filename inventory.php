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
<div class="itemborder">
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
<div class='item' id="<?php echo $v; ?>">
    <div class='iteminfo'>
        <?php echo $iteminfo['name'];
        echo ' - id:' . $v ?>
        <br> By 
        <?php echo getuser($iteminfo['owner'])['username']; ?>
        </div>
        <div style="display:flex;flex-direction:row;">
            <?php
            if ($iteminfo['type'] == 'Shr' || $iteminfo['type'] == 'Dec'):?>
                <img src="getfile?id=<?php echo $v; ?>" height="128" >
            <?php elseif ($iteminfo['type'] == 'Aud'): ?>
            <audio controls> 
                <source src="<?php echo '/getfile?id=' . $v; ?>" type="audio/mpeg">
            </audio>
            <?php
            elseif (!$iteminfo['type']):
                echo "Asset rejected.";
            endif;
            ?>
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