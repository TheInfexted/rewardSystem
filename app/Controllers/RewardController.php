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
        
        // Debug logging
        log_message('info', 'RewardController index - Winner data: ' . json_encode($winnerData));
        log_message('info', 'RewardController index - Session data: ' . json_encode([
            'customer_id' => $session->get('customer_id'),
            'customer_logged_in' => $session->get('customer_logged_in'),
            'session_id' => session_id()
        ]));
        
        // Check for customer login - check both customer_id and customer_logged_in
        $customerId = $session->get('customer_id');
        $customerLoggedIn = $session->get('customer_logged_in');
        $customerData = null;
        
        if ($customerId && $customerLoggedIn) {
            $customerData = $this->customerModel->find($customerId);
            if (!$customerData) {
                // Customer not found, clear session
                $session->remove(['customer_id', 'customer_logged_in', 'customer_data']);
                $customerLoggedIn = false;
            }
        }
        
        $data = [
            'title' => 'Claim Your Reward',
            'winner_data' => $winnerData,
            'logged_in' => $customerLoggedIn && !empty($customerData),
            'customer_data' => $customerData,
            'has_prize_data' => !empty($winnerData) && is_array($winnerData) && isset($winnerData['name']),
            'show_dashboard_access' => true // Always allow dashboard access
        ];

        return view('public/reward_system', $data);
    }

    /**
     * Auto register new customer
     */
    public function autoRegister()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $session = session();

        try {
            // Create auto customer
            $result = $this->customerModel->createAutoCustomer();
            
            if ($result) {
                // Set session data for the new customer
                $session->set([
                    'customer_id' => $result['id'],
                    'customer_logged_in' => true,
                    'customer_data' => [
                        'id' => $result['id'],
                        'username' => $result['username'],
                        'name' => $result['username'],
                        'phone' => $result['username'],
                        'points' => 0  // New customers start with 0 points
                    ]
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Account created successfully!',
                    'customer_data' => [
                        'username' => $result['username'],
                        'password' => $result['password'],
                        'id' => $result['id']
                    ],
                    'redirect' => base_url('customer/dashboard') 
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create account. Please try again.'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Auto registration error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ]);
        }
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
                    'customer_logged_in' => true,
                    'customer_data' => [
                        'id' => $customer['id'],
                        'username' => $customer['username'],
                        'name' => $customer['name'] ?? $customer['username'],
                        'phone' => $customer['phone'] ?? $customer['username'],
                        'points' => $customer['points'] ?? 0,
                    ]
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => base_url('customer/dashboard'),
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid username or password.'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Login failed. Please try again.'
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

        // Validate customer data
        if (!$customerData || !isset($customerData['id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid customer session. Please login again.'
            ]);
        }

        try {
            // Insert bonus claim record
            $claimData = [
                'session_id' => session_id(),
                'user_ip' => $this->request->getIPAddress(),
                'user_name' => $customerData['username'],
                'customer_id' => $customerData['id'],
                'phone_number' => $customerData['phone'] ?? $customerData['username'],
                'email' => null,
                'bonus_type' => $winnerData['name'],
                'bonus_amount' => isset($winnerData['prize']) ? floatval($winnerData['prize']) : 0.00,
                'claim_time' => date('Y-m-d H:i:s'),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'status' => 'pending',
                'platform_selected' => $platform,
                'account_type' => 'manual'
            ];

            $claimId = $this->bonusClaimModel->insert($claimData);

            if ($claimId) {
                // Clear winner data from session after successful claim
                $session->remove('winner_data');

                // Get platform settings
                $whatsappNumber = $this->adminSettingsModel->getSetting('reward_whatsapp_number', '60102763672');
                $telegramUsername = $this->adminSettingsModel->getSetting('reward_telegram_username', 'brendxn1127');

                // Create message for platform
                $message = "🎉 I just won {$winnerData['name']}!\nMy User ID is: {$customerData['username']}.\nClaim ID: {$claimId}";
                $encodedMessage = urlencode($message);
                
                // Platform URLs
                $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
                $telegramUrl = "https://t.me/{$telegramUsername}?text={$encodedMessage}";
                
                $redirectUrl = $platform === 'telegram' ? $telegramUrl : $whatsappUrl;
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Claim submitted successfully! Redirecting to ' . ucfirst($platform) . '...',
                    'redirect_url' => $redirectUrl,
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
        
        // Clear customer session data but keep winner_data for potential re-login
        $session->remove([
            'customer_id',
            'customer_data', 
            'customer_logged_in'
        ]);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Logged out successfully.'
            ]);
        }
        
        return redirect()->to('/reward')->with('success', 'Logged out successfully.');
    }

    /**
     * Test method to debug session data
     */
    public function test()
    {
        $session = session();
        
        $data = [
            'session_id' => session_id(),
            'winner_data' => $session->get('winner_data'),
            'customer_id' => $session->get('customer_id'),
            'customer_logged_in' => $session->get('customer_logged_in'),
            'customer_data' => $session->get('customer_data'),
            'all_session_data' => $session->get()
        ];
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($data);
        }
        
        echo '<pre>' . print_r($data, true) . '</pre>';
    }
}