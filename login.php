<?php
if (isset($_COOKIE['auth'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <link rel="stylesheet" href="../styles.css">
    </head>
    <body>
<?php
$db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

// Create the table if it doesn't exist (for some godforsaken reason i.e wiped or deleted)
$db->exec("
    CREATE TABLE IF NOT EXISTS users (
        username TEXT,
        pass TEXT,
        discordtag TEXT,
        key TEXT,
        timestamp TEXT,
        authuuid TEXT
    )
");

$usernamevalidateregex = '/^[a-zA-Z0-9_]{3,20}$/';

function error($reason) {
    return "<img src=\"error.png\"><span class=\"info\">$reason</span>";
}

function login($un,$pass) {
    global $usernamevalidateregex;
    global $db;

    if ($un === '' || $pass === '') {
        echo error("Password and username, you dingus.");
        return;
    }

    if (!preg_match($usernamevalidateregex, $un)) {
        echo error("The username '$un' is invalid.");
        return;
    }

    $stmtcheckusername = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = :un");
    $stmtcheckusername->bindValue(':un', $un, SQLITE3_TEXT);
    $result = $stmtcheckusername->execute();
    
    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $user_count = $row['count'];
    } else {
        echo error("Internal error while checking username.");
        return;
    }

    if ($user_count > 0) {
        $stmt = $db->prepare("
            SELECT pass
            FROM users 
            WHERE username = :user
        ");
        
        $stmt->bindValue(':user', $un, SQLITE3_TEXT);

        $storedpass = $stmt->execute();

        if ($storedpass) {
            while ($row = $storedpass->fetchArray(SQLITE3_ASSOC)) {
                $hashpw = password_verify($pass,$row['pass']);
                if ($hashpw === true) {
                    $stmt = $db->prepare("
                        SELECT authuuid
                        FROM users 
                        WHERE username = :user
                    ");
                    
                    $stmt->bindValue(':user', $un, SQLITE3_TEXT);
                    $auth = $stmt->execute();
                    if ($auth) {
                        while ($row = $auth->fetchArray(SQLITE3_ASSOC)) {
                            setcookie('auth', $row['authuuid'], time() + (86400 * 30), "/", "acdbx.top", true, true);
                            header("Location: index.php");
                            exit;
                        }
                    }
                } else {
                    echo error("BOOO wrong password try again");
                }
            }
        }
    } else {
        echo error("The username '$un' is not valid.");
    }
}
?>
        <div class="diva">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                Username: <input type="text" name="name">
                <br>
                <br>
                Password: <input type="password" name="pass">
                <br>
                <br>
                <input type="submit" name="submit" value="Login"><em> (this action may save a cookie to your device)</em>
            </form>
            <br>
            <a href="resetpassword.php">Forgot your password?</a>
            <br>
            <div class="msgbox">
                <br>
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    login(trim($_POST['name']),$_POST['pass']);
                }
            ?>
        </div>
        </div>

        <script>
            const tt=[];let ci=0;for(let e=0;e<9;e++){const t="▁▂▃▄▅▄▃▂".slice(e)+"▁▂▃▄▅▄▃▂".slice(0,e);tt.push(t)}document.addEventListener("DOMContentLoaded",(function(){setInterval((()=>{document.title="login"+tt[ci],ci=ci=(ci+1)%tt.length}),400)}));
        </script>
    </body>
</html>