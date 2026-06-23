<?php
require 'config/paths.php';
require 'vendor/autoload.php';

$app = new \App\Application('config');
$app->bootstrap();

$db = \Cake\Datasource\ConnectionManager::get('default');

function runScenario1($db) {
    echo "--- SCENARIO 1: The Late Joiner Villa Trap ---\n";
    
    $sessionsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('GroupSessions');
    $participantsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Participants');
    $expensesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Expenses');
    $allocsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('ExpenseAllocations');
    $chargesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('SessionCharges');

    $session = $sessionsTable->newEntity([
        'uuid' => 'scen1',
        'name' => 'Scenario 1',
        'host_id' => 1,
        'status' => 'open',
        'max_participants' => 20
    ]);
    $sessionsTable->save($session);
    $sessionId = $session->id;
    
    $names = ['Adam', 'Bella', 'Chong', 'Devi', 'Ethan', 'Farah', 'Gavin'];
    $pids = [];
    foreach ($names as $name) {
        $p = $participantsTable->newEntity(['session_id' => $sessionId, 'guest_name' => $name]);
        $participantsTable->save($p);
        $pids[$name] = $p->id;
    }
    
    // Exp A
    $expA = $expensesTable->newEntity(['session_id' => $sessionId, 'participant_id' => $pids['Adam'], 'description' => 'A', 'total_amount' => 257.89, 'expense_type' => 'group', 'split_type' => 'equal']);
    $expensesTable->save($expA);
    $allocsTable->save($allocsTable->newEntity(['expense_id' => $expA->id, 'participant_id' => $pids['Adam'], 'is_payer' => true, 'amount_owed' => 0]));
    $consA = ['Adam', 'Bella', 'Chong', 'Devi', 'Ethan'];
    $amt = round(257.89 / 5, 2);
    $accum = 0;
    foreach ($consA as $i => $c) {
        $owed = ($i == 4) ? round(257.89 - $accum, 2) : $amt;
        $allocsTable->save($allocsTable->newEntity(['expense_id' => $expA->id, 'participant_id' => $pids[$c], 'is_payer' => false, 'amount_owed' => $owed]));
        $accum += $amt;
    }
    
    // Exp B
    $expB = $expensesTable->newEntity(['session_id' => $sessionId, 'participant_id' => $pids['Bella'], 'description' => 'B', 'total_amount' => 73.00, 'expense_type' => 'group', 'split_type' => 'equal']);
    $expensesTable->save($expB);
    $allocsTable->save($allocsTable->newEntity(['expense_id' => $expB->id, 'participant_id' => $pids['Bella'], 'is_payer' => true, 'amount_owed' => 0]));
    $consB = ['Bella', 'Chong', 'Devi', 'Farah'];
    $amt = round(73.00 / 4, 2);
    $accum = 0;
    foreach ($consB as $i => $c) {
        $owed = ($i == 3) ? round(73.00 - $accum, 2) : $amt;
        $allocsTable->save($allocsTable->newEntity(['expense_id' => $expB->id, 'participant_id' => $pids[$c], 'is_payer' => false, 'amount_owed' => $owed]));
        $accum += $amt;
    }
    
    // Exp C
    $expC = $expensesTable->newEntity(['session_id' => $sessionId, 'participant_id' => $pids['Chong'], 'description' => 'C', 'total_amount' => 199.99, 'expense_type' => 'group', 'split_type' => 'equal']);
    $expensesTable->save($expC);
    $allocsTable->save($allocsTable->newEntity(['expense_id' => $expC->id, 'participant_id' => $pids['Chong'], 'is_payer' => true, 'amount_owed' => 0]));
    $consC = ['Adam', 'Chong', 'Devi', 'Ethan', 'Farah'];
    $amt = round(199.99 / 5, 2);
    $accum = 0;
    foreach ($consC as $i => $c) {
        $owed = ($i == 4) ? round(199.99 - $accum, 2) : $amt;
        $allocsTable->save($allocsTable->newEntity(['expense_id' => $expC->id, 'participant_id' => $pids[$c], 'is_payer' => false, 'amount_owed' => $owed]));
        $accum += $amt;
    }
    
    // Exp D
    $expD = $expensesTable->newEntity(['session_id' => $sessionId, 'participant_id' => $pids['Ethan'], 'description' => 'D', 'total_amount' => 11.11, 'expense_type' => 'group', 'split_type' => 'equal']);
    $expensesTable->save($expD);
    $allocsTable->save($allocsTable->newEntity(['expense_id' => $expD->id, 'participant_id' => $pids['Ethan'], 'is_payer' => true, 'amount_owed' => 0]));
    $allocsTable->save($allocsTable->newEntity(['expense_id' => $expD->id, 'participant_id' => $pids['Ethan'], 'is_payer' => false, 'amount_owed' => 11.11]));
    
    // Charges
    $chargesTable->save($chargesTable->newEntity(['session_id' => $sessionId, 'charge_name' => 'SST', 'charge_type' => 'percentage', 'charge_value' => 6, 'applies_to' => 'proportional']));
    $chargesTable->save($chargesTable->newEntity(['session_id' => $sessionId, 'charge_name' => 'Service', 'charge_type' => 'percentage', 'charge_value' => 10, 'applies_to' => 'proportional']));
    $chargesTable->save($chargesTable->newEntity(['session_id' => $sessionId, 'charge_name' => 'Villa', 'charge_type' => 'flat', 'charge_value' => 70.00, 'applies_to' => 'equal']));

    $calc = new \App\Service\CalculationEngineService();
    $totals = $calc->calculateParticipantTotals($sessionId);
    $netDebt = new \App\Service\NetDebtSettlementService();
    $txns = $netDebt->calculateSettlements($sessionId);
    
    $reversePids = array_flip($pids);
    
    echo "GRAND TOTALS:\n";
    $gt = 0;
    foreach ($totals as $pid => $data) {
        echo str_pad($reversePids[$pid], 6) . " : " . $data['grand_total'] . "\n";
        $gt += $data['grand_total'];
    }
    echo "SUM: " . $gt . "\n\n";
    
    echo "TRANSACTIONS:\n";
    foreach ($txns as $t) {
        echo $reversePids[$t['debtor_id']] . " pays " . $reversePids[$t['creditor_id']] . " : RM " . $t['amount'] . "\n";
    }
    echo "\n";
}

runScenario1($db);
