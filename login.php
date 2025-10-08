<?php
if (isset($_COOKIE['auth'])) {
    header("Location: index.php");
    exit;
}

require_once 'databaseconfig.php';

$usernamevalidateregex = '/^[a-zA-Z0-9_]{3,20}$/';

function error($reason) {
    return "<img src=\"error.png\" height='32'><span class=\"info\">$reason</span>";
}

function login($un, $pass) {
    global $usernamevalidateregex;
    $db = get_db_connection();

    $invalid = "The credentials you provided are invalid.";

    if ($un === '' || $pass === '') {
        echo error("Password and username, you dingus."); 
        return;
    }

    if (!preg_match($usernamevalidateregex, $un)) {
        echo error($invalid);
        return;
    }

    $stmt = $db->prepare("SELECT pass, authuuid FROM users WHERE username = ?");
    $stmt->bind_param('s', $un);
    $stmt->execute();
    $result = $stmt->get_result();

    $user_data = $result ? $result->fetch_assoc() : false;
    $stmt->close();

    if ($user_data && password_verify($pass, $user_data['pass'])) {

        setcookie('auth', $user_data['authuuid'], time() + (86400 * 30), "/", "acdbx.top", true, true);
        header("Location: index.php");
        exit;
        $db->close();
    } else {
        echo error($invalid);
    }
}
ob_start();
?>
<style>
.content {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction:column;
}
</style>
<div class="deadcenter" style="justify-content: center;">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        Username: <input type="text" name="name">
        <br>
        Password: <input type="password" name="pass">
        <br>
        <input type="submit" name="submit" value="Login"><em> (saves a cookie to your device)</em>
    </form>
    <br>
    <div class="msgbox">
        <br>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST") { login(trim($_POST['name']),$_POST['pass']); } ?>
    </div>
</div>
<?php 
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>