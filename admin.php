<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';

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

ob_start();?>
<style>.content{align-items:center;}</style>
<div class="buttons" style="flex-direction:column;">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:flex;flex-direction:column;">
        <?php
    if ($allowed == true) {
        echo "Current server load: " . round(percentloadavg()[0] * 100) . "%";
    } else {
        echo "<em>Forbidden</em><br>";
        echo "Go back to where you came from you scallywag.<br>";
        echo "<a href='/'>Home page</a>";
    }
        ?>
    </form>
</div>
<?php
$page_content = ob_get_clean();

require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>