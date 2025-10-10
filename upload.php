<?php
require 'vendor/autoload.php';

use FFMpeg\Format\Audio\DefaultAudio;
use FFMpeg\Format\AudioInterface;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;

class CustomMp3Format extends DefaultAudio implements AudioInterface
{
    protected $additionalParameters = [];

    public function __construct()
    {
        $this->audioCodec = 'libmp3lame';
    }

    public function setAdditionalParameters(array $additionalParameters): void
    {
        $this->additionalParameters = $additionalParameters;
    }

    public function getExtraParams(): array
    {
        return $this->additionalParameters;
    }

    public function getAvailableAudioCodecs(): array
    {
        return ['libmp3lame'];
    }
}

require_once 'auth.php';
if (!$authsuccessful) {
    header('Location: logout.php');
    exit;
}

header('Content-Type: application/json');

$target_dir = 'uploads/';

$allowed_types = [
    'Shr' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
    'Dec' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
    'Aud' => [
        'audio/mpeg',
        'audio/x-wav',
        'audio/ogg',
        'audio/wav'
    ]
];

$max_file_size = 10000000;
$audio_target_size_bits = 2 * 1024 * 1024 * 8 * 2;

function sendjsonback(string $status, string $message, int $http_code = 200): void
{
    http_response_code($http_code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

function handle_db_operations(
    mysqli $db, 
    string $assetname, 
    string $target_file, 
    int $uid, 
    int $assetvalue, 
    string $assettype, 
    string $inv,
    string $assetdesc
): void {
    if ($assetvalue > 2147483647) { // failsafe for if the user feels silly enough to put a quadvigintillion dollars for their price
        $assetvalue = 2147483647;
    }
    $false = 0;
    $true = 1;

    try {
        $uploadts = time();
        $insert_sql = 'INSERT INTO `items` (`name`,`asset`,`owner`,`value`,`public`,`approved`,`uploadts`,`type`,`desc`) VALUES (?,?,?,?,?,?,?,?,?)';
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param('ssiiiiiss', $assetname, $target_file, $uid, $assetvalue, $true, $false, $uploadts, $assettype, $assetdesc);
        $stmt->execute();
        $itemid = $db->insert_id;
        $stmt->close();

        $curinv = json_decode($inv, true) ?? [];
        $curinv[] = $itemid;
        $newinv = json_encode($curinv);

        $stmtupdinv = $db->prepare('UPDATE economy SET inv = ? WHERE id = ?');
        $stmtupdinv->bind_param('si', $newinv, $uid);
        $stmtupdinv->execute();
        $stmtupdinv->close();

        sendjsonback('success', 'Your asset has been uploaded, and is pending approval.', 201);

    } catch (\Exception $e) {
        error_log("DB Error: " . $e->getMessage());
        sendjsonback('error', 'Database operation failed.', 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['filetoupload'], $_POST['type'], $_POST['itemname'], $_POST['itemprice'])) {
    sendjsonback('error', 'Invalid or incomplete request.', 400);
}

$file = $_FILES['filetoupload'];
$assettype = $_POST['type'];
$assetname = trim($_POST['itemname']);
$assetdesc = trim($_POST['itemdesc']);
$assetvalue = (int)$_POST['itemprice'];
$tmp_name = $file['tmp_name'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    sendjsonback('error', 'Upload error code: ' . $file['error'], 500);
}

if ($assetvalue < 0) {
    sendjsonback('error', 'Sorry, but you may not upload items with negative prices.', 400);
}

if ($file['size'] > $max_file_size) {
    $max_size_mb = round($max_file_size / 1024 / 1024, 2); 
    sendjsonback('error', "Sorry, your file is too large (max: {$max_size_mb}MB).", 400);
}

if (empty($tmp_name) || !is_uploaded_file($tmp_name)) {
    sendjsonback('error', 'File upload failed or no file was selected.', 400);
}

if (!isset($allowed_types[$assettype])) {
    sendjsonback('error', 'Unsupported asset type provided.', 400);
}

$allowed_mimes = $allowed_types[$assettype];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
if ($finfo === false) {
    sendjsonback('error', 'Server error: Cannot open fileinfo.', 500);
}
$mime_type = finfo_file($finfo, $tmp_name);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_mimes, true)) {
    sendjsonback('error', "File type '{$mime_type}' is not allowed for asset type '{$assettype}'.", 400);
}

$new_file_name = uniqid();

if ($assettype === 'Shr' || $assettype === 'Dec') {
    try {
        $target_file = $target_dir . $new_file_name . '.png';

        $imagick = new Imagick();
        $imagick->readImage($tmp_name);
        $imagick->stripImage();
        $imagick->setImageFormat('png');

        if (!$imagick->writeImage($target_file)) {
            sendjsonback('error', 'Asset upload failed during image save.', 500);
        }
        $imagick->clear();
        $imagick->destroy();
        handle_db_operations($db, $assetname, $target_file, $uid, $assetvalue, $assettype, $inv, $assetdesc);

    } catch (ImagickException $e) {
        sendjsonback('error', 'Asset processing failed (Imagick): ' . $e->getMessage(), 500);
    }

} elseif ($assettype === 'Aud') {
    try {
        $target_file = $target_dir . $new_file_name . '.mp3';

        $ffmpeg = FFMpeg::create();
        $ffprobe = FFProbe::create();

        $audio = $ffmpeg->open($tmp_name);
        $duration_seconds = $ffprobe->format($tmp_name)->get('duration');

        if (!$duration_seconds || $duration_seconds <= 0) {
            sendjsonback('error', 'Could not determine audio duration or duration is zero.', 400);
        }

        $format = new CustomMp3Format();
        $target_abr = floor($audio_target_size_bits / $duration_seconds);
        $target_abr_kbps = round($target_abr / 1000);
        
        $min_abr_kbps = 64;
        $max_abr_kbps = 320;

        $final_abr_kbps = max($min_abr_kbps, min($max_abr_kbps, $target_abr_kbps));
        $format->setAudioKiloBitrate($final_abr_kbps);
        $format->setAdditionalParameters(['-af', 'loudnorm=i=-16:lra=11:tp=-1.5']);
        $audio->save($format, $target_file);
        handle_db_operations($db, $assetname, $target_file, $uid, $assetvalue, $assettype, $inv, $assetdesc);

    } catch (\FFMpeg\Exception\ExceptionInterface $e) {
        sendjsonback('error', 'Audio processing failed (FFMpeg): ' . $e->getMessage(), 500);
    } catch (\Exception $e) {
        sendjsonback('error', 'Asset upload failed: ' . $e->getMessage(), 500);
    }
}
sendjsonback('error', 'Unknown server issue.', 500);

?>