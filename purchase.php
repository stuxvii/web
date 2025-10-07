<?php
require_once 'auth.php';

if ($uid === null || $db === null) {
    http_response_code(500);
    die("Authentication Error...");
}

if (isset($_POST['itemid'])) {
$itemid = (int)$_POST['itemid'];
$curinv = json_decode($inv, true);
if (!is_array($curinv)) {
    $curinv = []; 
}
$stmtcheckitem = $db->prepare("
SELECT name, asset, owner, value, public, approved, type
FROM items 
WHERE id = ?
");
$stmtcheckitem->bind_param('i', $itemid);
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();
$row = $result->fetch_assoc();
$stmtcheckitem->close();

if ($row['asset'] == '') {
    $msg = 'Item not found';
    $status = 'error';
} else {
    if (in_array($itemid,$curinv)) {
        $status = 'error';
        $msg = 'You already own that item!';
    } else if ($money > $row['value']) {
        $price = $row['value'];
        $owner = $row['owner'];
        
        $stmtdeductmoney = $db->prepare("
        UPDATE economy
        SET money = money - ? 
        WHERE id = ?
        ");
        $stmtdeductmoney->bind_param('ii', $price, $uid);
        $stmtdeductmoney->execute();
        $stmtdeductmoney->close();
        
        $stmtpaymoney = $db->prepare("
        UPDATE economy
        SET money = money + ? 
        WHERE id = ?
        ");
        $stmtpaymoney->bind_param('ii', $price, $owner);
        $stmtpaymoney->execute();
        $stmtpaymoney->close();
        
        $curinv[] = $itemid;
        $newinv = json_encode($curinv);
        $stmtdeductmoney = $db->prepare("
        UPDATE economy
        SET inv = ? 
        WHERE id = ?
        ");
        $stmtdeductmoney->bind_param('si', $newinv, $uid); // but this replaces it???
        $stmtdeductmoney->execute();
        $stmtdeductmoney->close();
        $status = 'success';
        $msg = 'Purchased!';
    } else {
        $status = 'error';
        $msg = 'You do not have enough money.';
    }
}

echo json_encode([
    'status' => $status,
    'message' => $msg,
    'newmoney' => $money - $price
    ]
);
}
?>