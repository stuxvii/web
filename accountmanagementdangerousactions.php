<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
if (!$authsuccessful) {
    header("Location: logout.php");
    exit;
}

function guidv4($data = null) {
    $data = $data ?? random_bytes(64);
    assert(strlen($data) == 64);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
}

$usernamevalidateregex = '/^[a-zA-Z0-9_]{3,20}$/';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $candoaction = false;
    $rowsaffected = NULL;

    if (!isset($_POST['confirm']) || !password_verify($_POST['confirm'],$passwordhash)) { //fixed
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
            $newpass = password_hash($pass,PASSWORD_BCRYPT);
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
<div class="content">
    <div class="left midh">
        <form id="plrform" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
            <a href="/">Home</a>
            <br>
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
    <div class="rite midh">
        <span>Danger zone</span>
        <br>
        <span>This section makes the option of </span>
        <span>permanently erasing your</span>
        <span>account available to you.</span>
        <br>
        <span>This process is completely irreversible, </span>
        <span>and once activated is unable to be reverted.</span>
        <br>
        <span>You will be asked twice before </span>
        <span>your account is permanently wiped.</span>
        <br>
        <button onclick="location.href='deleteaccount'">Delete Account</button>
    </div>
</div>
<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>