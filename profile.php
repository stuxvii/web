<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/brickcolor.php';

$usrid = (int) $_GET['id'] ?? null;

$stmtcheckitem = $db->prepare('
SELECT username, registerts, isoperator
FROM users
WHERE id = ?
');
$stmtcheckitem->bind_param('i', $usrid);
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();

$row = [];
$isop = false;
$pfusername = NULL;
$registerdate = 1;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Fetch basic info
    $isop = $row['isoperator'];
    $pfusername = $row['username'];
    $registerdate = $row['registerts'];
    
    // Determine if we should get the 3d render of their character or the 2d representation
    $charisavailable = true;
    if (!file_exists("renders/$usrid.png")) {
        $charisavailable = false;
        $stmtgetcolors = $db->prepare('
        SELECT colors
        FROM avatars
        WHERE id = ?
        ');
        $stmtgetcolors->bind_param('i', $usrid);
        $stmtgetcolors->execute();
        $clrrslt = $stmtgetcolors->get_result();
        $undecoded = [];
        while ($row = $clrrslt->fetch_assoc()) {
            $undecoded = $row['colors'];
        }
        $colors = json_decode($undecoded, true);
    }

    // get economy values
    $checkifecon = $db->prepare("
    SELECT money, inv FROM economy WHERE id = ?
    "); 

    $checkifecon->bind_param('i', $usrid);
    $checkifecon->execute();

    $econ = $checkifecon->get_result();
    $econrow = $econ ? $econ->fetch_assoc() : false;

    $othrmoney = $econrow['money'] ?? null;
    $inv = $econrow['inv'] ?? null;
    }
ob_start();
?>
<div class="border" style="flex-direction:row;align-items:normal;">
    <?php
    if ($pfusername) {
    if ($charisavailable) {
        echo "<img src=\"renders/$usrid.png\">";
    } else {
        ?>
    <div style="flex-direction:column;justify-content:center;align-items:center;display:flex;position:relative;" id="char">
        <span class="bodypart" id="head" color="1009" style="background-color: #<?php echo array_search((int) $colors['head'], $brickcolor) ?>;"></span>
        <div class="horiz">
            <span class="bodypart limb" id="larm" color="1009" style="background-color: #<?php echo array_search((int) $colors['larm'], $brickcolor) ?>;"></span>
            <span class="bodypart" id="trso" color="23" style="background-color: #<?php echo array_search((int) $colors['trso'], $brickcolor) ?>;"></span>
            <span class="bodypart limb" id="rarm" color="1009" style="background-color: #<?php echo array_search((int) $colors['rarm'], $brickcolor) ?>;"></span>
        </div>
        <div class="horiz">
            <span class="bodypart limb" id="lleg" color="301" style="background-color: #<?php echo array_search((int) $colors['lleg'], $brickcolor) ?>;"></span>
            <span class="bodypart limb" id="rleg" color="301" style="background-color: #<?php echo array_search((int) $colors['rleg'], $brickcolor) ?>;"></span>
        </div>
    </div>
    <?php } ?>
    <div style='margin-left:1em;flex-direction:column;display:flex;'>
        <h1 <?php if ($isop) { echo "title='This user is a moderator.'"; } ?>><?php if ($isop) {
    echo '「';
}
echo $pfusername;
if ($isop) {
    echo '」';
} ?></h1>
        <span>Has ¥ <?php echo $othrmoney; ?></span>
        <span title="<?php echo date('jS l, F Y', $registerdate);?>">Join date: <br><?php echo date("d-m-y", $registerdate); ?><br></span>
    </div> <?php } else { ?> User not found.<?php }?>
</div>
<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>