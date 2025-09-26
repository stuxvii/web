<?php
setcookie('auth', "", time() - 3600, "/", "acdbx.top", true, true);
header("Location: index.php");
exit;
?>