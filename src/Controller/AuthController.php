<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\GoogleOAuthService;
use Cake\Event\EventInterface;
use Cake\Routing\Router;

class AuthController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        
        $this->Authentication->allowUnauthenticated(['login', 'google', 'callback']);
        
        if ($this->components()->has('Authorization')) {
            $this->Authorization->skipAuthorization();
        }
    }

    public function login()
    {
        // If already authenticated, go to dashboard
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }
        // Otherwise render the login page (GET only)
    }

    public function google()
    {
        // Initiate Google OAuth redirect (GET request — no form POST needed)
        $googleOAuth = new GoogleOAuthService();
        return $this->redirect($googleOAuth->getAuthorizationUrl());
    }

    public function callback()
    {
        $code = $this->request->getQuery('code');
        if (!$code) {
            $this->Flash->error('Invalid Google OAuth response.');
            return $this->redirect(['action' => 'login']);
        }

        try {
            $googleOAuth = new GoogleOAuthService();
            $token = $googleOAuth->getAccessToken((string)$code);
            $googleUser = $googleOAuth->getResourceOwner($token);

            $usersTable = $this->fetchTable('Users');
            // Fixed find method syntax with named argument for CakePHP 5.x
            $user = $usersTable->find('byGoogleId', googleId: $googleUser->getId())->first();

            if ($user) {
                $user->name = $googleUser->getName();
                $user->avatar_url = $googleUser->getAvatar();
                $usersTable->save($user);
            } else {
                $user = $usersTable->newEmptyEntity();
                $user->google_id = $googleUser->getId();
                $user->email = $googleUser->getEmail();
                $user->name = $googleUser->getName();
                $user->avatar_url = $googleUser->getAvatar();
                $user->created_at = date('Y-m-d H:i:s');
                
                if (!$usersTable->save($user)) {
                    $this->Flash->error('Could not create your account.');
                    return $this->redirect(['action' => 'login']);
                }
            }

            $this->Authentication->setIdentity($user);
            $this->request->getSession()->write('User.role', 'registered');

            $this->Flash->success('Successfully logged in with Google.');
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);

        } catch (\Throwable $e) {
            \Cake\Log\Log::error('OAuth callback failed: ' . get_class($e) . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            $this->Flash->error('Authentication failed. Please try again.');
            return $this->redirect(['action' => 'login']);
        }
    }

    public function logout()
    {
        $this->Authentication->logout();
        $this->request->getSession()->destroy();
        $this->Flash->success('You have been successfully logged out.');
        return $this->redirect(['action' => 'login']);
    }
}
