<?php
require 'config/paths.php';
require 'vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;

$app = new \App\Application(dirname(__DIR__));
$app->bootstrap();

$transactionsTable = TableRegistry::getTableLocator()->get('SettlementTransactions');
$txn = $transactionsTable->find()->where(['id' => 11])->first();
if ($txn) {
    echo "TXN FOUND: \n";
    print_r($txn->toArray());
} else {
    echo "TXN 11 NOT FOUND IN DB!\n";
}

$txnWithContain = $transactionsTable->find()->where(['SettlementTransactions.id' => 11])->contain(['GroupSessions'])->first();
if ($txnWithContain) {
    echo "TXN WITH CONTAIN FOUND\n";
} else {
    echo "TXN WITH CONTAIN NOT FOUND!\n";
}
