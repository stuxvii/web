<?php
echo "
<div class=\"border\" id=\"char\">
    <span class=\"bodypart\" id=\"head\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\">
        <img src=\"images/epicface.png\" width='56' height='56'>
    </span>
        <div class=\"horiz\">
    <span class=\"bodypart limb\" id=\"larm\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\"></span>
    <span class=\"bodypart\" id=\"trso\" color=\"23\" style=\"background-color: rgb(13, 105, 172);\"></span>
    <span class=\"bodypart limb\" id=\"rarm\" color=\"1009\" style=\"background-color: rgb(255, 255, 0);\"></span>
    </div>
        <div class=\"horiz\">
    <span class=\"bodypart limb\" id=\"lleg\" color=\"301\" style=\"background-color: rgb(80, 109, 84);\"></span>
    <span class=\"bodypart limb\" id=\"rleg\" color=\"301\" style=\"background-color: rgb(80, 109, 84);\"></span>
    </div>
</div>";
require_once 'brickcolor.php';
$bpdata = [];
$bodyparts_map = [
    "head" => $head,
    "trso" => $trso,
    "larm" => $larm,
    "rarm" => $rarm,
    "lleg" => $lleg,
    "rleg" => $rleg
];
foreach ($bodyparts_map as $part_id => $sql_color_id) {
    $color_id = $sql_color_id;
    $hex = array_search((int)$color_id, $brickcolor);
    if ($hex) {
        $bpdata[] = [
            'id' => $part_id,
            'color_id' => $color_id,
            'hex' => $hex
        ];
    }
}
$newjson = json_encode($bpdata);
echo "<script>
document.addEventListener(\"DOMContentLoaded\", e => {
let t = $newjson;
t.forEach(e => {
let n = document.getElementById(e.id);
if (n) {
n.style.backgroundColor = \"#\" + e.hex; 
}
});
});
</script>";