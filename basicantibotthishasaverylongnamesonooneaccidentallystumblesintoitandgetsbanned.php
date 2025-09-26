<?php
// i dislike the possibility of accidentally banning people but i dislike having bots roaming through my servers..
$logfile = __DIR__ . "/honeypot.log";
$blacklistfile = __DIR__ . "/ip_blacklist.txt";

$ip = $_SERVER['REMOTE_ADDR'];

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

$ipchk = filter_var($ip, FILTER_VALIDATE_IP);

if ($ipchk) {
    $preexistingips = file_exists($blacklistfile) ? file($blacklistfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    if (!in_array($ipchk . " deny", $preexistingips)) {
        $timestamp = date('Y-m-d H:i:s');
        $logger = fopen($logfile, "a");
        if ($logger) {
            fwrite($logger, "[$timestamp] Stinker: $ipchk\n");
            fclose($logger);
        }

        $blacklist = fopen($blacklistfile, "a");
        if ($blacklist) {
            fwrite($blacklist, $ipchk . " deny\n");
            fclose($blacklist);
        }
    }
}

die(filter_var($ip, FILTER_VALIDATE_IP) . " :p");
?>