<?php
require_once 'auth.php';

if ($uid === null || $db === null) {
    http_response_code(500);
    die("Authentication Error...");
}

if (isset($_POST['itemid'])) {
$itemid = (int)$_POST['itemid'];

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
    $status = 'success';
    $msg = 'item name: ' . $row['name'];
}

echo json_encode([
    'status' => $status,
    'message' => $msg
    ]
);
}
?>