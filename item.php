<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';

$itemid = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmtgetitem = $db->prepare('
SELECT owner, value, name, type, `desc`, uploadts, `public`
FROM items
WHERE id = ?
');
$stmtgetitem->bind_param('i', $itemid);
$stmtgetitem->execute();
$result = $stmtgetitem->get_result();

$row = [];
$itemname = NULL;
$invarray = json_decode($inv);
$owned = false;

if (!empty($invarray) && in_array($itemid,$invarray)) {
    $owned = true;
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Fetch basic info
    $value = $row['value'];
    $itemname = htmlspecialchars($row['name']);
    $itemdesc = htmlspecialchars($row['desc']);
    $itemupts = $row['uploadts']; // upload date as a unix timestamp
    $owner = $row['owner'];
    $type = $row['type'];
    $public = $row['public'];

    // We need to get the owner of the item,
    $stmtgetowner = $db->prepare('
    SELECT username
    FROM users
    WHERE id = ?
    ');
    $stmtgetowner->bind_param('i', $owner);
    $stmtgetowner->execute();
    $result = $stmtgetowner->get_result();
    $ownrow = $result->fetch_assoc();
    $ownername = htmlspecialchars($ownrow['username']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST"&&$owner == $uid) {
    if ($_POST['itemname']) {
        $changedtopublic = false;
        if ($_POST['itempub']) {
            $changedtopublic = true;
        }
        $stmtupditem = $db->prepare('
        UPDATE items
        SET value = ?, name = ?, `desc` = ?, `public` = ?
        WHERE id = ?
        ');
        $stmtupditem->bind_param('issii', $_POST['itemprice'], $_POST['itemname'], $_POST['itemdesc'], $changedtopublic, $itemid);
        $stmtupditem->execute();
        $result = $stmtupditem->get_result();
        header("Location: item?id=$itemid");
        exit;
    }
    ob_start();
    ?>
    <form method="post" action="item?id=<?php echo $itemid;?>">
        Item name
        <br>
        <input type="text" placeholder="My epic asset" name="itemname" id="itemname" required value="<?php echo $itemname;?>">
        <br>
        Description
        <br>
        <textarea type="textarea" placeholder="Nice shirt with alpha. Get good LSDBLOX street cred with this shirt." rows="4" cols="32" name="itemdesc" id="itemdesc"><?php echo $itemdesc;?></textarea>
        <br>
        <input type="checkbox" id="itempub" name="itempub" <?php if($public){echo"checked";}?>>
        <label for="itempub">On sale
        <br>
        Price
        <br>
        <input type="number" placeholder="0" name="itemprice" id="itemprice" required value="<?php echo $value;?>">
        <br>
        <input type="submit" value="Update" style="margin-top:1rem;">
    </form>
    <?php
    $msg = ob_get_clean();
    echo json_encode(['status' => 'success', 'message' => $msg,]);
    exit;
} 
ob_start();
?>
<span id="purchase-status-message"></span>
<div style="display:flex;align-items:center;flex-direction:column;"><div id="manage" style="width:100%;"></div>
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
        <div style="flex-direction:column;display:flex;">
            <span>
                Uploader:
                <a href="profile?id=<?php echo $owner;?>"><?php echo $ownername;?></a>
            </span>
            <span title="<?php echo date('jS l, F Y', (int)$itemupts);?>">Uploaded at <?php echo date("d-m-y",(int)$itemupts); ?></span>
            <span><?php if (!empty($itemdesc)) {
                echo $itemdesc;
            } else {
                echo "<em>Item has no description.</em>";
            }?></span>
        </div>
        <?php
        if ($public) {
            if ($value > 0) {
                echo "¥" . $value;
            } else {
                echo "Free";
            }
        } else {
            echo "Offsale";
        }
        ?>
        <div style="flex-direction:row;display:flex;">
        <?php if ($public) : ?>
        <button <?php if (!$owned) {echo "onclick=\"purchase($itemid)\"";} ?> style="background-color:var(<?php if ($owned) {echo "--primary-color";} else {echo "--good";} ?>);">
            <?php 
            if (!$owned) {
                echo "Get";
            } else {
                echo "Owned";
            }
            ?>
        </button>
        <?php endif; ?>
        <?php if ($owner == $uid): ?>
        <button onclick="promptmanage(<?php echo $itemid;?>)" style="background-color:var(--good);">
            Manage
        </button>
        <?php endif;?>
    </div> <?php } else { header("Location: 404.html"); }?>
<script>
function promptmanage(itemId) {
    const container = document.getElementById('manage');
    if (!itemId) {
        console.error("Item ID not found for management shit.");
        return;
    }
    this.disabled = true; // once manage panel is open, we dont want to spawn any new panels
    const postData = new URLSearchParams();
    postData.append('id', itemId);

    fetch('/item', {
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
            container.className = "border";
            container.innerHTML = data.message;
            console.log(data.message);
        } else {
            container.textContent = data.message;
            console.log('Auth failure:', data);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        container.textContent = 'Connection or Server Failure: ' + error.message;
        container.style.color = 'red';
    })
};

function purchase(itemId) {
    const statusMessage = document.getElementById('purchase-status-message');
    const amountPesos = document.getElementById('amountofmoney');
    if (!itemId) {
        console.error("Item ID not found for purchase.");
        return;
    }
    statusMessage.style.color = null;
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
            statusMessage.style.color = 'red';
            console.log('Purchase Failure Data:', data);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        statusMessage.textContent = 'Connection or Server Failure: ' + error.message;
        statusMessage.style.color = 'red';
    })
    .finally(() => {
        this.disabled = false;
    });
};
</script>
</div>
</div>
</div>
<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>