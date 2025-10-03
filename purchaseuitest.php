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
        <form id="plrform" method="post" action="<?php echo htmlspecialchars("purchase.php");?>">
                <input type="number" placeholder="id number goes here" name="itemid" id="itemid" required>
                <input type="submit" value="Buy">
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
            
                statusMessage.textContent = 'Buying.';
                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');
            fetch(actionUrl, {
                method: 'POST',
                body: formData
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
                    statusMessage.textContent = data.message || 'Item purchased successfully!';
                    statusMessage.style.color = 'green';
                } else {
                    statusMessage.textContent = data.message || 'Purchase failed with an unknown error. You have not been charged.';
                    statusMessage.style.color = 'orange';
                    console.log(data);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                statusMessage.textContent = 'Connection or Server Failure: ' + error.message;
            });
            });
        </script>
    </body>
</html>