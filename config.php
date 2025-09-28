<?php
if (!isset($_COOKIE['auth'])) {
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../normalize.css">
        <link rel="stylesheet" href="../styles.css">
    </head>
    <body>
        <div class="divaleft">
            <form id="plrform" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <hr>
                <span>Appearance</span>
                <hr>
                <input type="radio" id="light" name="thememode">
                <label for="brightness">Light mode</label>
                <br>
                <input type="radio" id="dark" name="thememode">
                <label for="brightness">Dark mode</label>
                <hr>
                <span>Site settings</span>
                <hr>
                <input type="checkbox" id="brightness" name="brightness">
                <label for="brightness">Moving background</label>
                <br>
                <input type="checkbox" id="brightness" name="brightness">
                <label for="brightness">Display your character <br>in the main page</label>
                <br>
                <input type="submit" value="Save">
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