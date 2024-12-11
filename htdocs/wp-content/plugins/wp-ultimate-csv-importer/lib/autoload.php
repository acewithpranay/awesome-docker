<?php
    spl_autoload_register(function ($class) {
    // An array where keys are namespace prefixes and values are base directories for the classes in those namespaces
    $prefixes = [
        'PhpParser\\' => __DIR__ . '/PhpParser/',
        'NXP\\' => __DIR__ . '/NXP/',
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        // Get the relative class name
        $relative_class = substr($class, $len);

        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
            return; 
        }
    }
});
