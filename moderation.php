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
    return $id;
}
function makeassetavailable($id) {
    global $db;
    $stmtcheckitem = $db->prepare("
    UPDATE items
    SET approved = 1
    WHERE id = ?
    ");
    $stmtcheckitem->bind_param('i',$id);
    $stmtcheckitem->execute();
    return $stmtcheckitem;
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
<div class="diva">
    <div class="itemborder">
    <?php
if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
    $id     = htmlspecialchars($row['id']);
    $name   = htmlspecialchars($row['name']);
    $asset  = htmlspecialchars($row['asset']);
    $owner  = htmlspecialchars($row['owner']);
    $value  = htmlspecialchars($row['value']);
    $public = htmlspecialchars($row['public']);
    $type   = htmlspecialchars($row['type']);
    ?>
    <div class='item' id="<?php echo $id;?>">
        <div class='iteminfo'>
            <?php echo $name; ?>
            <br>
            Selling for ₱ <?php echo $value; ?>
            <br>
            Uploaded by: <?php echo getuser($owner)['username'];?>
            </div>
            <div class='itemasset'>
                <?php
            if ($type == "Shr" || $type == "Dec") {
                ?>
                <img src="<?php echo $asset;?>" height="128" >
                <?php
            } else if ($type == "Aud") {
                ?>
                <audio controls> 
                    <source src="<?php echo $asset;?>" type="audio/mpeg">
                </audio>
                <?php
            }
            ?>
            </div>
            <span id="stat<?php echo $id;?>">Do you approve this <?php echo $type;?>?</span>
            <div class="buttons">
                <button style="background-color:var(--good);" onclick="approve(<?php echo $id;?>);">Approve</button>
                &nbsp;
                <button style="background-color:var(--evil);" onclick="reject(<?php echo $id;?>);">Reject</button>
            </div>
        </div>
<?php }} else {
    echo "No items up for moderation.";
} ?>
    </div>
</div>
<div class="midh rite">
<div class="border" style="max-width:40vw;">Guidelines and recommendations:
<br>
Unless there is clear evidence to the contrary, assume that fellow users that upload assets to the project are trying to improve it, not harm it.
If criticism is necessary, discuss users' actions, but avoid accusing them of harmful motives.
<br>
Slang for those new to the stream:
<br>
Dec: Decal
<br>
Shr: T-Shirt
<br>
Aud: Audio/Sound
</div>
</div>
<script>
function approve(id) {
                const item = document.getElementById(id);
    const statusMessage = document.getElementById('stat' + id);
    statusMessage.textContent = '';
    statusMessage.textContent = 'Processing...';
    const postdata = [{'id': id, 'action': 'approve'}];
    const actionUrl = "<?php echo $_SERVER['PHP_SELF'] ?>";
    fetch(actionUrl, {
        method: 'POST',
        body: JSON.stringify(postdata)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok. Status: ' + response.status);
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
function reject(id) {
                const item = document.getElementById(id);
    const statusMessage = document.getElementById('stat' + id);
    statusMessage.textContent = '';
    statusMessage.textContent = 'Saving...';
    const postdata = [{'id': id, 'action': 'reject'}];
    const actionUrl = "<?php echo $_SERVER['PHP_SELF'] ?>";
    fetch(actionUrl, {
        method: 'POST',
        body: JSON.stringify(postdata)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok. Status: ' + response.status);
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