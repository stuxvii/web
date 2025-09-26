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
            $db = new SQLite3('keys.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            if (isset($_COOKIE['auth'])) {
                $stmt = $db->prepare("
                    SELECT username
                    FROM users 
                    WHERE authuuid = :cookie
                ");
                
                $stmt->bindValue(':cookie', $_COOKIE['auth'], SQLITE3_TEXT);

                $name = $stmt->execute();

                if ($name) {
                    while ($row = $name->fetchArray(SQLITE3_ASSOC)) {
                        echo "Hey there, " . $row['username'];
                    }
                }
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
        <script>
            const tt=[];let ci=0;for(let e=0;e<9;e++){const t="▁▂▃▄▅▄▃▂".slice(e)+"▁▂▃▄▅▄▃▂".slice(0,e);tt.push(t)}document.addEventListener("DOMContentLoaded",(function(){setInterval((()=>{document.title="αcidbloχ-αlρha"+tt[ci],ci=ci=(ci+1)%tt.length}),400)}));
        </script>
    </body>
</html>