<?php

$phpIniPath = 'C:\MAMP\bin\php\php8.3.1\php.ini';

echo "🔧 Fixing PHP configuration...\n\n";

// Read current content
$content = file_get_contents($phpIniPath);

// Remove any literal \n artifacts
$content = str_replace('\\nupload_tmp_dir', PHP_EOL . 'upload_tmp_dir', $content);

// Settings to ensure
$settings = [
    'upload_max_filesize' => '10M',
    'post_max_size' => '20M',
    'upload_tmp_dir' => '"C:/MAMP/tmp/php"',
    'max_file_uploads' => '20',
];

foreach ($settings as $key => $value) {
    // Check if setting exists (commented or uncommented)
    if (preg_match('/^;?\s*' . preg_quote($key, '/') . '\s*=/m', $content)) {
        // Update existing line
        $content = preg_replace(
            '/^;?\s*' . preg_quote($key, '/') . '\s*=.*/m',
            $key . ' = ' . $value,
            $content
        );
        echo "✓ Updated: $key = $value\n";
    } else {
        // Add new setting at the end
        $content .= PHP_EOL . $key . ' = ' . $value;
        echo "✓ Added: $key = $value\n";
    }
}

// Write back
file_put_contents($phpIniPath, $content);

echo "\n✅ PHP configuration updated successfully!\n";
echo "\n📋 Current settings:\n";

// Verify
foreach ($settings as $key => $value) {
    $actual = ini_get($key);
    echo "   $key: $actual\n";
}

echo "\n⚠️  IMPORTANT: Restart MAMP for changes to take effect!\n";
