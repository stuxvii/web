<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
ob_start();
?>
<div class="deadcenter">
    <div class="border" style="max-width:50%;align-items:start;">
        lsdblox is a fully free project, and it will forever remain that way.
        <br>
        What is not free however, is hosting. I will not force anybody to pay money
        <br>
        to access lsdblox, and, asides from donations, money to host this comes
        <br>
        mostly out of my pocket. I cannot always assure that i will be able to 
        <br>
        keep lsdblox afloat, so if you are in the position to do so, 
        <br>
        and can afford it, please consider donating money.

        <br>
        <br>
        <div style="display:flex;flex-direction:row;width:100%;justify-content: space-between;">
            <div style="display:flex;flex-direction:column;">
                <a href="https://buymeacoffee.com/acidbox" target="_blank">Go to my buy me a coffee</a>
                <button onclick="copy();" id="copybutton">Copy Monero address (More reliable)</button>
            </div>            
            <div style="display:flex;flex-direction:column;">
                Generous donators
                <ul>
                    <li>edenco (uid:2) 14$</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
const walletaddress = "45GnxFz5K5mXMf3Fs96ZMtReEeCimF8JUJoVbaEvm2sLUtS4UJAqyUME7c3DBhef3MJ45oPNDSzD2T9ijmzVS4YFEKzfqAC";
let btn = document.getElementById("copybutton");
async function copy() {
    await navigator.clipboard.writeText(walletaddress);
    const btntext = btn.textContent;
    btn.textContent = "Thank you!";
    setTimeout(() => {
        btn.textContent = btntext;
    }, 3000);
}
</script>
<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>