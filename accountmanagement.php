<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
if (!$authsuccessful) {
    header("Location: logout.php");
    exit;
}

function guidv4($data = null) {
    $data = random_bytes(64);
    return bin2hex($data);
}

$usernamevalidateregex = '/^[a-zA-Z0-9_]{3,20}$/';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $candoaction = false;
    $rowsaffected = NULL;

    if (!isset($_POST['confirm']) || !password_verify($_POST['confirm'],$passwordhash)) {
        $msg = "The password confirmation<br>you inputted was invalid.";
    } else {
        $candoaction=true;
    }

    if (isset($_POST['username']) && $candoaction) {

        $newusername = trim($_POST['username']);
        if (preg_match($usernamevalidateregex,$newusername)){
            $stmtcheckusername = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
            $stmtcheckusername->bind_param('s', $newusername);
            $stmtcheckusername->execute();
            $result = $stmtcheckusername->get_result();
            $row = $result->fetch_assoc();
            $stmtcheckusername->close();

            if ($row['count'] > 0) {
                echo "The username '$newusername' is already taken.";
            } else {
                $updstmt = $db->prepare("
                    UPDATE users 
                    SET 
                        username = ?
                    WHERE authuuid = ?
                ");
                
                $updstmt->bind_param('ss', $newusername, $token);
                $updstmt->execute();
                $rowsaffected += $updstmt->affected_rows;
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
            $newpass = password_hash($pass,PASSWORD_ARGON2ID);
            $updstmt = $db->prepare("
                UPDATE users 
                SET 
                    pass = ?
                WHERE authuuid = ?
            ");
            
            $updstmt->bind_param('ss', $newpass, $token);
            $updstmt->execute();
            $rowsaffected += $updstmt->affected_rows;
        }
    }

    if ($rowsaffected > 0) {
        $updstmt = $db->prepare("
            UPDATE users 
            SET 
                authuuid = ?
            WHERE authuuid = ?
        ");
        
        $updstmt->bind_param('ss', guidv4(), $token);
        $updstmt->execute();
        header('Location: logout.php', true, 303);
        exit;
    } else {
        if (!isset($msg)) {
        $msg = "Internal error.<br><em>report to<br>dev plox</em>";
    }
}
}
ob_start();
?>
    <div>
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
            Use your current password <br>to authorize any changes.
            <hr>
            <span>Password confirmation</span>
            <br>
            <input type="password" id="confirm" name="confirm">
            <br>
            <input type="submit" value="Modify"> 
            <br>
            <?php if (!empty($msg)) { echo $msg; } ?>
        </form>
    </div>
    <div style="top:20vh;position:relative;">scroll down to access <br>the account deletion form...</div>
    <div class="midh" style="top:100vh;position:relative;">
        <span>Danger zone</span>
        <br>
        <span>You will be asked twice before</span>
        <span>your account is permanently wiped.</span>
        <br>
        <button onclick="location.href='deleteaccount'" style="background-color:var(--evil);">Delete Account</button>
    </div>
<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>