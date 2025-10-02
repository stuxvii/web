<?php
require_once __DIR__ . '/databaseconfig.php';
/* Info about token:
Generated using a slightly modified guidv4, 
to not include hyphens because i think they look weird.
also it makes it annoying to do regex on.
This token is static, not rotated or expiring, so you
don't lose your session every once in a while and have
to log in on every device if you get a new cookie on each
log in.
This cookie is fetched IN PLAIN TEXT from the server,
and placed into the client's computer OVER HTTPS
once they successfully log in. (flags Secure & HttpOnly set)

I know i shouldn't really be having tokens in plain text, 
but tbh if a leak gets so bad they have access to the main database file, 
then I actually have bigger things to worry about. I am not even
storing any sensitive information, passwords and ips are hashed,
all data that comes through this server is stored in an anonymized way.

Even if a leak ocurrs, I could just write like.. an apology letter.. or smth like that
and force everyone to get new cookies and invest even more time because honestly
i've read through this code a million times already i don't think there's a single
point of failure anywhere in here... But yeah i am just a singular person,
and this whole entire project rests on me, i may potentially skim
over something i didn't want to accidentally ignore.

There is no way for anyone to get the keys.db ever unless either
A. there turns out to be some actually massive exploit in php all along
where people can have RCE and just straight up fetch the keys.db file themselves
B. i get infected with a remote access trojan and someone comes in and manually yanks
the keys.db.

This cookie is only ever invalidated if the user either
A. changes their username
B. changes their password
*/
$token = $_COOKIE['auth'] ?? '';
$db = null;
$uid = null;

if (empty($token)) {
    $authsuccessful = false;
} else {
    
    if (!preg_match('/^[0-9a-f]{32}$/', $token)) { // I don't want to waste a whole entire SQL request on a token that shouldn't even be valid in the first place 😭🙏
        error_log($token);
        header("Location: logout.php");
        exit;
    }
    
    $db = get_db_connection();
    
    $stmt = $db->prepare("
        SELECT id, username, pass, discordtag, isoperator
        FROM users 
        WHERE authuuid = ?
    ");

    $stmt->bind_param('s', $token); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $row = $result->fetch_assoc();
        $uid = $row['id'] ?? null;
    }
    $stmt->close();

    if ($uid == NULL) { // If, somehow, the cookie was "valid", but didn't exist (let's say they got their data deleted by an admin) then log them out to clear the cookie.
        if ($db) $db->close(); // db should usually be open actually, but who knows maybe php could just hallucinate and not do anything but still proceed with script execution regardless
        header("Location: logout.php");
        exit;
    }
    // If it was successful, go get everything else >:]
    $name = $row['username'] ?? null;
    $passwordhash = $row['pass'] ?? null;
    $discordtag = $row['discordtag'] ?? null;
    $opperms = $row['isoperator'] ?? null;

    $authsuccessful = true; // user is valid ^_^

    $fetchsettings = $db->prepare("
    SELECT appearance, movingbg, dispchar, sidebarid, sidebars
    FROM config
    WHERE id = ?");

    $fetchsettings->bind_param('i', $uid);
    $fetchsettings->execute();
    $settings = $fetchsettings->get_result();
    $prefrow = $settings ? $settings->fetch_assoc() : false; // Tried adding this in to fix.
    
    // Fetch user settings so i can appropriately theme the website's look
    $theme = (bool)($prefrow['appearance'] ?? false);
    $movebg = (bool)($prefrow['movingbg'] ?? false);
    $dispchar = (bool)($prefrow['dispchar'] ?? false);
    $sidebarid = (int)($prefrow['sidebarid'] ?? 0);
    $sidebars = (bool)($prefrow['sidebars'] ?? false);

    // get the user's little guy so i can display them globaly on the website on a little corner as they watch the user cutely browse the page
    $fetchavatar = $db->prepare("
    SELECT head, torso, leftarm, rightarm, leftleg, rightleg
    FROM avatars
    WHERE id = ?");

    $fetchavatar->bind_param('i', $uid);
    $fetchavatar->execute();
    $colors = $fetchavatar->get_result();
    
    $clrrow = $colors ? $colors->fetch_assoc() : false;
    $fetchavatar->close();
    
    $theme = (bool)($prefrow['appearance'] ?? false);
    $movebg = (bool)($prefrow['movingbg'] ?? false);
    $dispchar = (bool)($prefrow['dispchar'] ?? false);
    $sidebarid = (int)($prefrow['sidebarid'] ?? 0);
    $sidebars = (bool)($prefrow['sidebars'] ?? false);
    
    $fetchavatar = $db->prepare("
    SELECT head, torso, leftarm, rightarm, leftleg, rightleg
    FROM avatars
    WHERE id = ?");

    $fetchavatar->bind_param('i', $uid);
    $fetchavatar->execute();
    $colors_result = $fetchavatar->get_result();

    $clrrow = $colors_result ? $colors_result->fetch_assoc() : false; 
    $fetchavatar->close(); 

    $head = (int)($clrrow['head'] ?? 0);
    $trso = (int)($clrrow['torso'] ?? 0);
    $lleg = (int)($clrrow['leftleg'] ?? 0);
    $larm = (int)($clrrow['leftarm'] ?? 0);
    $rleg = (int)($clrrow['rightleg'] ?? 0);
    $rarm = (int)($clrrow['rightarm'] ?? 0);

    if ($_SERVER['PHP_SELF'] == "/auth.php") {
        echo "Git Docksed xD";echo "<br>Ur Name: ";
        echo $name;echo "<br> Ur Discord Tag: ";
        echo $discordtag;echo "<br> Ur Hashed Password: ";
        echo $passwordhash;echo "<br> Are you OP? ";
        if ($opperms == 1) {
            echo "Your are 31337 Hax0r.";
        } else {
            echo "You are a mere pleb. xD";
        }
        echo "<br>";
        echo "Ur Settings m9.<br>";

        echo $theme;
        echo $movebg;
        echo $dispchar;
        echo $sidebarid;
        echo $sidebars;
    }
}
?>