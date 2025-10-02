<?php
require_once "auth.php";

if ($uid === null || $db === null) {
    http_response_code(500);
    die("Authentication Error..");
}

$db->query("
CREATE TABLE IF NOT EXISTS config (
    id INT PRIMARY KEY,
    appearance TINYINT(1) NOT NULL DEFAULT 0,
    dispchar TINYINT(1) NOT NULL DEFAULT 1,
    movingbg TINYINT(1) NOT NULL DEFAULT 1,
    sidebarid INT NOT NULL DEFAULT 1,
    sidebars TINYINT(1) NOT NULL DEFAULT 0
)");

$newconf = $db->prepare("
    INSERT IGNORE INTO config (id) VALUES (?)
");
$newconf->bind_param('i', $uid);
$newconf->execute();
$newconf->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $theme = false;
    $movebg = false;
    $dispchar = false;
    $sidebars = false;

    if (isset($_POST['thememode'])) {
        if ($_POST['thememode'] == "light") {
            $theme = true;
        } else {
            $theme = false;
        }
    }
    if (isset($_POST['movingbg'])) {
        if ($_POST['movingbg']) {
            $movebg = true;
        }
    }
    if (isset($_POST['displaychar'])) {
        if ($_POST['displaychar']) {
            $dispchar = true;
        }
    }
    if (isset($_POST['sidebars'])) {
        if ($_POST['sidebars']) {
            $sidebars = true;
        }
    }
    if (isset($_POST['sidebarid'])) {
        if ($_POST['sidebarid']) {
            $sidebarid = (int)$_POST['sidebarid'];
        }
    }

    $updstmt = $db->prepare("
    UPDATE config
    SET
        appearance = ?,
        movingbg = ?,
        dispchar = ?,
        sidebarid = ?,
        sidebars = ?
    WHERE id = ?
    ");

    $updstmt = $db->prepare("
    UPDATE config
    SET
        appearance = ?,
        movingbg = ?,
        dispchar = ?,
        sidebarid = ?,
        sidebars = ?
    WHERE id = ?
    ");

    // --- FIX IS HERE: Convert all necessary variables to integers ---

    // Casting booleans to integers (0 or 1) for the database TINYINT(1) field.
    $a = (int)$theme;
    $b = (int)$movebg;
    $c = (int)$dispchar;
    $d = (int)$sidebarid; // Already guaranteed to be int, but for consistency
    $e = (int)$sidebars;
    $id = (int)$uid; // Ensure UID is treated as an integer

    // Now bind the new variables ($a, $b, $c, $d, $e, $id) by reference
    $updstmt->bind_param('iiiiii', 
        $a, 
        $b, 
        $c, 
        $d, 
        $e, 
        $id
    );

    $success = $updstmt->execute();
    $updstmt->close();
    header('Content-Type: application/json');
    if ($success) {
        echo json_encode(['status' => 'success', 'message' => "Settings saved successfully! (refresh to apply)"]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save settings.']);
    }
    exit;
}
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
                echo "<style>body{animation-name: none;}</style>";
            }
        ?>
    </head>
    <body>
        <div class="content">

        <?php 
        require "sidebars.php";
        ?>
        <div class="diva">
            <em>For your convenience, these <br>settings persist across devices.</em>
            
            <span id="status-message" style="margin-bottom: 15px; max-width:14em;"></span>
            
            <form id="plrform" method="post" action="<?php echo htmlspecialchars("config.php");?>">
                <hr>
                <span>Appearance</span>
                <hr>
                <input type="radio" id="light" name="thememode" value="light"<?php if($theme){echo"checked";}?>>
                <label for="brightness">Light mode</label>
                <br>
                <input type="radio" id="dark" name="thememode" value="dark"<?php if(!$theme){echo"checked";}?>>
                <label for="brightness">Dark mode</label>
                <hr>
                <span>Site preferences</span>
                <hr>
                <input type="checkbox" id="movingbg" name="movingbg" <?php if($movebg){echo"checked";}?>>
                <label for="brightness">Moving background</label>
                <br>
                <input type="checkbox" id="displaychar" name="displaychar" <?php if($dispchar){echo"checked";}?>>
                <label for="displaychar">Display your  character in the <br> main page</label>
                <br>
                <input type="checkbox" id="sidebars" name="sidebars" <?php if($sidebars){echo"checked";}?>>
                <label for="sidebars">Decorative sidebars<label>
                <br>
                <input type="radio" id="1" name="sidebarid" value="1" <?php if($sidebarid==1){echo"checked";}?>>
                <label for="1">Day</label><br>

                <input type="radio" id="2" name="sidebarid" value="2" <?php if($sidebarid==2){echo"checked";}?>>
                <label for="2">Afternoon</label><br>

                <input type="radio" id="3" name="sidebarid" value="3" <?php if($sidebarid==3){echo"checked";}?>>
                <label for="3">Night</label>
                <hr>
                <span><a href="accountmanagementdangerousactions">Account management</a></span>
                <hr>
                <input type="submit" value="Save">
            </form>
        </div>
        <div class="rite">
                <a href="/">Home</a></div>
        <?php 
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
            
                statusMessage.textContent = '';
                statusMessage.style.color = 'black';
                statusMessage.textContent = 'Saving...';
                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok. Status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        statusMessage.style.color = 'green';
                        statusMessage.textContent = data.message;
                        
                    } else {
                        statusMessage.style.color = 'red';
                        statusMessage.textContent = data.message || 'An unknown error occurred.';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    statusMessage.style.color = 'red';
                    statusMessage.textContent = 'Failed to connect to server: ' + error.message;
                })
            });
        </script>
    </body>
</html>