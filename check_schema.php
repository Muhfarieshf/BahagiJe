<?php
require 'config/paths.php';
require 'vendor/autoload.php';
$app = new \App\Application('config');
$app->bootstrap();
$db = \Cake\Datasource\ConnectionManager::get('default');

$tables = ['group_sessions', 'settlement_transactions', 'payment_proofs', 'users', 'participants'];
foreach ($tables as $t) {
    echo "\n=== $t ===\n";
    $r = $db->execute("SHOW COLUMNS FROM $t");
    foreach ($r->fetchAll('assoc') as $col) {
        echo "  " . $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . " | " . $col['Default'] . "\n";
    }
}
