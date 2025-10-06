<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
$payoutamount = 100;
$curtime = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($stipend) {
        $stmtpaymoney = $db->prepare("
        UPDATE economy
        SET money = money + ? 
        WHERE id = ?
        ");
        $stmtpaymoney->bind_param('ii', $payoutamount, $uid);
        $stmtpaymoney->execute();
        $stmtpaymoney->close();

        $stmtsetlastclaim = $db->prepare("
        UPDATE economy
        SET lastbuxclaim = ? 
        WHERE id = ?
        ");
        $stmtsetlastclaim->bind_param('ii', $curtime, $uid);
        $stmtsetlastclaim->execute();
        $stmtsetlastclaim->close();

        echo json_encode([
            'status' => 'success',
            'money' => $money + $payoutamount
        ]);
        die();
    }
}

ob_start();
if ($authsuccessful):
?>
<div style="display:flex;justify-content:center;flex-direction:column;align-items:center;">
<img class="bounce" src="processing.png" id="speen">
<?php
if ($stipend):
    ?>

<span id="stipend">
    You may now <em id="claim" style="text-decoration: underline;background-color:var(--good);border-radius:2px;">claim</em> your stipend
</span>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const stipend = document.getElementById('stipend');
    const button = document.getElementById('claim');
    const moneys = document.getElementById('amountofmoney');

        button.addEventListener('click', function() {
            fetch('/index', {
                method: 'POST',
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
                    stipend.remove();
                    console.log(data.money);
                    moneys.textContent = data.money;
                } else {
                    console.log('Fail:', data);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
</script>
<?php
endif;
?>
</div>
<?php
endif;

$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>