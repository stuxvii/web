<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';

ob_start();

echo "<div class=\"content\">";
if ($authsuccessful) :
?>
    <div class="btmh">
        <span>Hey there, <?php echo htmlspecialchars($name); ?>. <a href="logout">Click to log out</a></span>
        <span><?php 
        switch ($uid) { 
            case 2:  echo "₴ryvnias: "; break;
            case 3:  echo "دمirhams: "; break;
            case 5:  echo "₺iras: ";    break;
            default: echo "₱esos: ";
        }
            echo htmlspecialchars($money); 
                
                ?></span>

        <?php
        if ($dispchar === 2) :
        ?>
            <img height='240px' class='jump' id='render' src='renders/<?php echo htmlspecialchars($uid); ?>.png'>
        <?php
        elseif ($dispchar === 1) :
            require_once $_SERVER['DOCUMENT_ROOT'] . "/getblockychar.php";
        endif;
        ?>
    </div>

    <div class="midh">
        <img class="bounce" src="processing.png" id="speen"><span id="maintext"></span>
    </div>

    <div class="rite">
        <a href="character">Character customization</a>
        <a href="https://discord.gg/7JwYGHAvJV" target="_blank">Official Discord server</a>
        <a href="https://buymeacoffee.com/acidbox" target="_blank">Buy me a coffee</a>
        <a href="uploadui">Upload asset</a>
        <?php if ($opperms) : ?>
            <a href="moderation">Moderate assets</a>
            <a href="admin">Admin panel</a>
        <?php endif; ?>
        <a href="inventory">Inventory</a>
        <a href="config">Settings</a>
        <a href="catalog">Catalog</a>
    </div> 
</div>
<?php
else :
?>
<style>
.content {
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
<div class="deadcenter">
    <a href="login">Login</a>
    <a href="register">Register</a>
</div>
</div>
<?php
endif;

// END OF PAGE CONTENT AND LOGIC.
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>