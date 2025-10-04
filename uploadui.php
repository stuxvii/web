<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../normalize.css">
        <link rel="stylesheet" href="../styles.css">
        <?php
            if ($theme) {
                echo "<style>:root{--primary-color: #fff;--secondary-color: #000;--bgimg: url(\"cargonetlight.bmp\");}</style>";
            }
            if (!$movebg) {
                echo "<style>body{animation: none;}</style>";
            }
        ?>
    </head>
    <body>
    <div class="content">
        <?php 
        global $sidebarid;
        global $sidebars;
        require "sidebars.php";
        ?>
        <div class="diva">
        <a href="/">Home</a>
        <br>
            <div class="border">
                <span id="status-message">Wilkommen.</span>
                <form id="plrform" method="post" action="<?php echo htmlspecialchars("upload.php");?>" enctype="multipart/form-data">
                    <input type="file" id="filetoupload" name="filetoupload" required>
                    <br>
                    Name
                    <br>
                    <input type="text" placeholder="My epic asset" name="itemname" id="itemname" required>
                    <br>
                    Price
                    <br>
                    <input type="number" placeholder="0" name="itemprice" id="itemprice" required>
                    <br>
                    <select id="type" name="type" style="margin-top:6px;">
                        <option value="Dec" <?php if($sidebarid==1){echo"selected";}?>>Decal</option>
                        <option value="Aud" <?php if($sidebarid==2){echo"selected";}?>>Audio</option>
                        <option value="Shr" <?php if($sidebarid==3){echo"selected";}?>>Shirt</option>
                    </select>
                    <br>
                    <input type="submit" value="Upload">
                </form>
            </div>
        </div>
        <?php
        global $sidebarid;
        global $sidebars;
        $rightside = true;
        require "sidebars.php";
        ?>
    </div>
    <script src="../titleanim.min.js"></script>
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
    </body>
</html>