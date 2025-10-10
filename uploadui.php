<?php
require_once 'auth.php';
if (!$authsuccessful) {
    header("Location: logout.php");
    exit;
}
ob_start();
?>
<div class="deadcenter" style="justify-content: center;">
    <div class="border" style="padding:15px;">
        <span id="status-message"></span>
        <form id="plrform" method="post" action="<?php echo htmlspecialchars("upload.php");?>" enctype="multipart/form-data">
            <input type="file" id="filetoupload" name="filetoupload" required>
            <select id="type" name="type" style="margin-top:6px;">
                <option value="Dec" <?php if(isset($sidebarid) && $sidebarid==1){echo"selected";}?>>Decal</option>
                <option value="Aud" <?php if(isset($sidebarid) && $sidebarid==2){echo"selected";}?>>Audio</option>
                <option value="Shr" <?php if(isset($sidebarid) && $sidebarid==3){echo"selected";}?>>T-Shirt</option>
            </select>
            <br>
            Name
            <br>
            <input type="text" placeholder="My epic asset" name="itemname" id="itemname" required>
            <br>
            Description
            <br>
            <textarea type="textarea" placeholder="Nice shirt with alpha. Get good LSDBLOX street cred with this shirt." rows="4" cols="16" name="itemdesc" id="itemdesc"></textarea>
            <br>
            Price
            <br>
            <input type="number" placeholder="0" name="itemprice" id="itemprice" required>
            <br>
            <input type="submit" value="Upload" style="margin-top:15px;">
        </form>
    </div>
</div>
<script>
    const form = document.getElementById('plrform');
    const statusMessage = document.getElementById('status-message');

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        statusMessage.textContent = 'Uploading...';
        const formData = new FormData(form);
        const actionUrl = form.getAttribute('action');
        fetch(actionUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const isOk = response.ok; 
            const status = response.status;
            return response.text().then(text => ({ 
                isOk, 
                status, 
                text 
            }));
        })
        .then(({ isOk, status, text }) => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                if (!isOk) {
                    throw new Error(`Server returned status ${status}. Non-JSON response: ${text.substring(0, 100)}...`);
                }
                throw new Error('Received non-JSON response from server.');
            }
            
            if (!isOk) {
                throw new Error(data.message || `Server error occurred with status ${status}.`);
            }

            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                form.reset()
                statusMessage.textContent = data.message || 'Item uploaded successfully!';
                statusMessage.style.color = 'green';
            } else {
                statusMessage.textContent = data.message || 'Upload failed with an unknown error.';
                statusMessage.style.color = 'orange';
                console.log(data);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            statusMessage.textContent = 'Upload Failed: ' + error.message;
            statusMessage.style.color = 'red';
        });
    });
</script>
<?php
$page_content = ob_get_clean();
require 'template.php'; 
?>