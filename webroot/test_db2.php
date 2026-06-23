<?php
require dirname(__DIR__) . '/config/paths.php';
require dirname(__DIR__) . '/vendor/autoload.php';

$app = new \App\Application(dirname(__DIR__));
$app->bootstrap();

$settlements = \Cake\ORM\TableRegistry::getTableLocator()->get('SettlementTransactions')->find()
    ->where(['SettlementTransactions.session_id' => 11])
    ->contain([
        'Debtors' => ['Users'], 
        'Creditors' => ['Users' => ['UserPaymentMethods']],
        'PaymentProofs'
    ])
    ->all()
    ->toArray();

echo "TESTING CAKEPHP ORM:\n";
foreach ($settlements as $s) {
    echo "Entity ID: " . $s->id . "\n";
    echo "Entity session_id: " . $s->session_id . "\n";
    print_r($s->toArray());
}
