<?php 
/*
    Random File Function
    Written By: Qassim Hassan
    Website: wp-time.com
    Twitter: @QQQHZ
*/
 
function Qassim_Random_File($folder_path = null){
    if( !empty($folder_path) ){ // if the folder path is not empty
        $files_array = scandir($folder_path);
        $count = count($files_array);
        if( $count > 2 ){ // if has files in the folder
            $minus = $count - 1;
            $random = rand(2, $minus);
            $random_file = $files_array[$random]; // random file, result will be for example: image.png
            $file_link = $folder_path . "/" . $random_file; // file link, result will be for example: your-folder-path/image.png
            //return str_replace('//','/',$file_link) . "\n";
            return readfile($file_link);
        }
    }
}
echo Qassim_Random_File("backgrounds/");
?>