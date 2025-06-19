<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\BonusClaimModel;
use App\Models\AdminSettingsModel;

class RewardController extends BaseController
{
    protected $customerModel;
    protected $bonusClaimModel;
    protected $adminSettingsModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->bonusClaimModel = new BonusClaimModel();
        $this->adminSettingsModel = new AdminSettingsModel();
    }

    /**
     * Display reward system page
     */
    public function index()
    {
        $session = session();
        
        // Check if user won a prize (from session)
        $winnerData = $session->get('winner_data');
        
        // Check for customer login
        $customerId = $session->get('customer_id');
        $customerData = null;
        
        if ($customerId) {
            $customerData = $this->customerModel->find($customerId);
        }
        
        $data = [
            'title' => 'Claim Your Reward',
            'winner_data' => $winnerData,
            'logged_in' => !empty($customerData),
            'customer_data' => $customerData,
            'no_prize_data' => empty($winnerData)
        ];

        return view('public/reward_system', $data);
    }

    /**
     * Customer login for existing users
     */
    public function login()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[4]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please fill in all required fields correctly.',
                'errors' => $validation->getErrors()
            ]);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        try {
            // Find customer by username
            $customer = $this->customerModel->where('username', $username)
                                          ->where('is_active', 1)
                                          ->first();
            
            if ($customer && password_verify($password, $customer['password'])) {
                // Set session data
                session()->set([
                    'customer_id' => $customer['id'],
                    'customer_data' => [
                        'id' => $customer['id'],
                        'username' => $customer['username'],
                        'name' => $customer['name'] ?? $customer['username'],
                        'points' => $customer['points']
                    ],
                    'customer_logged_in' => true
                ]);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Login successful! Welcome back, ' . $customer['username'] . '!',
                    'customer_data' => [
                        'username' => $customer['username'],
                        'points' => $customer['points']
                    ]
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid username or password. Please try again.'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Customer login error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Login failed. Please try again later.'
            ]);
        }
    }

    /**
     * Auto-generate and register new customer
     */
    public function autoRegister()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/');
        }

        try {
            // Use the model's auto creation method
            $result = $this->customerModel->createAutoCustomer();
            
            if ($result) {
                // Set session data
                session()->set([
                    'customer_id' => $result['id'],
                    'customer_data' => [
                        'id' => $result['id'],
                        'username' => $result['username'],
                        'name' => $result['username'],
                        'points' => 0
                    ],
                    'customer_logged_in' => true
                ]);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Account created successfully!',
                    'account_data' => [
                        'username' => $result['username'],
                        'password' => $result['password']
                    ]
                ]);
            }
            
            throw new \Exception('Failed to create customer account');
            
        } catch (\Exception $e) {
            log_message('error', 'Auto registration error: ' . $e->getMessage());
            
            // Get detailed error from model if available
            $errors = $this->customerModel->errors();
            $errorMessage = 'Registration failed. Please try again.';
            
            if (!empty($errors)) {
                $errorMessage = 'Registration failed: ' . implode(', ', $errors);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    /**
     * Claim reward after authentication
     */
    public function claimReward()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $session = session();
        
        // Check if customer is logged in
        if (!$session->get('customer_logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first to claim your reward.'
            ]);
        }

        // Check winner data
        $winnerData = $session->get('winner_data');
        if (!$winnerData) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No prize data found. Please spin the wheel first.'
            ]);
        }

        $platform = $this->request->getPost('platform') ?: 'whatsapp';
        $customerData = $session->get('customer_data');

        try {
            // Insert bonus claim record
            $claimData = [
                'session_id' => $session->get('session_id') ?: session_id(),
                'user_ip' => $this->request->getIPAddress(),
                'user_name' => $customerData['username'],
                'customer_id' => $customerData['id'],
                'phone_number' => $customerData['username'], // Using username as phone
                'email' => null,
                'bonus_type' => $winnerData['name'],
                'bonus_amount' => $winnerData['prize'] ?? 0.00,
                'claim_time' => date('Y-m-d H:i:s'),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'status' => 'pending',
                'platform_selected' => $platform
            ];

            $claimId = $this->bonusClaimModel->insert($claimData);

            if ($claimId) {
                // Clear winner data from session
                $session->remove('winner_data');

                // Create message for platform
                $message = "🎉 I just won {$winnerData['name']}! My User ID is: {$customerData['username']}. Claim ID: {$claimId}";
                $encodedMessage = urlencode($message);
                
                // Platform URLs (these should be configurable in admin settings)
                $whatsappUrl = "https://wa.me/60102763672?text={$encodedMessage}";
                $telegramUrl = "https://t.me/brendxn1127?text={$encodedMessage}";
                
                $redirectUrl = $platform === 'telegram' ? $telegramUrl : $whatsappUrl;
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Claim submitted successfully! Redirecting to ' . ucfirst($platform) . '...',
                    'redirect_url' => $redirectUrl,
                    'dashboard_url' => base_url('customer/dashboard'),
                    'claim_id' => $claimId
                ]);
            }
            
            throw new \Exception('Failed to process claim');
            
        } catch (\Exception $e) {
            log_message('error', 'Claim reward error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to process claim. Please try again.'
            ]);
        }
    }

    /**
     * Customer logout
     */
    public function logout()
    {
        $session = session();
        
        // Clear customer session data
        $session->remove([
            'customer_id',
            'customer_data', 
            'customer_logged_in',
            'winner_data'
        ]);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Logged out successfully.'
            ]);
        }
        
        return redirect()->to('/')->with('success', 'Logged out successfully.');
    }
}