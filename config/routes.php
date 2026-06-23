<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        // Set default home route to AuthController::login
        $builder->connect('/', ['controller' => 'Auth', 'action' => 'login']);

        // Explicit Auth Routes
        $builder->connect('/auth/login', ['controller' => 'Auth', 'action' => 'login']);
        $builder->connect('/auth/google', ['controller' => 'Auth', 'action' => 'google']);
        $builder->connect('/auth/google/callback', ['controller' => 'Auth', 'action' => 'callback']);
        $builder->connect('/auth/logout', ['controller' => 'Auth', 'action' => 'logout']);

        // Static Pages Routes
        $builder->connect('/terms', ['controller' => 'Pages', 'action' => 'display', 'terms']);
        $builder->connect('/privacy', ['controller' => 'Pages', 'action' => 'display', 'privacy']);

        // Group Session Routes
        $builder->connect('/sessions/create', ['controller' => 'GroupSessions', 'action' => 'create']);
        $builder->connect('/sessions/close/{id}', ['controller' => 'GroupSessions', 'action' => 'close'])
                ->setPatterns(['id' => '\d+'])
                ->setPass(['id']);
        $builder->connect('/sessions/join/{uuid}', ['controller' => 'GroupSessions', 'action' => 'join'])
                ->setPass(['uuid']);
        $builder->connect('/sessions/{uuid}', ['controller' => 'GroupSessions', 'action' => 'view'])
                ->setPass(['uuid']);

        // Receipts Routes
        $builder->post('/receipts/add/{sessionUuid}', ['controller' => 'Receipts', 'action' => 'add'])
                ->setPass(['sessionUuid']);
        $builder->post('/receipts/delete/{id}', ['controller' => 'Receipts', 'action' => 'delete'])
                ->setPass(['id']);
                
        // Payment Methods Routes
        $builder->resources('UserPaymentMethods', [
            'only' => ['index', 'add', 'edit', 'delete']
        ]);

        // Payment Proof Routes
        $builder->post('/payment-proofs/upload/{transactionId}', ['controller' => 'PaymentProofs', 'action' => 'upload'])
                ->setPass(['transactionId']);
        $builder->post('/payment-proofs/confirm/{transactionId}', ['controller' => 'PaymentProofs', 'action' => 'confirmSettlement'])
                ->setPass(['transactionId']);
        $builder->post('/payment-proofs/verify/{proofId}/{decision}', ['controller' => 'PaymentProofs', 'action' => 'verify'])
                ->setPass(['proofId', 'decision']);
        
        $builder->fallbacks();
    });
};
