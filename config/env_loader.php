<?php
// config/env_loader.php

/**
 * Helper to load environment variables from a .env file into PHP's environment.
 */
function loadEnv($dir) {
    $filePath = rtrim($dir, '/') . '/.env';
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignore comments
        if (strpos($line, '#') === 0) {
            continue;
        }

        // Must contain '='
        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove surrounding quotes if any
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match('/^\'(.*)\'$/', $value, $matches)) {
            $value = $matches[1];
        }

        // Put in environment
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        } elseif (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        } else {
            $value = getenv($key);
        }

        if ($value === false || $value === null) {
            return $default;
        }

        // Handle boolean and null representations
        $lowerValue = strtolower($value);
        if ($lowerValue === 'true') return true;
        if ($lowerValue === 'false') return false;
        if ($lowerValue === 'null') return null;

        return $value;
    }
}

// Automatically load .env from the project root directory
loadEnv(dirname(__DIR__));
