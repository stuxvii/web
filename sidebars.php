<?php
global $sidebars;
global $rightside;
$class = "sidebar";
$img = "sidebarafn";
if ($sidebarid == 1) {
    $img = "sidebarday";
}
if ($sidebarid == 2) {
    $img = "sidebarafn";
}
if ($sidebarid == 3) {
    $img = "sidebarnit";
}
if ($rightside) {
    $class = "sbright";
}
if ($sidebars) {
    echo "<div class='$class'><img src='images/$img.png'></div>";
}
?>