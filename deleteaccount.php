<?php
require_once 'auth.php';

$msg = "";
$curstep = "text";
$instructions = "(1/2) Enter the sentence <br>\"I wish to delete my account\" <br>in the box below to proceed.";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["text"])) {
        if ($_POST["text"] === "I wish to delete my account") {
            $curstep = "password";
            $instructions = "(2/2) Enter your password to confirm<br>the erasure of your data.<br>";
        } else {
            $msg = "Confirmation phrase was incorrect.<br>Check your spelling?";
        }
    }

    elseif (isset($_POST["password"])) {
        $curstep = "password";
        $instructions = "(2/2) Enter your password to confirm<br>the erasure of your data.<br>";
        
        if (password_verify($_POST["password"], $passwordhash)) {
            $delete_user_stmt = $db->prepare("DELETE FROM users WHERE id = :uid");
            $delete_user_stmt->bindValue(':uid', $uid, SQLITE3_INTEGER);
            $delete_user_stmt->execute();
            
            $delete_config_stmt = $db->prepare("DELETE FROM config WHERE id = :uid");
            $delete_config_stmt->bindValue(':uid', $uid, SQLITE3_INTEGER);
            $delete_config_stmt->execute();

            $delete_avatar_stmt = $db->prepare("DELETE FROM avatars WHERE id = :uid");
            $delete_avatar_stmt->bindValue(':uid', $uid, SQLITE3_INTEGER);
            $delete_avatar_stmt->execute();
            
            setcookie('auth', '', time() - 3600, '/');
            
            header("Location: https://www.google.com", true, 303);
            exit;
        } else {
            $msg = "Incorrect password.";
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
    <div class="diva">
        <em>Account deletion.</em>
        <form id="plrform" method="post" action=""> <hr>
            <?php if ($curstep === "text") { ?>
                <span>If you aren't satisfied with the <br>service, or are encountering <br>any issues, please tell us<br> over on <a href="https://discord.gg/7JwYGHAvJV">our Discord server.</a></span><hr><br>
            <?php } ?>
            
            <span><?php echo $instructions; ?></span>
            <br>
            <input type="<?php echo $curstep === 'password' ? 'password' : 'text'; ?>" 
                   name="<?php echo $curstep; ?>" 
                   style="width:400px;" 
                   autocomplete="<?php echo $curstep === 'password' ? 'current-password' : 'off'; ?>">
            <input type="submit" value="Continue">
            <br>
            <?php if (!empty($msg)) { echo $msg; } ?>
        </form>
    </div>
    <div class="btmrite"><?php echo htmlspecialchars($name); ?></div>
    <script src="../titleanim.min.js"></script>
    </body>
</html>