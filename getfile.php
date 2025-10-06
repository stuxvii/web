<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
$db = get_db_connection();
$file_id = (int) $_GET['id'] ?? null;
$file_path = $_SERVER['DOCUMENT_ROOT'] . '/images/modpending.png';

if (!$file_id || !is_numeric($file_id)) {
    http_response_code(400);
    exit('Invalid file request.');
}

$stmtcheckitem = $db->prepare('
SELECT approved, name, asset, owner, value, public, type
FROM items
WHERE id = ?
');
$stmtcheckitem->bind_param('i', $file_id);
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();
$row = $result->fetch_assoc();

if (!$row['approved'] == 1) {
    if (!$opperms) {
        header('Content-Length: ' . filesize($file_path));
        header('Content-Type: image/png');
        readfile($file_path);
    }
}

$file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $row['asset'];
$filename = basename($file_path);

if (!file_exists($file_path) || !is_readable($file_path)) {
    http_response_code(404);
    exit;
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header("Content-Disposition: inline; filename=\"$filename\"");
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>