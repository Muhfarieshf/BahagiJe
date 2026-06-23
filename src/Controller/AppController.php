<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;

class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        
        // Load Authentication component
        $this->loadComponent('Authentication.Authentication');
        
        // Load Authorization component (Foundational)
        $this->loadComponent('Authorization.Authorization');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Base authentication check is handled automatically by AuthenticationComponent and Middleware.
        // Unauthenticated requests will redirect to /auth/login as configured in Application.php

        $identity = $this->Authentication->getIdentity();
        
        // Role-Based Access Control Foundation
        if ($identity) {
            $session = $this->request->getSession();
            
            // Check if user role is not set in session yet for current context
            if (!$session->check('User.role')) {
                // Future Sprints: Logic to check if user is a host of any session or participant.
                // For now, set a foundational default role
                $session->write('User.role', 'registered');
            }
        } else {
            // Guest access flow (like joining via QR) will bypass Authentication using allowUnauthenticated()
            // in their specific controllers in future sprints.
        }
    }
}
