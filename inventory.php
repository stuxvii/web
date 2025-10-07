<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
$status = 'success';
$msg = 'Equipped';
if (isset($_POST['itemid'])) {
    echo json_encode([
        'status' => $status,
        'message' => $_POST['itemid']
    ]);
    exit;
}
ob_start();
$oldinv = json_decode($inv);
$inv = array_reverse($oldinv);
?>
<div class="deadcenter">
<span><a href="/">Home</a> -- Your inventory</span>
<div class="itemborder">
    <?php

    if (!empty($inv)) {
        foreach ($inv as $v) {
            $stmtcheckitem = $db->prepare('
        SELECT approved, name, asset, owner, value, public, type
        FROM items
        WHERE id = ?
        ');
            $stmtcheckitem->bind_param('i', $v);
            $stmtcheckitem->execute();
            $result = $stmtcheckitem->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $iteminfo = [];
                foreach ($row as $key => $value) {
                    $iteminfo[$key] = htmlspecialchars($value);
                }
            }
            ?>
    <div class='item' id="<?php echo $v; ?>">
        <div class='iteminfo'>
            <?php echo $iteminfo['name'];
            echo ' - id:' . $v ?>
            <br> By 
            <?php echo getuser($iteminfo['owner'])['username']; ?>
            </div>
            <div style="display:flex;flex-direction:row;">
                <?php
                if ($iteminfo['type'] == 'Shr' || $iteminfo['type'] == 'Dec') {?>
                    <img src="getfile?id=<?php echo $v; ?>" height="128" >
                <?php if ($iteminfo['type'] == 'Shr'): ?>
                    <button class="equip-button" data-item-id="<?php echo $v; ?>" style="background-color:var(--good); height:32px; width:4em;">Equip</button>
                <?php endif; } else if ($iteminfo['type'] == 'Aud') { ?>
                <audio controls> 
                    <source src="<?php echo '/getfile?id=' . $v; ?>" type="audio/mpeg">
                </audio>
                <?php
                } else if (!$iteminfo['type']) {
                    echo "Asset rejected.";
                }
                ?>
            </div>
        </div>
<script> 
document.addEventListener('DOMContentLoaded', () => {
    const purchaseButtons = document.querySelectorAll('.equip-button');

    purchaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id')
            const state = this;
            
            if (!itemId) {
                console.error("Item ID not found for purchase.");
                return;
            }
            this.disabled = true;
            const postData = new URLSearchParams();
            postData.append('itemid', itemId);

            fetch('/inventory', {
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
                    console.log(data.message);
                    state.innerHtml = data.message;
                } else {
                    state.innerHtml = data.message || 'error';
                    console.log('Purchase Failure Data:', data);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                state.innerHtml = 'error' + error.message;
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
});
</script>
<?php
        }
    } else {
        echo 'No items in your inventory.';
    }
    echo '</div>';
    echo '</div>';
    $page_content = ob_get_clean();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
    ?>