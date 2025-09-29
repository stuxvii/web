<?php
$token = $_COOKIE['auth'] ?? '';
if (empty($token)) {
    $authsuccessful = false;
} else {
    $authsuccessful = true;
    
    if (!preg_match('/^[0-9a-f]{32}$/', $token)) {
        header("Location: logout.php");
        error_log($token);
        exit;
    }

    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

    $stmt = $db->prepare("
        SELECT id,username,pass,discordtag,operator
        FROM users 
        WHERE authuuid = :cookie
    ");

    $stmt->bindValue(':cookie', $_COOKIE['auth'], SQLITE3_TEXT);
    $name = $stmt->execute();

    if ($name) {
        $row = $name->fetchArray(SQLITE3_ASSOC);
        $uid = $row['id'];
        $name = $row['username'];
        $passwordhash = $row['pass'];
        $discordtag = $row['discordtag'];
        $opperms = $row['operator'];
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
    $prefrow = $settings ? $settings->fetchArray(SQLITE3_ASSOC) : false;

    $theme = (bool)$prefrow['appearance'];
    $movebg = (bool)$prefrow['movingbg'];
    $dispchar = (bool)$prefrow['dispchar'];
    $sidebarid = (int)$prefrow['sidebarid'];
    $sidebars = (bool)$prefrow['sidebars'];
}
?>