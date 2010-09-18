<?php
$path = __DIR__ . DIRECTORY_SEPARATOR . 'TypeHintMe.phar';
if (file_exists($path)) {
    unlink($path);
}

$phar = new Phar($path, 0, basename($path));
$phar->setStub($phar->createDefaultStub('stub.php'));
$phar->buildFromDirectory(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src', '/\.php$/');

require_once $path;