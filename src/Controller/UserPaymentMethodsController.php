<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\CloudinaryService;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;

class UserPaymentMethodsController extends AppController
{
    private CloudinaryService $cloudinary;

    public function beforeFilter(\Cake\Event\EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->cloudinary = new CloudinaryService();

        if ($this->components()->has('Authorization')) {
            $this->Authorization->skipAuthorization();
        }
    }

    /**
     * List all payment methods for the logged-in user.
     */
    public function index()
    {
        $user = $this->Authentication->getIdentity();
        $methods = $this->fetchTable('UserPaymentMethods')
            ->find()
            ->where(['user_id' => $user->id])
            ->orderBy(['created_at' => 'ASC'])
            ->all();

        $this->set(compact('methods', 'user'));
    }

    /**
     * Add a new payment method.
     */
    public function add()
    {
        $user = $this->Authentication->getIdentity();
        $table = $this->fetchTable('UserPaymentMethods');

        // Enforce max 5 methods per user
        if ($table->countForUser((int)$user->id) >= 5) {
            $this->Flash->error('You can save up to 5 payment methods. Please delete one before adding a new one.');
            return $this->redirect(['action' => 'index']);
        }

        $method = $table->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $user->id;

            // Handle QR image upload
            if ($data['method_type'] === 'duitnow_qr') {
                $qrFile = $this->request->getUploadedFile('qr_image');
                if ($qrFile && $qrFile->getError() === UPLOAD_ERR_OK) {
                    try {
                        $tmpPath = $qrFile->getStream()->getMetadata('uri');
                        $folder  = 'equisplit/payment_qr/user_' . $user->id;
                        [$url, $publicId] = $this->cloudinary->uploadPrivate($tmpPath, $folder);
                        $data['qr_image_url'] = $url;
                        $data['qr_public_id'] = $publicId;
                    } catch (\Exception $e) {
                        $this->Flash->error('QR upload failed: ' . $e->getMessage());
                        $this->set(compact('method'));
                        return;
                    }
                } else {
                    $this->Flash->error('Please upload a valid QR code image.');
                    $this->set(compact('method'));
                    return;
                }
            }

            $method = $table->patchEntity($method, $data);
            if ($table->save($method)) {
                $this->Flash->success('Payment method saved successfully!');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not save. Please check the form and try again.');
        }

        $this->set(compact('method'));
    }

    /**
     * Edit an existing payment method (label / account details only, not method type).
     */
    public function edit(int $id)
    {
        $user   = $this->Authentication->getIdentity();
        $method = $this->fetchTable('UserPaymentMethods')->get($id);

        // Ownership check
        if ((int)$method->user_id !== (int)$user->id) {
            throw new ForbiddenException('You do not own this payment method.');
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            unset($data['method_type']); // method_type is immutable after creation

            // Handle QR re-upload
            if ($method->method_type === 'duitnow_qr') {
                $qrFile = $this->request->getUploadedFile('qr_image');
                if ($qrFile && $qrFile->getError() === UPLOAD_ERR_OK) {
                    // Delete old QR from Cloudinary
                    if ($method->qr_public_id) {
                        $this->cloudinary->destroy($method->qr_public_id);
                    }
                    $tmpPath = $qrFile->getStream()->getMetadata('uri');
                    $folder  = 'equisplit/payment_qr/user_' . $user->id;
                    [$url, $publicId] = $this->cloudinary->uploadPrivate($tmpPath, $folder);
                    $data['qr_image_url'] = $url;
                    $data['qr_public_id'] = $publicId;
                }
            }

            $method = $this->fetchTable('UserPaymentMethods')->patchEntity($method, $data);
            if ($this->fetchTable('UserPaymentMethods')->save($method)) {
                $this->Flash->success('Payment method updated.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not update. Please try again.');
        }

        $this->set(compact('method'));
        $this->render('add');
    }

    /**
     * Delete a payment method (and its Cloudinary asset if QR).
     */
    public function delete(int $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user   = $this->Authentication->getIdentity();
        $table  = $this->fetchTable('UserPaymentMethods');
        $method = $table->get($id);

        if ((int)$method->user_id !== (int)$user->id) {
            throw new ForbiddenException('You do not own this payment method.');
        }

        // Delete QR from Cloudinary first
        if ($method->method_type === 'duitnow_qr' && $method->qr_public_id) {
            try {
                $this->cloudinary->destroy($method->qr_public_id);
            } catch (\Exception $e) {
                // Log but don't block deletion
            }
        }

        if ($table->delete($method)) {
            $this->Flash->success('Payment method deleted.');
        } else {
            $this->Flash->error('Could not delete. Please try again.');
        }

        return $this->redirect(['action' => 'index']);
    }
}
