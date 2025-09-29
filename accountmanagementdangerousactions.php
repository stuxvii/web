<?php
require_once 'auth.php';

function guidv4($data = null) {
    $data = $data ?? random_bytes(64);
    assert(strlen($data) == 64);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
}

$uid = NULL;
$passwordhash = NULL;
$db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
$row = NULL;
$stmt = $db->prepare("
    SELECT id,pass
    FROM users 
    WHERE authuuid = :cookie
");

$stmt->bindValue(':cookie', $token, SQLITE3_TEXT);
$name = $stmt->execute();
$usernamevalidateregex = '/^[a-zA-Z0-9_]{3,20}$/';

if ($name) {
    $row = $name->fetchArray(SQLITE3_ASSOC);
    if ($row) {
    $uid = $row['id'];
    $passwordhash = $row['pass'];
    } else {
        header("Location: logout.php");
        exit;
    }
}
if ($uid == NULL) {
    header("Location: logout.php");
    exit;
}

$fetchsettings = $db->prepare("
SELECT appearance,movingbg,dispchar,sidebarid,sidebars
FROM config
WHERE id = :uid");

$fetchsettings->bindValue(":uid",$uid,SQLITE3_INTEGER);
$settings = $fetchsettings->execute();
$colorrow = $settings ? $settings->fetchArray(SQLITE3_ASSOC) : false;

$theme = (bool)$colorrow['appearance'];
$movebg = (bool)$colorrow['movingbg'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $candoaction = false;
    $rowsaffected = NULL;

    if (!password_verify($_POST['confirm'],$passwordhash)) { //fixed
        $msg = "The password confirmation<br>you inputted was invalid.";
    } else {
        $candoaction=true;
    }

    if (isset($_POST['username']) && $candoaction) {

        $newusername = trim($_POST['username']);
        if (preg_match($usernamevalidateregex,$newusername)){
            $stmtcheckusername = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = :un");
            $stmtcheckusername->bindValue(':un', $newusername, SQLITE3_TEXT);
            $result = $stmtcheckusername->execute();
            
            if ($result) {
                $row = $result->fetchArray(SQLITE3_ASSOC);
                $user_count = $row['count'];
            } else {
                echo "Internal error while checking username.";
                return;
            }

            if ($user_count > 0) {
                echo "The username '$newusername' is already taken.";
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
            }
        } else {
            $msg = "Your chosen username<br>is not valid.";
        }
    }
        
    if (isset($_POST['password']) && $candoaction) {

        $pass = $_POST['password'];
        if (strlen($pass) < 15) {
            $msg = "New password is not long<br>enough. Suggestion: 6 random<br>uncommon english words.";
        } else {
            $newpass = password_hash($pass,PASSWORD_BCRYPT);
            $updstmt = $db->prepare("
                UPDATE users 
                SET 
                    pass = :pass
                WHERE authuuid = :cookie
            ");
            
            $updstmt->bindValue(':pass', $newpass, SQLITE3_TEXT);
            $updstmt->bindValue(':cookie', $token, SQLITE3_TEXT);
            $updstmt->execute();
            $rowsaffected = $db->changes();
        }
    }

    if ($rowsaffected > 0) {
        $updstmt = $db->prepare("
            UPDATE users 
            SET 
                authuuid = :newcookie
            WHERE authuuid = :cookie
        ");
        
        $updstmt->bindValue(':newcookie', guidv4(), SQLITE3_TEXT);
        $updstmt->bindValue(':cookie', $token, SQLITE3_TEXT);
        $updstmt->execute();
        header('Location: logout.php', true, 303);
        exit;
    } else {
        if (!isset($msg)) {
        $msg = "Internal error.<br><em>report to<br>dev plox</em>";
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
                <input type="submit" value="Modify"> 
                <br>
                <?php if (!empty($msg)) { echo $msg; } ?>
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
                Only do this if you wish to fully
                <br>
                abandon the service, as you wont be let back in.
                <br>
                <br>
                You will be asked twice before 
                <br>
                your account is permanently wiped.
                <br>
                <br>
                <button onclick="location.href='deleteaccount'">bich</button>
            </div>
        </div>
        <?php
        $uid = null;
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
            $uid = $row['id'];
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