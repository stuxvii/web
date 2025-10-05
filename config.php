<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/auth.php";

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
    $dispchar = 0;
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
            $dispchar = (int)$_POST['displaychar'];
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

    $a = (int)$theme;
    $b = (int)$movebg;
    $c = (int)$dispchar;
    $d = (int)$sidebarid;
    $e = (int)$sidebars;
    $id = (int)$uid;

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
ob_start();
?>

        <div class="diva">

            <a href="/">Home page</a>
            <em>For your convenience, these <br>settings persist across devices.</em>
            
            <span id="status-message" style="margin-bottom: 15px; max-width:14em;"></span>
            
            <form id="plrform" method="post" action="<?php echo htmlspecialchars("config.php");?>">
                <hr>
                <span>Appearance</span>
                <hr>
                <input type="radio" id="dark" name="thememode" value="dark"<?php if(!$theme){echo"checked";}?>>
                <label for="dark">Dark</label>
                <br>
                <input type="radio" id="light" name="thememode" value="light"<?php if($theme){echo"checked";}?>>
                <label for="light">Light</label>
                <hr>
                <span>Site preferences</span>
                <hr>

                <label for="displaychar">Character style</label>
                <br>
                <select id="displaychar" name="displaychar" style="margin-top:6px;">
                    <option value="0" <?php if($dispchar==0){echo"selected";}?>>Hide</option>
                    <option value="1" <?php if($dispchar==1){echo"selected";}?>>2D</option>
                    <option value="2" <?php if($dispchar==2){echo"selected";}?>>3D</option>
                </select>
                <br>
                <input type="checkbox" id="movingbg" name="movingbg" <?php if($movebg){echo"checked";}?>>
                <label for="movingbg">Moving background</label>
                <br>
                <input type="checkbox" id="sidebars" name="sidebars" <?php if($sidebars){echo"checked";}?>>
                <label for="sidebars">Decorative sidebars<label>
                <br>
                <select id="sidebarid" name="sidebarid" style="margin-top:6px;">
                    <option value="1" <?php if($sidebarid==1){echo"selected";}?>>Day</option>
                    <option value="2" <?php if($sidebarid==2){echo"selected";}?>>Afternoon</option>
                    <option value="3" <?php if($sidebarid==3){echo"selected";}?>>Night</option>
                </select>
                <hr>
                <span><a href="accountmanagementdangerousactions">Account management</a></span>
                <hr>
                <input type="submit" value="Save">
            </form>
        </div>
        <script>
            const form = document.getElementById('plrform');
            const statusMessage = document.getElementById('status-message');

            form.addEventListener('submit', function(event) {
                event.preventDefault(); 
            
                statusMessage.textContent = '';
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
<?php
$page_content = ob_get_clean();

require_once $_SERVER['DOCUMENT_ROOT'] . "/template.php";
?>