<?php
$token = $_COOKIE['auth'] ?? '';
if (!isset($token)) {
    header("Location: index.php");
}
if (!preg_match('/^[0-9a-f]{32}$/', $token)) {
    header("Location: logout.php");
    exit;
}
$currentuid = NULL;
$db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

$stmt = $db->prepare("
    SELECT id
    FROM users 
    WHERE authuuid = :cookie
");

$stmt->bindValue(':cookie', $_COOKIE['auth'], SQLITE3_TEXT);
$name = $stmt->execute();

if ($name) {
    $row = $name->fetchArray(SQLITE3_ASSOC);
    $currentuid = $row['id'];
}

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
    $cc = 0;

    $theme = false;
    $movebg = false;
    $dispchar = false;
    if (isset($_POST['thememode'])) {
        if ($_POST['thememode'] == "light") {
            $theme = true;
            $cc += 1;
        } else {
            $theme = false;
            $cc += 1;
        }
    }
    if (isset($_POST['movingbg'])) {
        if ($_POST['movingbg']) {
            $movebg = true;
            $cc += 1;
        }
    }
    if (isset($_POST['displaychar'])) {
        if ($_POST['displaychar']) {
            $dispchar = true;
            $cc += 1;
        }
    }

    $updstmt = $db->prepare("
    UPDATE config 
    SET 
        appearance = :a,
        movingbg = :b,
        dispchar = :c
    WHERE id = :id
    ");
    $updstmt->bindValue(':a', (int)$theme, SQLITE3_INTEGER);
    $updstmt->bindValue(':b', (int)$movebg, SQLITE3_INTEGER);
    $updstmt->bindValue(':c', (int)$dispchar, SQLITE3_INTEGER);
    $updstmt->bindValue(':id', (int)$currentuid, SQLITE3_INTEGER);

    $updstmt->execute();
    header('Location: config.php', true, 303); 
}

$fetchsettings = $db->prepare("
SELECT appearance,movingbg,dispchar,sidebarid,sidebars
FROM config
WHERE id = :uid");

$fetchsettings->bindValue(":uid",$currentuid,SQLITE3_INTEGER);
$settings = $fetchsettings->execute();
$prefrow = $settings ? $settings->fetchArray(SQLITE3_ASSOC) : false;

$theme = (bool)$prefrow['appearance'];
$movebg = (bool)$prefrow['movingbg'];
$dispchar = (bool)$prefrow['dispchar'];

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
            <em>For your convinenience, <br>these settings persist across devices.</em>
            <form id="plrform" method="post" action="<?php echo htmlspecialchars("config");?>">
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
                <hr>
                <span><a href="accountmanagementdangerousactions">Account management</a></span>
                <hr>
                <input type="submit" value="Save">
            </form>
        </div>
        <?php
        ?>
        </div>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>