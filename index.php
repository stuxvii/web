<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles.css">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Orbitron:wght@400..900&family=Rajdhani:wght@300;400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap'); 
            .mwrtng {
                font-family: 'Great Vibes', cursive;
            }
        </style>
    </head>
    <body>
        <div class="diva">
            <?php
            if (!isset($_COOKIE['auth'])) {
                echo "<a href=\"login.php\">Login</a>
                <a href=\"register.php\">Register</a>";
            }
            if (isset($_COOKIE['auth'])) {
                echo "<a href=\"https://windows93.net/c/programs/acidBox93/\">acidBox93 (LOUD AUDIO)</a>
                <a href=\"character.php\">character customization</a>
                <a href=\"mwrtng/\" class=\"mwrtng\">mewity rating</a>";
            }
            ?>
            
        </div>
        <div class="btmleft">
            <?php
            const logout = 'logout.php';

            $db = null;
            try {
                $db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            } catch (Exception $e) {
                error_log("Database connection failed: " . $e->getMessage());
                exit("Database connection error.");
            }
            if (isset($_COOKIE['auth'])) {
                $authCookie = $_COOKIE['auth'];

                $stmt = $db->prepare("
                    SELECT username
                    FROM users 
                    WHERE authuuid = :cookie
                    LIMIT 1
                ");
                
                $stmt->bindValue(':cookie', $authCookie, SQLITE3_TEXT);
                
                $result = $stmt->execute();

                if ($result) {
                    $row = $result->fetchArray(SQLITE3_ASSOC);
                    if ($row) {
                        echo "Hey there, " . $row['username'];
                    } else {
                        header("Location: " . logout);
                        exit();
                    }
                    $result->finalize();
                } else {
                    error_log("SQL execution failed: " . $db->lastErrorMsg());
                    header("Location: " . logout);
                    exit();
                }
            }

            if ($db) {
                $db->close();
            }
            ?>
        </div>
        <div class="btmrite">
            <?php
            if (isset($_COOKIE['auth'])) {
                echo "<a href=\"logout.php\">Log out</a>";
            }
            ?>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>