<?php
$pdo = new PDO('mysql:host=localhost;dbname=equisplit', 'root', '');
$stmt = $pdo->query('SELECT * FROM settlement_transactions');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
