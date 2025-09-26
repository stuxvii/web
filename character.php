<?php
if (!isset($_COOKIE['auth'])) {
    global $allowed;
    echo "You're not logged in.";
    header("Location: index.php");
    return;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../animate.min.css">
        <link rel="stylesheet" href="../styles.css">
        <link rel="stylesheet" href="../character.css">
    </head>
    <body>
        <div class="char" id="char">
            <span class="bodypart" id="head" style="height:60px; width:60px; background-color:#fff; bottom:0px;"></span>
            <span class="bodypart" id="trso" style="height:100px; width:100px; background-color:#fff; top:0px;"></span>
            <span class="bodypart" id="lleg" style="height:100px; width:45px; background-color:#fff; top:105px; right:0px;"></span>
            <span class="bodypart" id="rleg" style="height:100px; width:45px; background-color:#fff; top:105px; left:0px;"></span>
            <span class="bodypart" id="rarm" style="height:100px; width:45px; background-color:#fff; left:52px;"></span>
            <span class="bodypart" id="larm" style="height:100px; width:45px; background-color:#fff; right:52px;"></span>
        </div>
        <div class="btmrite">
            <a href="/">Home page</a>
        </div>
        <div class="colorpicker" id="colorpicker">
            <?php
            $brickcolor = ["111111" => 1003, "CDCDCD" => 1002, "ECECEC" => 40, "F8F8F8" => 1001, "EDEAEA" => 348, "E9DADA" => 349, "FFC9C9" => 1025, "FF9494" => 337, "965555" => 344, 
            "A34B4B" => 1007, "883E3E" => 350, "562424" => 339, "FF5959" => 331, "750000" => 332, "970000" => 327, "FF0000" => 1004, "966766" => 360, "BE6862" => 338, "957977" => 153, 
            "CD544B" => 41, "C4281C" => 21, "958988" => 357, "BBB3B2" => 354, "DA867A" => 101, "D9856C" => 47, "97695B" => 176, "EEC4B6" => 100, "D36F4C" => 123, "904C2A" => 216, 
            "8F4C2A" => 345, "CF6024" => 193, "D5733D" => 133, "694028" => 192, "CC8E69" => 18, "564236" => 361, "AF9483" => 359, "AE7A59" => 128, "A05F35" => 38, "6C584B" => 355, 
            "7C5C46" => 217, "5A4C42" => 364, "E09864" => 137, "BFB7B1" => 111, "EAB892" => 125, "624732" => 25, "DA8541" => 106, "CB8442" => 12, "B48455" => 178, "6A3909" => 365, 
            "AA5500" => 1014, "FFCC99" => 1030, "756C62" => 168, "EBB87F" => 225, "E29B40" => 105, "E7AC58" => 121, "F3CF9B" => 36, "C7C1B7" => 103, "DCBC81" => 127, "7E683F" => 362, 
            "BC9B5D" => 351, "A0844F" => 356, "D3BE96" => 346, "C7AC78" => 352, "F0D5A0" => 224, "D7A94B" => 180, "E8AB2D" => 191, "685C43" => 108, "958A73" => 138, "B08E44" => 209, 
            "FFAF00" => 1017, "FFB000" => 1005, "EFB838" => 333, "D7C59A" => 5, "ECE8DE" => 50, "CABFA3" => 353, "938767" => 147, "F1E7C7" => 340, "ABA89E" => 358, "69665C" => 363, 
            "F8D96D" => 334, "F5CD30" => 24, "F9D62E" => 190, "FDEA8D" => 226, "F9E999" => 3, "E5E4DF" => 208, "FEF3BB" => 341, "E2DCBC" => 347, "FFF67B" => 157, "F8F184" => 49, 
            "F7F18D" => 44, "C1BE42" => 1008, "DFDFDE" => 325, "FFFFCC" => 1029, "FFFF00" => 1009, "D8DD56" => 134, "C7D23C" => 115, "828A5D" => 200, "D9E4A7" => 120, "A4BD47" => 119, 
            "7F8E64" => 1022, "6D6E6C" => 27, "B9C4B1" => 319, "A8BD99" => 324, "A1C48C" => 29, "3A7D15" => 1021, "7C9C6B" => 317, "94BE81" => 323, "C2DAB8" => 6, "2C651D" => 304, 
            "5B9A4C" => 310, "B1E5A6" => 328, "8AAB85" => 318, "1F801D" => 313, "575857" => 148, "CCFFCC" => 1028, "4B974B" => 37, "00FF00" => 1020, "348E40" => 309, "506D54" => 301, 
            "84B68D" => 48, "27462D" => 141, "709578" => 210, "A1A5A2" => 2, "287F47" => 28, "789082" => 151, "ABADAC" => 150, "9FF3E9" => 1027, "12EED4" => 1018, "B7D7D5" => 118, 
            "F2F3F3" => 1, "79B5B5" => 211, "00FFFF" => 1019, "008F9C" => 107, "55A5AF" => 116, "04AFEC" => 1013, "0989CF" => 315, "7DBBDD" => 232, "80BBDC" => 11, "C1DFF0" => 42, 
            "98C2DB" => 329, "B4D2E4" => 45, "9CA3A8" => 131, "0D69AC" => 23, "1B2A35" => 26, "AFDDFF" => 1024, "7BB6E8" => 43, "9FC3E9" => 212, "203A56" => 140, "CFE2F7" => 143, 
            "335882" => 306, "6E99CA" => 102, "527CAE" => 305, "C7D4E4" => 336, "74869D" => 135, "9FADC0" => 314, "7988A1" => 145, "4667A4" => 195, "23478B" => 196, "2154B9" => 1012, 
            "002060" => 1011, "C1CADE" => 39, "6C81B7" => 213, "161D32" => 149, "435493" => 110, "6874AC" => 112, "9FA1AC" => 311, "CACBD1" => 320, "5B5D69" => 302, "102ADC" => 307, 
            "0010B0" => 303, "A7A9CE" => 220, "E7E7EC" => 335, "A5A5CB" => 126, "0000FF" => 1010, "B1A7FF" => 1026, "342B75" => 268, "6B629B" => 219, "A3A2A5" => 194, "958EA3" => 146, 
            "6225D1" => 1031, "3D1585" => 308, "B480FF" => 1006, "877C90" => 136, "8C5B9F" => 1023, "6B327C" => 104, "96709F" => 218, "7B2F7B" => 322, "592259" => 312, "7B007B" => 316, 
            "AA00AA" => 1015, "8E4285" => 198, "A75E9B" => 321, "635F62" => 199, "FF00BF" => 1032, "923978" => 124, "FF66CC" => 1016, "D490BD" => 343, "FF98DC" => 330, "E0B2D0" => 342, 
            "C470A0" => 22, "CD6298" => 221, "898788" => 179, "E1A4C2" => 158, "E4ADC8" => 222, "E5ADC8" => 113, "E8BAC8" => 9, "DC9095" => 223, "7B2E2F" => 154];

            foreach ($brickcolor as $k => $v) {
                echo "<span class='color' colorbrick='$v' style='background-color:#$k;'></span>";
            }
            ?>
        </div>
        <script src="../character.js"></script>
        <script src="../titleanim.min.js"></script>
    </body>
</html>