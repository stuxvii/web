<?php
$token = $_COOKIE['auth'] ?? '';
$db = null;
$uid = null;

if (empty($token)) {
    $authsuccessful = false;
} else {
    
    if (!preg_match('/^[0-9a-f]{32}$/', $token)) {
        header("Location: logout.php");
        error_log($token);
        exit;
    }

    $authsuccessful = true;
    
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
    $db->busyTimeout(5000);

    $stmt = $db->prepare("
        SELECT id,username,pass,discordtag,operator
        FROM users 
        WHERE authuuid = :cookie
    ");

    $stmt->bindValue(':cookie', $token, SQLITE3_TEXT);
    $result = $stmt->execute();

    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $uid = $row['id'] ?? null;
        $name = $row['username'] ?? null;
        $passwordhash = $row['pass'] ?? null;
        $discordtag = $row['discordtag'] ?? null;
        $opperms = $row['operator'] ?? null;
        $result->finalize();
    }
    $stmt->close();

    if ($uid == NULL) {
        if ($db) $db->close();
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

    if ($settings) {
        $settings->finalize();
    }
    $fetchsettings->close();

    $theme = (bool)($prefrow['appearance'] ?? false);
    $movebg = (bool)($prefrow['movingbg'] ?? false);
    $dispchar = (bool)($prefrow['dispchar'] ?? false);
    $sidebarid = (int)($prefrow['sidebarid'] ?? 0);
    $sidebars = (bool)($prefrow['sidebars'] ?? false);

    $fetchavatar = $db->prepare("
    SELECT head,torso,leftarm,rightarm,leftleg,rightleg
    FROM avatars
    WHERE id = :uid");

    $fetchavatar->bindValue(":uid",$uid,SQLITE3_INTEGER);
    $colors = $fetchavatar->execute();
    
    $clrrow = $colors ? $colors->fetchArray(SQLITE3_ASSOC) : false;

    if ($colors) {
        $colors->finalize();
    }
    $fetchavatar->close();

    $head = (int)($clrrow['head'] ?? 0);
    $trso = (int)($clrrow['torso'] ?? 0);
    $lleg = (int)($clrrow['leftleg'] ?? 0);
    $larm = (int)($clrrow['leftarm'] ?? 0);
    $rleg = (int)($clrrow['rightleg'] ?? 0);
    $rarm = (int)($clrrow['rightarm'] ?? 0);

    $db->close(); 
    $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
}
?>