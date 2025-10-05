<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
$file_id = (int)$_GET['id'] ?? null;
$stmtcheckitem = $db->prepare("
SELECT approved, name, asset, owner, value, public, type
FROM items
WHERE id = ?
");
$stmtcheckitem->bind_param('i',$file_id);
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();
$iteminfo = [];
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $sanitized_row = [];
    foreach ($row as $key => $value) {
        $sanitized_row[$key] = htmlspecialchars($value);
    }
    $iteminfo[] = $sanitized_row;
    echo json_encode($iteminfo);
}
?>