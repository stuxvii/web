<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
if (!isset($page_content)) {
    $page_content = '';
}

use Jaybizzle\CrawlerDetect\CrawlerDetect;

$CrawlerDetect = new CrawlerDetect;

if ($CrawlerDetect->isCrawler()) {
    http_response_code(400);
    die();
}

/*
 * Info on how to use this template:
 * You must first create a .php file
 * Then place the following content inside of it
 *
 * <?php
 * require_once $_SERVER['DOCUMENT_ROOT'] . "/auth.php";
 * if ($authsuccessful) {
 *     header("Location: index.php") // You may choose to throw them out if they're not logged in
 * }
 *
 * ob_start(); // This starts grabbing every single line of info being echoed to the client and hijacks it
 * ?>
 *
 * <!-- Put all your page content inside here, output from PHP scripts is also captured. -->
 * <?php
 * $page_content = ob_get_clean(); // Get all the content generated from before
 * require_once $_SERVER['DOCUMENT_ROOT'] . "/template.php"; // Finally, include the template, which will read the $page content variable and insert it all in the html for the client
 * ?>
 */
?>
<!DOCTYPE html> 
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=0.8">
        <link rel="stylesheet" href="../normalize.css">
        <link rel="stylesheet" href="../styles.css">
        <meta name="robots" content="noindex">
        <style>
        <?php
        if ($theme):
            ?>
                :root{
                --primary-color: #fff;
                --secondary-color: #000;
                --good: #00bb00;
                --evil: #dd2222;
                }
                body {                background-image:
                linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)),
                var(--bgimg);}
            <?php endif;
        if (!$movebg): ?>
                body{animation-name: none;}

            <?php endif;
        if (!$sidebars): ?>
                .sidebars{justify-content: center;}

            <?php else: ?>
                body{font-family: 'dink';}
                html,
                body,
                input,
                select,
                option,
                button {
                    cursor: url('cursors/kangel.cur'), auto;
                }
                
            <?php endif; ?>
            </style>
    </head>
    <body>
    <div class="sidebars" <?php if (!$authsuccessful) { echo "style='flex-direction: column;'"; } ?>>
        <?php
        global $sidebarid;
        global $sidebars;
        require 'sidebars.php';
        ?>	
    <div class="main">
    <div class="navbar">
        <div>
            <a href="/"><img height='20px' src='images/anim/logo.gif'></a>
<?php if ($authsuccessful): ?>
            <a href="uploadui">Upload</a>
            <a href="catalog">Catalog</a>
            <a href="character">Character</a>
            <a href="inventory">Inventory</a>
        </div>
        <div>
            <a href="profile?id=<?php echo $uid;?>"><?php echo htmlspecialchars($name); ?></a>
            <?php
            $cursymbol = '¥';
            switch ($uid) {
                case 2:
                    $cursymbol = '₺';
                    break;
                case 3:
                    $cursymbol = '₴';
                    break;
                case 4:
                    $cursymbol = 'دم';
                    break;
            }
            echo 'has ' . $cursymbol . '<span id="amountofmoney">' . htmlspecialchars($money) . '</span>';
            
else: ?>
        <div>
            <a href="login">Login</a>
            <a href="register">Register</a>
        </div>
        <?php endif; ?>
    </div>
        
	</div>
	<div class="navbar">
        <?php if ($authsuccessful): ?>
        <div>
            <a href="https://discord.gg/7JwYGHAvJV" target="_blank">Discord</a>
            <a href="/support" >Donate</a>
        </div>
        <div>
            <?php if ($opperms): ?>
                <a href="moderation">Moderate assets</a>
                <a href="admin">Admin panel</a>
                <?php endif; ?>
                <a href="config">Settings</a>
                <a href="logout">Log out</a>
        </div>
        <?php endif; ?>
	</div>
	<div class="navbar" style="background-color:var(--evil);">
    <?php if ($_SERVER['SERVER_NAME'] == "acdbx.top"): ?>
        It has been detected that you're still on the older domain! (<?php echo $_SERVER['SERVER_NAME']; ?>) Please use 'lsdblox.cc' from now on, as this domain will soon be phased out, and replaced with other content.
    <?php endif;?>
	</div>
    <?php
    echo "<div class='content'>";
    if ($maintenanceon && !$opperms):?>
    <div class='border'>lsdblox is currently<br>under maintenance.<br>check #announcements &<br>#lsdblox for information.</div>
    <?php
    else:
        echo $page_content;
    endif;
    echo '</div>';
    ?>
	<div class="navbar bottomnavbar" style="flex-direction:column;">
        <a href="https://github.com/stuxvii/web">lsdblox - 2025. always ask for OSS.</a>
        <em>no one at lsdblox is attempting to impersonate anybody. we're not affiliated with any brands, products, sites, etc. users are responsible for their own content.</em>
        the tos and privacy policy are planned to be written soon. this website is a preliminary work.
    </div>
        <?php
        global $sidebarid;
        global $sidebars;
        $rightside = true;
        echo '</div>';
        require 'sidebars.php';
        ?>
    </div>
    <script src="../titleanim.min.js"></script>
    </body>
</html>