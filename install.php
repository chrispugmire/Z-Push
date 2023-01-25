<?php
$owner = "www-data";
mkdir("/var/lib/z-push");
mkdir("/var/log/z-push");
mkdir("/usr/share/z-push");
recurseCopy("src","/usr/share/z-push","");
echo "\n";



function recurseCopy(string $sourceDirectory, string $destinationDirectory) {
    global $owner;
    $directory = opendir($sourceDirectory);
    $ncopy = 0;
    if (is_dir($destinationDirectory) === false) {
        mkdir($destinationDirectory);
    }
    while (($file = readdir($directory)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $fullsrc = $sourceDirectory."/".$file;
        $fulldst = $destinationDirectory."/".$file;
        if (is_dir($fullsrc) === true) {
            recurseCopy($fullsrc,$fulldst);
        } else {
            if ($file == "config.php") {
                if (file_exists($fulldst)) {
                    echo "Not modifying ".$fulldst."\n";
                    continue ;
                }
            }
            if (strpos($file,".php")==false) continue;
              if (strpos($file,"policies.ini")==false) continue;
            //echo "copy ".$fullsrc." to ".$fulldst."\n";
            $ncopy += 1;
            copy($fullsrc,$fulldst);
        }
    }
    echo "Coppied ".$ncopy." files to ".$destinationDirectory."\n";

    closedir($directory);
}
