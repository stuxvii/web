<?php
/*
__/\\\\\\\\\\\\\\\_______/\\\\\_______/\\\\\\\\\\\\__________/\\\\\______        :
 _\///////\\\/////______/\\\///\\\____\/\\\////////\\\______/\\\///\\\____       :
  _______\/\\\_________/\\\/__\///\\\__\/\\\______\//\\\___/\\\/__\///\\\__      :
   _______\/\\\________/\\\______\//\\\_\/\\\_______\/\\\__/\\\______\//\\\_     :
    _______\/\\\_______\/\\\_______\/\\\_\/\\\_______\/\\\_\/\\\_______\/\\\_    : finish the whole asset validation and upload and all that.
     _______\/\\\_______\//\\\______/\\\__\/\\\_______\/\\\_\//\\\______/\\\__   :
      _______\/\\\________\///\\\__/\\\____\/\\\_______/\\\___\///\\\__/\\\____  :
       _______\/\\\__________\///\\\\\/_____\/\\\\\\\\\\\\/______\///\\\\\/_____ :
        _______\///_____________\/////_______\////////////__________\/////_______:
*/
require_once 'auth.php';
if (!$authsuccessful) {
    header("Location: logout.php");
    exit;
}
header('Content-Type: application/json');

$target_dir = "uploads/";

$allowed_types = [
    'image' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ],
    'audio' => [
        'audio/mpeg' => 'mp3',
        'audio/ogg' => 'ogg',
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
    sendjsonback('error', 'Invalid request method or missing file.', 400); 
}

if ($_FILES["filetoupload"]["error"] !== UPLOAD_ERR_OK) {
    $error_msg = "Upload error: " . $_FILES["filetoupload"]["error"];
    sendjsonback('error', $error_msg, 500);
}

if ($_FILES["filetoupload"]["size"] > $max_file_size) {
    $msg .= "Sorry, your file is too large (max: " . ($max_file_size / 1000) . "KB). ";
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
    finfo_close($finfo);
    
    if (!array_key_exists($mime_type, $allowed_types)) {
        $msg .= "Sorry, only JPG and PNG files are allowed (Detected: " . $mime_type . "). ";
        $uploadOk = 0;
    }
    
    $safe_ext = $allowed_types[$mime_type] ?? null;

    $check = getimagesize($tmp_name);
    if ($check === false) {
        $msg .= "File is not valid. ";
        $uploadOk = 0;
    }
}

if ($uploadOk == 0) {
    sendjsonback('error', "Upload failed. " . $msg, 400);
} 

$new_file_name = uniqid() . '.' . $safe_ext;
$target_file = $target_dir . $new_file_name;

try {
    $imagick = new Imagick();
    $imagick->readImage($tmp_name);
    $imagick->stripImage();

    if ($safe_ext === 'jpg') {
        $imagick->setImageFormat('jpeg');
        $imagick->setCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setCompressionQuality(90);
    } elseif ($safe_ext === 'png') {
        $imagick->setImageFormat('png');
    }

    $save_success = $imagick->writeImage($target_file);
    $imagick->clear();
    $imagick->destroy();

    if ($save_success) {
        $msg = "Your asset has been uploaded and is pending approval.";
        sendjsonback('success', $msg, 201);
    } else {
        $msg = "Asset upload failed.";
        sendjsonback('error', $msg, 500);
    }
    
} catch (ImagickException $e) {
    $msg = "Asset processing failed: " . $e->getMessage();
    sendjsonback('error', $msg, 500);
}
$insert_sql = "INSERT INTO `items` (`name`,`asset`,`owner`,`value`,`public`,`approved`,`type`) VALUES (?,?,?,?,?,?,?)";
$stmt = $db->prepare($insert_sql);
$stmt->bind_param('ssiiiis', $assetname, $assetpath, $uid, $assetvalue, true, false, $assettype);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Successfully wrote the key to the database.<br>";
} else {
    echo "Failed to write the key to the database.<br>";
}

$stmt->close();
sendjsonback('error', 'Unknown server issue.', 500); 

?>