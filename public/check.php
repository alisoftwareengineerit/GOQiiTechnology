<?php
$path = __DIR__ . '/../src/Database.php';
header('Content-Type: text/plain');
echo "path={$path}\n";
echo 'exists=' . (file_exists($path) ? '1' : '0') . "\n";
echo 'real=' . (realpath($path) ?: 'null') . "\n";
