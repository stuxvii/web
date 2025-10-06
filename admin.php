<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';

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

function genkey() {
    global $allowed;
    if ($allowed == false) {
        return;
    }
    try {
        $db = get_db_connection();
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
        global $new_key;
        $new_key = keygenfunc(); 
        $insert_sql = "INSERT INTO users (invkey) VALUES (?)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param('s', $new_key);
        $stmt->execute();

        if (!$stmt->affected_rows > 0) {
            echo "Failed to write the key to the database.<br>";
        }

        $stmt->close();
        echo "Success!<br>";

    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    } finally {
        if ($db) {
            $db->close();
        }
    }

}

ob_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($allowed == false | $requestedprev == true) {
        return;
    }
    if (isset($_POST['keygen'])) {
        genkey();
    }
}
    ?>
    <style>.content{align-items:center;}</style>
    <div class="buttons" style="flex-direction:column;">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:flex;flex-direction:column;">
            <?php
        if ($allowed == true) {
            if (isset($_POST['keygen'])) {
                echo "<button type=\"button\" onclick=\"alert('$new_key')\">See key</button>";
                    echo "<button type=\"button\" onclick=\"window.location.replace('/admin');\">Go Back</button>";
            } else {
                echo "Current server load: " . round(percentloadavg()[0] * 100) . "%";
                echo "<br><input type=\"submit\" name=\"keygen\" value=\"Generate  key\">";
            }
        } else {
            echo "<em>Forbidden</em><br>";
            echo "Go back to where you came from you scallywag.<br>";
            echo "<a href='/'>Home page</a>";
        }
            ?>
        </form>
    </div>
</div
<?php
$page_content = ob_get_clean();

require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>