<?php

// Check if people used Composer to include this project in theirs
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/src/Parser.php';
    require __DIR__ . '/src/Drawer.php';
    require __DIR__ . '/src/FieldAbstract.php';
    require __DIR__ . '/src/FieldText.php';
    require __DIR__ . '/src/FieldParam.php';
    require __DIR__ . '/src/Helpers.php';
} else {
    require __DIR__ . '/vendor/autoload.php';
}