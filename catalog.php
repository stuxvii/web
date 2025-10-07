<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';

$stmtgetitem = $db->prepare("
SELECT id, name, asset, owner, value, public, type
FROM items
WHERE approved = 1 AND type = 'Dec' ORDER BY id DESC
");
$stmtgetitem->execute();
$dec = $stmtgetitem->get_result();

$stmtgetitem = $db->prepare("
SELECT id, name, asset, owner, value, public, type
FROM items
WHERE approved = 1 AND type = 'Shr' ORDER BY id DESC
");
$stmtgetitem->execute();
$shr = $stmtgetitem->get_result();

$stmtgetitem = $db->prepare("
SELECT id, name, asset, owner, value, public, type
FROM items
WHERE approved = 1 AND type = 'Aud' ORDER BY id DESC
");
$stmtgetitem->execute();
$snd = $stmtgetitem->get_result();

ob_start();
?>
<div class="deadcenter">
    <span id="purchase-status-message"></span>
Decals
<div class="catalogitemborder">
    <?php
if ($dec->num_rows > 0) {
    while ($row = $dec->fetch_assoc()) {
        $id     = htmlspecialchars($row['id']);
        $itemname   = htmlspecialchars($row['name']);
        $owner  = htmlspecialchars($row['owner']);
        $value  = htmlspecialchars($row['value']);
        $public = htmlspecialchars($row['public']);
    ?>
    <div class='catalogitem' data-item-id="<?php echo $id;?>"> <div class='catalogitemasset'>
            <?php echo $itemname; ?>
                <img class="catalogitemimg" src="getfile?id=<?php echo $id;?>" height="128" >
            </div>
            <div class='catalogiteminfo'>
                <span>
                </span>
                <span>Uploader: <?php echo getuser($owner)['username'];?></span>
                <span>Price: <?php echo $value;?></span>
                <button class="purchase-button" data-item-id="<?php echo $id; ?>" style="background-color:var(--good); height:32px; width:6em;">Purchase</button>
            </div>
    
        </div>
<?php }} else {
    echo "No items up for sale.";
}?>
</div>
Shirts
<div class="catalogitemborder">
    <?php
if ($shr->num_rows > 0) {
    while ($row = $shr->fetch_assoc()) {
        $id     = htmlspecialchars($row['id']);
        $itemname   = htmlspecialchars($row['name']);
        $owner  = htmlspecialchars($row['owner']);
        $value  = htmlspecialchars($row['value']);
        $public = htmlspecialchars($row['public']);
    ?>
    <div class='catalogitem' data-item-id="<?php echo $id;?>"> <div class='catalogitemasset'>
            <?php echo $itemname; ?>
                <img class="catalogitemimg" src="getfile?id=<?php echo $id;?>" height="128" >
            </div>
            <div class='catalogiteminfo'>
                <span>
                </span>
                <span>Uploader: <?php echo getuser($owner)['username'];?></span>
                <span>Price: <?php echo $value;?></span>
                <button class="purchase-button" data-item-id="<?php echo $id; ?>" style="background-color:var(--good); height:32px; width:6em;">Purchase</button>
            </div>
    
        </div>
<?php }} else {
    echo "No items up for sale.";
}?>
</div>
Audios
<div class="catalogitemborder">
    <?php
if ($snd->num_rows > 0) {
    while ($row = $snd->fetch_assoc()) {
        $id     = htmlspecialchars($row['id']);
        $itemname   = htmlspecialchars($row['name']);
        $owner  = htmlspecialchars($row['owner']);
        $value  = htmlspecialchars($row['value']);
        $public = htmlspecialchars($row['public']);
    ?>
    <div class='catalogitem' data-item-id="<?php echo $id;?>"> <div class='catalogitemasset'>
            <?php echo $itemname; ?>
                <audio controls style='width:240px;height:60px;'> 
                    <source src="<?php echo "/getfile?id=" . $id;?>" type="audio/mpeg">
                </audio>
            </div>
            <div class='catalogiteminfo'>
                <span>
                </span>
                <span>Uploader: <?php echo getuser($owner)['username'];?></span>
                <span>Price: <?php echo $value;?></span>
                <button class="purchase-button" data-item-id="<?php echo $id; ?>" style="background-color:var(--good); height:32px; width:6em;">Purchase</button>
            </div>
    
        </div>
<?php }} else {
    echo "No items up for sale.";
}?>
</div>
</div>
<script> 
document.addEventListener('DOMContentLoaded', () => {
    const purchaseButtons = document.querySelectorAll('.purchase-button');
    const scrollContainer = document.querySelectorAll('.catalogitemborder');
    const statusMessage = document.getElementById('purchase-status-message');
    const amountPesos = document.getElementById('amountofmoney');
    scrollContainer.forEach(container => {
            container.addEventListener('wheel', (event) => {
                event.preventDefault();
                container.scrollBy({
                    left: event.deltaY,
                    top: 0
                }
            );
        }
    )})
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
        });
    });
});
</script>
<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>