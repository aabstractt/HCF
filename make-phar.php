<?php

$file_phar = 'HCF.phar';

if (file_exists($file_phar)) {
    echo "Phar file already exists!";

    echo PHP_EOL;

    echo "overwriting...";

    Phar::unlinkArchive($file_phar);
}

$files = [];
$dir = getcwd() . DIRECTORY_SEPARATOR;

$exclusions = [".idea", ".gitignore", "composer.json", "composer.lock", "make-phar.php", ".git", "vendor", "composer.phar"];

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $path => $file) {
    $bool = true;
    foreach ($exclusions as $exclusion) {
        if (str_contains($path, $exclusion)) {
            $bool = false;
        }
    }

    if (!$bool) {
        continue;
    }

    if ($file->isFile() === false) {
        continue;
    }
    $files[str_replace($dir, "", $path)] = $path;
}

echo "Compressing..." . PHP_EOL;

$phar = new Phar($file_phar);
$phar->startBuffering();
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->buildFromIterator(new ArrayIterator($files));
//$phar->setStub('<?php echo "by zOmArRD"; __HALT_COMPILER();');
$phar->compressFiles(Phar::GZ);
$phar->stopBuffering();
echo "end." . PHP_EOL;