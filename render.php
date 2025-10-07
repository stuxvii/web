<?php
require 'auth.php';
require 'brickcolor.php';

// --- Cooldown Configuration ---
$cooldown_seconds = 5;
$cooldown_file_path = '/tmp/render_cooldowns/';
// ------------------------------

if ($authsuccessful) {

    $cooldown_file = $cooldown_file_path . $uid . '.time';

    if (!is_dir($cooldown_file_path)) {
        if (!mkdir($cooldown_file_path, 0755, true)) {
            error_log('Failed to create cooldown directory: ' . $cooldown_file_path);
        }
    }

    $current_time = time();
    $last_render_time = 0;

    if (file_exists($cooldown_file)) {
        $last_render_time = (int) file_get_contents($cooldown_file);
    }

    $time_since_last_render = $current_time - $last_render_time;

    if ($time_since_last_render < $cooldown_seconds) {
        $wait_time = $cooldown_seconds - $time_since_last_render;
        http_response_code(429);
        exit;
    }

    $bpdata = [];

    foreach ($avatarcolors as $part => $code) {
        if (!is_int($code) || $code <= 0) {
            continue;
        }

        $hex = array_search($code, $brickcolor);

        if ($hex === false) {
            error_log("Color code $code for part $part not found in brickcolor map.");
            continue;
        }

        $bpdata[] = [
            'id' => $part,
            'hex' => '#' . $hex
        ];
    }

    if (empty($bpdata)) {
        http_response_code(400);
        exit;
    }

    $stmt = $db->prepare("SELECT asset FROM items WHERE id = ?");
    $stmt->bind_param('i', $tshirt);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $bpdata[] = [
        'id' => 'shirt',
        'image' => $row['asset']
    ];
    $json_arg = json_encode($bpdata);
    error_log($json_arg);
    $escaped_json_arg = escapeshellarg($json_arg);

    $output_file = "$uid" . '.png';
    $output_dir = '/usr/share/nginx/html/renders/';
    $full_output_path = $output_dir . $output_file;
    $command = "blender -b /usr/share/nginx/html/char.blend --python /usr/share/nginx/html/render.py -- {$escaped_json_arg} " . escapeshellarg($full_output_path);

    $render_output = exec($command, $output_lines, $return_var);

    if ($return_var === 0) {
        if (file_put_contents($cooldown_file, $current_time) === false) {
            error_log('Failed to write cooldown time to: ' . $cooldown_file);
        }

        echo 'renders/' . $output_file;
    } else {
        error_log("Blender execution failed with return code $return_var. Output: " . implode("\n", $output_lines));
        http_response_code(500);
        echo 'Error rendering avatar.';
    }
} else {
    http_response_code(401);
    echo 'Authentication failed.';
}
?>
