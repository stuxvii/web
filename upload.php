<?php
require 'vendor/autoload.php';
use FFMpeg\Format\Audio\DefaultAudio;
use FFMpeg\Format\AudioInterface;
class CustomMp3Format extends DefaultAudio implements AudioInterface
{
    protected $additionalParameters = [];

    public function __construct()
    {
        $this->audioCodec = 'libmp3lame';
    }
    public function setAdditionalParameters(array $additionalParameters)
    {
        $this->additionalParameters = $additionalParameters;
    }

    public function getExtraParams()
    {
        return $this->additionalParameters;
    }
    public function getAvailableAudioCodecs()
    {
        return ['libmp3lame'];
    }
}
require_once 'auth.php';
if (!$authsuccessful) {
    header("Location: logout.php");
    exit;
}
header('Content-Type: application/json');

$target_dir = "uploads/";

$allowed_types = [
    'Shr' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ],    
    'Dec' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ],
    'Aud' => [
        'audio/mpeg' => 'mp3',
        'audio/ogg' => 'ogg',
        'audio/wav' => 'wav'
    ]
];

$max_file_size = 10000000;
$msg = '';
$uploadOk = 1;

function sendjsonback($status, $message, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_FILES["filetoupload"])) {
    sendjsonback('error', 'Invalid upload.', 400);
}

if ($_FILES["filetoupload"]["error"] !== UPLOAD_ERR_OK) {
    $error_msg = "Upload error: " . $_FILES["filetoupload"]["error"];
    sendjsonback('error', $error_msg, 500);
}

if ((int)$_POST['itemprice'] < 0) {
    $msg .= "Sorry, but you may not upload items with negative prices. ";
    sendjsonback('error', $msg, 400);
    $uploadOk = 0;
}

if ($_FILES["filetoupload"]["size"] > $max_file_size) {
    $msg .= "Sorry, your file is too large (max: " . ($max_file_size / 1000) . "KB). ";
    sendjsonback('error', 'Invalid upload.', 400);
    $uploadOk = 0;
}

$tmp_name = $_FILES["filetoupload"]["tmp_name"];

if (empty($tmp_name) || !is_uploaded_file($tmp_name)) {
    $msg .= "File upload failed or no file was selected. ";
    $uploadOk = 0;
}

if ($uploadOk == 1) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $tmp_name);
    $assettype = $_POST['type'];
    $category = $allowed_types[$assettype];
    finfo_close($finfo);
    
    if (!array_key_exists($mime_type, $category)) {
        $msg .= "Sorry, only JPEG, PNG, WebP, MP3, Waveform and OGG files are allowed (Detected: " . $mime_type . "). ";
        $uploadOk = 0;
    }
    
    $safe_ext = $allowed_types[$mime_type] ?? null;
}

if ($uploadOk == 0) {
    sendjsonback('error', "Upload failed. " . $msg, 400);
} 

$new_file_name = uniqid();
$assetname = $_POST['itemname'];
$assetvalue = (int)$_POST['itemprice'];
$false = 0;
if ($assettype == "Shr" || $assettype == "Dec") {
    try {
        $target_file = $target_dir . $new_file_name . ".png";
        $imagick = new Imagick();
        $imagick->readImage($tmp_name);
        $imagick->stripImage();

        $imagick->setImageFormat('png');

        $save_success = $imagick->writeImage($target_file);
        $imagick->clear();
        $imagick->destroy();

        if ($save_success) {
            $insert_sql = "INSERT INTO `items` (`name`,`asset`,`owner`,`value`,`public`,`approved`,`type`) VALUES (?,?,?,?,?,?,?)";
            $stmt = $db->prepare($insert_sql);
            $stmt->bind_param('ssiiiis', $assetname, $target_file, $uid, $assetvalue, $false, $false, $assettype);
            $stmt->execute();
            $stmt->close();
            $msg = "Your asset has been uploaded, and is pending approval.";
            sendjsonback('success', $msg, 201);
        } else {
            $msg = "Asset upload failed.";
            sendjsonback('error', $msg, 500);
        }
        
    } catch (ImagickException $e) {
        $msg = "Asset processing failed: " . $e->getMessage();
        sendjsonback('error', $msg, 500);
    }
} else if ($assettype == "Aud") {
    $TARGET_FILE_SIZE_BITS = 2 * 1024 * 1024 * 16; 

    try {
        $target_file = $target_dir . $new_file_name . ".mp3";

        $ffmpeg = FFMpeg\FFMpeg::create();
        $ffprobe = FFMpeg\FFProbe::create();
        $audio = $ffmpeg->open($tmp_name);
        $duration_format = $ffprobe->format($tmp_name);
        $duration_seconds = $duration_format->get('duration');
        $format = new CustomMp3Format();

        $target_abr = floor($TARGET_FILE_SIZE_BITS / $duration_seconds);

        $target_abr_kbps = round($target_abr / 1000); 
        $min_abr_kbps = 64;
        $max_abr_kbps = 320;

        if ($target_abr_kbps < $min_abr_kbps) {
                $target_abr_kbps = $min_abr_kbps;
        } elseif ($target_abr_kbps > $max_abr_kbps) {
                $target_abr_kbps = $max_abr_kbps;
        }

        $format->setAudioKiloBitrate($target_abr_kbps);
        $format->setAdditionalParameters(array('-af', 'loudnorm=i=-16:lra=11:tp=-1.5')); // normalizing audio so your ears don't get exploded into a black hole
        $save_success = $audio->save($format, $target_file);

        $insert_sql = "INSERT INTO `items` (`name`,`asset`,`owner`,`value`,`public`,`approved`,`type`) VALUES (?,?,?,?,?,?,?)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param('ssiiiis', $assetname, $target_file, $uid, $assetvalue, $false, $false, $assettype);
        $stmt->execute();
        $stmt->close();

        $msg = "Your asset has been uploaded, and is pending approval.";
        sendjsonback('success', $msg, 201);

    } catch (\FFMpeg\Exception\ExceptionInterface $e) {
        $msg = "Audio processing failed: " . $e->getMessage();
        sendjsonback('error', $msg, 500);
    } catch (\Exception $e) {
        $msg = "Asset upload failed: " . $e->getMessage();
        sendjsonback('error', $msg, 500);
    }

}

sendjsonback('error', 'Unknown server issue.', 500); 

?>