# Barebones setup instructions
This guide will mostly focus on setup steps for sys-admins running the "Arch" Linux distribution.<br>
0. Prerequisites:<br>
[A fully up to date and configured MariaDB database](https://wiki.archlinux.org/title/MariaDB)<br>
[PHP](https://wiki.archlinux.org/title/PHP)<br>
Webserver software that supports PHP (like [Apache's HTTPD](https://wiki.archlinux.org/title/Apache_HTTP_Server))<br>
[Composer](https://wiki.archlinux.org/title/PHP#Composer)<br>
FFMpeg ([and its Composer plugin for PHP](https://github.com/PHP-FFMpeg/PHP-FFMpeg))<br>
ImageMagick ([and its plugin for PHP](https://archlinux.org/packages/extra/x86_64/php-imagick/))<br>
You can find how to install and setup these at their corresponding wiki/doc pages I linked.<br>
For the database, you will have to painstakingly set every single table and column yourself. I apologize for not bothering to give you a .sql template.<br>

1. Create a file called "databaseconfig.php" with contents like these:

```
<?php

define('DB_HOST', 'YOURDBIPADDRESSGOESHERE'); // CHANGE THIS TO THE IP ADDRESS THAT HOSTS YOUR DB
define('DB_USER', 'YOURDBUSRNAMEGOSEHERE'); // CHANGE THIS TO YOUR DB ACCESS USERNAME
define('DB_PASS', 'YOURPASSWORDGOESHERE'); // CHANGE THIS TO YOUR DB ACCESS PASSWORD FOR THAT USERNAME
define('DB_NAME', 'YOURDBNAMEGOESHERE'); // CHANGE THIS TO YOUR DB NAME

function get_db_connection() {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($db->connect_error) {
        error_log("MySQL Connection Failed: " . $db->connect_error);
        http_response_code(500);
        die("Internal Server Error: Database connection failed.");
    }
    $db->set_charset("utf8mb4");
    return $db;
}
?>
```

1.2. Block that file using .htaccess or the equivalent for your webserver
