<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';

$stmtcheckitem = $db->prepare("
SELECT id, name, asset, owner, value, public, type
FROM items
WHERE approved = 1
");
$stmtcheckitem->execute();
$result = $stmtcheckitem->get_result();
ob_start();
?>
<div class="deadcenter">
    <a href="/">Home</a>
    <span id="purchase-status-message">Welcome to the catalog!</span>
<div class="itemborder">
    <?php
if ($result->num_rows > 0) {
    ?>
    <?php
    while ($row = $result->fetch_assoc()) {
        $id     = htmlspecialchars($row['id']);
        $name   = htmlspecialchars($row['name']);
        $owner  = htmlspecialchars($row['owner']);
        $value  = htmlspecialchars($row['value']);
        $public = htmlspecialchars($row['public']);
        $type   = htmlspecialchars($row['type']);
    ?>
    <style>
        .catalogitemimg {
            max-height: 128px;
            max-width: 128px;
            width: auto;
            height: auto;
        }
    </style>
    <div class='catalogitem' data-item-id="<?php echo $id;?>"> <div class='catalogitemasset'>
            <?php echo $name; ?>
            <?php
            if ($type == "Shr" || $type == "Dec") {
                ?>
                <img class="catalogitemimg" src="getfile?id=<?php echo $id;?>" height="128" >
                <?php
            } else if ($type == "Aud") {
                ?>
                <audio controls style='width:240px;height:60px;'> 
                    <source src="<?php echo "/getfile?id=" . $id;?>" type="audio/mpeg">
                </audio>
                <?php
            }
            ?>
            </div>
            <div class='catalogiteminfo'>
                <span>
                <?php 
                switch ($type) { 
                    case "Aud":  echo "An audio "; break;
                    case "Dec":  echo "A decal "; break;
                    case "Shr":  echo "A T-Shirt ";    break;
                    default: echo "An asset ";
                }
                ?>
                </span>
                <span>Uploader: <?php echo getuser($owner)['username'];?></span>
                <span>Price: <?php echo $value;?></span>
                <button class="purchase-button" data-item-id="<?php echo $id; ?>" style="background-color:var(--good); height:32px; width:6em;">Purchase</button>
            </div>
    
        </div>
<?php } ?>

<script> 
document.addEventListener('DOMContentLoaded', () => {
    const purchaseButtons = document.querySelectorAll('.purchase-button');
    const statusMessage = document.getElementById('purchase-status-message');

    purchaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id')
            
            if (!itemId) {
                console.error("Item ID not found for purchase.");
                return;
            }
            statusMessage.textContent = `Attempting to purchase item ${itemId}...`;
            statusMessage.style.color = 'black';
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
        });
    });
});
</script>
<?php } else {
    echo "No items up for sale.";
}
echo "</div>";
echo "</div>";
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>