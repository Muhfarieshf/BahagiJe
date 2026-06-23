<?php
require 'config/paths.php';
require 'vendor/autoload.php';

$app = new \App\Application('config');
$app->bootstrap();

$db = \Cake\Datasource\ConnectionManager::get('default');

// Get the latest session that has expenses
$session = $db->execute('SELECT session_id as id FROM expenses ORDER BY id DESC LIMIT 1')->fetch('assoc');

if (!$session) {
    die("No expenses found in any session.\n");
}

$calc = new \App\Service\CalculationEngineService();
$totals = $calc->calculateParticipantTotals($session['id']);

$netDebt = new \App\Service\NetDebtSettlementService();
$txns = $netDebt->calculateSettlements($session['id']);

$expenses = $db->execute('SELECT * FROM expenses WHERE session_id = ?', [$session['id']])->fetchAll('assoc');
$allocs = $db->execute('
    SELECT ea.*, p.guest_name, p.user_id 
    FROM expense_allocations ea 
    JOIN participants p ON p.id = ea.participant_id 
    WHERE ea.expense_id IN (SELECT id FROM expenses WHERE session_id = ?)
', [$session['id']])->fetchAll('assoc');

$charges = $db->execute('SELECT * FROM session_charges WHERE session_id = ?', [$session['id']])->fetchAll('assoc');

echo "SESSION ID: " . $session['id'] . "\n\n";

echo "CHARGES:\n";
print_r($charges);
echo "\n";

echo "EXPENSES:\n";
print_r($expenses);
echo "\n";

echo "ALLOCATIONS:\n";
print_r($allocs);
echo "\n";

echo "TOTALS:\n";
print_r($totals);
echo "\n";

echo "TRANSACTIONS:\n";
print_r($txns);
echo "\n";
