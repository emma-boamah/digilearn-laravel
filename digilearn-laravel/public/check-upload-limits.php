<?php
// Temporary script to check PHP upload limits
// DELETE THIS FILE after checking for security

echo "<h2>PHP Upload Settings Check</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Setting</th><th>Current Value</th><th>Status</th></tr>";

$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$memory = ini_get('memory_limit');
$max_time = ini_get('max_execution_time');

echo "<tr><td>upload_max_filesize</td><td>{$upload_max}</td><td>" . (return_bytes($upload_max) >= return_bytes('600M') ? '✅ OK' : '❌ Too Low') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>{$post_max}</td><td>" . (return_bytes($post_max) >= return_bytes('600M') ? '✅ OK' : '❌ Too Low') . "</td></tr>";
echo "<tr><td>memory_limit</td><td>{$memory}</td><td>" . (return_bytes($memory) >= return_bytes('512M') ? '✅ OK' : '❌ Too Low') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>{$max_time}s</td><td>" . ($max_time >= 300 ? '✅ OK' : '❌ Too Low') . "</td></tr>";

echo "</table>";

echo "<h3>Recommendations:</h3>";
echo "<p>For 600MB video uploads, you need:</p>";
echo "<ul>";
echo "<li>upload_max_filesize = 600M</li>";
echo "<li>post_max_size = 600M</li>";
echo "<li>memory_limit = 512M</li>";
echo "<li>max_execution_time = 300</li>";
echo "</ul>";

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int) $val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

echo "<p><strong>Note:</strong> Delete this file after checking for security reasons.</p>";
?>
