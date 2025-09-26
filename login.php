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

function login($un, $pass) {
    global $usernamevalidateregex;
    global $db;

    $invalid = "The credentials you provided are invalid."; // We really shouldn't give like.. yk.. clues to people if they've guessed a username or not.

    if ($un === '' || $pass === '') {
        echo error("Password and username, you dingus."); 
        return;
    }

    if (!preg_match($usernamevalidateregex, $un)) {
        echo error($invalid);
        return;
    }

    $stmt = $db->prepare("SELECT pass, authuuid FROM users WHERE username = :un");
    $stmt->bindValue(':un', $un, SQLITE3_TEXT);
    $result = $stmt->execute();

    $user_data = $result ? $result->fetchArray(SQLITE3_ASSOC) : false;

    if ($user_data && password_verify($pass, $user_data['pass'])) {

        setcookie('auth', $user_data['authuuid'], time() + (86400 * 30), "/", "acdbx.top", true, true);
        header("Location: index.php");
        exit;
        
    } else {
        echo error($invalid);
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