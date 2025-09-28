
<?php
if (isset($_COOKIE['auth'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>[ [ [ ACDBX.TOP ] ] ] if u enabled javascript this would be a cool asf animation</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
<?php

$db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

$usernamevalidateregex = '/^[a-zA-Z0-9_]{3,20}$/';

function guidv4($data = null) { // ctrl+c ctrl+v (idfk what this is)
    $data = $data ?? random_bytes(24);
    assert(strlen($data) == 24);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
}

function error($reason) {
    return "<img src=\"error.png\"><span class=\"info\">$reason</span>";
}

function registernvalidate($un,$key,$pass,$confirmpass,$tag) {
    global $usernamevalidateregex;
    global $db;
    if ($key === '' || $un === '' || $pass === '' || $tag === '') {
        echo error("An invitation key, Discord username, site username, and password are required.");
        return;
    }

    if (strlen($pass) < 15) {
        echo error("Password is not long enough. Suggestion: 6 random uncommon english words.");
        return;
    }

    if ($pass != $confirmpass) {
        echo error("Passwords do not match.");
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
        echo error("The username '$un' is already taken.");
    } else {
        
        $stmt = $db->prepare("
            UPDATE users 
            SET 
                username = :newuser,
                pass = :newpass,
                discordtag = :newdctag,
                timestamp = :curunix,
                authuuid = :newuuid
            WHERE key = :pkey AND (username IS NULL OR username = '')
        ");
        $hashpw = password_hash($pass, PASSWORD_BCRYPT);
        
        $stmt->bindValue(':newuser', $un, SQLITE3_TEXT);
        $stmt->bindValue(':newpass', $hashpw, SQLITE3_TEXT);
        $stmt->bindValue(':newdctag', $tag, SQLITE3_TEXT);
        $stmt->bindValue(':pkey', $key, SQLITE3_TEXT);
        $stmt->bindValue(':curunix', time(), SQLITE3_TEXT);
        $stmt->bindValue(':newuuid', guidv4(), SQLITE3_TEXT);
        $stmt->execute();
        $rowsaffected = $db->changes();

        if ($rowsaffected > 0) {
            header("Location: login.php");
            exit;
        } else {
            echo error("Failed to register. The key may be invalid or already used.");
        }
    }
}
?>
        <div class="diva">
            <div id="deleteifsuccess">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

                    Username: 
                    <br>
                    <input type="text" name="name">
                    <br>
                    (3-20 chars, a-z/0-9/_)
                    <br>
                    <br>
                    Password: 
                    <br>
                    <input type="password" name="pass">
                    <br>
                    (must be 15 characters or more. <a href="https://www.nist.gov/cybersecurity/how-do-i-create-good-password#:~:text=NIST%20guidance%20recommends%20that%20a%20password%20should%20be%20at%20least%2015%20characters%20long"><em>why?</em></a>)
                    <br>
                    <br>
                    Password confirmation: <br>
                    <input type="password" name="confirmpass">
                    <br>
                    <br>
                    Discord: <br>
                    <div style="position:relative; right:0.84em;"><span style="font-size:1.4em;">@</span><input type="text" name="discord"></div>
                    (for contacting)
                    <br>                   
                    <br>
                    Inv Key:
                    <br>
                    <input type="password" name="key">
                    <br>
                    <br>
                    <input type="submit" name="submit" value="Register....">
                </form>
                <br>
            </div>
            
            <div class="msgbox">
                <br>
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    registernvalidate(trim($_POST['name']),trim($_POST['key']),$_POST['pass'],$_POST['confirmpass'],$_POST['discord']);
                }
                ?>
            </div>
        </div>
        <script>
            const tt=[];let ci=0;for(let e=0;e<9;e++){const t="▁▂▃▄▅▄▃▂".slice(e)+"▁▂▃▄▅▄▃▂".slice(0,e);tt.push(t)}document.addEventListener("DOMContentLoaded",(function(){setInterval((()=>{document.title="register"+tt[ci],ci=ci=(ci+1)%tt.length}),400)}));
        </script>
    </body>
</html>
