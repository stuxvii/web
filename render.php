<?php
require 'auth.php';
require 'brickcolor.php';

// --- Cooldown Configuration ---
$cooldown_seconds = 5;
$cooldown_file_path = "/tmp/render_cooldowns/";
// ------------------------------

if ($authsuccessful) {
    $input_colors = [
        "head" => $head,
        "trso" => $trso,
        "larm" => $larm,
        "rarm" => $rarm,
        "lleg" => $lleg,
        "rleg" => $rleg,
    ];

    $cooldown_file = $cooldown_file_path . $uid . ".time";
    
    if (!is_dir($cooldown_file_path)) {
        if (!mkdir($cooldown_file_path, 0755, true)) {
            error_log("Failed to create cooldown directory: " . $cooldown_file_path);
        }
    }
    
    $current_time = time();
    $last_render_time = 0;
    
    if (file_exists($cooldown_file)) {
        $last_render_time = (int)file_get_contents($cooldown_file);
    }
    
    $time_since_last_render = $current_time - $last_render_time;
    
    if ($time_since_last_render < $cooldown_seconds) {
        $wait_time = $cooldown_seconds - $time_since_last_render;
        http_response_code(429);
        echo "Please wait $wait_time seconds before requesting another render.";
        exit;
    }

    $bpdata = [];
    
    foreach ($input_colors as $part => $code) {
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
        echo "No valid brick color data provided to render.";
        exit;
    }
    
    $json_arg = json_encode($bpdata);

    $escaped_json_arg = escapeshellarg($json_arg);
    
    $output_file = "$uid" . ".png";
    $output_dir = "/usr/share/nginx/html/renders/";
    $full_output_path = $output_dir . $output_file; 
    $command = "blender -b /usr/share/nginx/html/char.blend --python /usr/share/nginx/html/render.py -- {$escaped_json_arg} " . escapeshellarg($full_output_path);

    $render_output = exec($command, $output_lines, $return_var);
    
    if ($return_var === 0) {
        if (file_put_contents($cooldown_file, $current_time) === false) {
             error_log("Failed to write cooldown time to: " . $cooldown_file);
        }
        
        echo "renders/" . $output_file; 
    } else {
        error_log("Blender execution failed with return code $return_var. Output: " . implode("\n", $output_lines));
        http_response_code(500); 
        echo "Error rendering avatar.";
    }

} else {
    http_response_code(401);
    echo "Authentication failed.";
}
?>
