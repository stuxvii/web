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
$curpasshash = NULL;
$db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
$row = NULL;
$stmt = $db->prepare("
    SELECT id,pass
    FROM users 
    WHERE authuuid = :cookie
");

$stmt->bindValue(':cookie', $_COOKIE['auth'], SQLITE3_TEXT);
$name = $stmt->execute();
$usernamevalidateregex = '/^[a-zA-Z0-9_]{3,20}$/';

if ($name) {
    $row = $name->fetchArray(SQLITE3_ASSOC);
    $currentuid = $row['id'];
    $curpasshash = $row['pass'];
}
$fetchsettings = $db->prepare("
SELECT appearance,movingbg,dispchar,sidebarid,sidebars
FROM config
WHERE id = :uid");

$fetchsettings->bindValue(":uid",$currentuid,SQLITE3_INTEGER);
$settings = $fetchsettings->execute();
$colorrow = $settings ? $settings->fetchArray(SQLITE3_ASSOC) : false;

$theme = (bool)$colorrow['appearance'];
$movebg = (bool)$colorrow['movingbg'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (password_verify(!$_POST['confirm'],$curpasshash)) {die();}
    if (isset($_POST['username'])) {

        $newusername = trim($_POST['username']);
        if (preg_match($usernamevalidateregex,$newusername)){
            $msg = "$newusername was valid. proceed with the rest";
            
            $stmtcheckusername = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = :un");
            $stmtcheckusername->bindValue(':un', $newusername, SQLITE3_TEXT);
            $result = $stmtcheckusername->execute();
            
            if ($result) {
                $row = $result->fetchArray(SQLITE3_ASSOC);
                $user_count = $row['count'];
            } else {
                echo error("Internal error while checking username.");
                return;
            }

            if ($user_count > 0) {
                echo error("The username '$newusername' is already taken.");
            } else {
                
                $updstmt = $db->prepare("
                    UPDATE users 
                    SET 
                        username = :newuser
                    WHERE authuuid = :cookie
                ");
                
                $updstmt->bindValue(':newuser', $newusername, SQLITE3_TEXT);
                $updstmt->bindValue(':cookie', $token, SQLITE3_TEXT);
                $updstmt->execute();
                $rowsaffected = $db->changes();

                if ($rowsaffected > 0) {
                    header("Location: logout.php"); // succ
                    exit;
                } else {
                    echo error("Internal error.<br><em>report to<br>dev plox</em>");
                }
            }
        } else {
            $msg = password_verify(!$_POST['confirm'],$curpasshash);
        }
    }

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
        <div class="divaleft">
            <form id="plrform" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
                <span>New username</span>
                <hr>
                <input type="username" id="username" name="username" maxlength="20">
                <br>
                (3-20 chars, a-z/0-9/_)
                <br>
                <br>
                <span>New password</span>
                <hr>
                <input type="password" id="password" name="password">
                <br>
                (15 characters minimum)
                <br>
                <br>
                <br>
                Changing your credentials <br>is going to log you out &
                <br>
                force you to log back in.
                <br><br>
                Use your current password <br>to authorize any changes.
                <hr>
                <br>
                <span>Password confirmation</span>
                <br>
                <input type="password" id="confirm" name="confirm">
                <br>
                <br>
                <input type="submit" value="Change"> 
                <br>
                <?php echo $msg; ?>
            </form>
        </div>
        <div class="divaright">
            <div>
                Danger zone
                <hr>
                This section makes the option of 
                <br>
                permanently erasing your
                <br>
                account available to you.
                <br>
                <br>
                This process is completely irreversible, 
                <br>
                and once activated is unable to be reverted.
                <br>
                <br>
                You will be asked twice before 
                <br>
                your account is permanently wiped.
                <br>
                <br>
                <input type="submit" value="Delete your account">
            </div>
        </div>
        <?php
        $currentuid = null;
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
        if (isset($stmt)) $stmt->close();
        if (isset($insertAvatarStmt)) $insertAvatarStmt->close();
        $db->close();
        ?>
        </div>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>