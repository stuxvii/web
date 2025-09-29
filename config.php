<?php
require_once "auth.php";

$db->exec("
CREATE TABLE IF NOT EXISTS config (
    id INTEGER PRIMARY KEY,
    appearance BOOLEAN NOT NULL DEFAULT FALSE,
    dispchar BOOLEAN NOT NULL DEFAULT TRUE,
    movingbg BOOLEAN NOT NULL DEFAULT TRUE,
    sidebarid INTEGER NOT NULL DEFAULT 1,
    sidebars BOOLEAN NOT NULL DEFAULT FALSE
)");

$newconf = $db->prepare("
    INSERT OR IGNORE INTO config (id) VALUES (:uid)
");
$newconf->bindValue(':uid', $currentuid, SQLITE3_TEXT);
$ncresult = $newconf->execute();

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
    $updstmt = $db->prepare("
    UPDATE config
    SET
        appearance = :a,
        movingbg = :b,
        dispchar = :c,
        sidebars = :d
    WHERE id = :id
    ");
    $updstmt->bindValue(':a', (int)$theme, SQLITE3_INTEGER);
    $updstmt->bindValue(':b', (int)$movebg, SQLITE3_INTEGER);
    $updstmt->bindValue(':c', (int)$dispchar, SQLITE3_INTEGER);
    $updstmt->bindValue(':d', (int)$sidebars, SQLITE3_INTEGER);
    $updstmt->bindValue(':id', (int)$uid, SQLITE3_INTEGER); 

    $success = $updstmt->execute();
    header('Content-Type: application/json');
    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully! (refresh to apply)']);
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
        <div class="diva">
            <em>For your convinenience, <br>these settings persist<br>across devices.</em>
            
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
                <label for="displaychar">Display your <br> character in the <br> main page</label>
                <br>
                <input type="checkbox" id="sidebars" name="sidebars" <?php if($sidebars){echo"checked";}?>>
                <label for="sidebars">Decorative sidebars<label>
                <hr>
                <span><a href="accountmanagementdangerousactions">Account management</a></span>
                <hr>
                <input type="submit" value="Save">
            </form>
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