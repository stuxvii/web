<?php
require_once '../auth.php';

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

function percentloadavg(){ // https://www.php.net/manual/en/function.sys-getloadavg.php#126283
    $cpu_count = 1;
    if(is_file('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        $cpu_count = count($matches[0]);
    }

    $sys_getloadavg = sys_getloadavg();
    $sys_getloadavg[0] = $sys_getloadavg[0] / $cpu_count;
    $sys_getloadavg[1] = $sys_getloadavg[1] / $cpu_count;
    $sys_getloadavg[2] = $sys_getloadavg[2] / $cpu_count;

    return $sys_getloadavg;
}

$allowed = ($opperms) ? true : false;
$new_key = 'failed to gen key there is no key you dingus';
$dbpath = "../keys.db";
$requestedprev = false;

function keygenfunc($length = 12) {
    global $allowed;
    if ($allowed == false) {
        return;
    }
    $hex_characters = '0123456789abcdef';
    $random_key = '';
    for ($i = 0; $i < $length; $i++) {
        $random_key .= $hex_characters[mt_rand(0, strlen($hex_characters) - 1)];
    }
    return $random_key;
}

function kill($victim, $allowed, $dbpath) {
    global $requestedprev;
    $requestedprev = true;
    if ($allowed === false) {
        return;
    }
    
    if (!is_numeric($victim) || $victim <= 0) {
        echo "Please enter a valid UserID.<a href='/admin/'>Go back</a>";
        return;
    }

    $db = null;
    try {
        echo "Attempting to connect to the database...<br>";
        $db = new SQLite3($dbpath, SQLITE3_OPEN_READWRITE); 
        echo "Successfully connected to the database...<br>";

        $select_sql = "SELECT id FROM users WHERE id = :id";
        $stmt_check = $db->prepare($select_sql);
        $stmt_check->bindValue(':id', (int)$victim, SQLITE3_INTEGER);
        
        echo "Performing database search...<br>";
        $result = $stmt_check->execute();
        
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($row) {
            echo "Found user.<br>";

            $update_sql = "
                UPDATE users 
                SET 
                    username = \"Content Deleted\",
                    pass = NULL,
                    discordtag = NULL,
                    timestamp = NULL,
                    key = NULL,
                    authuuid = NULL
                WHERE id = :id
            ";
            $stmt_update = $db->prepare($update_sql);
            $stmt_update->bindValue(':id', (int)$victim, SQLITE3_INTEGER);
            $stmt_update->execute();
            echo "User erased (anonymized).<br>";
            
        } else {
            echo "UserID not found.<a href='/admin/'>Go back</a>";
        }

    } catch (Exception $e) {
        error_log("Database error in kill(): " . $e->getMessage()); 
        echo "Internal error when querying users.<a href='/admin/'>Go back</a>";
    } finally {
        if ($db) {
            $db->close();
        }
    } 
}

function main() {
    global $allowed;
    if ($allowed == false) {
        return;
    }
    try {
        global $dbpath;
        echo "Attempting to connect to the database...<br>";
        $db = new SQLite3($dbpath, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

        echo "Successfully connected to the database...<br>";

        $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            pass TEXT,
            discordtag TEXT,
            key TEXT,
            timestamp TEXT,
            authuuid TEXT,
            operator BOOLEAN
        )
        ");

        echo "Database is ready.<br>";
        global $new_key;
        $new_key = keygenfunc();
        echo "Generated new key.<br>";

        $insert_sql = "INSERT INTO users (key) VALUES (:new_key)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bindValue(':new_key', $new_key, SQLITE3_TEXT);
        $stmt->execute();

        if ($db->changes() > 0) {
            echo "Successfully wrote the key to the database.<br>";
        } else {
            echo "Failed to write the key to the database.<br>";
        }

        $db->close();
        echo "Database connection closed.<br>";

    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    } finally {
        if ($db) {
            $db->close();
        }
    }

}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>ADMIN Dashboard</title>
        <link rel="stylesheet" href="../styles.css">
    </head>
    <body>
        <div class="diva">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if ($allowed == false | $requestedprev == true) {
                    return;
                }
                if (isset($_POST['username'])) {
                    kill($_POST['username'],$allowed,$dbpath);
                } else 
                if (isset($_POST['keygen'])) {
                    main();
                }
            }
            ?>
            <div class="buttons" style="flex-direction:column;">
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <?php
                    if ($allowed == true) {
                        if ($requestedprev == true) {return;}
                        echo "Current server load: " . round(percentloadavg()[0] * 100) . "%";
                        echo "<br><input type=\"submit\" name=\"keygen\" value=\"Generate  key\">";
                    } else {
                        echo "<em>Forbidden</em><br>";
                        echo "Go back to where you came from you scallywag.<br>";
                        echo "<a href='/'>Home page</a>";
                    }
                    ?>
                </form>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <?php
                    if ($allowed == true) {
                        if ($requestedprev == true) {return;}
                        echo "<br>Kill someone<br>";
                        echo "<input type=\"text\" name=\"username\" placeholder=\"Enter UserID\"><input type=\"submit\">";
                    }
                    ?>
                </form>
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if (isset($_POST['keygen'])) {
                        echo "<button type=\"button\" onclick=\"window.location.replace('/admin');\">Go Back</button>";
                        echo "<button type=\"button\" onclick=\"window.location.replace('/');\">Home!</button>";
                        echo "<button type=\"button\" onclick=\"alert('$new_key')\">See key</button>";
                    }
                }
                ?>
            </div>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>