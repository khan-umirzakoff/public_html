<?php
// Check PHP settings that affect session handling

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Output Buffering: " . (ini_get('output_buffering') ? 'ON (' . ini_get('output_buffering') . ')' : 'OFF') . "\n";
echo "Session Auto Start: " . (ini_get('session.auto_start') ? 'ON' : 'OFF') . "\n";
echo "Session Save Path: " . ini_get('session.save_path') . "\n";
echo "Session Name: " . ini_get('session.name') . "\n";
echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "\n";
echo "Error Reporting: " . ini_get('error_reporting') . "\n";
