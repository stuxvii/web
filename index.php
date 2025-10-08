<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
$payoutamount = 100;
$curtime = time();

if ($stipend) {
    $stmtpaymoney = $db->prepare("
    UPDATE economy
    SET money = money + ? 
    WHERE id = ?
    ");
    $stmtpaymoney->bind_param('ii', $payoutamount, $uid);
    $stmtpaymoney->execute();
    $stmtpaymoney->close();

    $stmtsetlastclaim = $db->prepare("
    UPDATE economy
    SET lastbuxclaim = ? 
    WHERE id = ?
    ");
    $stmtsetlastclaim->bind_param('ii', $curtime, $uid);
    $stmtsetlastclaim->execute();
    $stmtsetlastclaim->close();
    $money = $money + $payoutamount;
}
ob_start();
if ($authsuccessful):
?>
<div style="display:flex;justify-content:center;flex-direction:column;align-items:center;">
<img class="bounce" src="processing.png" id="speen">
<?php if ($stipend): ?>
    <em>stipend was claimed</em>
<?php endif; ?>
</div>
<?php
endif;

$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>