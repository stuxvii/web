<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';

$itemid = (int) $_GET['id'] ?? null;

$stmtcheckitem = $db->prepare('
SELECT owner, value, name, type
FROM items
WHERE id = ?
');
$stmtcheckitem->bind_param('i', $itemid);
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();

$row = [];
$itemname = NULL;
$invarray = json_decode($inv);
$owned = false;
if (in_array($itemid,$invarray)) {
    $owned = true;
}
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Fetch basic info
    $value = $row['value'];
    $itemname = $row['name'];
    $owner = $row['owner'];
    $type = $row['type'];

    // We need to get the owner of the item,
    $stmtcheckitem = $db->prepare('
    SELECT username
    FROM users
    WHERE id = ?
    ');
    $stmtcheckitem->bind_param('i', $owner);
    $stmtcheckitem->execute();
    $result = $stmtcheckitem->get_result();
    $row = $result->fetch_assoc();
    $ownername = $row['username'];
    }
ob_start();
?>
<span id="purchase-status-message"></span>
<div class="border" style="flex-direction:row;align-items:normal;">
    <?php
    if ($itemname) {
        if ($type === "Aud") {
            echo "<audio controls src=\"getfile?id=$itemid\"></audio>";
        } else {
            echo "<img class='catalogitemimg' src=\"getfile?id=$itemid\">";
        }
     ?>
    <div style='margin-left:1em;flex-direction:column;display:flex;justify-content: space-between;'>
        <h1><?php echo $itemname;?></h1>
        <div style="flex-direction:column;display:flex;"><span>Uploader:</span>
        <a href="profile?id=<?php echo $owner;?>"><?php echo $ownername;?></a></div>
        <button onclick="purchase(<?php echo $itemid;?>)" style="background-color:var(--good);">
            <?php 
            if (!$owned) {
                if ($value > 0) {
                    echo "¥" . $value;
                } else {
                    echo "Free";
                }
            } else {
                echo "Owned";
            }
            ?>
        </button>
    </div> <?php } else { ?> User not found.<?php }?>
</div>
<script> 
function purchase(itemId) {
    const statusMessage = document.getElementById('purchase-status-message');
    const amountPesos = document.getElementById('amountofmoney');
    if (!itemId) {
        console.error("Item ID not found for purchase.");
        return;
    }
    statusMessage.textContent = `Processing...`;
    this.disabled = true;
    const postData = new URLSearchParams();
    postData.append('itemid', itemId);

    fetch('/purchase', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: postData
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || 'Server error occurred.'); 
            }
            return data;
        });
    })
    .then(data => {
        if (data.status === 'success') {
            statusMessage.textContent = data.message || `Item ${itemId} purchased successfully!`;
            amountPesos.textContent = data.newmoney;
            statusMessage.style.color = 'green';
        } else {
            statusMessage.textContent = data.message || 'Purchase failed with an unknown error. You have not been charged.';
            statusMessage.style.color = 'orange';
            console.log('Purchase Failure Data:', data);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        statusMessage.textContent = 'Connection or Server Failure: ' + error.message;
        statusMessage.style.color = 'orange';
    })
    .finally(() => {
        this.disabled = false;
    });
};
</script>
<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>