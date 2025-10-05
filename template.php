<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/auth.php";
if (!isset($page_content)) {
    $page_content = '';
}

/*
Info on how to use this template:
You must first create a .php file
Then place the following content inside of it

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/auth.php";
if ($authsuccessful) {
    header("Location: index.php") // You may choose to throw them out if they're not logged in
}

ob_start(); // This starts grabbing every single line of info being echoed to the client and hijacks it
?>

<!-- Put all your page content inside here, output from PHP scripts is also captured. -->
<?php
$page_content = ob_get_clean(); // Get all the content generated from before
require_once $_SERVER['DOCUMENT_ROOT'] . "/template.php"; // Finally, include the template, which will read the $page content variable and insert it all in the html for the client
?>

*/
?>
<!DOCTYPE html> 
<html>
    <head>
        <link rel="stylesheet" href="../normalize.css">
        <link rel="stylesheet" href="../styles.css">
        <style>
        <?php
            if ($theme) {
                echo ":root{
                --primary-color: #fff;
                --secondary-color: #000;
                --bgimg: url(\"cargonetlight.bmp\");
                --good: #00bb00;
                --evil: #dd2222;
                }";
            }
            if (!$movebg) {
                echo "body{animation-name: none;}";
            }
        ?></style>
    </head>
    <body>
    <div class="sidebars">
        <?php 
        global $sidebarid;
        global $sidebars;
        require "sidebars.php";
        echo $page_content;
        global $sidebarid;
        global $sidebars;
        $rightside = true;
        require "sidebars.php";
        ?>
    </div>
    <script src="../titleanim.min.js"></script>
    </body>
</html>