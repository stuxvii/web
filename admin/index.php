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

function kill($victim, $allowed) {
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
        $db = get_db_connection(); 
        echo "Successfully connected to the database...<br>";

        $select_sql = "SELECT id FROM users WHERE id = ?";
        $stmt_check = $db->prepare($select_sql);
        $stmt_check->bindValue('i', $victim);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $row = $result->fetch_assoc();
        $stmt_check->close();
        
        if ($row) {
            echo "Found user.<br>";

            $update_sql = "
                UPDATE users 
                SET 
                    username = 'Content Deleted',
                    pass = NULL,
                    discordtag = NULL,
                    registerts = NULL,
                    invkey = NULL,
                    authuuid = NULL
                WHERE id = ?
            ";
            $stmt_update = $db->prepare($update_sql);
            $stmt_update->bindValue('i', $victim);
            $stmt_update->execute();
            $stmt_update->close();
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

function genkey() {
    global $allowed;
    if ($allowed == false) {
        return;
    }
    try {
        echo "Attempting to connect to the database...<br>";
        $db = get_db_connection();
        echo "Successfully connected to the database...<br>";
        $db->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(32) DEFAULT NULL,
            pass VARCHAR(100) DEFAULT NULL,
            discordtag VARCHAR(100) DEFAULT NULL,
            registerts VARCHAR(100) DEFAULT NULL,
            authuuid VARCHAR(100) DEFAULT NULL,
            invkey VARCHAR(100) DEFAULT NULL,       
            isoperator TINYINT(1) DEFAULT 0
        )
        ");
        echo "Database is ready.<br>";
        global $new_key;
        $new_key = keygenfunc(); 
        echo "Generated new key.<br>";
        $insert_sql = "INSERT INTO users (invkey) VALUES (?)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param('s', $new_key);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Successfully wrote the key to the database.<br>";
        } else {
            echo "Failed to write the key to the database.<br>";
        }

        $stmt->close();
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
        <div class="content">
            <?php
            global $sidebarid;
            global $sidebars;
            require "../sidebars.php";
            ?>
            <div class="diva">
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if ($allowed == false | $requestedprev == true) {
                        return;
                    }
                    if (isset($_POST['username'])) {
                        kill($_POST['username'],$allowed);
                    } else 
                    if (isset($_POST['keygen'])) {
                        genkey();
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
            <div class="rite"></div>
            <?php
            global $sidebarid;
            global $sidebars;
            $rightside = true;
            require "../sidebars.php";
            ?>
        </div>
        <script src="../titleanim.min.js"></script>
    </body>
</html>