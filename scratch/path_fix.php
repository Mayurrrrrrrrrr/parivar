<?php
function replaceInDir($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === '.git') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            replaceInDir($path);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, ['php', 'css', 'js', 'html', 'md', 'sh'])) {
                $content = file_get_contents($path);
                $newContent = str_replace('/parivar/', '/', $content);
                if ($newContent !== $content) {
                    file_put_contents($path, $newContent);
                    echo "Fixed: $path\n";
                }
            }
        }
    }
}

replaceInDir(__DIR__ . '/..');
