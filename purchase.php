<?php
require_once 'auth.php';

if ($uid === null || $db === null) {
    http_response_code(500);
    die("Authentication Error...");
}

$itemid = $_POST['itemid'];
$stmtcheckitem = $db->prepare("
SELECT id, name, asset, owner, value, public, approved, type
FROM items 
WHERE id = ?
");
$stmtcheckitem->bind_param('s', $itemid);
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();
$row = $result->fetch_assoc();
$stmtcheckitem->close();

if (!$row['count'] > 0) {
    $msg = "Item not found.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo json_encode([
        'status' => 'successa',
        'message' => $msg
        ]
    );
}
?>