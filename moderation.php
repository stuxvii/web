<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/auth.php";

if (!$opperms) {
    header("Location: index.php");
    exit;
}
if ($uid === null || $db === null) {
    http_response_code(500);
    die("Authentication Error...");
}
$stmtcheckitem = $db->prepare("
SELECT id, name, asset, owner, value, public, type
FROM items
WHERE approved = 0
");
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();

function deleteasset($id) {
    global $db;
    $stmtgetitempath = $db->prepare("
    SELECT asset
    FROM items
    WHERE id = ?
    ");
    $stmtgetitempath->bind_param('i',$id);
    $stmtgetitempath->execute();
    $result = $stmtgetitempath->get_result();
    $row = $result->fetch_assoc();

    unlink($row['asset']);

    $stmtdelistitem = $db->prepare("
    UPDATE items
    SET asset = NULL, approved = NULL
    WHERE id = ?
    ");
    $stmtdelistitem->bind_param('i',$id);
    $stmtdelistitem->execute();
    return $id;
}

function makeassetavailable($id) {
    global $db;

    $stmtapproveitem = $db->prepare("
    UPDATE items
    SET approved = 1
    WHERE id = ?
    ");
    $stmtapproveitem->bind_param('i',$id);
    $stmtapproveitem->execute();
    return $stmtapproveitem;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json_data = file_get_contents('php://input');
    
    $data = json_decode($json_data, true);
    if ($data === null || !is_array($data) || !isset($data[0]) || !is_array($data[0])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or malformed data structure.']);
        exit;
    }

    $post_item = $data[0];
    
    $id = filter_var($post_item['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid asset ID provided.']);
        exit;
    }
    
    $action = $post_item['action'] ?? '';
    $allowed_actions = ['reject', 'approve'];
    
    if (!in_array($action, $allowed_actions)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action specified. Must be "reject" or "approve".']);
        exit;
    }

    $success = false;
    $message = '';
    
    if ($action == 'reject') {
        if (deleteasset($id)) {
            $success = true;
            $message = "Asset deleted.";
        } else {
            $message = "Failed to delete asset.";
        }
    } else {
        if (makeassetavailable($id)) {
            $success = true;
            $message = "Asset approved.";
        } else {
            $message = "Failed to approve asset.";
        }
    }

    if ($success) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        http_response_code(500); 
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
    
    exit;
}
ob_start();
?>
<div class="deadcenter">
    <div class="itemborder">
    <?php
if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
    $id     = htmlspecialchars($row['id']);
    $itemname   = htmlspecialchars($row['name']);
    $owner  = htmlspecialchars($row['owner']);
    $value  = htmlspecialchars($row['value']);
    $public = htmlspecialchars($row['public']);
    $type   = htmlspecialchars($row['type']);
    ?>
    <div class='item' id="<?php echo $id;?>">
        <div class='iteminfo'>
            <?php echo $itemname; ?>
            <br>
            Selling for ¥<?php echo $value; ?>
            <br>
            Uploaded by: <?php echo getuser($owner)['username'];?>
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
                    <source src="getfile?id=<?php echo $id;?>" type="audio/mpeg">
                </audio>
                <?php
            }
            ?>
            </div>
            <span id="stat<?php echo $id;?>">&nbsp;</span>
            <div class="buttons">
                <button style="background-color:var(--good);" onclick="handleAction(<?php echo $id;?>,'approve');">Approve</button>
                &nbsp;
                <button style="background-color:var(--evil);" onclick="handleAction(<?php echo $id;?>,'reject');">Reject</button>
            </div>
        </div>
<?php }} else {
    echo "No items up for moderation.";
} ?>
    </div>
</div>
<script>
function handleAction(id, action) {
    const item = document.getElementById(id);
    const statusMessage = document.getElementById('stat' + id);

    if (!item || !statusMessage) {
        console.error(`DOM elements not found for ID: ${id}`);
        return;
    }

    statusMessage.style.color = 'black';
    statusMessage.textContent = 'Processing...';

    const postdata = [{'id': id, 'action': action}];
    const actionUrl = "<?php echo $_SERVER['PHP_SELF'] ?>";

    fetch(actionUrl, {
        method: 'POST',
        body: JSON.stringify(postdata)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok. Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            statusMessage.style.color = 'green';
            statusMessage.textContent = data.message;
            setTimeout(() => {
                item.remove()
            }, 1500);
        } else {
            statusMessage.style.color = 'red';
            statusMessage.textContent = data.message || 'An unknown error occurred.';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        statusMessage.style.color = 'red';
        statusMessage.textContent = 'Failed to connect to server: ' + error.message;
    })
}

</script>
<?php
$result->free();
$stmtcheckitem->close();

$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . "/template.php";

?>