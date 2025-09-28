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
$row = NULL;
$db->exec("
CREATE TABLE IF NOT EXISTS config (
    id INTEGER PRIMARY KEY,
    appearance BOOLEAN,
    movingbg BOOLEAN,
    sidebarid INTEGER,
    sidebars BOOLEAN
)");

$msg = "";
$cc = 0;
$theme = false;
$movebg = false;
$dispchar = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
}
if ($cc>0) {$msg = "$cc changes have been made.";}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../normalize.css">
        <link rel="stylesheet" href="../styles.css">
    </head>
    <body>
        <div class="divaleft">
            <form id="plrform" method="post" action="<?php echo htmlspecialchars("config");?>">
                <hr>
                <span>Appearance</span>
                <hr>
                <input type="radio" id="light" name="thememode" value="light">
                <label for="brightness">Light mode</label>
                <br>
                <input type="radio" id="dark" name="thememode" value="dark">
                <label for="brightness">Dark mode</label>
                <hr>
                <span>Site preferences</span>
                <hr>
                <input type="checkbox" id="movingbg" name="movingbg" checked>
                <label for="brightness">Moving background</label>
                <br>
                <input type="checkbox" id="displaychar" name="displaychar">
                <label for="brightness">Display your <br> character in the <br> main page</label>
                <hr>
                <span>Account management</span>
                <hr>
                <a href="">Change username</a> // unfinished
                <br>
                <a href="">Change password</a> // unfinished
                <br>
                <a href="">Erase account</a> // unfinished
                <br><br>
                <input type="submit" value="Save"> <?php echo $msg; ?>
            </form>
        </div>
        <?php
        $currentuid = null;
        $db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $avatarsdb = new SQLite3('avatars.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        
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
        if (isset($stmt)) $stmt->close();
        if (isset($insertAvatarStmt)) $insertAvatarStmt->close();
        $avatarsdb->close();
        $db->close();
        ?>
        </div>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>